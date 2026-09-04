<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Device::query()->delete();
        Device::query()->firstOrCreate(
            ['id' => 'ESP32_001'],
            [
                'name' => 'Kitchen Fire Monitor',
                'status' => 'active',
                'api_key_hash' => Hash::make(env('IOT_DEVICE_KEY', 'development-device-key')),
                'installed_at' => now(),
            ],
        );
    }
}
