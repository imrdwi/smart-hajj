<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\LocationReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocationReport>
 */
class LocationReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'latitude' => fake()->latitude(21.38, 21.46),
            'longitude' => fake()->longitude(39.80, 39.90),
            'accuracy_meters' => fake()->randomFloat(1, 3, 50),
            'recorded_at' => now(),
        ];
    }
}
