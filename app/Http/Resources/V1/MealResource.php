<?php

namespace App\Http\Resources\V1;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Meal
 */
class MealResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Meal $meal */
        $meal = $this->resource;

        return [
            'id' => $meal->id,
            'type' => $meal->type->value,
            'menu' => $meal->menu,
            'served_at' => $meal->served_at->toIso8601String(),
        ];
    }
}
