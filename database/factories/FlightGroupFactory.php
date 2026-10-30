<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FlightGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlightGroup>
 */
class FlightGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'code' => fake()->unique()->bothify('KLT-##??'),
            'airline' => fake()->randomElement(['Garuda Indonesia', 'Saudia', 'Lion Air']),
            'flight_number' => fake()->bothify('??###'),
            'departs_at' => $departure = fake()->dateTimeBetween('+1 month', '+6 months'),
            'returns_at' => (clone $departure)->modify('+40 days'),
        ];
    }
}
