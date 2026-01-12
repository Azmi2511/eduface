<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Device;
use App\Http\Resources\Api\V1\AttendanceResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class AttendanceController - Mengelola data absensi siswa.
 */
class AttendanceController extends Controller
{
    /**
     * Menampilkan daftar riwayat absensi (dibatasi 50 data terakhir).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AttendanceLog::with(['student.user', 'student.schoolClass', 'schedule.subject']);

        if ($user->role === 'parent' || $user->role === 'orang_tua') {
            $parent = $user->parentProfile ?? DB::table('parents')->where('user_id', $user->id)->first();
            if (!$parent) {
                return response()->json(['message' => 'Data profil orang tua tidak ditemukan'], 404);
            }
            $query->whereHas('student', function ($q) use ($parent) {
                $q->where('parent_id', $parent->id);
            });
        } elseif ($user->role === 'student' || $user->role === 'siswa') {
            $studentProfile = Student::where('user_id', $user->id)->first();
            if ($studentProfile) {
                $query->where('student_nisn', $studentProfile->nisn);
            } else {
                return response()->json(['message' => 'Profil siswa tidak ditemukan'], 404);
            }
        }

        if ($request->filled('nisn')) {
            $query->where('student_nisn', $request->nisn);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $logs = $query->latest('date')->latest('time_log')->limit(50)->get();

        return AttendanceResource::collection($logs);
    }

    /**
     * Endpoint penerima data dari mesin IoT.
     */
    public function deviceStore(Request $request)
    {
        $device = Device::where('api_token', $request->api_token)->firstOrFail();
        $student = Student::with('user')->where('nisn', $request->nisn)->firstOrFail();

        $now = Carbon::now();
        $todayName = $this->getIndonesianDay($now->format('l'));
        $time = $now->toTimeString();

        $activeSchedule = Schedule::where('class_id', $student->class_id)
            ->where('day_of_week', $todayName)
            ->where('start_time', '<=', $now->addMinutes(30)->toTimeString())
            ->where('end_time', '>=', $time)
            ->first();

        $status = 'Hadir';
        if ($activeSchedule) {
            $startTime = Carbon::parse($activeSchedule->start_time);
            if ($now->greaterThan($startTime->addMinutes(15))) {
                $status = 'Terlambat';
            }
        }

        AttendanceLog::updateOrCreate(
            [
                'student_nisn' => $student->nisn,
                'date'         => $now->toDateString(),
                'schedule_id'  => $activeSchedule->id ?? null,
            ],
            [
                'time_log'  => $time,
                'status'    => $status,
                'device_id' => $device->id,
            ]
        );

        return response()->json([
            'success' => true,
            'student' => $student->user->full_name,
            'status'  => $status,
            'subject' => $activeSchedule->subject->subject_name ?? 'Masuk Sekolah',
            'time'    => $now->format('H:i')
        ]);
    }

    /**
     * Helper konversi nama hari ke Bahasa Indonesia.
     */
    private function getIndonesianDay($day)
    {
        $map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $map[$day] ?? $day;
    }

    /**
     * Input absensi manual oleh Admin atau Guru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_nisn' => 'required|exists:students,nisn',
            'date'         => 'required|date',
            'time_log'     => 'required',
            'status'       => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpa',
            'schedule_id'  => 'nullable|exists:schedules,id',
        ]);

        $student = Student::where('nisn', $request->student_nisn)->first();
        $scheduleId = $request->schedule_id;

        if (!$scheduleId) {
            $carbonDate = Carbon::parse($request->date);
            $dayName = $this->getIndonesianDay($carbonDate->format('l'));

            $matchedSchedule = Schedule::where('class_id', $student->class_id)
                ->where('day_of_week', $dayName)
                ->where('start_time', '<=', $request->time_log)
                ->where('end_time', '>=', $request->time_log)
                ->first();

            if ($matchedSchedule) {
                $scheduleId = $matchedSchedule->id;
            }
        }

        $log = AttendanceLog::updateOrCreate(
            [
                'student_nisn' => $request->student_nisn,
                'date'         => $request->date,
                'schedule_id'  => $scheduleId,
            ],
            [
                'time_log'  => $request->time_log,
                'status'    => $request->status,
                'device_id' => null,
            ]
        );

        return new AttendanceResource($log->load(['student.user', 'student.schoolClass', 'schedule.subject']));
    }

    /**
     * Memperbarui status absensi siswa.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpa',
            'time_log' => 'nullable'
        ]);

        $log = AttendanceLog::findOrFail($id);
        $log->update($request->all());

        return (new AttendanceResource($log->load(['student.user', 'student.schoolClass', 'schedule.subject'])))
            ->additional(['message' => 'Status absensi berhasil diperbarui']);
    }

    /**
     * Menghapus data log absensi.
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat menghapus log.'], 403);
        }

        $log = AttendanceLog::findOrFail($id);
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus.'
        ]);
    }
}