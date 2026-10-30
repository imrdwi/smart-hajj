<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeofenceTest extends TestCase
{
    use RefreshDatabase;

    private const HARAM_LATITUDE = 21.4228764;

    private const HARAM_LONGITUDE = 39.8235460;

    #[Test]
    public function distance_matches_a_known_great_circle_distance(): void
    {
        $haram = Place::factory()->create(['latitude' => self::HARAM_LATITUDE, 'longitude' => self::HARAM_LONGITUDE]);

        // Masjid al-Haram to Masjid an-Nabawi is roughly 338 km as the crow flies.
        $this->assertEqualsWithDelta(338_000, $haram->distanceTo(24.4672154, 39.6089423), 3_000);
    }

    #[Test]
    public function a_coordinate_inside_the_radius_is_contained(): void
    {
        $haram = $this->haram(radiusMeters: 500);

        // About 330 m north of the centre.
        $found = Place::containing(self::HARAM_LATITUDE + 0.003, self::HARAM_LONGITUDE);

        $this->assertTrue($found->contains($haram));
    }

    #[Test]
    public function a_coordinate_just_outside_the_radius_is_not_contained(): void
    {
        $this->haram(radiusMeters: 500);

        // About 560 m north of the centre.
        $this->assertCount(0, Place::containing(self::HARAM_LATITUDE + 0.005, self::HARAM_LONGITUDE));
    }

    #[Test]
    public function overlapping_geofences_are_all_returned(): void
    {
        $haram = $this->haram(radiusMeters: 500);
        $kabah = Place::factory()->create([
            'latitude' => 21.4225289,
            'longitude' => 39.8239929,
            'radius_meters' => 60,
        ]);

        $found = Place::containing(21.4225289, 39.8239929);

        $this->assertEqualsCanonicalizing([$haram->id, $kabah->id], $found->modelKeys());
    }

    #[Test]
    public function a_place_is_entered_only_on_the_report_that_crosses_its_boundary(): void
    {
        $haram = $this->haram(radiusMeters: 500);
        $device = Device::factory()->create();

        $outside = $device->recordLocation(self::HARAM_LATITUDE + 0.02, self::HARAM_LONGITUDE, recordedAt: now()->subMinutes(10));
        $arrived = $device->recordLocation(self::HARAM_LATITUDE, self::HARAM_LONGITUDE, recordedAt: now()->subMinutes(5));
        $stillInside = $device->recordLocation(self::HARAM_LATITUDE + 0.001, self::HARAM_LONGITUDE, recordedAt: now());

        $this->assertCount(0, $outside->enteredPlaces());
        $this->assertSame([$haram->id], $arrived->enteredPlaces()->modelKeys());
        $this->assertCount(0, $stillInside->enteredPlaces());
    }

    #[Test]
    public function the_first_report_inside_a_place_counts_as_entering_it(): void
    {
        $haram = $this->haram(radiusMeters: 500);

        $report = Device::factory()->create()->recordLocation(self::HARAM_LATITUDE, self::HARAM_LONGITUDE);

        $this->assertSame([$haram->id], $report->enteredPlaces()->modelKeys());
    }

    #[Test]
    public function another_devices_reports_do_not_affect_entry(): void
    {
        $haram = $this->haram(radiusMeters: 500);

        Device::factory()->create()->recordLocation(self::HARAM_LATITUDE, self::HARAM_LONGITUDE, recordedAt: now()->subMinute());
        $report = Device::factory()->create()->recordLocation(self::HARAM_LATITUDE, self::HARAM_LONGITUDE);

        $this->assertSame([$haram->id], $report->enteredPlaces()->modelKeys());
    }

    private function haram(int $radiusMeters): Place
    {
        return Place::factory()->create([
            'latitude' => self::HARAM_LATITUDE,
            'longitude' => self::HARAM_LONGITUDE,
            'radius_meters' => $radiusMeters,
        ]);
    }
}
