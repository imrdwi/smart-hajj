<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agency>
 */
class AgencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Travel',
            'license_number' => fake()->unique()->numerify('PPIU-####/####'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
