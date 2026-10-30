<?php

namespace App\Http\Resources\V1;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Schedule
 */
class ScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Schedule $schedule */
        $schedule = $this->resource;

        return [
            'id' => $schedule->id,
            'title' => $schedule->title,
            'description' => $schedule->description,
            'starts_at' => $schedule->starts_at->toIso8601String(),
            'ends_at' => $schedule->ends_at?->toIso8601String(),
            'place' => $this->whenLoaded('place', fn () => $schedule->place ? new PlaceResource($schedule->place) : null),
        ];
    }
}
