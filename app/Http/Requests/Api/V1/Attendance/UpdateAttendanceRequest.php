<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha',
            'time_log'    => 'nullable|date_format:H:i:s',
            'schedule_id' => 'nullable|exists:schedules,id',
        ];
    }
}