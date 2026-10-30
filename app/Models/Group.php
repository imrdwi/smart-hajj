<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use App\UserRole;
use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'leader_id'])]
class Group extends Model implements BelongsToAgency
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<FlightGroup, $this>
     */
    public function flightGroup(): BelongsTo
    {
        return $this->belongsTo(FlightGroup::class);
    }

    /**
     * The group leader (mutawwif) responsible for these pilgrims.
     *
     * @return BelongsTo<User, $this>
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * @return HasMany<Pilgrim, $this>
     */
    public function pilgrims(): HasMany
    {
        return $this->hasMany(Pilgrim::class);
    }

    /**
     * @return MorphMany<Contact, $this>
     */
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    /**
     * Schedules assigned to this group directly.
     *
     * @return MorphToMany<Schedule, $this>
     */
    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable')->withTimestamps();
    }

    /**
     * Meals assigned to this group directly.
     *
     * @return MorphToMany<Meal, $this>
     */
    public function meals(): MorphToMany
    {
        return $this->morphToMany(Meal::class, 'mealable')->withTimestamps();
    }

    public function owningAgencyId(): ?int
    {
        return $this->flightGroup()->withTrashed()->value('agency_id');
    }

    /**
     * Records the user may see: their agency's for admins, their own groups' for leaders.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->whereHas('flightGroup', fn (Builder $flightGroups) => $flightGroups->where('agency_id', $user->agency_id));

        if ($user->hasRole(UserRole::GroupLeader)) {
            $query->where('leader_id', $user->id);
        }
    }
}
