<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\LocationReport;
use App\Models\Pilgrim;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    /**
     * The last known position of every visible pilgrim, with the holy sites for context.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Pilgrim::class);

        /** @var User $user */
        $user = $request->user();

        $latestPerDevice = LocationReport::query()
            ->selectRaw('max(id) as id')
            ->whereHas('device.pilgrim', fn ($pilgrims) => $pilgrims->visibleTo($user)
                ->when($request->integer('group_id'), fn ($query, int $id) => $query->where('group_id', $id)))
            ->groupBy('device_id');

        $positions = LocationReport::query()
            ->whereIn('id', $latestPerDevice)
            ->with('device.pilgrim:id,name,group_id', 'device.pilgrim.group:id,name')
            ->get()
            ->groupBy(fn (LocationReport $report): int => (int) $report->device?->pilgrim_id)
            ->map(fn ($reports) => $reports->sortByDesc('recorded_at')->first())
            ->filter()
            ->map(fn (LocationReport $report) => [
                'pilgrim_id' => $report->device?->pilgrim?->id,
                'name' => $report->device?->pilgrim?->name,
                'group' => $report->device?->pilgrim?->group?->name,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude,
                'recorded_at' => $report->recorded_at,
            ])
            ->values();

        return Inertia::render('locations/Index', [
            'positions' => $positions,
            'places' => Place::query()->get(['id', 'name', 'latitude', 'longitude', 'radius_meters']),
            'groups' => Group::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only('group_id'),
        ]);
    }
}
