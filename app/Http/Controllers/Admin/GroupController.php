<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupRequest;
use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Group::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('groups/Index', [
            'groups' => Group::query()
                ->visibleTo($user)
                ->with(['flightGroup:id,code', 'leader:id,name'])
                ->withCount('pilgrims')
                ->when($request->integer('flight_group_id'), fn ($query, int $id) => $query->where('flight_group_id', $id))
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'flightGroups' => FlightGroup::query()->visibleTo($user)->orderByDesc('departs_at')->get(['id', 'code']),
            'filters' => $request->only('search', 'flight_group_id'),
            'canManage' => $user->can('create', Group::class),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Group::class);

        return Inertia::render('groups/Form', ['group' => null, ...$this->options($request)]);
    }

    public function store(GroupRequest $request): RedirectResponse
    {
        Gate::authorize('create', Group::class);

        Group::query()->create($request->validated() + ['flight_group_id' => $request->integer('flight_group_id')]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group created.')]);

        return to_route('groups.index');
    }

    public function edit(Request $request, Group $group): Response
    {
        Gate::authorize('update', $group);

        return Inertia::render('groups/Form', ['group' => $group, ...$this->options($request)]);
    }

    public function update(GroupRequest $request, Group $group): RedirectResponse
    {
        Gate::authorize('update', $group);

        $group->forceFill(['flight_group_id' => $request->integer('flight_group_id')])->fill($request->validated())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group updated.')]);

        return to_route('groups.index');
    }

    public function destroy(Group $group): RedirectResponse
    {
        Gate::authorize('delete', $group);

        $group->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group archived.')]);

        return to_route('groups.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function options(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        return [
            'flightGroups' => FlightGroup::query()->visibleTo($user)->orderByDesc('departs_at')->get(['id', 'code']),
            'leaders' => User::query()->where('agency_id', $user->agency_id)->where('role', UserRole::GroupLeader)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
