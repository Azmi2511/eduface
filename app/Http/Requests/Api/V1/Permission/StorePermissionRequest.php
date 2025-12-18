<?php

namespace App\Http\Requests\Api\V1\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'student_id'      => 'required|exists:students,id',
            'type'            => 'required|in:Sakit,Izin',
            'start_date'      => 'required|date|after_or_equal:today',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'description'     => 'required|string|min:10',
            'proof_file'      => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048', // Max 2MB
        ];
    }
}