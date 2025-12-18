<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'subject_name' => $this->subject_name,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}