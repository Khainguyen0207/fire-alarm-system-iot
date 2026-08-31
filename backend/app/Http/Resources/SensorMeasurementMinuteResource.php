<?php

namespace App\Http\Resources;

use App\Models\SensorMeasurementMinute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorMeasurementMinuteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SensorMeasurementMinute $measurement */
        $measurement = $this->resource;

        return [
            'sensorId' => $measurement->sensor_id,
            'sensorType' => $measurement->sensor->type,
            'recordedAt' => $measurement->recorded_at->setTimezone('Asia/Ho_Chi_Minh')->toIso8601String(),
            'min' => $measurement->min_value === null ? null : (float) $measurement->min_value,
            'max' => $measurement->max_value === null ? null : (float) $measurement->max_value,
            'avg' => $measurement->avg_value === null ? null : (float) $measurement->avg_value,
            'detected' => $measurement->detected,
            'detectedCount' => $measurement->detected_count,
        ];
    }
}
