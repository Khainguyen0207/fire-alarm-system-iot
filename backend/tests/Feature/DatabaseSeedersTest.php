<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_the_device_sensors_and_detection_settings_once(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount((new Device)->getTable(), 1);
        $this->assertDatabaseCount((new Sensor)->getTable(), 4);
        $this->assertDatabaseCount((new Setting)->getTable(), 4);
        $this->assertDatabaseHas((new Device)->getTable(), [
            'id' => 'ESP32_001',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas((new Sensor)->getTable(), [
            'device_id' => 'ESP32_001',
            'type' => 'flame',
        ]);
    }
}
