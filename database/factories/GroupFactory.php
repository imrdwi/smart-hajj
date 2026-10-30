<?php

namespace Database\Factories;

use App\Models\FlightGroup;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flight_group_id' => FlightGroup::factory(),
            'leader_id' => null,
            'name' => 'Group '.fake()->unique()->numberBetween(1, 9999),
        ];
    }
}
