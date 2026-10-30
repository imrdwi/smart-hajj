<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use App\UserRole;
use Database\Factories\FlightGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'airline', 'flight_number', 'departs_at', 'returns_at'])]
class FlightGroup extends Model implements BelongsToAgency
{
    /** @use HasFactory<FlightGroupFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departs_at' => 'datetime',
            'returns_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Agency, $this>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return HasMany<Group, $this>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * @return HasManyThrough<Pilgrim, Group, $this>
     */
    public function pilgrims(): HasManyThrough
    {
        return $this->hasManyThrough(Pilgrim::class, Group::class);
    }

    /**
     * Schedules assigned to the whole flight group.
     *
     * @return MorphToMany<Schedule, $this>
     */
    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable')->withTimestamps();
    }

    /**
     * Meals assigned to the whole flight group.
     *
     * @return MorphToMany<Meal, $this>
     */
    public function meals(): MorphToMany
    {
        return $this->morphToMany(Meal::class, 'mealable')->withTimestamps();
    }

    public function owningAgencyId(): ?int
    {
        return $this->agency_id;
    }

    /**
     * Records the user may see: their agency's for admins, their own groups' for leaders.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->where('agency_id', $user->agency_id);

        if ($user->hasRole(UserRole::GroupLeader)) {
            $query->whereHas('groups', fn (Builder $groups) => $groups->where('leader_id', $user->id));
        }
    }
}
