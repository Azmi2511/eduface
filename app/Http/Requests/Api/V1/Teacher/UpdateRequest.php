<?php

namespace App\Http\Requests\Api\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        // $this->teacher merujuk pada parameter route {teacher}
        $teacher = $this->route('teacher');
        $userId = $teacher->user_id;

        return [
            'nip'               => 'required|string|max:50|unique:teachers,nip,' . $teacher->id,
            'employment_status' => 'nullable|in:PNS,Honorer,Kontrak',
            'teacher_code'      => 'nullable|string|max:50|unique:teachers,teacher_code,' . $teacher->id,
            // Validasi untuk tabel User (Atomic Update)
            'full_name'         => 'sometimes|string|max:255',
            'email'             => 'sometimes|email|unique:users,email,' . $userId,
            'phone'             => 'nullable|string|max:50',
            'is_active'         => 'sometimes|boolean',
        ];
    }
}