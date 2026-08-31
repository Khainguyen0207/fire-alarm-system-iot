<?php

namespace Tests\Feature;

use App\Events\TelemetryReceived;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_and_access_protected_device_apis(): void
    {
        $this->seed();

        $login = $this->postJson('/api/v1/auth/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'admin@example.com');

        $this->withToken($login->json('data.token'))
            ->getJson('/api/v1/devices')
            ->assertOk()
            ->assertJsonPath('data.0.id', 'ESP32_001');
    }

    public function test_unauthenticated_api_requests_return_a_json_401_response(): void
    {
        $this->get('/api/v1/devices')
            ->assertUnauthorized()
            ->assertExactJson([
                'success' => false,
                'data' => null,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ]);
    }

    public function test_admin_can_manage_devices_sensors_and_setting_values_only(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->firstOrFail());

        $this->postJson('/api/v1/devices', [
            'id' => 'ESP32_002', 'name' => 'Garage Monitor', 'status' => 'active', 'apiKey' => 'a-secure-device-key',
        ])->assertCreated()->assertJsonPath('data.id', 'ESP32_002');

        $device = Device::query()->findOrFail('ESP32_002');
        $this->postJson('/api/v1/devices/ESP32_002/sensors', [
            'name' => 'Garage Temperature', 'type' => 'temperature', 'unit' => 'C', 'status' => 'active',
        ])->assertCreated();

        $sensor = Sensor::query()->where('device_id', $device->id)->firstOrFail();
        $this->patchJson("/api/v1/sensors/{$sensor->id}", ['status' => 'inactive'])
            ->assertOk()->assertJsonPath('data.status', 'inactive');

        $this->patchJson('/api/v1/settings/1', ['value' => '41'])
            ->assertOk()->assertJsonPath('data.value', '41');
        $this->assertDatabaseHas('settings', ['id' => 1, 'key' => 'temperature_warning', 'value' => '41']);
    }

    public function test_ingestion_broadcasts_after_persisting_telemetry(): void
    {
        $this->seed();
        Event::fake([TelemetryReceived::class]);

        $this->withHeader('X-Device-Key', 'development-device-key')
            ->postJson('/api/v1/iot/data', [
                'deviceId' => 'ESP32_001', 'recordedAt' => '2026-08-31T10:30:00+07:00',
                'temperature' => 28, 'humidity' => 60, 'smokePpm' => 10, 'flameDetected' => false,
            ])
            ->assertCreated();

        Event::assertDispatched(TelemetryReceived::class);
    }
}
