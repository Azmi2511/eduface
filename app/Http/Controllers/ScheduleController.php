<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Schedule::with(['class', 'subject', 'teacher.user']);

        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }
        elseif ($user->role === 'student') {
            $student = $user->student;
            if ($student) {
                $query->where('class_id', $student->class_id);
            }
        }

        if ($request->filled('day')) {
            $dayInput = $request->day;

            $daysMap = [
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu',
                'Sunday'    => 'Minggu',
            ];

            $translatedDay = isset($daysMap[$dayInput]) 
                ? $daysMap[$dayInput] 
                : array_search($dayInput, $daysMap);

            $query->where(function($q) use ($dayInput, $translatedDay) {
                $q->where('day_of_week', $dayInput);
                if ($translatedDay) {
                    $q->orWhere('day_of_week', $translatedDay);
                }
            });
        }
        if ($request->filled('class_id') && $user->role === 'admin') {
            $query->where('class_id', $request->class_id);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(10);

        $classroom = SchoolClass::orderBy('class_name')->get();
        $subjects = Subject::orderBy('subject_name')->get(); 
        $teachers = Teacher::with('user')->get();

        return view('schedules.index', compact('schedules', 'classroom', 'subjects', 'teachers'));
    }

    /**
     * Form tambah jadwal (Hanya Admin).
     */
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $classes = SchoolClass::orderBy('class_name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->get();

        return view('schedules.create', compact('classes', 'subjects', 'teachers'));
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        // Cek Bentrok Jadwal (Overlap Check)
        if ($this->hasScheduleConflict($request)) {
            return back()->withInput()->withErrors(['conflict' => 'Jadwal bentrok! Guru atau Kelas sudah memiliki jadwal di jam tersebut.']);
        }

        Schedule::create($request->all());

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Form edit jadwal.
     */
    public function edit(Schedule $schedule)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $classes = SchoolClass::orderBy('class_name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->get();

        return view('schedules.edit', compact('schedule', 'classes', 'subjects', 'teachers'));
    }

    /**
     * Update jadwal.
     */
    public function update(Request $request, Schedule $schedule)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        if ($this->hasScheduleConflict($request, $schedule->id)) {
            return back()->withInput()->withErrors(['conflict' => 'Jadwal bentrok! Guru atau Kelas sudah memiliki jadwal di jam tersebut.']);
        }

        $schedule->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Hapus jadwal.
     */
    public function destroy(Schedule $schedule)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Fungsi Validasi Bentrok Jadwal (Private Helper)
     * Mencegah:
     * 1. Satu Guru mengajar di 2 kelas berbeda pada jam yang sama.
     * 2. Satu Kelas memiliki 2 mata pelajaran pada jam yang sama.
     */
    private function hasScheduleConflict(Request $request, $ignoreId = null)
    {
        $query = Schedule::where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->where('class_id', $request->class_id);
                  });
            })
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                  ->orWhere(function ($sub) use ($request) {
                      $sub->where('start_time', '<=', $request->start_time)
                          ->where('end_time', '>=', $request->end_time);
                  });
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}