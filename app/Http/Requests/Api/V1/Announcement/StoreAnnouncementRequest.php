<?php

namespace App\Http\Requests\Api\V1\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'recipient'       => 'required|in:all,student,parent,teacher,specific',
            'user_id'         => 'required_if:recipient,specific|exists:users,id',
            'message'         => 'required|string',
            'datetime_send'   => 'required|date',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'attachment_link' => 'nullable|url'
        ];
    }
}