<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlightGroupRequest;
use App\Models\FlightGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FlightGroupController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', FlightGroup::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('flight-groups/Index', [
            'flightGroups' => FlightGroup::query()
                ->visibleTo($user)
                ->withCount(['groups', 'pilgrims'])
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('code', 'like', "%{$search}%"))
                ->orderByDesc('departs_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
            'canManage' => $user->can('create', FlightGroup::class),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', FlightGroup::class);

        return Inertia::render('flight-groups/Form', ['flightGroup' => null]);
    }

    public function store(FlightGroupRequest $request): RedirectResponse
    {
        Gate::authorize('create', FlightGroup::class);

        /** @var User $user */
        $user = $request->user();
        $user->agency?->flightGroups()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trip created.')]);

        return to_route('flight-groups.index');
    }

    public function edit(FlightGroup $flightGroup): Response
    {
        Gate::authorize('update', $flightGroup);

        return Inertia::render('flight-groups/Form', ['flightGroup' => $flightGroup]);
    }

    public function update(FlightGroupRequest $request, FlightGroup $flightGroup): RedirectResponse
    {
        Gate::authorize('update', $flightGroup);

        $flightGroup->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trip updated.')]);

        return to_route('flight-groups.index');
    }

    public function destroy(FlightGroup $flightGroup): RedirectResponse
    {
        Gate::authorize('delete', $flightGroup);

        $flightGroup->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trip archived.')]);

        return to_route('flight-groups.index');
    }
}
