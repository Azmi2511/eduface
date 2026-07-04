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
use Illuminate\Support\Facades\Process;
use App\Helpers\NotificationHelper;

class FaceRecognitionController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|exists:students,nisn',
            'file' => 'required|image|max:5120'
        ]);

        $student = Student::where('nisn', $request->nisn)->first();

        try {
            $tempFile = $request->file('file')->store('temp_register');
            $fullPath = storage_path('app/private/' . $tempFile);

            $descriptor = $this->extractDescriptor($fullPath);

            $student->face_descriptor = json_encode($descriptor);
            $student->save();

            @unlink($fullPath);
        } catch (\Exception $e) {
            if (isset($fullPath))
                @unlink($fullPath);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Wajah berhasil diregistrasi'
        ]);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $settings = SystemSetting::first();
        $schoolLat = $settings->school_latitude ?? -6.200000;
        $schoolLng = $settings->school_longitude ?? 106.816666;
        $maxRadius = $settings->allowed_radius_meters ?? 100;

        $distanceToSchool = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $schoolLat,
            $schoolLng
        );

        if ($distanceToSchool > $maxRadius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda berada di luar jangkauan sekolah. Jarak Anda: ' . round($distanceToSchool) . ' meter.'
            ], 403);
        }

        try {
            $tempFile = $request->file('file')->store('temp_predict');
            $fullPath = storage_path('app/private/' . $tempFile);

            $input = $this->extractDescriptor($fullPath);
            @unlink($fullPath);
        } catch (\Exception $e) {
            if (isset($fullPath))
                @unlink($fullPath);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        $students = Student::with('user:id,full_name')
            ->select('id', 'user_id', 'nisn', 'class_id', 'face_descriptor')
            ->whereNotNull('face_descriptor')
            ->get();

        $matchedStudent = null;
        $threshold = 0.5;

        foreach ($students as $student) {
            $stored = json_decode($student->face_descriptor);
            if (!$stored)
                continue;

            $distance = $this->euclideanDistance($stored, $input);

            if ($distance < $threshold) {
                $matchedStudent = $student;
                break;
            }
        }

        if (!$matchedStudent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali'
            ], 401);
        }

        $now = Carbon::now();
        $student = $matchedStudent;
        $studentName = $student->user->full_name ?? 'Siswa';

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
                        'name' => $studentName,
                        'nisn' => $student->nisn,
                        'time' => $lastLog->time_log,
                        'status' => $lastLog->status,
                        'message' => $studentName . ' sudah absen beberapa saat lalu.'
                    ]
                ]);
            }
        }

        $dayName = $this->translateDay($now->format('l'));
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
                'name' => $studentName,
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
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            $sum += ($a[$i] - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }

    private function extractDescriptor($imagePath)
    {
        $scriptPath = base_path('face_service.js');

        $resultProcess = Process::env([
            'SystemRoot' => 'C:\\Windows',
            'PATH' => env('PATH', 'C:\\Program Files\\nodejs\\;C:\\Windows\\system32')
        ])->run([
                    'node',
                    $scriptPath,
                    $imagePath
                ]);

        if ($resultProcess->failed()) {
            throw new \Exception("Gagal menjalankan service pengenalan wajah: " . $resultProcess->errorOutput());
        }

        $output = $resultProcess->output();
        $result = json_decode($output, true);

        if ($result === null) {
            throw new \Exception("Service wajah gagal/error: " . $output);
        }

        if (isset($result['error'])) {
            throw new \Exception("Error dari node: " . $result['error']);
        }

        if (isset($result['success']) && isset($result['descriptor'])) {
            return $result['descriptor'];
        }

        throw new \Exception("Format output tidak valid dari service pengenalan wajah: " . $output);
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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function findMatchingSchedule($classId, $dayName, $time)
    {
        return Schedule::with('subject')
            ->where('class_id', $classId)
            ->where('day_of_week', $dayName)
            ->where('start_time', '<=', Carbon::parse($time)->addMinutes(30)->toTimeString())
            ->where('end_time', '>=', $time)
            ->first();
    }

    private function notifyParent($student, $status, $time, $schedule = null)
    {
        $student->loadMissing('parent.user', 'user');
        $userParent = $student->parent?->user;
        if (!$userParent)
            return;

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