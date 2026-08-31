<?php

namespace Tests\Feature;

use App\Models\Sensor;
use App\Models\SensorMeasurement;
use App\Models\SensorMeasurementMinute;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeasurementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_latest_complete_telemetry_with_the_authoritative_state(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->firstOrFail());
        $recordedAt = CarbonImmutable::parse('2026-08-31T03:30:00Z');

        $this->storeTelemetry($recordedAt, 56, 65, 151, true);

        $this->getJson('/api/devices/ESP32_001/measurements/latest')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recordedAt', '2026-08-31T10:30:00+07:00')
            ->assertJsonPath('data.temperature', 56)
            ->assertJsonPath('data.state', 'DANGER');
    }

    public function test_it_lists_raw_and_minute_measurements_with_filters(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->firstOrFail());
        $recordedAt = CarbonImmutable::parse('2026-08-31T03:30:00Z');
        $sensors = $this->sensors();

        $this->storeMeasurement($sensors['temperature'], 25, $recordedAt);
        $this->storeMeasurement($sensors['smoke'], 12, $recordedAt);
        SensorMeasurementMinute::query()->create([
            'sensor_id' => $sensors['temperature']->id,
            'recorded_at' => $recordedAt,
            'min_value' => 20,
            'max_value' => 30,
            'avg_value' => 25,
            'detected' => false,
            'detected_count' => 0,
        ]);

        $this->getJson('/api/devices/ESP32_001/measurements?sensorType=temperature')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sensorType', 'temperature')
            ->assertJsonPath('data.0.raw.deviceId', 'ESP32_001')
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/devices/ESP32_001/measurements/minutes?sensorType=temperature')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.avg', 25)
            ->assertJsonPath('data.0.detected', false);
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

    private function storeTelemetry(CarbonImmutable $recordedAt, float $temperature, float $humidity, float $smokePpm, bool $flameDetected): void
    {
        $sensors = $this->sensors();
        $payload = [
            'deviceId' => 'ESP32_001',
            'recordedAt' => $recordedAt->toIso8601String(),
            'temperature' => $temperature,
            'humidity' => $humidity,
            'smokePpm' => $smokePpm,
            'flameDetected' => $flameDetected,
            'fan' => false,
        ];

        $this->storeMeasurement($sensors['temperature'], $temperature, $recordedAt, $payload);
        $this->storeMeasurement($sensors['humidity'], $humidity, $recordedAt, $payload);
        $this->storeMeasurement($sensors['smoke'], $smokePpm, $recordedAt, $payload);
        $this->storeMeasurement($sensors['flame'], $flameDetected ? 1 : 0, $recordedAt, $payload);
    }

    /** @param array<string, mixed>|null $raw */
    private function storeMeasurement(Sensor $sensor, float $value, CarbonImmutable $recordedAt, ?array $raw = null): void
    {
        SensorMeasurement::query()->create([
            'sensor_id' => $sensor->id,
            'value' => $value,
            'raw' => $raw ?? ['deviceId' => 'ESP32_001'],
            'recorded_at' => $recordedAt,
        ]);
    }
}
