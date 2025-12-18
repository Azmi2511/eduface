<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'teacher_code'      => $this->teacher_code,
            'nip'               => $this->nip,
            'employment_status' => $this->employment_status,
            'user_details'      => [
                'id'         => $this->user->id,
                'full_name'  => $this->user->full_name,
                'email'      => $this->user->email,
                'phone'      => $this->user->phone,
                'is_active'  => (bool) $this->user->is_active,
                'profile_picture' => $this->user->profile_picture,
            ],
            'created_at'        => $this->created_at->toDateTimeString(),
        ];
    }
}