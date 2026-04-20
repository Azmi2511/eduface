<?php

namespace Tests\Unit\Schedule;

use Tests\TestCase;
use App\Models\Schedule;

class ScheduleModelTest extends TestCase
{
    public function test_model_create()
    {
        $schedule = new Schedule([
            'day' => 'Monday'
        ]);

        $this->assertEquals('Monday', $schedule->day);
    }
}