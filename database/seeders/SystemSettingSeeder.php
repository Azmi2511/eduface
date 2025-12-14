<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::create([
            'id' => 1,
            'school_name' => 'Sekolah Contoh',
            'npsn' => '12345678',
            'address' => 'Jl. Contoh No. 1',
            'email' => 'info@sekolah.com',
            'phone' => '08123456789',
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',

            'entry_time' => '07:00',
            'late_limit' => '07:15',
            'exit_time' => '16:00',
            'tolerance_minutes' => 5,

            'face_rec_enabled' => true,
            'min_accuracy' => 85,

            'notif_late' => true,
            'notif_absent' => true,
        ]);
    }
}
