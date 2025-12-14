<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Subject;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SystemSettingSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,
            ParentSeeder::class,
            PermissionSeeder::class,
            SchoolClassSeeder::class,
            SubjectSeeder::class,
            ScheduleSeeder::class,
            DeviceSeeder::class,
            AttendanceLogSeeder::class,
            AnnouncementSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}