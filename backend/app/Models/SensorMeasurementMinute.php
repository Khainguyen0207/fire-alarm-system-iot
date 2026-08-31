<?php

namespace App\Models;

use Database\Factories\SensorMeasurementMinuteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorMeasurementMinute extends Model
{
    /** @use HasFactory<SensorMeasurementMinuteFactory> */
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'recorded_at',
        'min_value',
        'max_value',
        'avg_value',
        'detected',
        'detected_count',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'min_value' => 'decimal:3',
            'max_value' => 'decimal:3',
            'avg_value' => 'decimal:3',
            'detected' => 'boolean',
        ];
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
