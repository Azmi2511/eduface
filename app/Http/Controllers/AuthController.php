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
}
