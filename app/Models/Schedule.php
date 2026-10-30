<?php

namespace App\Models;

use App\Models\Concerns\AssignedThroughHierarchy;
use App\Models\Concerns\BelongsToAgency;
use Carbon\CarbonImmutable;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $agency_id
 * @property int|null $place_id
 * @property string $title
 * @property string|null $description
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable|null $ends_at
 */
#[Fillable(['place_id', 'title', 'description', 'starts_at', 'ends_at'])]
class Schedule extends Model implements BelongsToAgency
{
    /** @use HasFactory<ScheduleFactory> */
    use AssignedThroughHierarchy, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected function assignmentMorphName(): string
    {
        return 'schedulable';
    }

    /**
     * @return BelongsTo<Agency, $this>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
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
    }
}
