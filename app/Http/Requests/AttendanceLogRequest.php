<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceLogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'student_id'  => 'required|exists:students,id',
            'schedule_id' => 'required|exists:schedules,id',
            'device_id'   => 'required|exists:devices,id',

            'timestamp'   => 'required|date',

            'status' => 'required|in:present,late,absent,excused',

            'face_image_path' => 'nullable|string|max:255',
            'confidence_score'=> 'nullable|numeric|min:0|max:1',
        ];
    }
}
