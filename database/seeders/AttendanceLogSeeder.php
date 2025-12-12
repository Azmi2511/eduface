<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceLog;

class AttendanceLogSeeder extends Seeder
{
    public function run(): void
    {
        AttendanceLog::factory()->count(200)->create();
    }
}
