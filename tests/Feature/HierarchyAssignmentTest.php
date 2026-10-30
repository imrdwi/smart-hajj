<?php

namespace Tests\Feature;

use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Meal;
use App\Models\Pilgrim;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HierarchyAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private FlightGroup $flightGroup;

    private Group $group;

    private Pilgrim $pilgrim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flightGroup = FlightGroup::factory()->create();
        $this->group = Group::factory()->for($this->flightGroup)->create();
        $this->pilgrim = Pilgrim::factory()->for($this->group)->create();
    }

    #[Test]
    public function a_pilgrim_inherits_schedules_from_every_level_above_them(): void
    {
        $forFlightGroup = $this->scheduleAssignedTo($this->flightGroup);
        $forGroup = $this->scheduleAssignedTo($this->group);
        $forPilgrim = $this->scheduleAssignedTo($this->pilgrim);

        $this->assertEqualsCanonicalizing(
            [$forFlightGroup->id, $forGroup->id, $forPilgrim->id],
            $this->pilgrim->effectiveSchedules()->pluck('id')->all(),
        );
    }

    #[Test]
    public function a_pilgrim_does_not_see_schedules_of_other_groups_or_pilgrims(): void
    {
        $siblingGroup = Group::factory()->for($this->flightGroup)->create();
        $siblingPilgrim = Pilgrim::factory()->for($this->group)->create();

        $this->scheduleAssignedTo($siblingGroup);
        $this->scheduleAssignedTo($siblingPilgrim);
        $this->scheduleAssignedTo(FlightGroup::factory()->create());

        $this->assertSame(0, $this->pilgrim->effectiveSchedules()->count());
    }

    #[Test]
    public function a_schedule_assigned_at_several_levels_appears_once(): void
    {
        $schedule = $this->scheduleAssignedTo($this->flightGroup);
        $schedule->assignTo($this->group);
        $schedule->assignTo($this->pilgrim);
        $schedule->assignTo($this->pilgrim);

        $this->assertSame([$schedule->id], $this->pilgrim->effectiveSchedules()->pluck('id')->all());
        $this->assertSame(1, $schedule->pilgrims()->count());
    }

    #[Test]
    public function unassigning_a_level_removes_only_what_it_granted(): void
    {
        $schedule = $this->scheduleAssignedTo($this->flightGroup);
        $schedule->assignTo($this->group);

        $schedule->unassignFrom($this->flightGroup);

        $this->assertSame([$schedule->id], $this->pilgrim->effectiveSchedules()->pluck('id')->all());

        $schedule->unassignFrom($this->group);

        $this->assertSame(0, $this->pilgrim->effectiveSchedules()->count());
    }

    #[Test]
    public function effective_schedules_are_ordered_by_start_time(): void
    {
        $later = $this->scheduleAssignedTo($this->flightGroup, ['starts_at' => now()->addDays(2)]);
        $sooner = $this->scheduleAssignedTo($this->pilgrim, ['starts_at' => now()->addDay()]);

        $this->assertSame([$sooner->id, $later->id], $this->pilgrim->effectiveSchedules()->pluck('id')->all());
    }

    #[Test]
    public function meals_follow_the_same_inheritance_rules(): void
    {
        $groupMeal = Meal::factory()->create();
        $groupMeal->assignTo($this->group);

        $otherMeal = Meal::factory()->create();
        $otherMeal->assignTo(Group::factory()->for($this->flightGroup)->create());

        $this->assertSame([$groupMeal->id], $this->pilgrim->effectiveMeals()->pluck('id')->all());
    }

    #[Test]
    public function the_pivot_stores_short_morph_names_rather_than_class_names(): void
    {
        $this->scheduleAssignedTo($this->group);

        $this->assertDatabaseHas('schedulables', [
            'schedulable_type' => 'group',
            'schedulable_id' => $this->group->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function scheduleAssignedTo(FlightGroup|Group|Pilgrim $assignee, array $attributes = []): Schedule
    {
        $schedule = Schedule::factory()->create($attributes);
        $schedule->assignTo($assignee);

        return $schedule;
    }
}
