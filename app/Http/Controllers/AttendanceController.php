<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    private function getAccessibleNisns(): ?array
    {
        $user = Auth::user();
        $userId = auth()->id();

        if (!$user) {
            return [];
        }

        if ($user->role === 'admin') {
            return null;
        }

        if ($user->role === 'student') {
            $student = Student::where('user_id', $userId)->first();
            return $student ? [$student->nisn] : [];
        }

        if ($user->role === 'parent') {
            $parent = DB::table('parents')->where('user_id', $userId)->first();
            if (!$parent) return [];
            return Student::where('parent_id', $parent->id)->pluck('nisn')->toArray();
        }

        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $userId)->first();
            if (!$teacher) return [];

            $classIds = Schedule::where('teacher_id', $teacher->id)
                        ->pluck('class_id')
                        ->unique()
                        ->toArray();

            return Student::whereIn('class_id', $classIds)->pluck('nisn')->toArray();
        }

        return [];
    }

    public function index(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $dateFilter = $request->input('date', date('Y-m-d'));
        $scheduleId = $request->input('schedule_id');
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $availableSchedules = collect([]);
        $selectedSchedule = null;

        if (Auth::user()->role == 'teacher') {
            $carbonDate = Carbon::parse($dateFilter);
            $englishDay = $carbonDate->format('l');
            $daysMap = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $dayName = $daysMap[$englishDay] ?? $englishDay;
            $availableSchedules = Schedule::where('teacher_id', Auth::user()->teacher->id)
                ->where('day_of_week', $dayName)
                ->orderBy('start_time')
                ->get();
        }

        if ($scheduleId) {
            $selectedSchedule = Schedule::findOrFail($scheduleId);
        }

        $query = Student::with(['user', 'class', 'attendanceLogs' => function ($q) use ($dateFilter, $scheduleId) {
            $q->where('date', $dateFilter);
            
            if ($scheduleId) {
                $q->where('schedule_id', $scheduleId);
            }
        }]);

        if ($selectedSchedule) {
            $query->where('class_id', $selectedSchedule->class_id);
        } elseif (Auth::user()->role == 'teacher') {
            $todayClassIds = $availableSchedules->pluck('class_id')->unique();
            
            if ($todayClassIds->isNotEmpty()) {
                $query->whereIn('class_id', $todayClassIds);
            } else {
                $query->whereRaw('1 = 0'); 
            }
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('class_id')->get();

        $counts = [
            'present' => 0,
            'late' => 0,
            'permit' => 0,
            'absent' => 0
        ];

        $students->transform(function ($student) use ($statusFilter, &$counts) {
            $log = $student->attendanceLogs->first();

            $student->today_status = $log ? $log->status : 'Belum Hadir';
            $student->today_time = $log ? $log->time_log : '-';
            $student->log_id = $log ? $log->id : null;

            if ($statusFilter && $student->today_status !== $statusFilter) {
                if ($statusFilter == 'Alpha' && $student->today_status == 'Belum Hadir') {
                    
                } else {
                    return null;
                }
            }

            if ($student->today_status == 'Hadir') $counts['present']++;
            elseif ($student->today_status == 'Terlambat') $counts['late']++;
            elseif ($student->today_status == 'Izin' || $student->today_status == 'Sakit') $counts['permit']++;
            else $counts['absent']++;

            return $student;
        });

        $students = $students->filter();

        return view('attendance.index', compact(
            'students', 
            'dateFilter', 
            'counts', 
            'availableSchedules', 
            'selectedSchedule'
        ));
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $request->validate([
            'student_nisn' => 'required|exists:students,nisn',
            'date' => 'required|date',
            'time_log' => 'required',
            'status' => 'required',
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $student = Student::where('nisn', $request->student_nisn)->firstOrFail();
        $scheduleId = $request->schedule_id;

        if (!$scheduleId) {
            $carbonDate = Carbon::parse($request->date);
            $daysMap = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $dayName = $daysMap[$carbonDate->format('l')];
            $timeLog = $request->time_log;

            $matchedSchedule = Schedule::where('class_id', $student->class_id)
                ->where('day_of_week', $dayName)
                ->where('start_time', '<=', $timeLog)
                ->where('end_time', '>=', $timeLog)
                ->first();

            if ($matchedSchedule) {
                $scheduleId = $matchedSchedule->id;
            }
        }

        AttendanceLog::updateOrCreate(
            [
                'student_nisn' => $request->student_nisn,
                'date' => $request->date,
                'schedule_id' => $scheduleId
            ],
            [
                'time_log' => $request->time_log,
                'status' => $request->status,
                'device_id' => null,
            ]
        );

        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $log = AttendanceLog::findOrFail($id);

        if ($log->status === $request->status) {
            $log->delete();
            return redirect()->back()->with('success', 'Status absensi berhasil di-reset.');
        }

        $updateData = [
            'status' => $request->status,
            'time_log' => $request->time_log, 
        ];

        if ($request->filled('schedule_id')) {
            $updateData['schedule_id'] = $request->schedule_id;
        }

        $log->update($updateData);

        return redirect()->back()->with('success', 'Status berhasil diubah menjadi: ' . $request->status);
    }

    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        AttendanceLog::findOrFail($id)->delete();

        return redirect()->route('attendance.index')->with('success', 'Data berhasil dihapus');
    }
    
    public function export(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = AttendanceLog::query()->with(['student.user', 'student.class']);

        $accessibleNisns = $this->getAccessibleNisns();
        if (is_array($accessibleNisns)) {
            $query->whereIn('student_nisn', $accessibleNisns);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('schedule_id')) {
             $query->where('schedule_id', $request->schedule_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data absensi untuk kriteria tersebut.');
        }

        $fileName = 'Laporan-Absensi-' . Carbon::now()->format('Ymd-His') . '.xlsx';
        
        return Excel::download(new AttendanceExport($query), $fileName);
    }

    public function storeAjax(Request $request)
    {
        try {
            $request->validate([
                'nisn' => 'required|string|exists:students,nisn',
                'device_id' => 'nullable|exists:devices,id'
            ]);

            $nisn = $request->nisn;
            
            $now = Carbon::now();
            $date = $now->toDateString();
            $time = $now->toTimeString();
            
            $settings = SystemSetting::first(); 
            $globalTolerance = $settings->tolerance_minutes ?? 15;
            
            $student = Student::where('nisn', $nisn)->first();
            
            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan'], 404);
            }

            $daysMap = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $todayName = $daysMap[$now->format('l')];
            
            $activeSchedule = Schedule::with('subject')
                ->where('class_id', $student->class_id)
                ->where('day_of_week', $todayName)
                ->where(function($q) use ($time) {
                    $q->whereRaw("SUBTIME(start_time, '00:30:00') <= ?", [$time])
                      ->where('end_time', '>=', $time);
                })
                ->first();

            
            $scheduleId = null;
            $status = 'Hadir';
            $logMessage = '';

            if ($activeSchedule) {
                $scheduleId = $activeSchedule->id;
                
                $startTime = Carbon::parse($activeSchedule->start_time);
                $lateLimit = $startTime->copy()->addMinutes($globalTolerance);

                if ($now->greaterThan($lateLimit)) {
                    $status = 'Terlambat';
                }

                $subjectName = $activeSchedule->subject->subject_name ?? 'Pelajaran';
                $logMessage = "Absen Mapel: $subjectName ($status)";

            } else {
                // return response()->json(['success' => false, 'message' => 'Tidak ada jadwal aktif saat ini.'], 404);
                
                // Jika ingin tetap mencatat sebagai "Masuk Sekolah" (General):
                $limitMasuk = $settings->late_limit ?? '07:30:00';
                if ($time > $limitMasuk) {
                    $status = 'Terlambat';
                }
                $logMessage = "Absen Masuk Sekolah ($status)";
            }
            $matchAttributes = [
                'student_nisn' => $nisn,
                'date'         => $date,
                'schedule_id'  => $scheduleId, 
            ];

            // Data yang diupdate
            $updateValues = [
                'time_log'    => $time,
                'status'      => $status,
                'device_id'   => $request->device_id ?? null,
                // created_at & updated_at otomatis dihandle Eloquent jika timestamps aktif
            ];

            $log = AttendanceLog::updateOrCreate($matchAttributes, $updateValues);

            return response()->json([
                'success' => true,
                'message' => $logMessage,
                'data'    => $log,
                'student_name' => $student->user->full_name ?? 'Siswa', // Asumsi relasi ke user
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getCurrentScheduleId($carbonTime)
    {
        return Schedule::where('start_time', '<=', $carbonTime)
            ->where('end_time', '>=', $carbonTime)
            ->value('id');
    }
}