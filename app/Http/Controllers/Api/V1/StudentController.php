<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Http\Requests\Api\V1\Student\StoreRequest;
use App\Http\Requests\Api\V1\Student\UpdateRequest;
use App\Http\Resources\Api\V1\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Student::with(['user', 'class', 'parent.user']);

        // Logic RBAC: Teacher only sees their classes' students
        if ($user->role === 'teacher') {
            $query->whereHas('class.schedules.teacher', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Search Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$search}%"));
            });
        }

        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students = $query->latest()->paginate($request->get('per_page', 15));
        return StudentResource::collection($students);
    }

    public function store(StoreRequest $request)
    {
        try {
            $student = DB::transaction(function () use ($request) {
                // Update full name in User table
                User::where('id', $request->user_id)->update([
                    'full_name' => $request->full_name
                ]);

                $data = $request->only(['user_id', 'nisn', 'class_id', 'parent_id']);
                
                if ($request->hasFile('photo')) {
                    $data['photo_path'] = $request->file('photo')->store('students/photos', 'public');
                }

                return Student::create($data);
            });

            return (new StudentResource($student->load(['user', 'class'])))
                ->additional(['message' => 'Siswa berhasil didaftarkan']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambah data', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Student $student)
    {
        return new StudentResource($student->load(['user', 'class', 'parent.user']));
    }

    public function update(UpdateRequest $request, Student $student)
    {
        try {
            DB::transaction(function () use ($request, $student) {
                // Update User Info
                $student->user->update($request->only([
                    'full_name', 'email', 'phone', 'dob', 'gender', 'is_active'
                ]));

                // Update Student Info
                $studentData = $request->only(['nisn', 'class_id', 'parent_id']);
                
                if ($request->hasFile('photo')) {
                    if ($student->photo_path) Storage::disk('public')->delete($student->photo_path);
                    $studentData['photo_path'] = $request->file('photo')->store('students/photos', 'public');
                }

                $student->update($studentData);
            });

            return (new StudentResource($student->fresh(['user', 'class'])))
                ->additional(['message' => 'Data siswa berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Student $student)
    {
        // Jangan hapus user, hanya hapus profile student (atau sesuai kebijakan bisnis)
        if ($student->photo_path) Storage::disk('public')->delete($student->photo_path);
        $student->delete();

        return response()->json(['message' => 'Data profil siswa berhasil dihapus']);
    }
}