<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot');
    }

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        $resendKey = 'otp_resend_' . $email;
        $resendTtl = config('otp.resend_ttl', 60);
        if (Cache::has($resendKey)) {
            return response()->json(['status' => 'error', 'message' => 'Tunggu sebelum mengirim ulang kode OTP.'], 429);
        }

        $otp = rand(100000, 999999);
        Cache::put('otp_' . $email, $otp, config('otp.otp_ttl', 300));
        Cache::put($resendKey, true, $resendTtl);
        Cache::put('otp_attempts_' . $email, 0, config('otp.lock_ttl', 900));

        try {
            Mail::to($email)->send(new OtpMail($otp, 'reset'));
            return response()->json(['status' => 'success', 'message' => 'Kode OTP telah dikirim ke email Anda.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        $lockKey = 'otp_lock_' . $email;
        if (Cache::has($lockKey)) {
            return response()->json(['status' => 'error', 'message' => 'Terlalu banyak percobaan. Coba lagi nanti.'], 423);
        }

        $cachedOtp = Cache::get('otp_' . $email);
        if (!$cachedOtp || $cachedOtp != $request->code) {
            $attemptKey = 'otp_attempts_' . $email;
            $attempts = Cache::get($attemptKey, 0) + 1;
            Cache::put($attemptKey, $attempts, config('otp.lock_ttl', 900));

            if ($attempts >= config('otp.max_attempts', 3)) {
                Cache::put($lockKey, true, config('otp.lock_ttl', 900));
                return response()->json(['status' => 'error', 'message' => 'Terlalu banyak percobaan. Akun terkunci sementara.'], 423);
            }

            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah atau kedaluwarsa.'], 400);
        }

        // allow reset for a short window
        Cache::put('password_reset_allowed_' . $email, true, config('otp.otp_ttl', 300));
        // clear the otp so it cannot be reused
        Cache::forget('otp_' . $email);
        Cache::forget('otp_resend_' . $email);

        return response()->json(['status' => 'success', 'message' => 'OTP terverifikasi. Silakan buat password baru.']);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        if (!Cache::has('password_reset_allowed_' . $email)) {
            return response()->json(['status' => 'error', 'message' => 'Permintaan tidak sah atau sudah kedaluwarsa.'], 403);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // clean up
        Cache::forget('password_reset_allowed_' . $email);
        Cache::forget('otp_attempts_' . $email);
        Cache::forget('otp_lock_' . $email);
        Cache::forget('otp_resend_' . $email);

        return response()->json(['status' => 'success', 'message' => 'Password berhasil direset.']);
    }
}
