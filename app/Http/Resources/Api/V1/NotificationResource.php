<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'message'    => $this->message,
            'is_read'    => (bool) $this->is_read,
            'ann_id'     => $this->ann_id,
            // Jika link tidak ada di DB, kita buat secara dinamis dari ann_id
            'link'       => $this->ann_id ? "/announcements/{$this->ann_id}" : null,
            'time_ago'   => $this->created_at ? $this->created_at->diffForHumans() : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}