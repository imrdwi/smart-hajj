<?php

namespace Database\Factories;

use App\Gender;
use App\Models\Group;
use App\Models\Pilgrim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pilgrim>
 */
class PilgrimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'name' => fake()->name(),
            'passport_number' => fake()->unique()->bothify('?#######'),
            'gender' => fake()->randomElement(Gender::cases()),
            'birth_date' => fake()->dateTimeBetween('-75 years', '-18 years'),
            'phone' => fake()->e164PhoneNumber(),
        ];
    }
}
