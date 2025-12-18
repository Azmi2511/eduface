<?php

namespace App\Http\Requests\Api\V1\Parent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $parentId = $this->route('parent'); // Mengambil ID dari instance ParentProfile
        $userId = \App\Models\ParentProfile::find($parentId)?->user_id;

        return [
            'full_name'    => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:users,email,' . $userId,
            'password'     => 'nullable|string|min:6',
            'phone'        => 'nullable|string|max:20',
            'relationship' => 'sometimes|in:Ayah,Ibu,Wali',
            'fcm_token'    => 'nullable|string',
            'gender'       => 'nullable|in:L,P',
            'is_active'    => 'sometimes|boolean',
        ];
    }
}