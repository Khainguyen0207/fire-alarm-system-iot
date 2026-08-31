<?php

namespace Database\Factories;

use App\Models\Sensor;
use App\Models\SensorMeasurementMinute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorMeasurementMinute>
 */
class SensorMeasurementMinuteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sensor_id' => Sensor::factory(),
            'recorded_at' => fake()->dateTimeBetween('-1 day')->setTime(fake()->numberBetween(0, 23), fake()->numberBetween(0, 59)),
            'min_value' => 20,
            'max_value' => 30,
            'avg_value' => 25,
            'detected' => false,
            'detected_count' => 0,
        ];
    }
}
