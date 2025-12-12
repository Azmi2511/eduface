<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'class_name'   => 'required|string|max:100',
            'grade_level'  => 'required|integer|min:1|max:12',
            'academic_year'=> 'required|string|max:20',
        ];
    }
}
