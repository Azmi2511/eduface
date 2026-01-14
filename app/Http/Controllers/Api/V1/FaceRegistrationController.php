<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'pose' => 'required|in:depan,kiri,kanan',
            'file' => 'required|image|max:5120', // Max 5MB
        ]);

        $user = $request->user();
        
        if (!$user->student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $student = $user->student;
        $file = $request->file('file');
        
        $filename = $student->nisn . '_' . $request->pose . '.jpg';

        try {
            $path = $file->storeAs('face_archives', $filename, 'public');

            $fastApiUrl = config('services.fastapi.url') . "/register";

            $response = Http::attach(
                'file', file_get_contents($file->getRealPath()), $filename
            )->post($fastApiUrl, [
                'name' => $user->full_name, // Kirim nama
                'nisn' => $student->nisn,   // Kirim NISN
                'pose' => $request->pose,   // Kirim pose
            ]);

            if ($response->successful()) {
                
                $student->update([
                    'face_registered' => true,
                    'face_registered_at' => now(),
                    'photo_path' => ($request->pose == 'depan') ? $path : $student->photo_path
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Wajah berhasil didaftarkan ke sistem AI.',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal di AI Engine: ' . $response->json()['detail'] ?? 'Unknown error',
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}