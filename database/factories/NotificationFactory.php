<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Announcement;

class NotificationFactory extends Factory
{
    protected $model = \App\Models\Notification::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'message' => $this->faker->sentence(),
            'ann_id' => Announcement::factory(),
            'is_read' => $this->faker->boolean(30),
            'created_at' => now(),
        ];
    }
}
