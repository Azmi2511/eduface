<?php

namespace Tests\Unit\Schedule;

use Tests\TestCase;
use App\Http\Requests\ScheduleUpdateRequest;
use Illuminate\Support\Facades\Validator;

class ScheduleUpdateRequestTest extends TestCase
{
    public function test_validation()
    {
        $data = ['day' => 'Monday'];

        $request = new ScheduleUpdateRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }
}