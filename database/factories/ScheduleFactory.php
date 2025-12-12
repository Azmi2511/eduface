<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = \App\Models\Schedule::class;

    public function definition()
    {
        return [
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),

            'day_of_week' => $this->faker->randomElement([
                'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
            ]),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
        ];
    }
}