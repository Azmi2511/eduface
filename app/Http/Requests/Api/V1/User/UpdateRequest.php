<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $userId = $this->route('user')->id ?? $this->user;
        return [
            'username'        => 'sometimes|string|max:50|unique:users,username,' . $userId,
            'email'           => 'sometimes|email|max:255|unique:users,email,' . $userId,
            'password'        => 'nullable|string|min:8|confirmed',
            'full_name'       => 'sometimes|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'dob'             => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'role'            => 'sometimes|in:admin,teacher,student,parent',
            'is_active'       => 'boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}