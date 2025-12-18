<?php

namespace App\Http\Requests\Api\V1\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $student = $this->route('student'); // Instance model student
        $userId = $student->user_id;

        return [
            'nisn'      => 'required|string|max:50|unique:students,nisn,' . $student->id,
            'full_name' => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $userId,
            'phone'     => 'nullable|string|max:20',
            'dob'       => 'nullable|date',
            'gender'    => 'nullable|in:L,P',
            'class_id'  => 'sometimes|exists:classes,id',
            'parent_id' => 'sometimes|exists:parents,id',
            'is_active' => 'sometimes|boolean',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}