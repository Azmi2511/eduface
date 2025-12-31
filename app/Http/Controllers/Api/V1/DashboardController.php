<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::today()->toDateString();

            $parentRecord = DB::table('parents')->where('user_id', $user->id)->first();

            if (!$parentRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Profil orang tua tidak ditemukan'
                ], 404);
            }

            $children = Student::where('parent_id', $parentRecord->id)->with('user')->get();
            
            if ($children->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'parent_name' => $user->full_name,
                    'children' => [],
                    'statistics' => [
                        'attendancePercentage' => "0%",
                        'lateCount' => 0,
                        'absentCount' => 0,
                        'permissionCount' => 0
                    ],
                    'attendance_logs' => []
                ]);
            }

            $childNisns = $children->pluck('nisn');

            $logsToday = AttendanceLog::whereIn('student_nisn', $childNisns)
                ->whereDate('date', $today)
                ->get();

            $totalPresent = $logsToday->where('status', 'Hadir')->count();
            $totalLate    = $logsToday->where('status', 'Terlambat')->count();
            $totalPermit  = $logsToday->whereIn('status', ['Izin', 'Sakit'])->count();
            $totalChildren = $children->count();
            
            $totalAbsent = max(0, $totalChildren - $logsToday->count());
            $percentage = $totalChildren > 0 ? round((($totalPresent + $totalLate) / $totalChildren) * 100) : 0;

            $attendanceLogs = AttendanceLog::whereIn('student_nisn', $childNisns)
                ->with(['student.user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'student_name' => optional(optional($log->student)->user)->full_name ?? 'Siswa',
                        'status' => $log->status,
                        'time' => $log->time_log ? Carbon::parse($log->time_log)->format('H:i') : '-',
                        'date' => $log->date ? Carbon::parse($log->date)->format('d M Y') : '-',
                    ];
                });

            return response()->json([
                'status' => 'success',
                'parent_name' => $user->full_name,
                'children' => $children->map(function($c) {
                    return [
                        'id' => $c->id, 
                        'name' => optional($c->user)->full_name ?? 'Anak'
                    ];
                }),
                'statistics' => [
                    'presentCount' => $totalPresent,
                    'attendancePercentage' => $percentage . "%",
                    'lateCount' => $totalLate,
                    'absentCount' => $totalAbsent,
                    'permissionCount' => $totalPermit
                ],
                'attendance_logs' => $attendanceLogs
            ]);

        } catch (\Exception $e) {
            Log::error("Dashboard Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}