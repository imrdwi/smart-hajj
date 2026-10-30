<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
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
            'place_id' => null,
            'title' => fake()->randomElement(['Tawaf', 'Sa\'i', 'Ziarah', 'Briefing', 'Wukuf']),
            'description' => fake()->sentence(),
            'starts_at' => $start = fake()->dateTimeBetween('+1 month', '+2 months'),
            'ends_at' => (clone $start)->modify('+2 hours'),
        ];
    }
}
