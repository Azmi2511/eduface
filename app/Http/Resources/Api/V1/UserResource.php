<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'username'        => $this->username,
            'email'           => $this->email,
            'full_name'       => $this->full_name,
            'phone'           => $this->phone,
            'dob'             => $this->dob ? $this->dob->format('Y-m-d') : null,
            'gender'          => $this->gender,
            'role'            => $this->role,
            'is_active'       => (bool) $this->is_active,
            'profile_picture' => $this->profile_picture ? url(Storage::url($this->profile_picture)) : null,
            'relationships'   => [
                'teacher' => new TeacherResource($this->whenLoaded('teacher')),
                'student' => new StudentResource($this->whenLoaded('student')),
                'parent'  => new ParentResource($this->whenLoaded('parentProfile')),
            ],
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}