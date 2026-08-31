<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Setting $setting */
        $setting = $this->resource;

        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'value' => $setting->value,
            'description' => $setting->description,
        ];
    }
}
