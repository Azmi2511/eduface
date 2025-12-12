<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'email'         => $this->email,
            'full_name'     => $this->full_name,
            'phone'         => $this->phone,
            'profile_picture' => $this->profile_picture,
            'role'          => $this->role,
            'is_active'     => $this->is_active,

            // Include profiles
            'teacher'       => $this->whenLoaded('teacher'),
            'student'       => $this->whenLoaded('student'),
            'parent'        => $this->whenLoaded('parentProfile'),

            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
