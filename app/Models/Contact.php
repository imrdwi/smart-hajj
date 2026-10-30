<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A phone number pilgrims can call, published by an agency or a group.
 */
#[Fillable(['name', 'role', 'phone', 'address', 'latitude', 'longitude'])]
class Contact extends Model implements BelongsToAgency
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owningAgencyId(): ?int
    {
        $owner = $this->contactable;

        return match (true) {
            $owner instanceof Agency => $owner->id,
            $owner instanceof Group => $owner->owningAgencyId(),
            default => null,
        };
    }

    /**
     * Records the user may see: their agency's for admins, their own groups' for leaders.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $owners) => $owners
            ->whereMorphRelation('contactable', Agency::class, 'id', $user->agency_id)
            ->orWhereHasMorph('contactable', Group::class, fn (Builder $groups) => $groups->visibleTo($user)));
    }
}
