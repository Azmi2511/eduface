<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\SystemSetting;
use App\Models\User;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // contoh konversi query dari index.php
        $today = date('Y-m-d');

        $total_students = Student::count();

        $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
            ->whereDate('date', $today)
            ->groupBy('student_id')
            ->pluck('id');

        $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

        $total_present = $today_logs->where('status', 'Hadir')->count();
        $total_late = $today_logs->where('status', 'Terlambat')->count();

        $late_limit = SystemSetting::value('late_limit');

        $total_absent  = $total_students - $total_present - $total_late;
        $attendance_percentage = $total_students > 0 ? round(($total_present / $total_students) * 100) : 0;

        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $check_date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('d M', strtotime($check_date));

            $row = AttendanceLog::whereDate('date', $check_date)
                ->distinct('student_id')
                ->count('student_id');

            $chart_data[] = $row;
        }

        // ambil aktivitas terbaru (gabungkan ke student->user jika tersedia)
        $result_activity = AttendanceLog::with('student.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($a) {
                $student = $a->student;
                $name = $student->full_name ?? ($student->user->full_name ?? 'User Terhapus');
                return (object)[
                    'full_name' => $name,
                    'time_log' => $a->time_log,
                    'status' => $a->status,
                ];
            });

        $result_users = User::orderBy('created_at', 'desc')->limit(3)->get();

        return view('dashboard', compact(
            'total_students', 'total_present', 'total_late', 'total_absent', 'attendance_percentage',
            'chart_labels', 'chart_data', 'result_activity', 'result_users', 'late_limit'
        ));
    }
}
