<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AnnouncementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'message'         => $this->message,
            'excerpt'         => Str::limit(strip_tags($this->message), 100),
            'attachment'      => [
                'file_name' => $this->attachment_file,
                'file_url'  => $this->attachment_file ? asset('uploads/' . $this->attachment_file) : null,
                'link'      => $this->attachment_link,
            ],
            'sent_at'         => $this->sent_at ? $this->sent_at->format('Y-m-d H:i') : null,
            'recipient_type'  => $this->recipient_id ? 'Specific User' : 'Broadcast',
            'created_at'      => $this->created_at->format('d M Y H:i'),
        ];
    }
}