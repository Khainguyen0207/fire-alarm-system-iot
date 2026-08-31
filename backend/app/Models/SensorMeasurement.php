<?php

namespace App\Models;

use Database\Factories\SensorMeasurementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorMeasurement extends Model
{
    /** @use HasFactory<SensorMeasurementFactory> */
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'value',
        'raw',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'recorded_at' => 'datetime',
            'value' => 'decimal:3',
        ];
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
