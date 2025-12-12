<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'nisn'              => $this->nisn,
            'dob'               => $this->dob,
            'gender'            => $this->gender,
            'photo_path'        => $this->photo_path,
            'face_registered'   => $this->face_registered,
            'face_registered_at'=> $this->face_registered_at,

            'user'   => new UserResource($this->whenLoaded('user')),
            'class'  => $this->whenLoaded('class'),
            'parent' => $this->whenLoaded('parent'),

            'attendance_logs_count' => $this->attendanceLogs()->count(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
