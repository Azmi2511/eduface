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
        $today = date('Y-m-d');
        $role = strtolower(session('role', 'user'));
        $userId = auth()->id();

        // Base general metrics
        $total_students = Student::count();

        $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
            ->whereDate('date', $today)
            ->groupBy('student_nisn')
            ->pluck('id');

        $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

        $total_present = $today_logs->where('status', 'Hadir')->count();
        $total_late = $today_logs->where('status', 'Terlambat')->count();

        $late_limit = SystemSetting::value('late_limit');

        $total_absent  = $total_students - $total_present - $total_late;
        $attendance_percentage = $total_students > 0 ? round(($total_present / $total_students) * 100) : 0;

        // Custom stats based on roles
        $parent_children = [];
        $parent_permissions_count = 0;
        $teacher_schedules_count = 0;
        $pending_permissions_count = 0;
        
        $student_info = null;
        $student_today_status = 'Belum Absen';
        $student_today_time = null;
        $student_attendance_rate = 100;

        if ($role === 'parent') {
            $parent = \App\Models\ParentProfile::where('user_id', $userId)->first();
            if ($parent) {
                $parent_children = \App\Models\Student::where('parent_id', $parent->id)->with('user', 'schoolClass')->get();
                $nisns = $parent_children->pluck('nisn')->toArray();
                $children_today_logs = AttendanceLog::whereIn('student_nisn', $nisns)
                    ->whereDate('date', $today)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                foreach ($parent_children as $child) {
                    $childLog = $children_today_logs->where('student_nisn', $child->nisn)->first();
                    $child->today_status = $childLog ? $childLog->status : 'Belum Absen';
                    $child->today_time = $childLog ? date('H:i', strtotime($childLog->time_log)) : null;
                }
                
                $parent_permissions_count = \App\Models\Permission::where('parent_id', $parent->id)->count();
            }
        } elseif ($role === 'teacher') {
            $teacher = \App\Models\Teacher::where('user_id', $userId)->first();
            if ($teacher) {
                $day_names = [
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu'
                ];
                $current_day = $day_names[date('l')] ?? 'Senin';
                $teacher_schedules_count = \App\Models\Schedule::where('teacher_id', $teacher->id)
                    ->where('day_of_week', $current_day)
                    ->count();
            }
            $pending_permissions_count = \App\Models\Permission::where('approval_status', 'Pending')->count();
        } elseif ($role === 'student') {
            $student_info = \App\Models\Student::where('user_id', $userId)->with('schoolClass')->first();
            if ($student_info) {
                $student_log = AttendanceLog::where('student_nisn', $student_info->nisn)
                    ->whereDate('date', $today)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($student_log) {
                    $student_today_status = $student_log->status;
                    $student_today_time = date('H:i', strtotime($student_log->time_log));
                }
                
                $total_school_days = AttendanceLog::where('student_nisn', $student_info->nisn)->distinct()->count('date');
                $present_days = AttendanceLog::where('student_nisn', $student_info->nisn)
                    ->whereIn('status', ['Hadir', 'Terlambat'])
                    ->distinct()
                    ->count('date');
                $student_attendance_rate = $total_school_days > 0 ? round(($present_days / $total_school_days) * 100) : 100;
            }
        }

        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $check_date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('d M', strtotime($check_date));

            $row = AttendanceLog::whereDate('date', $check_date)
                ->distinct('student_nisn')
                ->count('student_nisn');

            $chart_data[] = $row;
        }

        // ambil aktivitas terbaru
        $result_activity = AttendanceLog::with('student.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($a) {
                $student = $a->student;
                $name = $student->user->full_name ?? 'User Terhapus';
                return (object)[
                    'full_name' => $name,
                    'time_log' => $a->time_log,
                    'status' => $a->status,
                ];
            });

        $result_users = User::orderBy('created_at', 'desc')->limit(3)->get();

        return view('dashboard', compact(
            'total_students', 'total_present', 'total_late', 'total_absent', 'attendance_percentage',
            'chart_labels', 'chart_data', 'result_activity', 'result_users', 'late_limit',
            'parent_children', 'parent_permissions_count', 'teacher_schedules_count', 'pending_permissions_count',
            'student_info', 'student_today_status', 'student_today_time', 'student_attendance_rate'
        ));
    }
}
