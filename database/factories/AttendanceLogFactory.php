<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Student;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLogFactory extends Factory
{
    protected $model = \App\Models\AttendanceLog::class;

    public function definition()
    {
        return [
            'student_id'  => Student::factory(),
            'schedule_id' => Schedule::factory(),
            'device_id'   => Device::factory(),

            'timestamp' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'status'     => $this->faker->randomElement(['present', 'late', 'absent', 'excused']),

            'face_image_path' => null,
            'confidence_score'=> $this->faker->randomFloat(2, 0.0, 1.0),
        ];
    }
}
