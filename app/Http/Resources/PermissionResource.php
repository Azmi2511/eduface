<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'approval_status' => $this->approval_status,
            'status'          => $this->status,
            'created_at'      => $this->created_at,
        ];
    }
}
