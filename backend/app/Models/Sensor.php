<?php

namespace App\Models;

use Database\Factories\SensorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    /** @use HasFactory<SensorFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'type',
        'unit',
        'status',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(SensorMeasurement::class);
    }

    public function minuteMeasurements(): HasMany
    {
        return $this->hasMany(SensorMeasurementMinute::class);
    }
}
