<?php

namespace App\Http\Requests\Api\V1\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'class_name'    => 'sometimes|string|max:100',
            'grade_level'   => 'sometimes|integer|min:1|max:12',
            'academic_year' => 'sometimes|string|max:20',
        ];
    }
}