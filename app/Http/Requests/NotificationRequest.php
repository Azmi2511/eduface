<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Atur gate/policy jika perlu
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'user_role' => 'nullable|string|max:50',
            'message' => 'required|string',
            'ann_id' => 'nullable|exists:announcements,id',
            'is_read' => 'nullable|boolean',
        ];
    }
}
