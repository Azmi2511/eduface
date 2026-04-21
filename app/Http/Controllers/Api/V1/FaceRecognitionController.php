<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class FaceRecognitionController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|exists:students,nisn',
            'descriptor' => 'required|array'
        ]);

        $student = Student::where('nisn', $request->nisn)->first();

        $student->face_descriptor = json_encode($request->descriptor);
        $student->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Wajah berhasil diregistrasi'
        ]);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|array'
        ]);

        $input = $request->descriptor;

        $students = Student::whereNotNull('face_descriptor')->get();
        $matchedStudent = null;

        foreach ($students as $student) {
            $stored = json_decode($student->face_descriptor);
            $distance = $this->euclideanDistance($stored, $input);

            if ($distance < 0.5) {
                $matchedStudent = $student;
                break;
            }
        }

        if (!$matchedStudent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali'
            ]);
        }

        $now = Carbon::now();
        $student = $matchedStudent;

        $lastLog = AttendanceLog::where('student_nisn', $student->nisn)
            ->whereDate('date', $now->toDateString())
            ->latest('time_log')
            ->first();

        if ($lastLog) {
            $lastTime = Carbon::parse($lastLog->time_log);
            if ($now->diffInMinutes($lastTime) < 5) {
                return response()->json([
                    'status' => 'duplicate',
                    'data' => [
                        'name' => $student->user->full_name ?? $student->full_name,
                        'nisn' => $student->nisn,
                        'time' => $lastLog->time_log,
                        'status' => $lastLog->status,
                        'message' => ($student->user->full_name ?? $student->full_name) . ' sudah absen'
                    ]
                ]);
            }
        }

        $dayName = $this->translateDay($now->format('l'));

        $settings = SystemSetting::first();
        $globalTolerance = $settings->tolerance_minutes ?? 15;

        $activeSchedule = $this->findMatchingSchedule(
            $student->class_id,
            $dayName,
            $now->toTimeString()
        );

        $status = 'Hadir';

        if ($activeSchedule) {
            $startTime = Carbon::parse($activeSchedule->start_time);

            if ($now->greaterThan($startTime->addMinutes($globalTolerance))) {
                $status = 'Terlambat';
            }

            $message = "Absen Mapel: " . ($activeSchedule->subject->subject_name ?? 'Pelajaran') . " ($status)";
        } else {
            $limitMasuk = $settings->late_limit ?? '07:30:00';

            if ($now->toTimeString() > $limitMasuk) {
                $status = 'Terlambat';
            }

            $message = "Absen Masuk Sekolah ($status)";
        }

        $log = AttendanceLog::updateOrCreate(
            [
                'student_nisn' => $student->nisn,
                'date' => $now->toDateString(),
                'schedule_id' => $activeSchedule?->id
            ],
            [
                'time_log' => $now->toTimeString(),
                'status' => $status
            ]
        );

        $this->notifyParent($student, $status, $now->toTimeString(), $activeSchedule);

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $student->user->full_name ?? $student->full_name,
                'nisn' => $student->nisn,
                'time' => $log->time_log,
                'status' => $status,
                'message' => $message
            ]
        ]);
    }

    private function euclideanDistance($a, $b)
    {
        $sum = 0;

        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }

        return sqrt($sum);
    }

    private function translateDay($englishDay)
    {
        $map = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

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

    private function notifyParent($student, $status, $time, $schedule = null)
    {
        try {
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
                NotificationHelper::sendPush(
                    $userParent->fcm_token,
                    "Update Presensi: $status",
                    $message
                );
            }

            Notification::create([
                'user_id' => $userParent->id,
                'message' => $message,
                'is_read' => 0,
                'created_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Notif Error: " . $e->getMessage());
        }
    }
}