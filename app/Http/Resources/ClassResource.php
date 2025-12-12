<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'class_name'    => $this->class_name,
            'grade_level'   => $this->grade_level,
            'academic_year' => $this->academic_year,

            'students'  => StudentResource::collection($this->whenLoaded('students')),
            'schedules' => $this->whenLoaded('schedules'),

            'students_count' => $this->students()->count(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
