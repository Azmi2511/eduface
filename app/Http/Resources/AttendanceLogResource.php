<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,

            'status'      => $this->status,
            'timestamp'   => $this->timestamp,

            'face_image_path' => $this->face_image_path,
            'confidence_score'=> $this->confidence_score,

            'student'  => new StudentResource($this->whenLoaded('student')),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'device'   => new DeviceResource($this->whenLoaded('device')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
