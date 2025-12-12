<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_role' => $this->user_role,
            'message' => $this->message,
            'ann_id' => $this->ann_id,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at,
            'recipient' => $this->whenLoaded('recipient'), // user relation
        ];
    }
}
