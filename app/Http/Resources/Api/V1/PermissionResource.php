<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'student'         => [
                'id'        => $this->student_id,
                'full_name' => $this->student->user->full_name ?? 'N/A',
            ],
            'type'            => $this->type,
            'date_range'      => [
                'start' => $this->start_date,
                'end'   => $this->end_date,
            ],
            'description'     => $this->description,
            'proof_url' => $this->proof_url ? url(Storage::url($this->proof_url)) : null,
            'approval_status' => $this->approval_status,
            'approved_by'     => $this->approvedBy->full_name ?? null,
            'created_at'      => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}