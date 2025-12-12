<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Sesuaikan jika perlu
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:teachers,user_id,' . $this->id,
            'nip' => 'required|string|max:50|unique:teachers,nip,' . $this->id,
            'dob' => 'nullable|date',
            'employment_status' => 'nullable|in:PNS,Honorer,Kontrak',
            'teacher_code' => 'nullable|string|max:50',
        ];
    }
}
