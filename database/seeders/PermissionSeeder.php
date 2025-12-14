<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // seed permission requests (Sakit/Izin) for students
        Permission::factory()->count(40)->create();
    }
}
