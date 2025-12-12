<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeviceRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'device_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
            'api_token'   => 'required|string|max:255',
        ];
    }
}
