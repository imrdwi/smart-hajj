<?php

namespace App\Http\Requests\Admin;

use App\Models\Concerns\BelongsToAgency;
use App\UserRole;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Base for forms whose foreign keys must point inside the user's own agency.
 */
abstract class AgencyScopedRequest extends FormRequest
{
    /**
     * Check permission before validation, so a foreign record answers 403, not a validation error.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $record = collect($this->route()?->parameters() ?? [])->first(fn (mixed $parameter): bool => $parameter instanceof BelongsToAgency);

        if ($record instanceof BelongsToAgency) {
            return (bool) $user?->can('update', $record);
        }

        return $user !== null && $user->hasRole(UserRole::AgencyAdmin) && $user->agency_id !== null;
    }

    protected function agencyId(): int
    {
        return (int) $this->user()?->agency_id;
    }

    protected function existsInAgency(string $table): Exists
    {
        return match ($table) {
            'flight_groups' => Rule::exists('flight_groups', 'id')->where('agency_id', $this->agencyId())->withoutTrashed(),
            'groups' => Rule::exists('groups', 'id')->withoutTrashed()->where(fn (Builder $query) => $query
                ->whereIn('flight_group_id', fn (Builder $sub) => $sub->select('id')->from('flight_groups')->where('agency_id', $this->agencyId()))),
            'pilgrims' => Rule::exists('pilgrims', 'id')->withoutTrashed()->where(fn (Builder $query) => $query
                ->whereIn('group_id', fn (Builder $sub) => $sub->select('groups.id')->from('groups')
                    ->join('flight_groups', 'flight_groups.id', '=', 'groups.flight_group_id')
                    ->where('flight_groups.agency_id', $this->agencyId()))),
            'users' => Rule::exists('users', 'id')->where('agency_id', $this->agencyId()),
            default => throw new \InvalidArgumentException("No agency scope for [{$table}]."),
        };
    }
}
