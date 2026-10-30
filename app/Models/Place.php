<?php

namespace App\Models;

use App\City;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A site with a circular geofence, such as a miqat or a station of the pilgrimage.
 *
 * @property int $id
 * @property string $name
 * @property City $city
 * @property float $latitude
 * @property float $longitude
 * @property int $radius_meters
 * @property string|null $description
 */
#[Fillable(['name', 'city', 'latitude', 'longitude', 'radius_meters', 'description'])]
class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory;

    private const EARTH_RADIUS_METERS = 6_371_000;

    private const METERS_PER_DEGREE_LATITUDE = 111_320;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'city' => City::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
        ];
    }

    /**
     * @return HasMany<Prayer, $this>
     */
    public function prayers(): HasMany
    {
        return $this->hasMany(Prayer::class);
    }

    /**
     * Places whose geofence contains the coordinate.
     *
     * A bounding box sized to the largest radius narrows the rows in SQL;
     * the exact great-circle check runs in PHP so it works on any database.
     *
     * @return Collection<int, static>
     */
    public static function containing(float $latitude, float $longitude): Collection
    {
        $largestRadius = (int) static::query()->max('radius_meters');

        if ($largestRadius === 0) {
            return new Collection;
        }

        $latitudeDelta = $largestRadius / self::METERS_PER_DEGREE_LATITUDE;
        $longitudeDelta = $largestRadius / (self::METERS_PER_DEGREE_LATITUDE * max(cos(deg2rad($latitude)), 0.01));

        return static::query()
            ->whereBetween('latitude', [$latitude - $latitudeDelta, $latitude + $latitudeDelta])
            ->whereBetween('longitude', [$longitude - $longitudeDelta, $longitude + $longitudeDelta])
            ->get()
            ->filter(fn (Place $place): bool => $place->contains($latitude, $longitude))
            ->values();
    }

    public function contains(float $latitude, float $longitude): bool
    {
        return $this->distanceTo($latitude, $longitude) <= $this->radius_meters;
    }

    /**
     * Great-circle distance from the place's centre, in meters (haversine formula).
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        $latitudeDifference = deg2rad($latitude - $this->latitude);
        $longitudeDifference = deg2rad($longitude - $this->longitude);

        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($longitudeDifference / 2) ** 2;

        return 2 * self::EARTH_RADIUS_METERS * asin(min(1.0, sqrt($a)));
    }
}
