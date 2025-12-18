<?php

namespace App\Http\Requests\Api\V1\Parent;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'phone'        => 'nullable|string|max:20',
            'relationship' => 'required|in:Ayah,Ibu,Wali',
            'fcm_token'    => 'nullable|string',
            'gender'       => 'nullable|in:L,P',
        ];
    }
}