<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(['role' => 'teacher']),
            'nip' => $this->faker->unique()->numerify('1980####'),
            'employment_status' => $this->faker->randomElement(['PNS','Honorer','Kontrak']),
            'teacher_code' => strtoupper($this->faker->bothify('TC###')),
        ];
    }
}