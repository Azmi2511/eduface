<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'school_name'       => 'required|string|max:255',
            'npsn'               => 'nullable|string|max:50',
            'address'            => 'nullable|string',
            'email'              => 'nullable|email',
            'phone'              => 'nullable|string|max:50',
            'language'           => 'nullable|string|max:20',
            'timezone'           => 'nullable|string|max:50',

            'entry_time'         => 'nullable|string|max:10',
            'late_limit'         => 'nullable|string|max:10',
            'exit_time'          => 'nullable|string|max:10',
            'tolerance_minutes'  => 'nullable|integer|min:0|max:120',

            'face_rec_enabled'   => 'boolean',
            'min_accuracy'       => 'nullable|numeric|min:0|max:1',

            'notif_late'         => 'boolean',
            'notif_absent'       => 'boolean',
        ];
    }
}
