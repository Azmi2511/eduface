<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'relationship' => $this->relationship,
            'fcm_token'    => $this->fcm_token,
            'user'         => [
                'id'         => $this->user->id,
                'full_name'  => $this->user->full_name,
                'username'   => $this->user->username,
                'email'      => $this->user->email,
                'phone'      => $this->user->phone,
                'gender'     => $this->user->gender,
                'is_active'  => (bool) $this->user->is_active,
            ],
            // Memuat daftar anak jika relasi students di-eager load
            'students'     => StudentResource::collection($this->whenLoaded('students')),
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}