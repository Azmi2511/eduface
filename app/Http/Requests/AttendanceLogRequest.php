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
            // attendance endpoint uses student_nisn as FK
            'student_nisn'  => 'required|exists:students,nisn',
            'schedule_id' => 'required|exists:schedules,id',
            'device_id'   => 'required|exists:devices,id',

            'timestamp'   => 'required|date',

            // match DB enum values
            'status' => 'required|in:Hadir,Terlambat,Alpha',

            'face_image_path' => 'nullable|string|max:255',
            'confidence_score'=> 'nullable|numeric|min:0|max:1',
        ];
    }
}
