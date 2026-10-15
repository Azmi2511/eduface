<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\Notification;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FaceRecognitionController extends Controller
{
    // ===============================
    // REGISTER WAJAH
    // ===============================
    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|exists:students,nisn',
            'descriptor' => 'nullable|array',
            'file' => 'nullable|image|max:5120'
        ]);

        if (!$request->has('descriptor') && !$request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data wajah (descriptor atau foto) wajib dikirimkan.'
            ], 422);
        }

        $student = Student::where('nisn', $request->nisn)->first();

        if (Auth::check() && Auth::user()->role === 'student' && $student->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya dapat mendaftarkan wajah untuk akun sendiri.'
            ], 403);
        }

        try {
            if ($request->has('descriptor')) {
                $descriptor = $request->descriptor;
            } else {
                $descriptor = $this->extractDescriptorFromFile($request->file('file'));
            }

            $student->face_descriptor = $descriptor;
            $student->face_registered = true;
            $student->face_registered_at = now();
            $student->is_face_registered = 1;
            $student->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Wajah berhasil diregistrasi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal registrasi wajah: ' . $e->getMessage()
            ], 400);
        }
    }

    // ===============================
    // ABSENSI (VERIFY / PREDICT)
    // ===============================
    public function predict(Request $request)
    {
        $request->validate([
            'descriptor' => 'nullable|array',
            'file' => 'nullable|image|max:5120'
        ]);

        if (!$request->has('descriptor') && !$request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data wajah (descriptor atau foto) wajib dikirimkan.'
            ], 422);
        }

        try {
            if ($request->has('descriptor')) {
                $input = $request->descriptor;
            } else {
                $input = $this->extractDescriptorFromFile($request->file('file'));
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses wajah: ' . $e->getMessage()
            ], 400);
        }

        $students = Student::with('user')
            ->whereNotNull('face_descriptor')
            ->get();

        $candidates = [];
        $threshold = 0.5;

        foreach ($students as $student) {
            $stored = is_array($student->face_descriptor)
                ? $student->face_descriptor
                : json_decode($student->face_descriptor, true);

            if (!$stored || !is_array($stored)) {
                continue;
            }

            $candidates[] = [
                'student' => $student,
                'distance' => $this->euclideanDistance($stored, $input),
            ];
        }

        usort($candidates, fn ($left, $right) => $left['distance'] <=> $right['distance']);
        $bestCandidate = $candidates[0] ?? null;
        $secondCandidate = $candidates[1] ?? null;
        $matchedStudent = $bestCandidate && $bestCandidate['distance'] < $threshold
            ? $bestCandidate['student']
            : null;

        if ($matchedStudent && $secondCandidate &&
            $secondCandidate['distance'] - $bestCandidate['distance'] < 0.05) {
            return response()->json([
                'status' => 'ambiguous',
                'message' => 'Wajah terlalu mirip dengan lebih dari satu siswa. Silakan ulangi dengan posisi dan pencahayaan yang lebih baik.'
            ], 409);
        }

        // ===============================
        // TIDAK DITEMUKAN
        // ===============================
        if (!$matchedStudent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali'
            ], 404);
        }

        $studentName = $matchedStudent->user?->full_name ?? $matchedStudent->full_name ?? 'Siswa';
        $now = Carbon::now();

        // ===============================
        // CEK DUPLIKAT ABSENSI HARI INI
        // ===============================
        $already = AttendanceLog::where('student_nisn', $matchedStudent->nisn)
            ->whereDate('date', today())
            ->latest('time_log')
            ->first();

        if ($already) {
            return response()->json([
                'status' => 'duplicate',
                'message' => $studentName . ' sudah absen hari ini.',
                'student' => $studentName,
                'data' => [
                    'name' => $studentName,
                    'nisn' => $matchedStudent->nisn,
                    'time' => $already->time_log,
                    'status' => $already->status,
                    'message' => $studentName . ' sudah absen hari ini (' . $already->status . ').'
                ]
            ]);
        }

        // ===============================
        // HITUNG STATUS HADIR / TERLAMBAT
        // ===============================
        $settings = SystemSetting::first();
        $dayName = $this->translateDay($now->format('l'));
        $globalTolerance = $settings->tolerance_minutes ?? 15;

        $activeSchedule = Schedule::with('subject')
            ->where('class_id', $matchedStudent->class_id)
            ->where('day_of_week', $dayName)
            ->where('start_time', '<=', $now->copy()->addMinutes(30)->toTimeString())
            ->where('end_time', '>=', $now->toTimeString())
            ->first();

        $statusAbsen = 'Hadir';

        if ($activeSchedule) {
            $startTime = Carbon::parse($activeSchedule->start_time);
            if ($now->greaterThan($startTime->addMinutes($globalTolerance))) {
                $statusAbsen = 'Terlambat';
            }
            $logMessage = "Absen Mapel: " . ($activeSchedule->subject->subject_name ?? 'Pelajaran') . " ($statusAbsen)";
        } else {
            $limitMasuk = $settings->late_limit ?? '07:30:00';
            if ($now->toTimeString() > $limitMasuk) {
                $statusAbsen = 'Terlambat';
            }
            $logMessage = "Absen Masuk Sekolah ($statusAbsen)";
        }

        // ===============================
        // SIMPAN ABSENSI
        // ===============================
        $log = AttendanceLog::create([
            'student_nisn' => $matchedStudent->nisn,
            'date' => $now->toDateString(),
            'time_log' => $now->toTimeString(),
            'status' => $statusAbsen,
            'schedule_id' => $activeSchedule?->id
        ]);

        $this->notifyParent($matchedStudent, $statusAbsen, $now->toTimeString(), $activeSchedule);

        return response()->json([
            'status' => 'success',
            'message' => $logMessage,
            'student' => $studentName,
            'data' => [
                'name' => $studentName,
                'nisn' => $matchedStudent->nisn,
                'time' => $log->time_log,
                'status' => $log->status,
                'message' => $logMessage
            ]
        ]);
    }

    // ===============================
    // HITUNG JARAK WAJAH
    // ===============================
    private function euclideanDistance($a, $b)
    {
        $sum = 0;
        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }

        return sqrt($sum);
    }

    // ===============================
    // EKSTRAK DESCRIPTOR DARI FILE
    // ===============================
    private function extractDescriptorFromFile($uploadedFile)
    {
        $tempPath = $uploadedFile->store('temp_faces');
        $fullPath = storage_path('app/private/' . $tempPath);

        try {
            $scriptPath = base_path('face_service.js');
            $resultProcess = Process::env([
                'SystemRoot' => 'C:\\Windows',
                'PATH' => env('PATH', 'C:\\Program Files\\nodejs\\;C:\\Windows\\system32')
            ])->run(['node', $scriptPath, $fullPath]);

            if ($resultProcess->failed()) {
                throw new \Exception("Gagal menjalankan service pengenalan wajah: " . $resultProcess->errorOutput());
            }

            $output = $resultProcess->output();
            $result = null;

            if (preg_match('/JSON_RESULT:(.+)$/m', $output, $matches)) {
                $result = json_decode(trim($matches[1]), true);
            } elseif (preg_match('/\{.*"descriptor".*\}/s', $output, $matches)) {
                $result = json_decode(trim($matches[0]), true);
            } else {
                $result = json_decode($output, true);
            }

            if ($result === null) {
                throw new \Exception("Service wajah gagal/error: " . $output);
            }

            if (isset($result['error'])) {
                throw new \Exception("Error dari model pengenalan: " . $result['error']);
            }

            if (isset($result['success']) && isset($result['descriptor'])) {
                return $result['descriptor'];
            }

            throw new \Exception("Format output tidak valid dari service pengenalan wajah");
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    // ===============================
    // TRANSLATE HARI
    // ===============================
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

    // ===============================
    // NOTIFIKASI ORANG TUA
    // ===============================
    private function notifyParent($student, $status, $time, $schedule = null)
    {
        $student->loadMissing('parent.user', 'user');
        $userParent = $student->parent?->user;
        if (!$userParent) {
            return;
        }

        $context = "Sekolah";
        if ($schedule && $schedule->subject) {
            $context = "Mapel " . $schedule->subject->subject_name;
        }

        $studentName = $student->user?->full_name ?? 'Siswa';
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