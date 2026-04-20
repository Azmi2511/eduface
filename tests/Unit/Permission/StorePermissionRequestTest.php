<?php

namespace Tests\Unit\Permission;

use Tests\TestCase;
use App\Http\Requests\StorePermissionRequest;
use Illuminate\Support\Facades\Validator;

class StorePermissionRequestTest extends TestCase
{
    public function test_validation()
    {
        $data = ['reason' => 'Sick'];

        $request = new StorePermissionRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }
}