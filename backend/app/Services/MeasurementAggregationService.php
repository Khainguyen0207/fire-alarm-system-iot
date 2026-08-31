<?php

namespace App\Services;

use App\Models\SensorMeasurement;
use App\Models\SensorMeasurementMinute;
use Carbon\CarbonImmutable;

class MeasurementAggregationService
{
    public function aggregate(CarbonImmutable $before): int
    {
        $currentBucket = null;
        $measurements = [];
        $aggregatedMinutes = 0;

        SensorMeasurement::query()
            ->with('sensor:id,type')
            ->where('recorded_at', '<', $before)
            ->orderBy('sensor_id')
            ->orderBy('recorded_at')
            ->lazy()
            ->each(function (SensorMeasurement $measurement) use (&$currentBucket, &$measurements, &$aggregatedMinutes): void {
                $minute = $measurement->recorded_at->utc()->startOfMinute();
                $bucket = $measurement->sensor_id.'|'.$minute->toDateTimeString();

                if ($currentBucket !== null && $currentBucket !== $bucket) {
                    $this->storeMinute($measurements);
                    $aggregatedMinutes++;
                    $measurements = [];
                }

                $currentBucket = $bucket;
                $measurements[] = $measurement;
            });

        if ($measurements !== []) {
            $this->storeMinute($measurements);
            $aggregatedMinutes++;
        }

        return $aggregatedMinutes;
    }

    /**
     * @param  list<SensorMeasurement>  $measurements
     */
    private function storeMinute(array $measurements): void
    {
        $firstMeasurement = $measurements[0];
        $values = array_map(
            fn (SensorMeasurement $measurement): float => (float) $measurement->value,
            $measurements,
        );
        $isFlameSensor = $firstMeasurement->sensor->type === 'flame';
        $detectedCount = $isFlameSensor
            ? count(array_filter($values, fn (float $value): bool => $value > 0))
            : 0;

        SensorMeasurementMinute::query()->updateOrCreate(
            [
                'sensor_id' => $firstMeasurement->sensor_id,
                'recorded_at' => $firstMeasurement->recorded_at->utc()->startOfMinute(),
            ],
            [
                'min_value' => min($values),
                'max_value' => max($values),
                'avg_value' => array_sum($values) / count($values),
                'detected' => $detectedCount > 0,
                'detected_count' => $detectedCount,
            ],
        );
    }
}
