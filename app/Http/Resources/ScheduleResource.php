<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'class_id'    => $this->class_id,
            'subject_id'  => $this->subject_id,
            'teacher_id'  => $this->teacher_id,
            'day'         => $this->day,
            'day_of_week' => $this->day_of_week,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'class'       => $this->whenLoaded('class'),
            'subject'     => $this->whenLoaded('subject'),
            'teacher'     => $this->whenLoaded('teacher'),
        ];
    }
}
