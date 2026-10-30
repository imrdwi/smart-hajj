<?php

namespace App\Policies;

use App\Models\Concerns\BelongsToAgency;
use App\Models\Group;
use App\Models\Pilgrim;
use App\Models\User;
use App\UserRole;

/**
 * One rule for every agency-owned record: agency admins manage their own
 * agency's records, and group leaders may view the pilgrims and groups they lead.
 */
class AgencyRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->agency_id !== null;
    }

    public function view(User $user, BelongsToAgency $record): bool
    {
        if ($user->hasRole(UserRole::GroupLeader)) {
            return $this->leads($user, $record);
        }

        return $this->manages($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::AgencyAdmin) && $user->agency_id !== null;
    }

    public function update(User $user, BelongsToAgency $record): bool
    {
        return $this->manages($user, $record);
    }

    public function delete(User $user, BelongsToAgency $record): bool
    {
        return $this->manages($user, $record);
    }

    private function manages(User $user, BelongsToAgency $record): bool
    {
        return $user->hasRole(UserRole::AgencyAdmin)
            && $user->agency_id !== null
            && $user->agency_id === $record->owningAgencyId();
    }

    private function leads(User $user, BelongsToAgency $record): bool
    {
        return match (true) {
            $record instanceof Group => $record->leader_id === $user->id,
            $record instanceof Pilgrim => $record->group?->leader_id === $user->id,
            default => false,
        };
    }
}
