<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // contoh konversi query dari index.php
        $today = date('Y-m-d');

        $total_students = DB::table('students')->count();

        $latest_ids = DB::table('attendance_logs')
            ->selectRaw('MAX(id) as id')
            ->where('date', $today)
            ->groupBy('student_nisn');

        $today_logs = DB::table('attendance_logs')
            ->whereIn('id', $latest_ids)
            ->get();

        $total_present = $today_logs->where('status', 'Hadir')->count();

        $total_late = $today_logs->where('status', 'Terlambat')->count();

        $late_limit = DB::table('system_settings')->value('late_limit');

        $total_absent  = $total_students - $total_present - $total_late;
        $attendance_percentage = $total_students > 0 ? round(($total_present / $total_students) * 100) : 0;

        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $check_date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('d M', strtotime($check_date));

            $row = DB::table('attendance_logs')
                ->where('date', $check_date)
                ->distinct('student_nisn')
                ->count('student_nisn');

            $chart_data[] = $row;
        }

        // ambil aktivitas terbaru
        $result_activity = DB::table('attendance_logs as a')
            ->join('students as s', 'a.student_nisn', '=', 's.nisn')
            ->select('s.full_name', 'a.time_log', 'a.status')
            ->orderBy('a.created_at', 'desc')
            ->limit(5)
            ->get();

        $result_users = DB::table('users')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'total_students', 'total_present', 'total_late', 'total_absent', 'attendance_percentage',
            'chart_labels', 'chart_data', 'result_activity', 'result_users', 'late_limit'
        ));
    }
}
