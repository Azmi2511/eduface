<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'class_name'     => $this->class_name,
            'grade_level'    => (int) $this->grade_level,
            'academic_year'  => $this->academic_year,
            'students_count' => $this->whenCounted('students'),
            'schedules'      => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}