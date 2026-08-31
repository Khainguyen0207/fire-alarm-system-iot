<?php

namespace App\Http\Resources;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Device $device */
        $device = $this->resource;

        return [
            'id' => $device->id,
            'name' => $device->name,
            'status' => $device->status,
            'installedAt' => $device->installed_at?->setTimezone('Asia/Ho_Chi_Minh')->toIso8601String(),
            'sensors' => SensorResource::collection($this->whenLoaded('sensors')),
        ];
    }
}
