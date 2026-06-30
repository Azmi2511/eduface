<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    // ===============================
    // REGISTER WAJAH
    // ===============================
    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|exists:students,nisn',
            'descriptor' => 'required|array'
        ]);

        $student = Student::where('nisn', $request->nisn)->first();

        // Simpan descriptor sebagai JSON
        $student->face_descriptor = $request->descriptor;
        $student->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Wajah berhasil diregistrasi'
        ]);
    }

    // ===============================
    // ABSENSI (VERIFY)
    // ===============================
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

        // ===============================
        // TIDAK DITEMUKAN
        // ===============================
        if (!$matchedStudent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali'
            ]);
        }

        // ===============================
        // CEK DUPLIKAT
        // ===============================
        $already = AttendanceLog::where('student_nisn', $matchedStudent->nisn)
            ->whereDate('date', today())
            ->exists();

        if ($already) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Sudah absen hari ini',
                'student' => $matchedStudent->full_name
            ]);
        }

        // ===============================
        // STATUS HADIR / TERLAMBAT
        // ===============================
        $limitTime = Carbon::createFromTime(7, 0, 0);
        $statusAbsen = now()->greaterThan($limitTime) ? 'Terlambat' : 'Hadir';

        // ===============================
        // SIMPAN ABSENSI
        // ===============================
        $log = AttendanceLog::create([
            'student_nisn' => $matchedStudent->nisn,
            'date' => today(),
            'time_log' => now(),
            'status' => $statusAbsen
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $matchedStudent->full_name,
                'nisn' => $matchedStudent->nisn,
                'time' => $log->time_log,
                'status' => $log->status
            ]
        ]);
    }

    // ===============================
    // HITUNG JARAK WAJAH
    // ===============================
    private function euclideanDistance($a, $b)
    {
        $sum = 0;

        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }

        return sqrt($sum);
    }
}