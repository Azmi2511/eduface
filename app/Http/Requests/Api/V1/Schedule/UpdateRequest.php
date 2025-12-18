<?php

namespace App\Http\Requests\Api\V1\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Gunakan 'sometimes' agar field tidak wajib dikirim semua saat update
            'class_id'    => 'sometimes|exists:classes,id',
            'subject_id'  => 'sometimes|exists:subjects,id',
            'teacher_id'  => 'sometimes|exists:teachers,id',
            
            'day_of_week' => 'sometimes|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time'  => 'sometimes|date_format:H:i',
            // End time harus setelah start_time jika keduanya dikirim
            'end_time'    => 'sometimes|date_format:H:i|after:start_time',
        ];
    }
}