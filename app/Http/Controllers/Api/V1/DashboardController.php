<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::today()->toDateString();

            $isRegistered = false; 
            $isStudentLogin = false;
            $parentRecord = null;
            $children = collect();

            if ($user->role === 'student') {
                $isStudentLogin = true;
                $studentProfile = Student::where('user_id', $user->id)->first();
                
                if ($studentProfile) {
                    $children = collect([$studentProfile]);
                    $isRegistered = $studentProfile->face_registered == 1;
                    
                    $parentRecord = DB::table('parents')->where('id', $studentProfile->parent_id)->first();
                }

            } elseif ($user->role === 'parent') {
                $parentRecord = DB::table('parents')->where('user_id', $user->id)->first();
                
                if ($parentRecord) {
                    $children = Student::where('parent_id', $parentRecord->id)->with('user')->get();
                }
            }

            if (($user->role === 'parent' && !$parentRecord) || ($user->role === 'student' && $children->isEmpty())) {
                 return response()->json([
                    'status' => 'error',
                    'message' => 'Data profil pengguna tidak ditemukan di sistem.'
                ], 404);
            }

            if ($children->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'parent_name' => $user->full_name,
                    'children' => [],
                    'statistics' => [
                        'presentCount' => 0,
                        'attendancePercentage' => "0%",
                        'lateCount' => 0,
                        'absentCount' => 0,
                        'permissionCount' => 0
                    ],
                    'attendance_logs' => [],
                    'is_face_registered' => false
                ]);
            }

            $childNisns = $children->pluck('nisn');

            $allLogsToday = AttendanceLog::whereIn('student_nisn', $childNisns)
                ->whereDate('date', $today)
                ->with(['student.user'])
                ->get();

            $totalPresentOnly = $allLogsToday->where('status', 'Hadir')->count();
            $totalLate         = $allLogsToday->where('status', 'Terlambat')->count();
            $totalAttending   = $totalPresentOnly + $totalLate;
            $totalPermit      = $allLogsToday->whereIn('status', ['Izin', 'Sakit'])->count();
            $totalChildren    = $children->count();
            
            $hasLogNisns = $allLogsToday->pluck('student_nisn')->toArray();
            $noLogCount  = $children->whereNotIn('nisn', $hasLogNisns)->count();
            $explicitAbsentCount = $allLogsToday->where('status', 'Alpa')->count();
            $totalAbsent = $noLogCount + $explicitAbsentCount;

            $overallPercentage = $totalChildren > 0 
                ? round(($totalAttending / $totalChildren) * 100) 
                : 0;

            $formattedLogs = $allLogsToday->sortByDesc('created_at')->values()->map(function ($log) {
                return [
                    'id' => $log->id,
                    'student_name' => $log->student->user->full_name ?? 'Siswa',
                    'status' => $log->status,
                    'time' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                    'date' => $log->date ? Carbon::parse($log->date)->format('d M Y') : '-',
                ];
            });

            $childrenData = $children->map(function($c) use ($allLogsToday) {
                $childLogs = $allLogsToday->where('student_nisn', $c->nisn);
                $presentCount = $childLogs->whereIn('status', ['Hadir', 'Terlambat'])->count();
                $lateCount    = $childLogs->where('status', 'Terlambat')->count();
                $permitCount  = $childLogs->whereIn('status', ['Izin', 'Sakit'])->count();
                $absentCount  = ($childLogs->count() == 0) ? 1 : $childLogs->where('status', 'Alpa')->count();
                $percentage   = $presentCount > 0 ? "100%" : "0%";

                return [
                    'id'   => $c->id, 
                    'name' => $c->user->full_name ?? 'Siswa',
                    'nisn' => $c->nisn,
                    'statistics' => [
                        'presentCount' => $presentCount,
                        'attendancePercentage' => $percentage,
                        'lateCount' => $lateCount,
                        'absentCount' => $absentCount,
                        'permissionCount' => $permitCount
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'parent_name' => $isStudentLogin ? ($parentRecord->name ?? 'Orang Tua') : $user->full_name,
                'children' => $childrenData,
                'statistics' => [
                    'presentCount' => $totalAttending, 
                    'attendancePercentage' => $overallPercentage . "%",
                    'lateCount' => $totalLate,
                    'absentCount' => $totalAbsent,
                    'permissionCount' => $totalPermit
                ],
                'attendance_logs' => $formattedLogs,
                'is_face_registered' => $isRegistered
            ]);

        } catch (\Exception $e) {
            Log::error("Dashboard Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
}