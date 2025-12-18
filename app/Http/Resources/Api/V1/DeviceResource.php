<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'device_name'    => $this->device_name,
            'location'       => $this->location,
            'api_token'      => $this->api_token, // Token dikembalikan agar bisa disalin ke perangkat
            'logs_count'     => $this->whenCounted('attendanceLogs'),
            'created_at'     => $this->created_at,
        ];
    }
}