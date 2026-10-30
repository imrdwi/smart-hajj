<?php

namespace App\Http\Resources\V1;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Place
 */
class PlaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Place $place */
        $place = $this->resource;

        return [
            'id' => $place->id,
            'name' => $place->name,
            'city' => $place->city->value,
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'radius_meters' => $place->radius_meters,
            'description' => $place->description,
            'prayers' => PrayerResource::collection($this->whenLoaded('prayers')),
        ];
    }
}
