<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'profile_picture' => null,
            'role' => $this->faker->randomElement(['admin','teacher','student','parent']),
            'is_active' => 1,
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }
}