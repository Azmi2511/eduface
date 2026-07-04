<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPreference;

class PreferencesController extends Controller
{
    public function index()
    {
        return view('settings.preferences');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Daftar semua key yang diizinkan untuk disimpan
        $allowedKeys = [
            'theme', 
            'locale', 
        ];

        foreach ($allowedKeys as $key) {
            if (!$request->has($key)) continue;
            $value = $request->input($key);

            // Simpan atau Update
            UserPreference::updateOrCreate(
                ['user_id' => $user->id, 'key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Preferensi aplikasi berhasil diperbarui!');
    }
}