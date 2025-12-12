<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'subject_name' => $this->subject_name,

            'schedules'    => $this->whenLoaded('schedules'),

            'schedules_count' => $this->schedules()->count(),

            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
