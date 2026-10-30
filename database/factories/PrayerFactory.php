<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\Prayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prayer>
 */
class PrayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'title' => fake()->sentence(3),
            'arabic' => fake()->sentence(),
            'transliteration' => fake()->sentence(),
            'translation' => fake()->sentence(),
        ];
    }
}
