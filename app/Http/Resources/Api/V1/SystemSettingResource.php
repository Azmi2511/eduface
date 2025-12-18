<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'school_info' => [
                'name'    => $this->school_name,
                'npsn'    => $this->npsn,
                'address' => $this->address,
                'contact' => [
                    'email' => $this->email,
                    'phone' => $this->phone,
                ],
            ],
            'attendance_config' => [
                'entry_time'        => $this->entry_time,
                'late_limit'        => $this->late_limit,
                'exit_time'         => $this->exit_time,
                'tolerance_minutes' => (int) $this->tolerance_minutes,
            ],
            'face_recognition' => [
                'enabled'      => (bool) $this->face_rec_enabled,
                'min_accuracy' => (int) $this->min_accuracy, // Skala 0-100
            ],
            'notifications' => [
                'on_late'   => (bool) $this->notif_late,
                'on_absent' => (bool) $this->notif_absent,
            ],
            'localization' => [
                'language' => $this->language,
                'timezone' => $this->timezone,
            ]
        ];
    }
}