<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    public function show()
    {
        $classroom = DB::table('classes')->select('id', 'class_name')->orderBy('class_name')->get();
        
        $parents = DB::table('parents')
            ->join('users', 'parents.user_id', '=', 'users.id')
            ->select('parents.id', 'users.full_name', 'users.phone', 'users.email')
            ->orderBy('users.full_name')
            ->get();

        return view('auth.register', compact('classroom', 'parents'));
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:student,teacher,parent',
            'gender'    => 'required|in:L,P',
            'phone'     => 'nullable|string|max:50',
            'dob'       => 'nullable|date',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_number' => 'nullable|string',
            'class_id'  => 'required_if:role,student',
            'parent_id' => 'nullable|exists:parents,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        if ($request->role == 'student' && Student::where('nisn', $request->id_number)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'NISN sudah terdaftar.'], 422);
        } elseif ($request->role == 'teacher' && Teacher::where('nip', $request->id_number)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'NIP sudah terdaftar.'], 422);
        }

        $email = $request->email;

        // Resend protection
        $resendKey = 'otp_resend_' . $email;
        $resendTtl = config('otp.resend_ttl', 60);
        if (Cache::has($resendKey)) {
            return response()->json(['status' => 'error', 'message' => 'Tunggu sebelum mengirim ulang kode OTP.'], 429);
        }

        $otp = rand(100000, 999999);
        Cache::put('otp_' . $email, $otp, config('otp.otp_ttl', 300));
        Cache::put($resendKey, true, $resendTtl);
        // reset attempt counter on new send
        Cache::put('otp_attempts_' . $email, 0, config('otp.lock_ttl', 900));

        try {
            Mail::to($request->email)->send(new OtpMail($otp, 'registration'));
            return response()->json(['status' => 'success', 'message' => 'Kode OTP telah dikirim ke email Anda.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }

    public function verifyAndCreate(Request $request)
    {
        $email = $request->email;

        // check lock
        $lockKey = 'otp_lock_' . $email;
        if (Cache::has($lockKey)) {
            return response()->json(['status' => 'error', 'message' => 'Terlalu banyak percobaan. Coba lagi nanti.'], 423);
        }

        $cachedOtp = Cache::get('otp_' . $email);

        if (!$cachedOtp || $cachedOtp != $request->otp_code) {
            $attemptKey = 'otp_attempts_' . $email;
            $attempts = Cache::get($attemptKey, 0) + 1;
            Cache::put($attemptKey, $attempts, config('otp.lock_ttl', 900));

            if ($attempts >= config('otp.max_attempts', 3)) {
                Cache::put($lockKey, true, config('otp.lock_ttl', 900));
                return response()->json(['status' => 'error', 'message' => 'Akun terkunci sementara karena terlalu banyak percobaan.'], 423);
            }

            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah atau kedaluwarsa.'], 400);
        }

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('profile_pictures', 'public');
            }

            $generatedUsername = $request->username ?? explode('@', $request->email)[0] . rand(100, 999);
            
            while(User::where('username', $generatedUsername)->exists()) {
                $generatedUsername = explode('@', $request->email)[0] . rand(1000, 9999);
            }

            $user = User::create([
                'full_name'       => $request->name,
                'email'           => $request->email,
                'password'        => Hash::make($request->password),
                'role'            => $request->role,
                'username'        => $generatedUsername,
                'phone'           => $request->phone,
                'dob'             => $request->dob,
                'gender'          => $request->gender,
                'profile_picture' => $photoPath,
                'is_active'       => 1
            ]);

            if ($request->role == 'student') {
                Student::create([
                    'user_id'   => $user->id, 
                    'nisn'      => $request->id_number,
                    'class_id'  => $request->class_id,
                    'parent_id' => $request->parent_id
                ]);
            } elseif ($request->role == 'teacher') {
                Teacher::create(['user_id' => $user->id, 'nip' => $request->id_number]);
            } elseif ($request->role == 'parent') {
                ParentProfile::create(['user_id' => $user->id]);
            }

            Cache::forget('otp_' . $request->email);
            // clear attempt counters and locks on successful verification
            Cache::forget('otp_attempts_' . $request->email);
            Cache::forget('otp_lock_' . $request->email);
            Cache::forget('otp_resend_' . $request->email);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi Berhasil!',
                'username' => $generatedUsername,
                'redirect' => route('login.perform')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}