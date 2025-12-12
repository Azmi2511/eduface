<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'relationship' => $this->relationship,
            'fcm_token'    => $this->fcm_token,

            'user'     => new UserResource($this->whenLoaded('user')),
            'students' => $this->whenLoaded('students'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
