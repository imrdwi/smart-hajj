<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Contact;
use App\Models\Device;
use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Meal;
use App\Models\Pilgrim;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with holy sites and one demo agency.
     */
    public function run(): void
    {
        $this->call(HolySiteSeeder::class);

        User::factory()->systemAdmin()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
        ]);

        $agency = Agency::factory()->create(['name' => 'Demo Travel']);

        User::factory()->agencyAdmin()->for($agency)->create([
            'name' => 'Agency Admin',
            'email' => 'agency@example.com',
        ]);

        $leader = User::factory()->groupLeader()->for($agency)->create([
            'name' => 'Group Leader',
            'email' => 'leader@example.com',
        ]);

        $flightGroup = FlightGroup::factory()->for($agency)->create(['code' => 'HAJJ-2027-A']);

        $groups = Group::factory()
            ->count(3)
            ->for($flightGroup)
            ->sequence(fn ($sequence) => [
                'name' => 'Group '.($sequence->index + 1),
                'leader_id' => $sequence->index === 0 ? $leader->id : User::factory()->groupLeader()->for($agency),
            ])
            ->has(Pilgrim::factory()->count(10)->has(Device::factory()))
            ->create();

        Contact::factory()->create(['contactable_type' => 'agency', 'contactable_id' => $agency->id, 'name' => 'Agency office', 'role' => 'Office']);
        Contact::factory()->create(['contactable_type' => 'group', 'contactable_id' => $groups->first()?->id, 'name' => 'Group 1 doctor', 'role' => 'Doctor']);

        // Demo positions scattered around Masjid al-Haram for the map.
        Device::query()->each(fn (Device $device) => $device->recordLocation(
            21.4225 + fake()->randomFloat(5, -0.006, 0.006),
            39.8262 + fake()->randomFloat(5, -0.006, 0.006),
            fake()->randomFloat(1, 3, 30),
            now()->toImmutable()->subMinutes(fake()->numberBetween(1, 90)),
        ));

        Schedule::factory()->count(5)->for($agency)->create()
            ->each(fn (Schedule $schedule) => $schedule->assignTo($flightGroup));

        Meal::factory()->count(3)->for($agency)->create()
            ->each(fn (Meal $meal) => $meal->assignTo($flightGroup));
    }
}
