<?php

namespace App\Models;

use App\Gender;
use App\Models\Concerns\BelongsToAgency;
use Database\Factories\PilgrimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'passport_number', 'gender', 'birth_date', 'phone', 'occupation', 'address', 'photo_path'])]
#[Hidden(['passport_number'])]
class Pilgrim extends Model implements BelongsToAgency
{
    /** @use HasFactory<PilgrimFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return HasMany<Device, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Schedules assigned to this pilgrim only. See {@see self::effectiveSchedules()} for the full itinerary.
     *
     * @return MorphToMany<Schedule, $this>
     */
    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'schedulable')->withTimestamps();
    }

    /**
     * Meals assigned to this pilgrim only. See {@see self::effectiveMeals()} for every meal they receive.
     *
     * @return MorphToMany<Meal, $this>
     */
    public function meals(): MorphToMany
    {
        return $this->morphToMany(Meal::class, 'mealable')->withTimestamps();
    }

    /**
     * Every schedule that applies to the pilgrim, including those inherited from their group and flight group.
     *
     * @return Builder<Schedule>
     */
    public function effectiveSchedules(): Builder
    {
        return Schedule::query()->forPilgrim($this)->orderBy('starts_at');
    }

    /**
     * Every meal that applies to the pilgrim, including those inherited from their group and flight group.
     *
     * @return Builder<Meal>
     */
    public function effectiveMeals(): Builder
    {
        return Meal::query()->forPilgrim($this)->orderBy('served_at');
    }

    public function owningAgencyId(): ?int
    {
        return $this->group()->withTrashed()->first()?->owningAgencyId();
    }

    /**
     * Records the user may see: their agency's for admins, their own groups' for leaders.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->whereHas('group', fn (Builder $groups) => $groups->visibleTo($user));
    }
}
