<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
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
            session(['user_id' => $user->id, 'full_name' => $user->username, 'role' => $user->role]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['username' => 'Credentials not valid']);
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login.show');
    }
}
