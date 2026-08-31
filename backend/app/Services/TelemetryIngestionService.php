<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorMeasurement;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class TelemetryIngestionService
{
    /**
     * @param  array{recordedAt: string, temperature: int|float|string, humidity: int|float|string, smokePpm: int|float|string, flameDetected: bool|int|string}  $telemetry
     * @param  array<string, mixed>  $rawPayload
     * @return array{device: Device, recordedAt: CarbonImmutable, temperature: float, humidity: float, smokePpm: float, flameDetected: bool, state: string, metadata: array<string, mixed>}
     */
    public function ingest(Device $device, array $telemetry, array $rawPayload): array
    {
        return DB::transaction(function () use ($device, $telemetry, $rawPayload): array {
            $sensors = $device->sensors()
                ->where('status', 'active')
                ->get()
                ->keyBy('type');

            foreach (['temperature', 'humidity', 'smoke', 'flame'] as $sensorType) {
                if (! $sensors->has($sensorType)) {
                    throw new LogicException("The device is missing an active {$sensorType} sensor.");
                }
            }

            $recordedAt = CarbonImmutable::parse($telemetry['recordedAt'])->utc();
            $temperature = (float) $telemetry['temperature'];
            $humidity = (float) $telemetry['humidity'];
            $smokePpm = (float) $telemetry['smokePpm'];
            $flameDetected = (bool) $telemetry['flameDetected'];

            $this->storeMeasurements(
                $sensors,
                [
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'smoke' => $smokePpm,
                    'flame' => $flameDetected ? 1.0 : 0.0,
                ],
                $rawPayload,
                $recordedAt,
            );

            $state = $this->determineState($temperature, $smokePpm, $flameDetected);

            return [
                'device' => $device,
                'recordedAt' => $recordedAt,
                'temperature' => $temperature,
                'humidity' => $humidity,
                'smokePpm' => $smokePpm,
                'flameDetected' => $flameDetected,
                'state' => $state,
                'metadata' => array_diff_key($rawPayload, array_flip([
                    'deviceId',
                    'recordedAt',
                    'temperature',
                    'humidity',
                    'smokePpm',
                    'flameDetected',
                ])),
            ];
        });
    }

    /**
     * @param  Collection<string, Sensor>  $sensors
     * @param  array<string, float>  $values
     * @param  array<string, mixed>  $rawPayload
     */
    private function storeMeasurements(Collection $sensors, array $values, array $rawPayload, CarbonImmutable $recordedAt): void
    {
        foreach ($values as $sensorType => $value) {
            SensorMeasurement::query()->create([
                'sensor_id' => $sensors->get($sensorType)->id,
                'value' => $value,
                'raw' => $rawPayload,
                'recorded_at' => $recordedAt,
            ]);
        }
    }

    private function determineState(float $temperature, float $smokePpm, bool $flameDetected): string
    {
        $thresholds = Setting::query()
            ->whereIn('key', [
                'temperature_warning',
                'temperature_danger',
                'smoke_warning',
                'smoke_danger',
            ])
            ->pluck('value', 'key');

        foreach (['temperature_warning', 'temperature_danger', 'smoke_warning', 'smoke_danger'] as $key) {
            if (! $thresholds->has($key)) {
                throw new LogicException("The {$key} setting is missing.");
            }
        }

        if (
            $temperature >= (float) $thresholds->get('temperature_danger')
            && $smokePpm >= (float) $thresholds->get('smoke_danger')
            && $flameDetected
        ) {
            return 'DANGER';
        }

        if (
            $temperature >= (float) $thresholds->get('temperature_warning')
            || $smokePpm >= (float) $thresholds->get('smoke_warning')
        ) {
            return 'WARNING';
        }

        return 'NORMAL';
    }
}
