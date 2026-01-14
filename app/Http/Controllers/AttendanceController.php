<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Helpers\NotificationHelper;

class AttendanceController extends Controller
{
    private function getAccessibleNisns(): ?array
    {
        $user = Auth::user();
        if (!$user) return [];
        if ($user->role === 'admin') return null;

        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            return $student ? [$student->nisn] : [];
        }

        if ($user->role === 'parent') {
            $parent = DB::table('parents')->where('user_id', $user->id)->first();
            return $parent ? Student::where('parent_id', $parent->id)->pluck('nisn')->toArray() : [];
        }

        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) return [];
            $classIds = Schedule::where('teacher_id', $teacher->id)->pluck('class_id')->unique()->toArray();
            return Student::whereIn('class_id', $classIds)->pluck('nisn')->toArray();
        }

        return [];
    }

    public function index(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) abort(403);

        $dateFilter = $request->input('date', date('Y-m-d'));
        $scheduleId = $request->input('schedule_id');
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $availableSchedules = collect([]);
        $selectedSchedule = null;

        if (Auth::user()->role == 'teacher') {
            $dayName = $this->translateDay(Carbon::parse($dateFilter)->format('l'));
            $availableSchedules = Schedule::where('teacher_id', Auth::user()->teacher->id)
                ->where('day_of_week', $dayName)
                ->orderBy('start_time')
                ->get();
        }

        if ($scheduleId) $selectedSchedule = Schedule::find($scheduleId);

        $query = Student::with(['user', 'schoolClass', 'attendanceLogs' => function ($q) use ($dateFilter, $scheduleId) {
            $q->where('date', $dateFilter);
            if ($scheduleId) $q->where('schedule_id', $scheduleId);
        }]);

        if ($selectedSchedule) {
            $query->where('class_id', $selectedSchedule->class_id);
        } elseif (Auth::user()->role == 'teacher') {
            $todayClassIds = $availableSchedules->pluck('class_id')->unique();
            $todayClassIds->isNotEmpty() ? $query->whereIn('class_id', $todayClassIds) : $query->whereRaw('1 = 0');
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('class_id')->get();
        $counts = ['present' => 0, 'late' => 0, 'permit' => 0, 'absent' => 0];

        $students->transform(function ($student) use ($statusFilter, &$counts) {
            $log = $student->attendanceLogs->first();
            $student->today_status = $log ? $log->status : 'Belum Hadir';
            $student->today_time = $log ? $log->time_log : '-';
            $student->log_id = $log ? $log->id : null;

            if ($statusFilter && $student->today_status !== $statusFilter) {
                if (!($statusFilter == 'Alpha' && $student->today_status == 'Belum Hadir')) return null;
            }

            if ($student->today_status == 'Hadir') $counts['present']++;
            elseif ($student->today_status == 'Terlambat') $counts['late']++;
            elseif (in_array($student->today_status, ['Izin', 'Sakit'])) $counts['permit']++;
            else $counts['absent']++;

            return $student;
        });

        $students = $students->filter();
        return view('attendance.index', compact('students', 'dateFilter', 'counts', 'availableSchedules', 'selectedSchedule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_nisn' => 'required|exists:students,nisn',
            'date' => 'required|date',
            'time_log' => 'required',
            'status' => 'required',
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $student = Student::where('nisn', $request->student_nisn)->firstOrFail();
        
        if ($request->filled('schedule_id')) {
            $activeSchedule = Schedule::with('subject')->find($request->schedule_id);
        } else {
            $dayName = $this->translateDay(Carbon::parse($request->date)->format('l'));
            $activeSchedule = $this->findMatchingSchedule($student->class_id, $dayName, $request->time_log);
        }

        AttendanceLog::updateOrCreate(
            ['student_nisn' => $request->student_nisn, 'date' => $request->date, 'schedule_id' => $activeSchedule?->id],
            ['time_log' => $request->time_log, 'status' => $request->status, 'device_id' => null]
        );

        $this->notifyParentPush($student, $request->status, $request->time_log, $activeSchedule);

        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) abort(403);

        $log = AttendanceLog::with('student')->findOrFail($id);

        if ($log->status === $request->status) {
            $log->delete();
            return redirect()->back()->with('success', 'Status berhasil di-reset.');
        }

        $activeSchedule = null;
        if ($request->filled('schedule_id')) {
            $activeSchedule = Schedule::with('subject')->find($request->schedule_id);
        } else {
            $dayName = $this->translateDay(Carbon::parse($log->date)->format('l'));
            $activeSchedule = $this->findMatchingSchedule($log->student->class_id, $dayName, $request->time_log);
        }

        $log->update([
            'status' => $request->status,
            'time_log' => $request->time_log,
            'schedule_id' => $activeSchedule?->id ?? $log->schedule_id
        ]);

        $this->notifyParentPush($log->student, $request->status, $request->time_log, $activeSchedule);

        return redirect()->back()->with('success', 'Status berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);
        AttendanceLog::findOrFail($id)->delete();
        return redirect()->route('attendance.index')->with('success', 'Data berhasil dihapus');
    }

    public function export(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $query = AttendanceLog::query()->with(['student.user', 'student.schoolClass']);
        $accessibleNisns = $this->getAccessibleNisns();
        if (is_array($accessibleNisns)) $query->whereIn('student_nisn', $accessibleNisns);

        if ($request->filled('date')) $query->whereDate('date', $request->date);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('schedule_id')) $query->where('schedule_id', $request->schedule_id);
        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        $data = $query->get();
        if ($data->isEmpty()) return back()->with('error', 'Tidak ada data.');

        return Excel::download(new AttendanceExport($query), 'Laporan-Absensi-' . now()->format('YmdHis') . '.xlsx');
    }

    public function storeAjax(Request $request)
    {
        try {
            $request->validate(['nisn' => 'required|string|exists:students,nisn']);
            
            $now = Carbon::now();
            $student = Student::where('nisn', $request->nisn)->first();
            $dayName = $this->translateDay($now->format('l'));
            
            $settings = SystemSetting::first();
            $globalTolerance = $settings->tolerance_minutes ?? 15;
            
            $activeSchedule = $this->findMatchingSchedule($student->class_id, $dayName, $now->toTimeString());
            $status = 'Hadir';

            if ($activeSchedule) {
                $startTime = Carbon::parse($activeSchedule->start_time);
                if ($now->greaterThan($startTime->addMinutes($globalTolerance))) $status = 'Terlambat';
                $logMessage = "Absen Mapel: " . ($activeSchedule->subject->subject_name ?? 'Pelajaran') . " ($status)";
            } else {
                $limitMasuk = $settings->late_limit ?? '07:30:00';
                if ($now->toTimeString() > $limitMasuk) $status = 'Terlambat';
                $logMessage = "Absen Masuk Sekolah ($status)";
            }

            AttendanceLog::updateOrCreate(
                ['student_nisn' => $request->nisn, 'date' => $now->toDateString(), 'schedule_id' => $activeSchedule?->id],
                ['time_log' => $now->toTimeString(), 'status' => $status, 'device_id' => $request->device_id]
            );

            $this->notifyParentPush($student, $status, $now->toTimeString(), $activeSchedule);

            return response()->json(['success' => true, 'message' => $logMessage, 'student_name' => $student->user->full_name]);
        } catch (\Exception $e) {
            Log::error('Attendance Ajax Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function translateDay($englishDay)
    {
        $map = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        return $map[$englishDay] ?? $englishDay;
    }

    private function findMatchingSchedule($classId, $dayName, $time)
    {
        return Schedule::with('subject')
            ->where('class_id', $classId)
            ->where('day_of_week', $dayName)
            ->where(function($q) use ($time) {
                $q->whereRaw("SUBTIME(start_time, '00:30:00') <= ?", [$time])
                  ->where('end_time', '>=', $time);
            })
            ->first();
    }

    private function notifyParentPush($student, $status, $time, $schedule = null)
    {
        $student->loadMissing('parent.user', 'user');
        $userParent = $student->parent?->user;
        if (!$userParent) return;

        $context = "Sekolah";
        if ($schedule && $schedule->subject) {
            $context = "Mapel " . $schedule->subject->subject_name;
        }

        $studentName = $student->user->full_name ?? 'Siswa';
        $message = "Presensi $studentName tercatat $status untuk $context pukul " . substr($time, 0, 5) . " WIB.";

        if ($userParent->fcm_token) {
            try {
                NotificationHelper::sendPush($userParent->fcm_token, "Update Presensi: $status", $message);
            } catch (\Exception $e) {
                Log::error("FCM Error: " . $e->getMessage());
            }
        }

        Notification::create([
            'user_id' => $userParent->id,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
        ]);
    }
}