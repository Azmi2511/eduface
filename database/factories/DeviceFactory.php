<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = \App\Models\Device::class;

    public function definition()
    {
        return [
            'device_name' => 'Device ' . $this->faker->numerify('##'),
            'location'    => $this->faker->randomElement([
                'Gerbang Utama', 'Pintu Belakang', 'Lobi Sekolah', 'Ruang Guru'
            ]),
            'api_token'   => $this->faker->sha256(),
        ];
    }
}
