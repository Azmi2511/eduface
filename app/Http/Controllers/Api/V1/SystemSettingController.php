<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    /**
     * Get System Settings
     * Endpoint ini digunakan oleh Android Service untuk cek status 'upload_file_enabled'
     */
    public function index()
    {
        // Ambil settingan pertama atau default
        $settings = SystemSetting::firstOrNew(['id' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'System settings retrieved successfully.',
            'data'    => $settings
        ], 200);
    }

    /**
     * Update General Information
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_name' => 'required|string|max:255',
            'npsn'        => 'nullable|string|max:20',
            'address'     => 'nullable|string',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $settings = SystemSetting::firstOrNew(['id' => 1]);
        $settings->fill($request->only(['school_name', 'npsn', 'address', 'email', 'phone']));
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'General information updated.',
            'data'    => $settings
        ]);
    }

    /**
     * Update Attendance Rules
     * Menangani logika update jam dan sinkronisasi log harian
     */
    public function updateAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entry_time'        => 'required|date_format:H:i', // Atau H:i:s jika perlu
            'late_limit'        => 'required|date_format:H:i',
            'exit_time'         => 'required|date_format:H:i',
            'tolerance_minutes' => 'required|integer|min:0',
            'face_rec_enabled'  => 'boolean', // Menerima true/false/1/0
            'upload_file_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $settings = SystemSetting::firstOrNew(['id' => 1]);

            // Update data setting
            $settings->entry_time        = $request->entry_time;
            $settings->late_limit        = $request->late_limit;
            $settings->exit_time         = $request->exit_time;
            $settings->tolerance_minutes = $request->tolerance_minutes;
            
            // Menggunakan boolean() helper agar aman menerima json true/false atau 1/0
            $settings->face_rec_enabled    = $request->boolean('face_rec_enabled');
            $settings->upload_file_enabled = $request->boolean('upload_file_enabled');
            
            $settings->save();

            // Logika Sinkronisasi Log Hari Ini (Sama dengan controller web)
            $today = now()->toDateString();
            
            // Reset status log hari ini sesuai aturan baru
            DB::table('attendance_logs')
                ->where('date', $today)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->update([
                    'status' => DB::raw("CASE 
                        WHEN time_log > '{$request->late_limit}' THEN 'Terlambat' 
                        ELSE 'Hadir' 
                    END")
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance parameters updated and logs synchronized.',
                'data'    => $settings
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance settings.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Notification Preferences
     */
    public function updateNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notif_late'   => 'boolean',
            'notif_absent' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $settings = SystemSetting::firstOrNew(['id' => 1]);
        $settings->notif_late   = $request->boolean('notif_late');
        $settings->notif_absent = $request->boolean('notif_absent');
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated.',
            'data'    => $settings
        ]);
    }
}