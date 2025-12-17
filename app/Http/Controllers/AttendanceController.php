<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Models\Schedule;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $today = Carbon::today(); // Ambil tanggal hari ini
        $dateFilter = $request->date ?? $today->format('Y-m-d'); // Default ke hari ini jika tidak ada filter

        // 1. QUERY LOG (Untuk History/Pagination biasa)
        $query = AttendanceLog::with(['student.user', 'student.class']);
        $accessibleNisns = $this->getAccessibleNisns();

        if (is_array($accessibleNisns)) {
            $query->whereIn('student_nisn', $accessibleNisns);
        }

        // Filter tanggal (Gunakan input atau default hari ini)
        $query->whereDate('date', $dateFilter);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_nisn', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function ($u) use ($search) {
                      $u->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $attendanceLogs = $query->orderBy('time_log', 'desc')->paginate(10);

        // 2. QUERY REKAPITULASI (Hitung Total Status Hari Ini)
        $statsQuery = AttendanceLog::select('status', DB::raw('count(*) as total'))
            ->whereDate('date', $dateFilter);

        if (is_array($accessibleNisns)) {
            $statsQuery->whereIn('student_nisn', $accessibleNisns);
        }

        $stats = $statsQuery->groupBy('status')->pluck('total', 'status')->toArray();

        // 3. QUERY UTAMA: DAFTAR SEMUA SISWA + STATUS HARI INI
        // Ini kuncinya: Kita ambil Siswa, lalu "Join" manual dengan Log hari ini
        $students = collect();

        if ($user->role === 'admin' || $user->role === 'teacher') {
            $studentQuery = Student::with(['user', 'class']);

            // Filter Hak Akses Guru
            if ($user->role === 'teacher' && is_array($accessibleNisns)) {
                $studentQuery->whereIn('nisn', $accessibleNisns);
            }

            // Eager Load Absensi HANYA untuk tanggal yang dipilih
            $studentQuery->with(['attendanceLogs' => function($q) use ($dateFilter) {
                $q->whereDate('date', $dateFilter);
            }]);

            // Filter pencarian nama siswa di daftar lengkap
            if ($request->filled('search')) {
                $search = $request->search;
                $studentQuery->whereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }

            $students = $studentQuery->get()->map(function($student) {
                // Ambil log pertama (karena tanggal sudah difilter di query, pasti cuma 1 atau 0)
                $log = $student->attendanceLogs->first();
                
                // Menentukan status final untuk ditampilkan
                $student->today_status = $log ? $log->status : 'Belum Hadir'; 
                $student->today_time = $log ? $log->time_log : '-';
                
                return $student;
            });
            
            // Urutkan berdasarkan Nama
            $students = $students->sortBy(function($s) { 
                return $s->user->full_name ?? ''; 
            });

            // Jika User memfilter status "Belum Hadir" atau "Alpha" yang tidak ada di tabel Log
            if ($request->filled('status')) {
                $students = $students->filter(function($s) use ($request) {
                    // Jika filter 'Alpha' atau 'Belum Hadir', cocokkan dengan yang tidak punya log
                    if ($request->status == 'Alpha' || $request->status == 'Belum Hadir') {
                        return $s->today_status == 'Belum Hadir';
                    }
                    return $s->today_status == $request->status;
                });
            }
        }

        // Update Counts agar mencakup yang belum hadir
        // Total Siswa yang harusnya absen
        $totalStudents = ($user->role === 'admin' || $user->role === 'teacher') ? $students->count() : 0;
        
        $counts = [
            'present' => intval($stats['Hadir'] ?? 0),
            'late'    => intval($stats['Terlambat'] ?? 0),
            'permit'  => intval($stats['Izin'] ?? 0),
            'sick'    => intval($stats['Sakit'] ?? 0),
            // Absen = Total Siswa - (Hadir + Telat + Izin + Sakit)
            'absent'  => $totalStudents - (array_sum($stats)) 
        ];

        return view('attendance.index', compact('attendanceLogs', 'counts', 'students', 'dateFilter'));
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
        $student = Student::where('nisn', $request->student_nisn)->firstOrFail();
        
        $request->validate([
            'student_nisn' => 'required|exists:students,nisn',
            'date' => 'required|date',
            'time_log' => 'required',
            'status' => 'required'
        ]);

        AttendanceLog::create($request->all());

        return redirect()->route('attendance.index')->with('success', 'Data berhasil disimpan');
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
        $log->update([
            'status'   => $request->status,
            'time_log' => $request->time_log, 
        ]);

        return redirect()->route('attendance.index')->with('success', 'Status berhasil diubah menjadi: ' . $request->status);
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

        // 4. Filter Input dari User (Tanggal, Status)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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