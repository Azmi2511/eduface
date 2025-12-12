<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Sesuaikan bila perlu
    }

    public function rules()
    {
        return [
            'user_id'   => 'nullable|exists:users,id|unique:students,user_id,' . $this->id,
            'nisn'      => 'required|string|max:50|unique:students,nisn,' . $this->id,
            'class_id'  => 'nullable|exists:classes,id',
            'parent_id' => 'nullable|exists:parents,id',
            'dob'       => 'nullable|date',
            'gender'    => 'required|in:L,P',
            'photo_path' => 'nullable|string',
            'face_registered' => 'boolean',
            'face_registered_at' => 'nullable|date',
        ];
    }
}
