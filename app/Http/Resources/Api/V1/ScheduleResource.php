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
                'id'   => $this->subject->id,
                'name' => $this->subject->subject_name,
            ],
            'class'       => [
                'id'   => $this->class->id,
                'name' => $this->class->class_name,
                'level'=> $this->class->grade_level,
            ],
            'teacher'     => [
                'id'        => $this->teacher->id,
                'full_name' => $this->teacher->user->full_name,
                'code'      => $this->teacher->teacher_code,
            ],
        ];
    }
}