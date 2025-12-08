<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\User;

class TeachersTableSeeder extends Seeder
{
    public function run()
    {
        Teacher::truncate();

        $user = User::where('role','teacher')->first();

        Teacher::create([
            'nip' => 'T2025001',
            'user_id' => $user ? $user->id : null,
            'full_name' => 'Guru Contoh 1',
            'phone_number' => '081234567890',
        ]);
    }
}
