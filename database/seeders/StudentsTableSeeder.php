<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;

class StudentsTableSeeder extends Seeder
{
    public function run()
    {
        Student::truncate();

        $user = User::where('role','student')->first();

        Student::create([
            'nisn' => '2025001',
            'user_id' => $user ? $user->id : null,
            'class_id' => null,
            'parent_id' => null,
            'full_name' => 'Siswa Contoh 1',
            'gender' => 'L',
            'photo_path' => null,
            'is_face_registered' => 0,
        ]);

        Student::create([
            'nisn' => '2025002',
            'user_id' => null,
            'class_id' => null,
            'parent_id' => null,
            'full_name' => 'Siswa Contoh 2',
            'gender' => 'P',
            'photo_path' => null,
            'is_face_registered' => 0,
        ]);
    }
}
