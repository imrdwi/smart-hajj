<?php

namespace App\Models\Concerns;

use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Pilgrim;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Lets a model be assigned to a flight group, a group, or a single pilgrim.
 *
 * A pilgrim inherits every assignment made to their group and flight group,
 * so the same record is never copied once per pilgrim.
 */
trait AssignedThroughHierarchy
{
    /**
     * The morph name of the pivot, e.g. "schedulable" for the "schedulables" table.
     */
    abstract protected function assignmentMorphName(): string;

    /**
     * @return MorphToMany<FlightGroup, $this>
     */
    public function flightGroups(): MorphToMany
    {
        return $this->morphedByMany(FlightGroup::class, $this->assignmentMorphName())->withTimestamps();
    }

    /**
     * @return MorphToMany<Group, $this>
     */
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(Group::class, $this->assignmentMorphName())->withTimestamps();
    }

    /**
     * @return MorphToMany<Pilgrim, $this>
     */
    public function pilgrims(): MorphToMany
    {
        return $this->morphedByMany(Pilgrim::class, $this->assignmentMorphName())->withTimestamps();
    }

    /**
     * Assign this record to a level of the hierarchy. Repeating an assignment is a no-op.
     */
    public function assignTo(FlightGroup|Group|Pilgrim $assignee): void
    {
        $this->relationFor($assignee)->syncWithoutDetaching([$assignee->getKey()]);
    }

    /**
     * Replace every direct assignment with the given ids at each level.
     *
     * @param  list<int>  $flightGroupIds
     * @param  list<int>  $groupIds
     * @param  list<int>  $pilgrimIds
     */
    public function syncAssignments(array $flightGroupIds, array $groupIds, array $pilgrimIds): void
    {
        $this->flightGroups()->sync($flightGroupIds);
        $this->groups()->sync($groupIds);
        $this->pilgrims()->sync($pilgrimIds);
    }

    /**
     * Remove an assignment made directly to the given level.
     */
    public function unassignFrom(FlightGroup|Group|Pilgrim $assignee): void
    {
        $this->relationFor($assignee)->detach($assignee->getKey());
    }

    /**
     * Records that apply to the pilgrim directly, through their group, or through their flight group.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function forPilgrim(Builder $query, Pilgrim $pilgrim): void
    {
        $morph = $this->assignmentMorphName();
        $pivot = str($morph)->plural()->toString();
        $group = $pilgrim->group()->withTrashed()->first();

        $query->whereExists(function (QueryBuilder $assignments) use ($morph, $pivot, $pilgrim, $group): void {
            $assignments->selectRaw('1')
                ->from($pivot)
                ->whereColumn("{$pivot}.{$this->getForeignKey()}", $this->getQualifiedKeyName())
                ->where(function (QueryBuilder $levels) use ($morph, $pilgrim, $group): void {
                    $levels->where(fn (QueryBuilder $level) => $level
                        ->where("{$morph}_type", $pilgrim->getMorphClass())
                        ->where("{$morph}_id", $pilgrim->getKey()));

                    if ($group !== null) {
                        $levels->orWhere(fn (QueryBuilder $level) => $level
                            ->where("{$morph}_type", $group->getMorphClass())
                            ->where("{$morph}_id", $group->getKey()))
                            ->orWhere(fn (QueryBuilder $level) => $level
                                ->where("{$morph}_type", (new FlightGroup)->getMorphClass())
                                ->where("{$morph}_id", $group->flight_group_id));
                    }
                });
        });
    }

    /**
     * @return MorphToMany<FlightGroup, $this>|MorphToMany<Group, $this>|MorphToMany<Pilgrim, $this>
     */
    private function relationFor(FlightGroup|Group|Pilgrim $assignee): MorphToMany
    {
        return match (true) {
            $assignee instanceof FlightGroup => $this->flightGroups(),
            $assignee instanceof Group => $this->groups(),
            $assignee instanceof Pilgrim => $this->pilgrims(),
        };
    }
}
