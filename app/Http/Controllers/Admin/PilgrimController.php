<?php

namespace App\Http\Controllers\Admin;

use App\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PilgrimRequest;
use App\Models\Group;
use App\Models\LocationReport;
use App\Models\Pilgrim;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PilgrimController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Pilgrim::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('pilgrims/Index', [
            'pilgrims' => Pilgrim::query()
                ->visibleTo($user)
                ->with('group:id,name,flight_group_id', 'group.flightGroup:id,code')
                ->withCount('devices')
                ->when($request->integer('group_id'), fn ($query, int $id) => $query->where('group_id', $id))
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'groups' => Group::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only('search', 'group_id'),
            'canManage' => $user->can('create', Pilgrim::class),
        ]);
    }

    public function show(Request $request, Pilgrim $pilgrim): Response
    {
        Gate::authorize('view', $pilgrim);

        /** @var User $user */
        $user = $request->user();
        $pilgrim->load('group.flightGroup', 'group.leader:id,name', 'devices');

        return Inertia::render('pilgrims/Show', [
            'pilgrim' => [
                ...$pilgrim->toArray(),
                'photo_url' => $pilgrim->photo_path ? Storage::disk('public')->url($pilgrim->photo_path) : null,
            ],
            'schedules' => $pilgrim->effectiveSchedules()->with('place:id,name')->get(),
            'meals' => $pilgrim->effectiveMeals()->get(),
            'lastLocation' => $pilgrim->devices->isEmpty() ? null : LocationReport::query()
                ->whereIn('device_id', $pilgrim->devices->modelKeys())
                ->latest('recorded_at')
                ->first(['latitude', 'longitude', 'recorded_at']),
            'canManage' => $user->can('update', $pilgrim),
            'pairedToken' => session('pairedToken'),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Pilgrim::class);

        return Inertia::render('pilgrims/Form', ['pilgrim' => null, ...$this->options($request)]);
    }

    public function store(PilgrimRequest $request): RedirectResponse
    {
        Gate::authorize('create', Pilgrim::class);

        $pilgrim = new Pilgrim($request->safe()->except('photo', 'group_id'));
        $pilgrim->forceFill([
            'group_id' => $request->integer('group_id'),
            'photo_path' => $request->file('photo')?->store('pilgrims', 'public') ?: null,
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pilgrim registered.')]);

        return to_route('pilgrims.show', $pilgrim);
    }

    public function edit(Request $request, Pilgrim $pilgrim): Response
    {
        Gate::authorize('update', $pilgrim);

        return Inertia::render('pilgrims/Form', ['pilgrim' => $pilgrim->makeVisible('passport_number'), ...$this->options($request)]);
    }

    public function update(PilgrimRequest $request, Pilgrim $pilgrim): RedirectResponse
    {
        Gate::authorize('update', $pilgrim);

        $pilgrim->fill($request->safe()->except('photo', 'group_id'));
        $pilgrim->forceFill(['group_id' => $request->integer('group_id')]);

        if ($request->hasFile('photo')) {
            if ($pilgrim->photo_path) {
                Storage::disk('public')->delete($pilgrim->photo_path);
            }

            $pilgrim->forceFill(['photo_path' => $request->file('photo')?->store('pilgrims', 'public') ?: null]);
        }

        $pilgrim->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pilgrim updated.')]);

        return to_route('pilgrims.show', $pilgrim);
    }

    public function destroy(Pilgrim $pilgrim): RedirectResponse
    {
        Gate::authorize('delete', $pilgrim);

        $pilgrim->devices->each(fn ($device) => $device->tokens()->delete());
        $pilgrim->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pilgrim archived.')]);

        return to_route('pilgrims.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function options(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        return [
            'groups' => Group::query()->visibleTo($user)->with('flightGroup:id,code')->orderBy('name')->get(['id', 'name', 'flight_group_id']),
            'genders' => array_map(fn (Gender $gender) => $gender->value, Gender::cases()),
        ];
    }
}
