<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'nisn'            => $this->nisn,
            'face_registered' => (bool) $this->face_registered,
            'photo_url'       => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'user'            => [
                'id'         => $this->user->id,
                'full_name'  => $this->user->full_name,
                'email'      => $this->user->email,
                'phone'      => $this->user->phone,
                'gender'     => $this->user->gender,
                'dob'        => $this->user->dob,
                'is_active'  => (bool) $this->user->is_active,
            ],
            'class'           => new SchoolClassResource($this->whenLoaded('class')),
            'parent'          => new ParentResource($this->whenLoaded('parent')),
            'created_at'      => $this->created_at,
        ];
    }
}