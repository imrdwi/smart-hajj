<?php

namespace App\Models;

use Database\Factories\LocationReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['latitude', 'longitude', 'accuracy_meters', 'recorded_at'])]
class LocationReport extends Model
{
    /** @use HasFactory<LocationReportFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * The device's report recorded immediately before this one.
     */
    public function previous(): ?self
    {
        return static::query()
            ->where('device_id', $this->device_id)
            ->where(fn ($query) => $query
                ->where('recorded_at', '<', $this->recorded_at)
                ->orWhere(fn ($tie) => $tie
                    ->where('recorded_at', $this->recorded_at)
                    ->where('id', '<', $this->id)))
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Places this report is inside that the previous report was not,
     * so a pilgrim standing still is not notified again on every fix.
     *
     * @return Collection<int, Place>
     */
    public function enteredPlaces(): Collection
    {
        $current = Place::containing($this->latitude, $this->longitude);
        $previous = $this->previous();

        if ($previous === null) {
            return $current;
        }

        $alreadyInside = Place::containing($previous->latitude, $previous->longitude)->modelKeys();

        return $current->reject(fn (Place $place): bool => in_array($place->getKey(), $alreadyInside, true))->values();
    }
}
