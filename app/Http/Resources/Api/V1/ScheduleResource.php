<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'day_of_week' => $this->day_of_week,
            'time_range'  => substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5),
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'subject'     => [
                'id'   => $this->subject?->id ?? 0,
                'name' => $this->subject?->subject_name ?? 'Tanpa Mapel',
            ],
            'class'       => [
                'id'   => $this->class?->id ?? 0,
                'name' => $this->class?->class_name ?? 'Tanpa Kelas',
                'level'=> $this->class?->grade_level ?? 0,
            ],
            'teacher'     => [
                'id'        => $this->teacher?->id ?? 0,
                'full_name' => $this->teacher?->user?->full_name ?? 'Guru Tidak Terdaftar',
                'code'      => $this->teacher?->teacher_code ?? '-',
            ],
        ];
    }
}