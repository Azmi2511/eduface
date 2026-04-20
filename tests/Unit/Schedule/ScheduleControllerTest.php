<?php

namespace Tests\Unit\Schedule;

use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    public function test_index_endpoint()
    {
        $response = $this->get('/api/v1/schedules');
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}