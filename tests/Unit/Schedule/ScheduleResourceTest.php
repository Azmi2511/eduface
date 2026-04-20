<?php

namespace Tests\Unit\Schedule;

use Tests\TestCase;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\Request;

class ScheduleResourceTest extends TestCase
{
    public function test_resource_to_array()
    {
        $schedule = new \App\Models\Schedule([
            'id' => 1,
            'day' => 'Monday'
        ]);

        $resource = new ScheduleResource($schedule);

        $array = $resource->toArray(new Request());

        $this->assertArrayHasKey('day', $array);
    }
}