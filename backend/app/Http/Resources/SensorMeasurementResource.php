<?php

namespace App\Http\Resources;

use App\Models\SensorMeasurement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorMeasurementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SensorMeasurement $measurement */
        $measurement = $this->resource;

        return [
            'id' => $measurement->id,
            'sensorId' => $measurement->sensor_id,
            'sensorType' => $measurement->sensor->type,
            'value' => (float) $measurement->value,
            'recordedAt' => $measurement->recorded_at->setTimezone('Asia/Ho_Chi_Minh')->toIso8601String(),
            'raw' => $measurement->raw,
        ];
    }
}
