<?php

namespace App\Http\Requests\Api\V1\Subject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'subject_name' => 'sometimes|string|max:255|unique:subjects,subject_name,' . $this->route('subject')->id,
        ];
    }
}