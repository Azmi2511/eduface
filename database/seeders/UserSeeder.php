<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin
        User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'full_name' => 'Administrator',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => 1,
        ]);

        // Random users
        User::factory()->count(20)->create();
    }
}
