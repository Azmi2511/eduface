<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Http\Requests\Api\V1\Teacher\StoreRequest;
use App\Http\Requests\Api\V1\Teacher\UpdateRequest;
use App\Http\Resources\Api\V1\TeacherResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Menampilkan daftar guru.
     */
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        // Search by User Fields
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->whereHas('user', fn($q) => $q->where('is_active', $request->boolean('is_active')));
        }

        $teachers = $query->latest()->paginate($request->get('per_page', 10));
        return TeacherResource::collection($teachers);
    }

    /**
     * Menambahkan guru baru.
     */
    public function store(StoreRequest $request)
    {
        try {
            $teacher = DB::transaction(function () use ($request) {
                $teacherCode = $this->generateTeacherCode();
                
                return Teacher::create([
                    'user_id'           => $request->user_id,
                    'nip'               => $request->nip,
                    'employment_status' => $request->employment_status,
                    'teacher_code'      => $teacherCode,
                ]);
            });

            return (new TeacherResource($teacher->load('user')))
                ->additional(['message' => 'Guru berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambah guru', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail guru.
     */
    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher->load('user'));
    }

    /**
     * Memperbarui data guru.
     */
    public function update(UpdateRequest $request, Teacher $teacher)
    {
        try {
            DB::transaction(function () use ($request, $teacher) {
                // Update User Info
                $teacher->user->update($request->only([
                    'full_name', 'email', 'phone', 'is_active'
                ]));

                // Update Teacher Info
                $teacher->update($request->only([
                    'nip', 'employment_status', 'teacher_code'
                ]));
            });

            return (new TeacherResource($teacher->fresh('user')))
                ->additional(['message' => 'Data guru berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus data guru.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return response()->json(['message' => 'Data guru berhasil dihapus']);
    }

    private function generateTeacherCode()
    {
        $year = date('Y');
        $prefix = "TCH-{$year}-";
        $lastTeacher = Teacher::where('teacher_code', 'like', $prefix . '%')
            ->orderBy('teacher_code', 'desc')
            ->first();
        
        $nextNumber = $lastTeacher ? ((int) substr($lastTeacher->teacher_code, -4)) + 1 : 1;
        $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        while (Teacher::where('teacher_code', $code)->exists()) {
            $nextNumber++;
            $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return $code;
    }
}