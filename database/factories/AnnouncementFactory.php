<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = \App\Models\Announcement::class;

    public function definition()
    {
        return [
            'title'   => $this->faker->sentence(),
            'content' => $this->faker->paragraph(3),
            'image_url' => null,

            'target_audience' => $this->faker->randomElement(['all','teachers','students','parents']),
            'is_pinned'       => $this->faker->boolean(20),
        ];
    }
}
