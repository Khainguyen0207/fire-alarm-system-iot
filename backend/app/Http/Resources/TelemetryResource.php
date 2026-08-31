<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TelemetryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'deviceId' => $this->resource['device']->id,
            'recordedAt' => $this->resource['recordedAt']->setTimezone('Asia/Ho_Chi_Minh')->toIso8601String(),
            'temperature' => $this->resource['temperature'],
            'humidity' => $this->resource['humidity'],
            'smokePpm' => $this->resource['smokePpm'],
            'flameDetected' => $this->resource['flameDetected'],
            'state' => $this->resource['state'],
            'metadata' => $this->resource['metadata'],
        ];
    }
}
