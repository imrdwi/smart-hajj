<?php

namespace App\Models;

use App\MealType;
use App\Models\Concerns\AssignedThroughHierarchy;
use App\Models\Concerns\BelongsToAgency;
use Carbon\CarbonImmutable;
use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $agency_id
 * @property MealType $type
 * @property string|null $menu
 * @property CarbonImmutable $served_at
 */
#[Fillable(['type', 'menu', 'served_at'])]
class Meal extends Model implements BelongsToAgency
{
    /** @use HasFactory<MealFactory> */
    use AssignedThroughHierarchy, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MealType::class,
            'served_at' => 'datetime',
        ];
    }

    protected function assignmentMorphName(): string
    {
        return 'mealable';
    }

    /**
     * @return BelongsTo<Agency, $this>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
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
