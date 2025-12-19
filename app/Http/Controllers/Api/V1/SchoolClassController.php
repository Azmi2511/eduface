<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Http\Requests\Api\V1\SchoolClass\StoreRequest;
use App\Http\Requests\Api\V1\SchoolClass\UpdateRequest;
use App\Http\Resources\Api\V1\SchoolClassResource;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * Menampilkan daftar kelas.
     */
    public function index(Request $request)
    {
        $query = SchoolClass::withCount('students');

        // Search by class name
        if ($request->filled('search')) {
            $query->where('class_name', 'like', '%' . $request->search . '%');
        }

        // Filter by grade
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Filter by academic year
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $classes = $query->orderBy('grade_level', 'asc')
                         ->orderBy('class_name', 'asc')
                         ->paginate($request->get('per_page', 15));

        return SchoolClassResource::collection($classes);
    }

    /**
     * Menambahkan kelas baru.
     */
    public function store(StoreRequest $request)
    {
        try {
            $class = SchoolClass::create($request->validated());
            
            return (new SchoolClassResource($class))
                ->additional(['message' => 'Kelas berhasil dibuat']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat kelas', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail kelas.
     */
    public function show(SchoolClass $schoolClass)
    {
        return new SchoolClassResource($schoolClass->loadCount('students')->load('schedules'));
    }

    /**
     * Memperbarui data kelas.
     */
    public function update(UpdateRequest $request, SchoolClass $schoolClass)
    {
        try {
            $schoolClass->update($request->validated());
            
            return (new SchoolClassResource($schoolClass))
                ->additional(['message' => 'Data kelas berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui kelas', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus kelas.
     */
    public function destroy(SchoolClass $schoolClass)
    {
        if ($schoolClass->students()->exists()) {
            return response()->json([
                'message' => 'Tidak dapat menghapus kelas yang masih memiliki siswa.'
            ], 422);
        }

        $schoolClass->delete();
        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }
}