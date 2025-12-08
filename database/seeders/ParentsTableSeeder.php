<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParentModel;
use App\Models\User;

class ParentsTableSeeder extends Seeder
{
    public function run()
    {
        ParentModel::truncate();

        $user = User::where('role','parent')->first();

        ParentModel::create([
            'user_id' => $user ? $user->id : null,
            'full_name' => 'Ortu Contoh 1',
            'phone_number' => '081298765432',
            'fcm_token' => null,
        ]);
    }
}
