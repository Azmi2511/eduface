<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'username'        => 'required|string|max:50|unique:users,username',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'full_name'       => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'dob'             => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'role'            => 'required|in:admin,teacher,student,parent',
            'is_active'       => 'boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}