<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'school_name'        => $this->school_name,
            'npsn'               => $this->npsn,
            'address'            => $this->address,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'language'           => $this->language,
            'timezone'           => $this->timezone,

            'entry_time'         => $this->entry_time,
            'late_limit'         => $this->late_limit,
            'exit_time'          => $this->exit_time,
            'tolerance_minutes'  => $this->tolerance_minutes,

            'face_rec_enabled'   => (bool) $this->face_rec_enabled,
            'min_accuracy'       => $this->min_accuracy,

            'notif_late'         => (bool) $this->notif_late,
            'notif_absent'       => (bool) $this->notif_absent,

            'updated_at'         => $this->updated_at,
        ];
    }
}
