<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Handle user login and token creation.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required'
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Username/Email atau password salah.'
            ], 401);
        }

        $user = User::where($loginType, $request->login)->firstOrFail();

        $user->load(['student.schoolClass', 'student.user', 'teacher', 'parentProfile']);

        if ($user->is_active == 0) {
            return response()->json(['message' => 'Akun anda tidak aktif.'], 403);
        }

        $token = $user->createToken('Mobile Device')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null 
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}