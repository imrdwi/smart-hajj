<?php

namespace Database\Seeders;

use App\City;
use App\Models\Place;
use Illuminate\Database\Seeder;

class HolySiteSeeder extends Seeder
{
    /**
     * Seed the pilgrimage sites used for geofence notifications.
     *
     * Coordinates are approximate centre points; radii are rough working
     * values to tune against real devices, not surveyed boundaries.
     */
    public function run(): void
    {
        /** @var list<array{name: string, city: City, latitude: float, longitude: float, radius_meters: int}> $sites */
        $sites = [
            ['name' => 'Miqat Dhul Hulaifah (Bir Ali)', 'city' => City::Madinah, 'latitude' => 24.4137395, 'longitude' => 39.5408443, 'radius_meters' => 400],
            ['name' => "Masjid Aisyah (Tan'im)", 'city' => City::Makkah, 'latitude' => 21.4700194, 'longitude' => 39.7812452, 'radius_meters' => 300],
            ['name' => 'Masjid al-Haram', 'city' => City::Makkah, 'latitude' => 21.4228764, 'longitude' => 39.8235460, 'radius_meters' => 500],
            ['name' => "Ka'bah", 'city' => City::Makkah, 'latitude' => 21.4225289, 'longitude' => 39.8239929, 'radius_meters' => 60],
            ['name' => "Safa and Marwa (Mas'a)", 'city' => City::Makkah, 'latitude' => 21.4235308, 'longitude' => 39.8251857, 'radius_meters' => 200],
            ['name' => 'Masjid an-Nabawi', 'city' => City::Madinah, 'latitude' => 24.4672154, 'longitude' => 39.6089423, 'radius_meters' => 400],
            ['name' => 'Arafah', 'city' => City::Makkah, 'latitude' => 21.3549047, 'longitude' => 39.9753612, 'radius_meters' => 3000],
            ['name' => 'Muzdalifah', 'city' => City::Makkah, 'latitude' => 21.3999302, 'longitude' => 39.8768512, 'radius_meters' => 2000],
            ['name' => 'Mina', 'city' => City::Makkah, 'latitude' => 21.4152071, 'longitude' => 39.8701678, 'radius_meters' => 2000],
            ['name' => "Jabal an-Nur (Gua Hira')", 'city' => City::Makkah, 'latitude' => 21.4573622, 'longitude' => 39.8567476, 'radius_meters' => 300],
        ];

        foreach ($sites as $site) {
            Place::query()->updateOrCreate(['name' => $site['name']], $site);
        }
    }
}
