<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\ParentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(['role' => 'student']),
            'nisn' => $this->faker->unique()->numerify('20########'),
            'class_id' => SchoolClass::factory(),
            'parent_id' => ParentProfile::factory(),
            'dob' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['L','P']),
            'photo_path' => null,
            'face_registered' => false,
            'face_registered_at' => null,
        ];
    }
}
