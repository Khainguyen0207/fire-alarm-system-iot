<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['key' => 'temperature_warning', 'value' => '40', 'description' => 'Temperature warning threshold in °C.'],
            ['key' => 'temperature_danger', 'value' => '55', 'description' => 'Temperature danger threshold in °C.'],
            ['key' => 'smoke_warning', 'value' => '50', 'description' => 'Smoke warning threshold in ppm.'],
            ['key' => 'smoke_danger', 'value' => '150', 'description' => 'Smoke danger threshold in ppm.'],
        ] as $setting) {
            Setting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
