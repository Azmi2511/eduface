<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Device;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Requests\Api\V1\Attendance\DeviceLogRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Endpoint untuk LIST riwayat absensi (Admin & Teacher)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AttendanceLog::with(['student.user', 'student.class', 'schedule.subject']);

        // Filter Role: Teacher hanya lihat kelasnya
        if ($user->role === 'teacher') {
            $query->whereIn('student_nisn', function($q) use ($user) {
                $q->select('nisn')->from('students')
                  ->whereIn('class_id', Schedule::where('teacher_id', $user->teacher->id)->pluck('class_id'));
            });
        }

        // Filter Tanggal
        $date = $request->get('date', Carbon::today()->toDateString());
        $query->whereDate('date', $date);

        $logs = $query->latest('time_log')->get();
        return AttendanceResource::collection($logs);
    }

    /**
     * Endpoint UTAMA untuk Device IoT (Wajah/Kartu)
     */
    public function deviceStore(DeviceLogRequest $request)
    {
        $device = Device::where('api_token', $request->api_token)->first();
        $student = Student::with('user')->where('nisn', $request->nisn)->first();
        
        $now = Carbon::now();
        $todayName = $this->getIndonesianDay($now->format('l'));
        $time = $now->toTimeString();

        // 1. Cari Jadwal yang sedang berlangsung (Toleransi 30 menit sebelum mulai)
        $activeSchedule = Schedule::where('class_id', $student->class_id)
            ->where('day_of_week', $todayName)
            ->where('start_time', '<=', $now->addMinutes(30)->toTimeString())
            ->where('end_time', '>=', $time)
            ->first();

        // 2. Tentukan Status (Logic dari SystemSetting bisa ditaruh di sini)
        $status = 'Hadir';
        if ($activeSchedule) {
            $startTime = Carbon::parse($activeSchedule->start_time);
            if ($now->greaterThan($startTime->addMinutes(15))) { // Contoh toleransi 15 menit
                $status = 'Terlambat';
            }
        }

        // 3. Simpan atau Perbarui (Upsert)
        $log = AttendanceLog::updateOrCreate(
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

    private function getIndonesianDay($day)
    {
        $map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $map[$day];
    }
}