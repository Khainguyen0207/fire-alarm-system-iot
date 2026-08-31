<?php

namespace App\Services;

use App\Models\Setting;
use LogicException;

class FireStateService
{
    public function determine(float $temperature, float $smokePpm, bool $flameDetected): string
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
