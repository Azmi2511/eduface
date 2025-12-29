<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get Overview Stats for Dashboard
     * * Mengambil statistik kehadiran hari ini, data grafik 7 hari terakhir, 
     * dan aktivitas terbaru.
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Statistik Utama
        $total_students = Student::count();

        // Mengambil ID log terakhir untuk setiap siswa hari ini agar tidak double count
        $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
            ->whereDate('date', $today)
            ->groupBy('student_nisn')
            ->pluck('id');

        $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

        $total_present = $today_logs->where('status', 'Hadir')->count();
        $total_late    = $today_logs->where('status', 'Terlambat')->count();
        $total_permit  = $today_logs->whereIn('status', ['Izin', 'Sakit'])->count();
        $total_absent  = $total_students - ($total_present + $total_late + $total_permit);
        
        $attendance_percentage = $total_students > 0 
            ? round((($total_present + $total_late) / $total_students) * 100) 
            : 0;

        // 2. Data Chart (7 Hari Terakhir)
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $check_date = Carbon::today()->subDays($i);
            $date_string = $check_date->toDateString();
            
            $count = AttendanceLog::whereDate('date', $date_string)
                ->distinct('student_nisn')
                ->count('student_nisn');

            $chart[] = [
                'label' => $check_date->translatedFormat('d M'),
                'value' => $count
            ];
        }

        // 3. Aktivitas Terbaru (Log Kehadiran)
        $recent_activity = AttendanceLog::with(['student.user', 'student.class'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'student_name' => $log->student->user->full_name ?? 'N/A',
                    'class'        => $log->student->class->class_name ?? '-',
                    'time'         => Carbon::parse($log->time_log)->format('H:i'),
                    'status'       => $log->status,
                ];
            });

        // 4. User Terbaru (Opsional untuk Admin)
        $recent_users = User::select('id', 'full_name', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_students' => $total_students,
                    'present'        => $total_present,
                    'late'           => $total_late,
                    'permit'         => $total_permit,
                    'absent'         => max(0, $total_absent), // Menghindari nilai negatif
                    'percentage'     => $attendance_percentage,
                ],
                'chart'           => $chart,
                'recent_activity' => $recent_activity,
                'recent_users'    => $recent_users,
                'settings' => [
                    'late_limit' => SystemSetting::value('late_limit') ?? '07:30:00',
                ]
            ]
        ]);
    }
}