<?php

namespace App\Http\Requests\Api\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'user_id'           => 'required|exists:users,id|unique:teachers,user_id',
            'nip'               => 'required|string|max:50|unique:teachers,nip',
            'employment_status' => 'nullable|in:PNS,Honorer,Kontrak',
        ];
    }

    public function messages()
    {
        return [
            'user_id.unique' => 'User ini sudah terdaftar sebagai guru.',
            'nip.unique'     => 'NIP sudah digunakan oleh guru lain.',
        ];
    }
}