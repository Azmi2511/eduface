<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AnnouncementResource extends JsonResource
{
    public function toArray($request)
    {
        $date = Carbon::parse($this->sent_at)->locale('id');

        return [
            'id' => $this->id,
            'message' => $this->message,
            'recipient' => $this->recipient,
            'recipient_id' => $this->recipient_id,
            'attachment' => [
                'file' => $this->attachment_file ? asset('uploads/' . $this->attachment_file) : null,
                'file_name' => $this->attachment_file ? Str::after($this->attachment_file, '_') : null,
                'link' => $this->attachment_link
            ],
            'sent_at' => [
                'raw' => $this->sent_at,
                'formatted' => [
                    'day' => $date->format('d'),
                    'month_year' => $date->isoFormat('MMM Y'),
                    'full' => $date->isoFormat('D MMMM Y'),
                    'time' => $date->format('H:i'),
                ]
            ],
            'created_at' => $this->created_at
        ];
    }
}