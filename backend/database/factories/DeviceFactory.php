<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => 'DEV-'.fake()->unique()->numerify('###'),
            'name' => fake()->company().' Fire Monitor',
            'status' => 'active',
            'api_key_hash' => Hash::make('test-device-key'),
            'installed_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
