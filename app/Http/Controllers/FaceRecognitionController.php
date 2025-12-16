<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\AttendanceLog; // Pastikan model ini ada
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    // URL Python API (Pastikan port sesuai dengan uvicorn)
    private $pythonUrl = 'http://127.0.0.1:8001';

    // --- FUNGSI 1: REGISTRASI ---
    public function register(Request $request)
    {
        // Validasi input Laravel
        $request->validate([
            'nisn' => 'required|exists:students,nisn',
            'file' => 'required|image|max:10240', // Max 10MB
            'pose' => 'required', // depan/kiri/kanan
        ]);

        try {
            // Kirim data ke Python /register
            // Python butuh: name, nisn, pose, file
            $student = Student::where('nisn', $request->nisn)->first();

            $response = Http::attach(
                'file', file_get_contents($request->file('file')), $request->file('file')->getClientOriginalName()
            )->post($this->pythonUrl . '/register', [
                'name' => $student->full_name,
                'nisn' => $request->nisn,
                'pose' => $request->pose
            ]);

            $result = $response->json();

            // Jika Python sukses memproses
            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                return response()->json([
                    'status' => 'success',
                    'message' => $result['message']
                ]);
            } else {
                // Jika validasi Python gagal (misal: wajah tak terdeteksi)
                return response()->json([
                    'status' => 'error',
                    'message' => $result['detail'] ?? $result['message'] ?? 'Gagal memproses di Python'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal koneksi ke Server AI (Pastikan face_api.py jalan)'], 500);
        }
    }

    // --- FUNGSI 2: PREDICT (ABSENSI) ---
    public function predict(Request $request)
    {
        $request->validate(['file' => 'required|image']);

        try {
            // Kirim foto ke Python /predict
            $response = Http::attach(
                'file', file_get_contents($request->file('file')), 'scan.jpg'
            )->post($this->pythonUrl . '/predict');

            if ($response->failed()) {
                return response()->json(['status' => 'error', 'message' => 'Server AI Error'], 500);
            }

            $result = $response->json(); // Terima JSON dari Python

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server AI Offline'], 500);
        }

        // Python mengembalikan: { "status": "success", "new_entries": [...], "all_detected": [...] }
        
        if (!isset($result['new_entries']) || empty($result['new_entries'])) {
            // Cek apakah ada wajah tapi statusnya 'ignored' (masih cooldown)
            if (isset($result['all_detected']) && count($result['all_detected']) > 0) {
                 $firstDet = $result['all_detected'][0];
                 if ($firstDet['status'] == 'ignored') {
                     return response()->json([
                         'status' => 'ignored',
                         'student' => $firstDet['name'],
                         'message' => 'Sudah absen barusan (Cooldown).'
                     ]);
                 }
            }
            return response()->json(['status' => 'error', 'message' => 'Wajah tidak dikenal / Belum terdaftar']);
        }

        // LOOP DATA YANG VALID DARI PYTHON
        // Python Script Anda TIDAK melakukan INSERT ke database log, 
        // jadi Laravel yang harus menyimpannya berdasarkan hasil analisa Python.
        
        $savedLog = null;

        foreach ($result['new_entries'] as $entry) {
            // $entry berisi: {'nisn': '...', 'name': '...', 'status': 'recorded', 'timestamp': '...'}
            
            // Tentukan status kehadiran (Hadir/Terlambat)
            // Karena logic jam terlambat ada di Python tapi tidak dikirim eksplisit sebagai string 'Terlambat',
            // Kita bisa hitung ulang simpel di sini atau percaya data Python jika Python mengirim field status kehadiran.
            // *Catatan: Script Python Anda mengembalikan status='recorded', belum membedakan 'Hadir'/'Terlambat' di output JSON finalnya,
            // meskipun di dalamnya sudah dihitung.
            
            // Mari kita hitung ulang status terlambat di Laravel agar aman:
            $limitTime = Carbon::createFromTime(7, 0, 0); // Jam 7 Pagi
            $statusAbsen = now()->greaterThan($limitTime) ? 'Terlambat' : 'Hadir';

            $savedLog = AttendanceLog::create([
                'student_nisn' => $entry['nisn'],
                'date' => today(),
                'time_log' => now(),
                'status' => $statusAbsen
            ]);
        }

        if ($savedLog) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $entry['name'],
                    'nisn' => $entry['nisn'],
                    'time' => now()->format('H:i:s'),
                    'status' => $savedLog->status
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan log']);
    }
}