<?php

namespace Database\Factories;

use App\MealType;
use App\Models\Agency;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
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
            'type' => fake()->randomElement(MealType::cases()),
            'menu' => fake()->words(4, true),
            'served_at' => fake()->dateTimeBetween('+1 month', '+2 months'),
        ];
    }
}
