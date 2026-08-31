<?php

namespace Database\Seeders;

use App\Models\Sensor;
use Illuminate\Database\Seeder;

class SensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Temperature Sensor', 'type' => 'temperature', 'unit' => '°C'],
            ['name' => 'Humidity Sensor', 'type' => 'humidity', 'unit' => '%'],
            ['name' => 'Smoke Sensor', 'type' => 'smoke', 'unit' => 'ppm'],
            ['name' => 'Flame Sensor', 'type' => 'flame', 'unit' => null],
        ] as $sensor) {
            Sensor::query()->firstOrCreate(
                ['device_id' => 'ESP32_001', 'type' => $sensor['type']],
                [...$sensor, 'device_id' => 'ESP32_001', 'status' => 'active'],
            );
        }
    }
}
