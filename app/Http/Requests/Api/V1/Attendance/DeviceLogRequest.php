<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class DeviceLogRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'nisn'      => 'required|exists:students,nisn',
            'api_token' => 'required|exists:devices,api_token', // Validasi token perangkat
        ];
    }
}