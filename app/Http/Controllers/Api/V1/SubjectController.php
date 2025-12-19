<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Http\Requests\Api\V1\Subject\StoreRequest;
use App\Http\Requests\Api\V1\Subject\UpdateRequest;
use App\Http\Resources\Api\V1\SubjectResource;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Menampilkan daftar mata pelajaran.
     */
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $query->where('subject_name', 'like', "%{$request->search}%");
        }

        $subjects = $query->orderBy('subject_name', 'asc')->get();
        return SubjectResource::collection($subjects);
    }

    /**
     * Menambahkan mata pelajaran baru.
     */
    public function store(StoreRequest $request)
    {
        $subject = Subject::create($request->validated());
        return (new SubjectResource($subject))
            ->additional(['message' => 'Mata pelajaran berhasil ditambahkan']);
    }

    /**
     * Menampilkan detail mata pelajaran.
     */
    public function show(Subject $subject)
    {
        return new SubjectResource($subject);
    }

    /**
     * Memperbarui data mata pelajaran.
     */
    public function update(UpdateRequest $request, Subject $subject)
    {
        $subject->update($request->validated());
        return (new SubjectResource($subject))
            ->additional(['message' => 'Mata pelajaran berhasil diperbarui']);
    }

    /**
     * Menghapus data mata pelajaran.
     */
    public function destroy(Subject $subject)
    {
        // Cek jika sudah digunakan di jadwal
        if ($subject->schedules()->exists()) {
            return response()->json([
                'message' => 'Mata pelajaran tidak dapat dihapus karena sudah digunakan dalam jadwal.'
            ], 422);
        }

        $subject->delete();
        return response()->json(['message' => 'Mata pelajaran berhasil dihapus']);
    }
}