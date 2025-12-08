<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;

class ClassesTableSeeder extends Seeder
{
    public function run()
    {
        ClassModel::truncate();

        ClassModel::create([
            'class_name' => 'X IPA 1',
            'grade_level' => 10,
            'academic_year' => '2025/2026',
        ]);

        ClassModel::create([
            'class_name' => 'XI IPS 1',
            'grade_level' => 11,
            'academic_year' => '2025/2026',
        ]);
    }
}
