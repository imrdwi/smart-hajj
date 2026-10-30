<?php

namespace App\Models;

use Database\Factories\PrayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A supplication shown to pilgrims when they arrive at its place.
 */
#[Fillable(['title', 'arabic', 'transliteration', 'translation'])]
class Prayer extends Model
{
    /** @use HasFactory<PrayerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
