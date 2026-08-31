<?php

namespace Tests\Feature;

use App\Models\Sensor;
use App\Models\SensorMeasurement;
use App\Models\SensorMeasurementMinute;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateSensorMeasurementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_closed_minutes_and_is_idempotent(): void
    {
        $this->seed();
        $sensors = $this->sensors();
        $firstMinute = CarbonImmutable::parse('2026-08-31T03:30:00Z');

        $this->storeMeasurement($sensors['temperature'], 20, $firstMinute->addSeconds(5));
        $this->storeMeasurement($sensors['temperature'], 30, $firstMinute->addSeconds(45));
        $this->storeMeasurement($sensors['flame'], 1, $firstMinute->addSeconds(10));
        $this->storeMeasurement($sensors['flame'], 0, $firstMinute->addSeconds(40));
        $this->storeMeasurement($sensors['humidity'], 60, $firstMinute->addMinute());

        $this->artisan('telemetry:aggregate-minutes', ['--before' => '2026-08-31T03:31:00Z'])
            ->assertSuccessful();

        $this->assertDatabaseCount((new SensorMeasurementMinute)->getTable(), 2);
        $this->assertDatabaseHas((new SensorMeasurementMinute)->getTable(), [
            'sensor_id' => $sensors['temperature']->id,
            'recorded_at' => '2026-08-31 03:30:00',
            'min_value' => 20,
            'max_value' => 30,
            'avg_value' => 25,
            'detected' => false,
            'detected_count' => 0,
        ]);
        $this->assertDatabaseHas((new SensorMeasurementMinute)->getTable(), [
            'sensor_id' => $sensors['flame']->id,
            'recorded_at' => '2026-08-31 03:30:00',
            'detected' => true,
            'detected_count' => 1,
        ]);

        $this->artisan('telemetry:aggregate-minutes', ['--before' => '2026-08-31T03:31:00Z'])
            ->assertSuccessful();

        $this->assertDatabaseCount((new SensorMeasurementMinute)->getTable(), 2);
    }

    /** @return array<string, Sensor> */
    private function sensors(): array
    {
        return Sensor::query()
            ->where('device_id', 'ESP32_001')
            ->get()
            ->keyBy('type')
            ->all();
    }

    private function storeMeasurement(Sensor $sensor, float $value, CarbonImmutable $recordedAt): void
    {
        SensorMeasurement::query()->create([
            'sensor_id' => $sensor->id,
            'value' => $value,
            'raw' => [],
            'recorded_at' => $recordedAt,
        ]);
    }
}
