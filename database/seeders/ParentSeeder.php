<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParentProfile;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        ParentProfile::factory()->count(20)->create();
    }
}
