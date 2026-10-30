<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MealRequest;
use App\MealType;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MealController extends Controller
{
    use ProvidesAssignmentOptions;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Meal::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('meals/Index', [
            'meals' => Meal::query()
                ->visibleTo($user)
                ->withCount(['flightGroups', 'groups', 'pilgrims'])
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('menu', 'like', "%{$search}%"))
                ->orderBy('served_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
            'canManage' => $user->can('create', Meal::class),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Meal::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('meals/Form', [
            'meal' => null,
            'assigned' => ['flight_group_ids' => [], 'group_ids' => [], 'pilgrim_ids' => []],
            'types' => array_map(fn (MealType $type) => $type->value, MealType::cases()),
            ...$this->assignmentOptions($user),
        ]);
    }

    public function store(MealRequest $request): RedirectResponse
    {
        Gate::authorize('create', Meal::class);

        /** @var User $user */
        $user = $request->user();

        DB::transaction(function () use ($request, $user): void {
            $meal = new Meal($request->safe()->except('flight_group_ids', 'group_ids', 'pilgrim_ids'));
            $meal->forceFill(['agency_id' => $user->agency_id])->save();
            $meal->syncAssignments(...$request->assignments());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meal created.')]);

        return to_route('meals.index');
    }

    public function edit(Request $request, Meal $meal): Response
    {
        Gate::authorize('update', $meal);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('meals/Form', [
            'meal' => $meal,
            'assigned' => $this->currentAssignments($meal),
            'types' => array_map(fn (MealType $type) => $type->value, MealType::cases()),
            ...$this->assignmentOptions($user),
        ]);
    }

    public function update(MealRequest $request, Meal $meal): RedirectResponse
    {
        Gate::authorize('update', $meal);

        DB::transaction(function () use ($request, $meal): void {
            $meal->update($request->safe()->except('flight_group_ids', 'group_ids', 'pilgrim_ids'));
            $meal->syncAssignments(...$request->assignments());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meal updated.')]);

        return to_route('meals.index');
    }

    public function destroy(Meal $meal): RedirectResponse
    {
        Gate::authorize('delete', $meal);

        $meal->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meal deleted.')]);

        return to_route('meals.index');
    }
}
