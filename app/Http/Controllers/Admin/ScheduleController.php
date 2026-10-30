<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Place;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    use ProvidesAssignmentOptions;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Schedule::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('schedules/Index', [
            'schedules' => Schedule::query()
                ->visibleTo($user)
                ->with('place:id,name')
                ->withCount(['flightGroups', 'groups', 'pilgrims'])
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
                ->orderBy('starts_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
            'canManage' => $user->can('create', Schedule::class),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Schedule::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('schedules/Form', [
            'schedule' => null,
            'assigned' => ['flight_group_ids' => [], 'group_ids' => [], 'pilgrim_ids' => []],
            'places' => Place::query()->orderBy('name')->get(['id', 'name']),
            ...$this->assignmentOptions($user),
        ]);
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Schedule::class);

        /** @var User $user */
        $user = $request->user();

        DB::transaction(function () use ($request, $user): void {
            $schedule = new Schedule($request->safe()->except('flight_group_ids', 'group_ids', 'pilgrim_ids'));
            $schedule->forceFill(['agency_id' => $user->agency_id])->save();
            $schedule->syncAssignments(...$request->assignments());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Schedule created.')]);

        return to_route('schedules.index');
    }

    public function edit(Request $request, Schedule $schedule): Response
    {
        Gate::authorize('update', $schedule);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('schedules/Form', [
            'schedule' => $schedule,
            'assigned' => $this->currentAssignments($schedule),
            'places' => Place::query()->orderBy('name')->get(['id', 'name']),
            ...$this->assignmentOptions($user),
        ]);
    }

    public function update(ScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        Gate::authorize('update', $schedule);

        DB::transaction(function () use ($request, $schedule): void {
            $schedule->update($request->safe()->except('flight_group_ids', 'group_ids', 'pilgrim_ids'));
            $schedule->syncAssignments(...$request->assignments());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Schedule updated.')]);

        return to_route('schedules.index');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        Gate::authorize('delete', $schedule);

        $schedule->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Schedule deleted.')]);

        return to_route('schedules.index');
    }
}
