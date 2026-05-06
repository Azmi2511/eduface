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
            'user'            => new UserResource($this->whenLoaded('user')),
            'class'           => new SchoolClassResource($this->whenLoaded('schoolClass')),
            'parent'          => new ParentResource($this->whenLoaded('parent')),
            'created_at'      => $this->created_at,
        ];
    }
}