<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'content'   => $this->content,
            'image_url' => $this->image_url,

            'target_audience' => $this->target_audience,
            'is_pinned'       => $this->is_pinned,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
