<?php

namespace App\Services;

use App\Models\Device;
use App\Models\SensorMeasurement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MeasurementQueryService
{
    /**
     * @return array{device: Device, recordedAt: CarbonImmutable, temperature: float, humidity: float, smokePpm: float, flameDetected: bool, state: string, metadata: array<string, mixed>}|null
     */
    public function latest(Device $device, FireStateService $fireStateService): ?array
    {
        $latestMeasurement = SensorMeasurement::query()
            ->whereHas('sensor', fn ($query) => $query->where('device_id', $device->id))
            ->latest('recorded_at')
            ->first();

        if ($latestMeasurement === null) {
            return null;
        }

        $measurements = SensorMeasurement::query()
            ->with('sensor:id,type')
            ->whereHas('sensor', fn ($query) => $query->where('device_id', $device->id))
            ->where('recorded_at', $latestMeasurement->recorded_at)
            ->get()
            ->keyBy(fn (SensorMeasurement $measurement): string => $measurement->sensor->type);

        if (! $this->hasAllSensorTypes($measurements)) {
            return null;
        }

        $temperature = (float) $measurements->get('temperature')->value;
        $smokePpm = (float) $measurements->get('smoke')->value;
        $flameDetected = (float) $measurements->get('flame')->value > 0;

        return [
            'device' => $device,
            'recordedAt' => $latestMeasurement->recorded_at->toImmutable(),
            'temperature' => $temperature,
            'humidity' => (float) $measurements->get('humidity')->value,
            'smokePpm' => $smokePpm,
            'flameDetected' => $flameDetected,
            'state' => $fireStateService->determine($temperature, $smokePpm, $flameDetected),
            'metadata' => array_diff_key($latestMeasurement->raw, array_flip([
                'deviceId',
                'recordedAt',
                'temperature',
                'humidity',
                'smokePpm',
                'flameDetected',
            ])),
        ];
    }

    /** @param Collection<string, SensorMeasurement> $measurements */
    private function hasAllSensorTypes(Collection $measurements): bool
    {
        return $measurements->has(['temperature', 'humidity', 'smoke', 'flame']);
    }
}
