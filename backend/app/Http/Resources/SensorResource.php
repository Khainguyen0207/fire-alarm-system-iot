<?php

namespace App\Http\Resources;

use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Sensor $sensor */
        $sensor = $this->resource;

        return [
            'id' => $sensor->id,
            'deviceId' => $sensor->device_id,
            'name' => $sensor->name,
            'type' => $sensor->type,
            'unit' => $sensor->unit,
            'status' => $sensor->status,
        ];
    }
}
