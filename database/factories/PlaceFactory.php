<?php

namespace Database\Factories;

use App\City;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->streetName(),
            'city' => City::Makkah,
            'latitude' => fake()->latitude(21.38, 21.46),
            'longitude' => fake()->longitude(39.80, 39.90),
            'radius_meters' => 200,
            'description' => fake()->sentence(),
        ];
    }
}
