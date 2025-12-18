<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Http\Resources\Api\V1\SystemSettingResource;
use App\Http\Requests\SystemSettingRequest; // Menggunakan request yang Anda buat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemSettingController extends Controller
{
    /**
     * Ambil pengaturan sistem (Public/Authenticated)
     * Digunakan oleh perangkat IoT atau Aplikasi Mobile
     */
    public function index()
    {
        $settings = SystemSetting::firstOrCreate(['id' => 1], [
            'school_name' => 'Eduface School'
        ]);
        
        return new SystemSettingResource($settings);
    }

    /**
     * Update Pengaturan (Admin Only)
     */
    public function update(SystemSettingRequest $request)
    {
        $settings = SystemSetting::firstOrCreate(['id' => 1]);
        
        DB::beginTransaction();
        try {
            $oldLateLimit = $settings->late_limit;
            
            // Update data
            $settings->update($request->validated());

            // Jika late_limit berubah, sinkronisasi ulang log absensi hari ini
            if ($request->has('late_limit') && $request->late_limit !== $oldLateLimit) {
                $this->syncTodayAttendance($request->late_limit);
            }

            DB::commit();
            return (new SystemSettingResource($settings))
                ->additional(['message' => 'Pengaturan berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Re-calculate status absensi hari ini berdasarkan jam terlambat baru
     */
    private function syncTodayAttendance($newLimit)
    {
        $today = date('Y-m-d');

        // Hadir -> Terlambat
        DB::table('attendance_logs')
            ->where('date', $today)
            ->whereTime('time_log', '>', $newLimit)
            ->update(['status' => 'Terlambat']);

        // Terlambat -> Hadir
        DB::table('attendance_logs')
            ->where('date', $today)
            ->whereTime('time_log', '<=', $newLimit)
            ->update(['status' => 'Hadir']);
    }
}