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

        $total_present = DB::table('attendance_logs')
            ->where('date', $today)
            ->distinct('student_nisn')
            ->count('student_nisn');

        $limit_time = '07:30:59';
        $total_late = DB::table('attendance_logs')
            ->where('date', $today)
            ->where('time_log', '>', $limit_time)
            ->count();

        $total_absent = max(0, $total_students - $total_present);
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
            'chart_labels', 'chart_data', 'result_activity', 'result_users'
        ));
    }
}
