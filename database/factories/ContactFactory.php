<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contactable_type' => (new Agency)->getMorphClass(),
            'contactable_id' => Agency::factory(),
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Tour leader', 'Doctor', 'Muthawwif']),
            'phone' => fake()->e164PhoneNumber(),
        ];
    }
}
