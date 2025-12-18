<?php

namespace App\Http\Requests\Api\V1\Device;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'device_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
            // Token opsional di request, jika tidak diisi kita generate otomatis
            'api_token'   => 'nullable|string|max:255|unique:devices,api_token',
        ];
    }
}