<?php

namespace Tests\Unit\Schedule;

use Tests\TestCase;
use App\Http\Requests\ScheduleStoreRequest;
use Illuminate\Support\Facades\Validator;

class ScheduleStoreRequestTest extends TestCase
{
    public function test_validation_pass()
    {
        $data = [
            'teacher_id' => 1,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00'
        ];

        $request = new ScheduleStoreRequest();

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }
}