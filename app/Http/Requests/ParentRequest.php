<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:parents,user_id,' . $this->id,
            'relationship' => 'nullable|in:Ayah,Ibu,Wali',
            'fcm_token' => 'nullable|string',
        ];
    }
}
