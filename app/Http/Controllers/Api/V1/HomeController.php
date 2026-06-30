<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Schedule;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Get dashboard statistics adjusted for user roles (admin, teacher, student, parent)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::today()->toDateString();
            $late_limit = SystemSetting::value('late_limit');

            if ($user->role === 'admin') {
                // ==========================================
                // ADMIN ROLE (School-wide statistics)
                // ==========================================
                $total_students = Student::count();

                // Latest attendance logs for each student today
                $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
                    ->whereDate('date', $today)
                    ->groupBy('student_nisn')
                    ->pluck('id');

                $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

                $total_present = $today_logs->where('status', 'Hadir')->count();
                $total_late = $today_logs->where('status', 'Terlambat')->count();
                $total_permit = $today_logs->whereIn('status', ['Izin', 'Sakit'])->count();
                $total_absent = $total_students - $total_present - $total_late - $total_permit;
                $attendance_percentage = $total_students > 0 ? round((($total_present + $total_late) / $total_students) * 100) : 0;

                // Chart data for last 7 days (distinct students present per day)
                $chart_labels = [];
                $chart_data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $check_date = Carbon::now()->subDays($i)->toDateString();
                    $chart_labels[] = Carbon::now()->subDays($i)->format('d M');

                    $row = AttendanceLog::whereDate('date', $check_date)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
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
                        return [
                            'full_name' => $log->student->user->full_name ?? 'User Terhapus',
                            'time_log' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                            'status' => $log->status,
                            'date' => $log->date,
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
                            'total_students' => $total_students,
                            'total_present' => $total_present,
                            'total_late' => $total_late,
                            'total_absent' => $total_absent,
                            'total_permit' => $total_permit,
                            'attendance_percentage' => $attendance_percentage,
                            'late_limit' => $late_limit,
                        ],
                        'chart' => [
                            'labels' => $chart_labels,
                            'data' => $chart_data,
                        ],
                        'recent_activities' => $recent_activities,
                        'recent_users' => $recent_users,
                    ]
                ]);

            } elseif ($user->role === 'teacher') {
                // ==========================================
                // TEACHER ROLE (Filtered by assigned classes)
                // ==========================================
                $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();

                $myClassIds = collect();
                if ($teacher) {
                    $homeroomClassIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
                    $scheduleClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('class_id');
                    $myClassIds = $homeroomClassIds->concat($scheduleClassIds)->unique()->filter();
                }

                // If teacher has no assigned classes, fallback to school-wide admin statistics
                if ($myClassIds->isEmpty()) {
                    $total_students = Student::count();

                    $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
                        ->whereDate('date', $today)
                        ->groupBy('student_nisn')
                        ->pluck('id');

                    $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

                    $total_present = $today_logs->where('status', 'Hadir')->count();
                    $total_late = $today_logs->where('status', 'Terlambat')->count();
                    $total_permit = $today_logs->whereIn('status', ['Izin', 'Sakit'])->count();
                    $total_absent = $total_students - $total_present - $total_late - $total_permit;
                    $attendance_percentage = $total_students > 0 ? round((($total_present + $total_late) / $total_students) * 100) : 0;

                    $chart_labels = [];
                    $chart_data = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $check_date = Carbon::now()->subDays($i)->toDateString();
                        $chart_labels[] = Carbon::now()->subDays($i)->format('d M');
                        $row = AttendanceLog::whereDate('date', $check_date)
                            ->whereIn('status', ['Hadir', 'Terlambat'])
                            ->distinct('student_nisn')
                            ->count('student_nisn');
                        $chart_data[] = $row;
                    }

                    $recent_activities = AttendanceLog::with('student.user')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($log) {
                            return [
                                'full_name' => $log->student->user->full_name ?? 'User Terhapus',
                                'time_log' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                                'status' => $log->status,
                            ];
                        });

                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'statistics' => [
                                'total_students' => $total_students,
                                'total_present' => $total_present,
                                'total_late' => $total_late,
                                'total_absent' => $total_absent,
                                'total_permit' => $total_permit,
                                'attendance_percentage' => $attendance_percentage,
                                'late_limit' => $late_limit,
                            ],
                            'chart' => [
                                'labels' => $chart_labels,
                                'data' => $chart_data,
                            ],
                            'recent_activities' => $recent_activities,
                            'recent_users' => [],
                        ]
                    ]);
                }

                $studentNisns = Student::whereIn('class_id', $myClassIds)->pluck('nisn');
                $total_students = Student::whereIn('class_id', $myClassIds)->count();

                $latest_ids = AttendanceLog::selectRaw('MAX(id) as id')
                    ->whereIn('student_nisn', $studentNisns)
                    ->whereDate('date', $today)
                    ->groupBy('student_nisn')
                    ->pluck('id');

                $today_logs = AttendanceLog::whereIn('id', $latest_ids)->get();

                $total_present = $today_logs->where('status', 'Hadir')->count();
                $total_late = $today_logs->where('status', 'Terlambat')->count();
                $total_permit = $today_logs->whereIn('status', ['Izin', 'Sakit'])->count();
                $total_absent = $total_students - $total_present - $total_late - $total_permit;
                $attendance_percentage = $total_students > 0 ? round((($total_present + $total_late) / $total_students) * 100) : 0;

                // Chart data for teacher's students (distinct students present per day)
                $chart_labels = [];
                $chart_data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $check_date = Carbon::now()->subDays($i)->toDateString();
                    $chart_labels[] = Carbon::now()->subDays($i)->format('d M');

                    $row = AttendanceLog::whereIn('student_nisn', $studentNisns)
                        ->whereDate('date', $check_date)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->distinct('student_nisn')
                        ->count('student_nisn');

                    $chart_data[] = $row;
                }

                // Recent activity for teacher's students
                $recent_activities = AttendanceLog::whereIn('student_nisn', $studentNisns)
                    ->with('student.user')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'full_name' => $log->student->user->full_name ?? 'User Terhapus',
                            'time_log' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                            'status' => $log->status,
                            'date' => $log->date,
                        ];
                    });

                // Recent registered students in teacher's classes
                $recent_users = User::whereIn('id', Student::whereIn('class_id', $myClassIds)->pluck('user_id'))
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get(['id', 'full_name', 'email', 'role', 'created_at']);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'statistics' => [
                            'total_students' => $total_students,
                            'total_present' => $total_present,
                            'total_late' => $total_late,
                            'total_absent' => $total_absent,
                            'total_permit' => $total_permit,
                            'attendance_percentage' => $attendance_percentage,
                            'late_limit' => $late_limit,
                        ],
                        'chart' => [
                            'labels' => $chart_labels,
                            'data' => $chart_data,
                        ],
                        'recent_activities' => $recent_activities,
                        'recent_users' => $recent_users,
                    ]
                ]);

            } elseif ($user->role === 'student') {
                // ==========================================
                // STUDENT ROLE (Cumulative stats)
                // ==========================================
                $student = $user->student ?? Student::where('user_id', $user->id)->first();

                if (!$student) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Profil siswa tidak ditemukan.'
                    ], 404);
                }

                $allStudentLogs = AttendanceLog::where('student_nisn', $student->nisn)->get();

                $total_present = $allStudentLogs->where('status', 'Hadir')->count();
                $total_late = $allStudentLogs->where('status', 'Terlambat')->count();
                $total_absent = $allStudentLogs->whereIn('status', ['Alpha', 'Alpa'])->count();
                $total_permit = $allStudentLogs->whereIn('status', ['Izin', 'Sakit'])->count();
                $total_days = $allStudentLogs->count();
                $attendance_percentage = $total_days > 0 ? round((($total_present + $total_late) / $total_days) * 100) : 0;

                // Chart data: 7 days, 1 if present/late, 0 if not
                $chart_labels = [];
                $chart_data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $check_date = Carbon::now()->subDays($i)->toDateString();
                    $chart_labels[] = Carbon::now()->subDays($i)->format('d M');

                    $has_log = AttendanceLog::where('student_nisn', $student->nisn)
                        ->whereDate('date', $check_date)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->exists();

                    $chart_data[] = $has_log ? 1 : 0;
                }

                // Recent activity (last 5 logs of this student)
                $recent_activities = AttendanceLog::where('student_nisn', $student->nisn)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($log) use ($user) {
                        return [
                            'full_name' => $user->full_name,
                            'time_log' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                            'status' => $log->status,
                            'date' => $log->date,
                        ];
                    });

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'statistics' => [
                            'total_students' => 1,
                            'total_present' => $total_present,
                            'total_late' => $total_late,
                            'total_absent' => $total_absent,
                            'total_permit' => $total_permit,
                            'attendance_percentage' => $attendance_percentage,
                            'late_limit' => $late_limit,
                        ],
                        'chart' => [
                            'labels' => $chart_labels,
                            'data' => $chart_data,
                        ],
                        'recent_activities' => $recent_activities,
                        'recent_users' => [],
                    ]
                ]);

            } elseif ($user->role === 'parent') {
                // ==========================================
                // PARENT ROLE (Combined children stats)
                // ==========================================
                $parent = $user->parentProfile ?? ParentProfile::where('user_id', $user->id)->first();

                if (!$parent) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Profil orang tua tidak ditemukan.'
                    ], 404);
                }

                $children = Student::where('parent_id', $parent->id)->get();
                if ($children->isEmpty()) {
                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'statistics' => [
                                'total_students' => 0,
                                'total_present' => 0,
                                'total_late' => 0,
                                'total_absent' => 0,
                                'total_permit' => 0,
                                'attendance_percentage' => 0,
                                'late_limit' => $late_limit,
                            ],
                            'chart' => [
                                'labels' => [],
                                'data' => [],
                            ],
                            'recent_activities' => [],
                            'recent_users' => [],
                        ]
                    ]);
                }

                $childNisns = $children->pluck('nisn');
                $allChildrenLogs = AttendanceLog::whereIn('student_nisn', $childNisns)->get();

                $total_present = $allChildrenLogs->where('status', 'Hadir')->count();
                $total_late = $allChildrenLogs->where('status', 'Terlambat')->count();
                $total_absent = $allChildrenLogs->whereIn('status', ['Alpha', 'Alpa'])->count();
                $total_permit = $allChildrenLogs->whereIn('status', ['Izin', 'Sakit'])->count();
                $total_days = $allChildrenLogs->count();
                $attendance_percentage = $total_days > 0 ? round((($total_present + $total_late) / $total_days) * 100) : 0;

                // Chart data: 7 days, count of children present/late on each day
                $chart_labels = [];
                $chart_data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $check_date = Carbon::now()->subDays($i)->toDateString();
                    $chart_labels[] = Carbon::now()->subDays($i)->format('d M');

                    $row = AttendanceLog::whereIn('student_nisn', $childNisns)
                        ->whereDate('date', $check_date)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->distinct('student_nisn')
                        ->count('student_nisn');

                    $chart_data[] = $row;
                }

                // Recent activity of children
                $recent_activities = AttendanceLog::whereIn('student_nisn', $childNisns)
                    ->with('student.user')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'full_name' => $log->student->user->full_name ?? 'Anak',
                            'time_log' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                            'status' => $log->status,
                            'date' => $log->date,
                        ];
                    });

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'statistics' => [
                            'total_students' => $children->count(),
                            'total_present' => $total_present,
                            'total_late' => $total_late,
                            'total_absent' => $total_absent,
                            'total_permit' => $total_permit,
                            'attendance_percentage' => $attendance_percentage,
                            'late_limit' => $late_limit,
                        ],
                        'chart' => [
                            'labels' => $chart_labels,
                            'data' => $chart_data,
                        ],
                        'recent_activities' => $recent_activities,
                        'recent_users' => [],
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Role tidak valid.'
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}