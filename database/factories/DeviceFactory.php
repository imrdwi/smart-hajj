<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Pilgrim;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'pilgrim_id' => Pilgrim::factory(),
            'identifier' => fake()->unique()->uuid(),
            'platform' => fake()->randomElement(['android', 'ios']),
            'last_seen_at' => null,
        ];
    }
}
