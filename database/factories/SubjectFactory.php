<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = \App\Models\Subject::class;

    public function definition()
    {
        return [
            'subject_name' => $this->faker->randomElement([
                'Matematika',
                'Fisika',
                'Kimia',
                'Biologi',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'Sosiologi',
                'Ekonomi',
                'Geografi'
            ]),
        ];
    }
}
