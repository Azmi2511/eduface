<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            StudentsTableSeeder::class,
            TeachersTableSeeder::class,
            ParentsTableSeeder::class,
            ClassesTableSeeder::class,
        ]);
    }
}
