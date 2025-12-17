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
            'accent_color', 
            'layout_density', 
            'sidebar_mode',
            'locale', 
            'date_format', 
            'timezone',
            // Checkbox keys (boolean)
            'notify_grades', 
            'notify_attendance', 
            'notify_announcements'
        ];

        foreach ($allowedKeys as $key) {
            // Logika khusus untuk Checkbox:
            // Jika checkbox tidak dicentang, form HTML tidak mengirim key tersebut.
            // Kita harus cek manual.
            if (in_array($key, ['notify_grades', 'notify_attendance', 'notify_announcements'])) {
                $value = $request->has($key) ? '1' : '0';
            } else {
                // Untuk input biasa (select/radio/text), ambil valuenya
                // Jika tidak ada di request, skip saja
                if (!$request->has($key)) continue;
                $value = $request->input($key);
            }

            // Simpan atau Update
            UserPreference::updateOrCreate(
                ['user_id' => $user->id, 'key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Preferensi aplikasi berhasil diperbarui!');
    }
}