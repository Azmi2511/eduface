<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Services\JwtService;

class AuthController extends Controller
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            session(['user_id' => $user->id, 'full_name' => $user->full_name, 'role' => $user->role]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['username' => 'Credentials not valid']);
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login.show');
    }

    public function apiLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credentials not valid'], 401);
        }

        // Generate JWT token
        $payload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'email' => $user->email
        ];

        $token = $this->jwtService->generate($payload);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 200);
    }

    public function apiLogout(Request $request)
    {
        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function validateRegistration(Request $request)
    {
        // 1. Mapping Role Frontend ke Database Enum
        $roleMap = [
            'GURU' => 'teacher',
            'SISWA' => 'student',
            'ORTU' => 'parent'
        ];

        $request->validate([
            'role' => 'required|in:GURU,SISWA,ORTU',
            'id_number' => 'required', // NIP atau NISN
            'dob' => 'required|date',
        ]);

        $dbRole = $roleMap[$request->role];
        $id = $request->id_number;
        $dob = $request->dob;

        $masterData = null;
        $phoneNumber = null;
        $existingUserId = null;

        // 2. Logika Pengecekan Database 'atlas'
        if ($dbRole === 'teacher') {
            // Cek tabel teachers
            $teacher = Teacher::where('nip', $id)->where('dob', $dob)->first();
            
            if ($teacher) {
                $masterData = $teacher;
                $phoneNumber = $teacher->phone_number;
                $existingUserId = $teacher->user_id; // Cek apakah sudah punya akun
            }

        } elseif ($dbRole === 'student') {
            // Cek tabel students
            $student = Student::with('parentData')->where('nisn', $id)->where('dob', $dob)->first();
            
            if ($student) {
                $masterData = $student;
                $existingUserId = $student->user_id;
                
                // ISSUE: Tabel students tidak punya kolom HP.
                // Opsi A: Ambil dari parent (jika ada relasi)
                if ($student->parentData) {
                    $phoneNumber = $student->parentData->phone_number;
                } else {
                    // Opsi B: Fail atau gunakan email
                    return response()->json(['message' => 'Data kontak siswa tidak ditemukan di database.'], 404);
                }
            }

        } elseif ($dbRole === 'parent') {
            // KHUSUS ORTU: Input NISN Anak, tapi yang dicek tabel parents
            // Cari Siswa dulu
            $student = Student::where('nisn', $id)->where('dob', $dob)->first();

            if (!$student) {
                return response()->json(['message' => 'Data Siswa (Anak) tidak ditemukan.'], 404);
            }

            if (!$student->parent_id) {
                return response()->json(['message' => 'Data Orang Tua belum terhubung dengan Siswa ini. Hubungi Admin.'], 404);
            }

            // Ambil data Parent berdasarkan parent_id di table students
            $parent = SchoolParent::find($student->parent_id);
            
            if ($parent) {
                $masterData = $parent;
                $phoneNumber = $parent->phone_number;
                $existingUserId = $parent->user_id;
            }
        }

        // 3. Validasi Error
        if (!$masterData) {
            return response()->json(['message' => 'Data tidak ditemukan. Cek NIP/NISN dan Tanggal Lahir.'], 404);
        }

        if ($existingUserId) {
            return response()->json(['message' => 'Akun sudah terdaftar. Silakan Login.'], 400);
        }

        if (empty($phoneNumber)) {
            return response()->json(['message' => 'Nomor HP belum terdata di sistem. Hubungi Tata Usaha.'], 400);
        }

        // 4. Proses OTP (Sama seperti sebelumnya)
        $maskedPhone = preg_replace('/(\d{4})\d{4}(\d{4})/', '$1-****-$2', $phoneNumber);
        
        // Simpan sesi validasi sementara (Cache)
        $tempToken = \Str::random(40);
        Cache::put('reg_sess_'.$tempToken, [
            'role' => $dbRole,
            'id_number' => $id, // Simpan ID asli (NIP/NISN)
            'master_id' => ($dbRole === 'parent') ? $masterData->id : $id, // ID untuk update tabel master nanti
            'phone' => $phoneNumber
        ], 600); // 10 menit

        // Generate OTP (Mock)
        $otp = rand(1000, 9999);
        Cache::put('otp_'.$phoneNumber, $otp, 300);

        return response()->json([
            'status' => 'success',
            'masked_phone' => $maskedPhone,
            'temp_token' => $tempToken,
            'debug_otp' => $otp 
        ]);
    }

    // --- KARTU 4: Finalisasi (Create User di Tabel `users`) ---
    public function registerFinal(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8',
            'temp_token' => 'required',
            'otp' => 'required'
        ]);

        // Ambil data dari Cache session
        $sessionData = Cache::get('reg_sess_'.$request->temp_token);
        if (!$sessionData) {
            return response()->json(['message' => 'Sesi habis. Silakan ulangi dari awal.'], 401);
        }

        // Verifikasi OTP
        $cachedOtp = Cache::get('otp_'.$sessionData['phone']);
        if ($request->otp != $cachedOtp) {
            return response()->json(['message' => 'Kode OTP Salah.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Create User di tabel `users`
            // Kolom: username, password, full_name, role, is_active
            $newUser = User::create([
                'username' => $sessionData['id_number'], // Gunakan NIP/NISN sebagai username
                'password' => Hash::make($request->password),
                'role'     => $sessionData['role'], // enum: teacher, student, parent
                'is_active'=> 1, // Default active
                // Full name nanti bisa diambil dari master data untuk update profile
            ]);

            // 2. Update Foreign Key di Tabel Master
            $masterId = $sessionData['master_id'];
            $role = $sessionData['role'];

            if ($role === 'teacher') {
                Teacher::where('nip', $masterId)->update(['user_id' => $newUser->id]);
            } elseif ($role === 'student') {
                Student::where('nisn', $masterId)->update(['user_id' => $newUser->id]);
            } elseif ($role === 'parent') {
                SchoolParent::where('id', $masterId)->update(['user_id' => $newUser->id]);
            }

            DB::commit();
            
            // Hapus cache
            Cache::forget('reg_sess_'.$request->temp_token);
            Cache::forget('otp_'.$sessionData['phone']);

            return response()->json(['status' => 'success', 'redirect' => '/login']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat akun: ' . $e->getMessage()], 500);
        }
    }

    public function checkOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'temp_token' => 'required'
        ]);

        // Ambil data session
        $sessionData = Cache::get('reg_sess_'.$request->temp_token);
        if (!$sessionData) {
            return response()->json(['message' => 'Sesi kadaluarsa.'], 401);
        }

        // Cek kecocokan OTP
        $cachedOtp = Cache::get('otp_'.$sessionData['phone']);
        
        if ($request->otp == $cachedOtp) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['message' => 'Kode OTP Salah.'], 400);
        }
    }
}
