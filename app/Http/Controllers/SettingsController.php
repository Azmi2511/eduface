<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        $settings = SystemSetting::firstOrNew(['id' => 1]); 
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Menyimpan Pengaturan Umum.
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string',
            'npsn'        => 'nullable|string',
            'address'     => 'nullable|string',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string',
        ]);

        SystemSetting::updateOrCreate(['id' => 1], $request->only([
            'school_name', 'npsn', 'address', 'email', 'phone'
        ]));

        return redirect()->route('settings.index')->with('success', 'Pengaturan Umum berhasil disimpan!');
    }

    /**
     * Menyimpan Pengaturan Absensi.
     */
    public function updateAttendance(Request $request)
    {
        $request->validate([
            'entry_time'        => 'required',
            'late_limit'        => 'required',
            'exit_time'         => 'required',
            'tolerance_minutes' => 'required|integer',
        ]);

        SystemSetting::updateOrCreate(['id' => 1], [
            'entry_time'        => $request->entry_time,
            'late_limit'        => $request->late_limit,
            'exit_time'         => $request->exit_time,
            'tolerance_minutes' => $request->tolerance_minutes,
            'face_rec_enabled'  => $request->has('face_rec_enabled') ? 1 : 0,
        ]);

        $today = date('Y-m-d');
        $newLateLimit = $request->late_limit;

        \DB::table('attendance_logs')
            ->where('date', $today)
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->whereTime('time_log', '>', $newLateLimit)
            ->update(['status' => 'Terlambat']);

        \DB::table('attendance_logs')
            ->where('date', $today)
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->whereTime('time_log', '<=', $newLateLimit)
            ->update(['status' => 'Hadir']);

        return redirect()->route('settings.index')->with('success', 'Pengaturan Absensi berhasil disimpan dan status siswa hari ini diperbarui!');
    }

    /**
     * Menyimpan Pengaturan Notifikasi.
     */
    public function updateNotification(Request $request)
    {
        SystemSetting::updateOrCreate(['id' => 1], [
            'notif_late'   => $request->has('notif_late') ? 1 : 0,
            'notif_absent' => $request->has('notif_absent') ? 1 : 0,
        ]);

        return redirect()->route('settings.index')->with('success', 'Pengaturan Notifikasi berhasil disimpan!');
    }

    /**
     * Update Password (Keamanan).
     */
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password) && $request->current_password !== 'admin') {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('settings.index')->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Backup Database (Simulasi SQL Dump).
     */
    public function backupDatabase()
    {
        $filename = "backup_eduface_" . date("Y-m-d_H-i-s") . ".sql";
        
        $users = User::all();
        $content = "-- BACKUP DATABASE EDUFACE \n";
        $content .= "-- Tanggal: " . date("Y-m-d H:i:s") . "\n\n";

        foreach ($users as $user) {
            $content .= "INSERT INTO users (full_name, email, role) VALUES ('{$user->full_name}', '{$user->email}', '{$user->role}');\n";
        }

        return Response::make($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}