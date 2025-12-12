<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = \App\Models\SchoolClass::class;

    public function definition()
    {
        return [
            'class_name' => $this->faker->randomElement([
                'X IPA 1','X IPA 2','X IPS 1',
                'XI IPA 1','XI IPS 1',
                'XII IPA 1','XII IPS 1'
            ]),
            'grade_level' => $this->faker->numberBetween(10, 12),
            'academic_year' => '2024/2025',
        ];
    }
}