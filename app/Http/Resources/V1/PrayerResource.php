<?php

namespace App\Http\Resources\V1;

use App\Models\Prayer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Prayer
 */
class PrayerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'arabic' => $this->arabic,
            'transliteration' => $this->transliteration,
            'translation' => $this->translation,
        ];
    }
}
