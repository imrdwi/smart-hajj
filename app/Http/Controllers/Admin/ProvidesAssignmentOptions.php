<?php

namespace App\Http\Controllers\Admin;

use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Meal;
use App\Models\Pilgrim;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Choices and current values for the "assign to" fields on schedule and meal forms.
 */
trait ProvidesAssignmentOptions
{
    /**
     * @return array<string, mixed>
     */
    private function assignmentOptions(User $user): array
    {
        return [
            'flightGroups' => FlightGroup::query()->visibleTo($user)->orderByDesc('departs_at')->get(['id', 'code']),
            'groups' => Group::query()->visibleTo($user)->with('flightGroup:id,code')->orderBy('name')->get(['id', 'name', 'flight_group_id']),
            'pilgrims' => Pilgrim::query()->visibleTo($user)->orderBy('name')->get(['id', 'name', 'group_id']),
        ];
    }

    /**
     * @return array{flight_group_ids: array<int, int>, group_ids: array<int, int>, pilgrim_ids: array<int, int>}
     */
    private function currentAssignments(Schedule|Meal $record): array
    {
        /** @param MorphToMany<FlightGroup|Group|Pilgrim, Schedule|Meal> $relation */
        $ids = fn (MorphToMany $relation): array => $relation->pluck($relation->getRelated()->getQualifiedKeyName())->map(fn ($id) => (int) $id)->values()->all();

        return [
            'flight_group_ids' => $ids($record->flightGroups()),
            'group_ids' => $ids($record->groups()),
            'pilgrim_ids' => $ids($record->pilgrims()),
        ];
    }
}
