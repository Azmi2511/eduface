<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParentProfileFactory extends Factory
{
    protected $model = \App\Models\ParentProfile::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(['role' => 'parent']),
            'relationship' => $this->faker->randomElement(['Ayah','Ibu','Wali']),
            'fcm_token' => null,
        ];
    }
}
