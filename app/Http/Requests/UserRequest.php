<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|max:100|unique:users,username,' . $this->id,
            'email' => 'nullable|email|unique:users,email,' . $this->id,
            'password' => $this->id ? 'nullable|min:6' : 'required|min:6',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'profile_picture' => 'nullable|string|max:255',
            'role' => 'required|in:admin,teacher,student,parent',
            'is_active' => 'boolean',
        ];
    }
}
