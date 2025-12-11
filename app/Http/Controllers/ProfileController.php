<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profil.
     */
    private function getAuthenticatedUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $userId = session('id') ?? session('user_id'); 

        if ($userId) {
            return User::find($userId);
        }

        return null;
    }
    
    public function edit()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir, silakan login kembali.');
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update informasi umum user.
     */
    public function update(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            // 'phone'  => 'nullable|string|max:20', 
        ]);

        $user->update([
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
        ]);
        
        session(['full_name' => $validated['full_name']]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password user.
     */
    public function updatePassword(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}