<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use Carbon\CarbonInterface;
use Database\Factories\DeviceFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * A pilgrim's phone. It authenticates with its own revocable Sanctum token,
 * so it is an authenticatable without a password.
 */
#[Fillable(['identifier', 'platform'])]
class Device extends Model implements AuthenticatableContract, BelongsToAgency
{
    /** @use HasFactory<DeviceFactory> */
    use Authenticatable, HasApiTokens, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pilgrim, $this>
     */
    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    /**
     * @return HasMany<LocationReport, $this>
     */
    public function locationReports(): HasMany
    {
        return $this->hasMany(LocationReport::class);
    }

    /**
     * Store a location fix. Reports can arrive out of order after the phone
     * regains signal, so last_seen_at only ever moves forward.
     */
    public function recordLocation(
        float $latitude,
        float $longitude,
        ?float $accuracyMeters = null,
        ?CarbonInterface $recordedAt = null,
    ): LocationReport {
        $recordedAt ??= now();

        $report = $this->locationReports()->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => $accuracyMeters,
            'recorded_at' => $recordedAt,
        ]);

        if ($this->last_seen_at === null || $recordedAt->greaterThan($this->last_seen_at)) {
            $this->forceFill(['last_seen_at' => $recordedAt])->save();
        }

        return $report;
    }

    public function owningAgencyId(): ?int
    {
        return $this->pilgrim()->withTrashed()->first()?->owningAgencyId();
    }

    /**
     * Records the user may see: their agency's for admins, their own groups' for leaders.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->whereHas('pilgrim', fn (Builder $pilgrims) => $pilgrims->visibleTo($user));
    }
}
