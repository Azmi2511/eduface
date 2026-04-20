<?php
namespace Tests\Unit\Schedule;

use Tests\TestCase;
use App\Http\Controllers\ScheduleController;

class ScheduleConflictTest extends TestCase
{
    /** @test */
    public function it_detects_overlapping_schedules()
    {
        $controller = new ScheduleController();
        
        $existingSchedule = (object)[
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'day' => 'Monday'
        ];

        // Skenario 1: Tabrakan tepat di tengah
        $this->assertTrue($controller->isConflict($existingSchedule, '09:00', '11:00', 'Monday'));

        // Skenario 2: Jam sama persis
        $this->assertTrue($controller->isConflict($existingSchedule, '08:00', '10:00', 'Monday'));

        // Skenario 3: Tidak tabrakan (setelah jadwal selesai)
        $this->assertFalse($controller->isConflict($existingSchedule, '10:01', '12:00', 'Monday'));

        // Skenario 4: Jam sama tapi hari berbeda
        $this->assertFalse($controller->isConflict($existingSchedule, '08:00', '10:00', 'Tuesday'));
    }
}