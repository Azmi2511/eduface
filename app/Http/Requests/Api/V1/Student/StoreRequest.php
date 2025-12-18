<?php

namespace App\Http\Requests\Api\V1\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'user_id'   => 'required|exists:users,id|unique:students,user_id',
            'nisn'      => 'required|string|max:50|unique:students,nisn',
            'full_name' => 'required|string|max:255', // Untuk update data di tabel users
            'class_id'  => 'required|exists:classes,id',
            'parent_id' => 'required|exists:parents,id',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}