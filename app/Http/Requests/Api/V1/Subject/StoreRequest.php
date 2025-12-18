<?php

namespace App\Http\Requests\Api\V1\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'subject_name' => 'required|string|max:255|unique:subjects,subject_name',
        ];
    }
}