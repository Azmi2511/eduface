<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::truncate();

        User::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'is_active' => 1,
            'email' => 'admin@example.com',
        ]);

        $teacher = User::create([
            'username' => 'teacher1',
            'password' => Hash::make('password'),
            'full_name' => 'Guru Satu',
            'role' => 'teacher',
            'is_active' => 1,
            'email' => 'teacher@example.com',
        ]);

        $parent = User::create([
            'username' => 'parent1',
            'password' => Hash::make('password'),
            'full_name' => 'Orang Tua Satu',
            'role' => 'parent',
            'is_active' => 1,
            'email' => 'parent@example.com',
        ]);

        $studentUser = User::create([
            'username' => 'student1',
            'password' => Hash::make('password'),
            'full_name' => 'Siswa Satu',
            'role' => 'student',
            'is_active' => 1,
            'email' => 'student@example.com',
        ]);
    }
}
