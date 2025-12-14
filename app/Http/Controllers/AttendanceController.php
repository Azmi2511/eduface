<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Models\Schedule;
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

        $query = AttendanceLog::with(['student.user', 'student.class']);

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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_nisn', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function ($u) use ($search) {
                      $u->where('full_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $attendanceLogs = $query->orderBy('date', 'desc')
                                ->orderBy('time_log', 'desc')
                                ->paginate(10);

        $today = Carbon::today();
        $statsQuery = AttendanceLog::select('status', DB::raw('count(*) as total'))
            ->whereDate('date', $today);

        if (is_array($accessibleNisns)) {
            $statsQuery->whereIn('student_nisn', $accessibleNisns);
        }

        $stats = $statsQuery->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts = [
            'present' => intval($stats['Hadir'] ?? 0),
            'late'    => intval($stats['Terlambat'] ?? 0),
            'permit'  => intval($stats['Izin'] ?? 0),
            'absent'  => intval($stats['Alpha'] ?? 0),
        ];

        $students = collect();
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'teacher') {
            $studentQuery = Student::with('user');
            
            if (Auth::user()->role === 'teacher' && is_array($accessibleNisns)) {
                $studentQuery->whereIn('nisn', $accessibleNisns);
            }
            
            $students = $studentQuery->get()->sortBy(function($s){ return $s->user->full_name ?? ''; });
        }

        return view('attendance.index', compact('attendanceLogs', 'counts', 'students'));
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
        $log->update($request->all());

        return redirect()->route('attendance.index')->with('success', 'Data berhasil diperbarui');
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

        $query = AttendanceLog::with(['student.user', 'student.class']);

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

        $fileName = 'Laporan-Absensi-' . Carbon::now()->format('Ymd-His') . '.xlsx';
        
        return Excel::download(new AttendanceExport($query), $fileName);
    }
}