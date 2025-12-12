<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Can manage users'],
            ['name' => 'manage_students', 'description' => 'Can manage students'],
            ['name' => 'manage_teachers', 'description' => 'Can manage teachers'],
            ['name' => 'manage_classes', 'description' => 'Can manage classes'],
            ['name' => 'manage_subjects', 'description' => 'Can manage subjects'],
            ['name' => 'manage_attendance', 'description' => 'Can manage attendance'],
            ['name' => 'manage_announcements', 'description' => 'Can manage announcements'],
            ['name' => 'manage_devices', 'description' => 'Can manage devices'],
        ];

        foreach ($permissions as $item) {
            Permission::create($item);
        }

        // tambahan random
        Permission::factory()->count(10)->create();
    }
}
