<?php

namespace Tests\Feature;

use App\Models\SensorMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IoTDataIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_device_stores_all_sensor_measurements_and_computes_danger(): void
    {
        $this->seed();

        $payload = $this->payload([
            'temperature' => 56.5,
            'smokePpm' => 151.25,
            'flameDetected' => true,
            'state' => 'NORMAL',
            'flameConfirmed' => false,
        ]);

        $response = $this->withHeader('X-Device-Key', 'development-device-key')
            ->postJson('/api/iot/data', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.state', 'DANGER')
            ->assertJsonPath('data.recordedAt', '2026-08-31T10:30:00+07:00')
            ->assertJsonPath('data.metadata.state', 'NORMAL')
            ->assertJsonPath('data.metadata.flameConfirmed', false);

        $this->assertDatabaseCount((new SensorMeasurement)->getTable(), 4);
        $this->assertDatabaseHas((new SensorMeasurement)->getTable(), [
            'value' => 56.5,
            'recorded_at' => '2026-08-31 03:30:00',
        ]);

        $this->assertSame($payload, SensorMeasurement::query()->firstOrFail()->raw);
    }

    public function test_warning_is_based_on_temperature_or_smoke_when_danger_is_not_met(): void
    {
        $this->seed();

        $response = $this->withHeader('X-Device-Key', 'development-device-key')
            ->postJson('/api/iot/data', $this->payload([
                'temperature' => 42,
                'smokePpm' => 10,
                'flameDetected' => false,
            ]));

        $response->assertCreated()->assertJsonPath('data.state', 'WARNING');
    }

    public function test_normal_is_returned_when_no_threshold_is_met(): void
    {
        $this->seed();

        $response = $this->withHeader('X-Device-Key', 'development-device-key')
            ->postJson('/api/iot/data', $this->payload());

        $response->assertCreated()->assertJsonPath('data.state', 'NORMAL');
    }

    public function test_a_device_key_is_required(): void
    {
        $this->seed();

        $response = $this->postJson('/api/iot/data', $this->payload());

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The device credentials are invalid.');
    }

    public function test_invalid_payload_uses_the_standard_api_error_shape(): void
    {
        $this->seed();

        $response = $this->withHeader('X-Device-Key', 'development-device-key')
            ->postJson('/api/iot/data', $this->payload(['humidity' => 101]));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonStructure(['errors' => ['humidity']]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'deviceId' => 'ESP32_001',
            'recordedAt' => '2026-08-31T10:30:00+07:00',
            'temperature' => 28.5,
            'humidity' => 65,
            'smokePpm' => 12.75,
            'flameDetected' => false,
            'fan' => false,
            'pump' => false,
            'buzzer' => false,
        ], $overrides);
    }
}
