<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\SystemSetting;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Get dashboard statistics for the whole school (admin/officer view)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $today = date('Y-m-d');

            // Total students
            $total_students = Student::count();

            // Latest attendance logs for each student today
            $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
                ->whereDate('date', $today)
                ->groupBy('student_nisn')
                ->pluck('id');

            $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

            $total_present = $today_logs->where('status', 'Hadir')->count();
            $total_late    = $today_logs->where('status', 'Terlambat')->count();

            $late_limit = SystemSetting::value('late_limit');

            $total_absent  = $total_students - $total_present - $total_late;
            $attendance_percentage = $total_students > 0 ? round(($total_present / $total_students) * 100) : 0;

            // Chart data for last 7 days (distinct students per day)
            $chart_labels = [];
            $chart_data   = [];
            for ($i = 6; $i >= 0; $i--) {
                $check_date = date('Y-m-d', strtotime("-$i days"));
                $chart_labels[] = date('d M', strtotime($check_date));

                $row = AttendanceLog::whereDate('date', $check_date)
                    ->distinct('student_nisn')
                    ->count('student_nisn');

                $chart_data[] = $row;
            }

            // Recent activity (last 5 attendance logs)
            $recent_activities = AttendanceLog::with('student.user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($log) {
                    $student = $log->student;
                    $name = $student->user->full_name ?? 'User Terhapus';
                    return [
                        'full_name' => $name,
                        'time_log'  => $log->time_log,
                        'status'    => $log->status,
                    ];
                });

            // Recent registered users (last 3)
            $recent_users = User::orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['id', 'full_name', 'email', 'role', 'created_at']);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'statistics' => [
                        'total_students'          => $total_students,
                        'total_present'           => $total_present,
                        'total_late'              => $total_late,
                        'total_absent'            => $total_absent,
                        'attendance_percentage'   => $attendance_percentage,
                        'late_limit'              => $late_limit,
                    ],
                    'chart' => [
                        'labels' => $chart_labels,
                        'data'   => $chart_data,
                    ],
                    'recent_activities' => $recent_activities,
                    'recent_users'      => $recent_users,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}