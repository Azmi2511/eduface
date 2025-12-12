<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'nip'               => $this->nip,
            'dob'               => $this->dob,
            'employment_status' => $this->employment_status,
            'teacher_code'      => $this->teacher_code,

            'user'     => new UserResource($this->whenLoaded('user')),
            'schedules'=> $this->whenLoaded('schedules'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
