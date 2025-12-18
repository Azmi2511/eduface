<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Http\Requests\Api\V1\Schedule\StoreRequest;
use App\Http\Resources\Api\V1\ScheduleResource;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Schedule::with(['class', 'subject', 'teacher.user']);

        // Filter Berdasarkan Role
        if ($user->role === 'teacher') {
            $query->whereHas('teacher', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === 'student') {
            $query->where('class_id', $user->student->class_id);
        } elseif ($user->role === 'parent') {
            // Mengambil jadwal untuk semua anak dari orang tua ini
            $classIds = $user->parentProfile->students->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        // Filter Tambahan
        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $schedules = $query->orderByRaw("FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                          ->orderBy('start_time')
                          ->get();

        return ScheduleResource::collection($schedules);
    }

    public function store(StoreRequest $request)
    {
        // Cek Bentrok (Contoh sederhana untuk Guru)
        $isConflict = Schedule::where('teacher_id', $request->teacher_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->exists();

        if ($isConflict) {
            return response()->json(['message' => 'Guru sudah memiliki jadwal di jam tersebut.'], 422);
        }

        $schedule = Schedule::create($request->validated());
        return (new ScheduleResource($schedule->load(['class', 'subject', 'teacher.user'])))
            ->additional(['message' => 'Jadwal berhasil dibuat']);
    }

    public function show(Schedule $schedule)
    {
        return new ScheduleResource($schedule->load(['class', 'subject', 'teacher.user']));
    }

   public function update(UpdateRequest $request, Schedule $schedule)
    {
        $criticalFields = ['start_time', 'end_time', 'day_of_week', 'teacher_id', 'class_id'];
    
        if ($request->hasAny($criticalFields)) {
            
            $mergedData = (object) array_merge($schedule->toArray(), $request->all());

            if ($this->isConflict($mergedData, $schedule->id)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Perubahan gagal! Jadwal baru menyebabkan bentrok dengan jadwal lain (Guru atau Kelas sudah terisi pada jam tersebut).'
                ], 422);
            }
        }

        $schedule->update($request->validated());

        return (new ScheduleResource($schedule->load(['class', 'subject', 'teacher.user'])))
            ->additional(['message' => 'Jadwal berhasil diperbarui']);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(['message' => 'Jadwal berhasil dihapus']);
    }
    private function isConflict($request, $excludeId = null)
    {
        $query = Schedule::where('day_of_week', $request->day_of_week)
            ->where(function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id)
                ->orWhere('class_id', $request->class_id);
            })
            ->where(function($q) use ($request) {
                $q->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}