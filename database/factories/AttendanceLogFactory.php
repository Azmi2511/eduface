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
            // store student by NISN (string) to match AttendanceLog model
            'student_nisn' => function () {
                $s = Student::inRandomOrder()->first();
                return $s ? $s->nisn : Student::factory()->create()->nisn;
            },
            'schedule_id' => Schedule::factory(),
            'device_id'   => Device::factory(),

            'date' => $this->faker->date(),
            'time_log' => $this->faker->time(),
            'status'     => $this->faker->randomElement(['Hadir', 'Terlambat', 'Alpha']),
        ];
    }
}
