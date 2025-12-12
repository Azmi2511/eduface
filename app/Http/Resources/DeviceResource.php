<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'device_name' => $this->device_name,
            'location'    => $this->location,
            'api_token'   => $this->api_token,

            'attendance_logs_count' => $this->attendanceLogs()->count(),

            'attendance_logs' => $this->whenLoaded('attendanceLogs'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
