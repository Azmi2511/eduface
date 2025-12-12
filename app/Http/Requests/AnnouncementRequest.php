<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'image_url'       => 'nullable|string|max:255',

            'target_audience' => 'required|in:all,teachers,students,parents',
            'is_pinned'       => 'boolean',
        ];
    }
}
