<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'student'      => [
                'nisn'      => $this->student_nisn,
                'name'      => $this->student->user->full_name ?? null,
                'class'     => $this->student->class->class_name ?? null,
            ],
            'schedule'     => [
                'id'        => $this->schedule_id,
                'subject'   => $this->schedule->subject->subject_name ?? 'Masuk Sekolah (Umum)',
                'start'     => $this->schedule->start_time ?? null,
            ],
            'attendance'   => [
                'date'      => $this->date,
                'time'      => $this->time_log,
                'status'    => $this->status,
                'method'    => $this->device_id ? 'Device' : 'Manual',
            ],
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}