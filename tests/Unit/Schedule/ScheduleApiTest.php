<?php
namespace Tests\Feature\Schedule;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_schedule_with_valid_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subject = Subject::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $teacher = Teacher::factory()->create();

        $response = $this->postJson('/api/v1/schedules', [
            'subject_id' => $subject->id,
            'class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('schedules', [
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    /** @test */
    public function schedule_deletion_works_correctly()
    {
        $schedule = Schedule::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/schedules/{$schedule->id}")
             ->assertOk();

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }
}