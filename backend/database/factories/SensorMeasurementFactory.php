<?php

namespace Database\Factories;

use App\Models\Sensor;
use App\Models\SensorMeasurement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorMeasurement>
 */
class SensorMeasurementFactory extends Factory
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
            'value' => fake()->randomFloat(3, 0, 100),
            'raw' => [],
            'recorded_at' => fake()->dateTimeBetween('-1 day'),
        ];
    }
}
