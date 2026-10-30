<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Device;
use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Pilgrim;
use App\Models\Place;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isSystemAdmin()) {
            return Inertia::render('Dashboard', [
                'stats' => [
                    ['label' => __('Agencies'), 'value' => Agency::query()->count()],
                    ['label' => __('Staff accounts'), 'value' => User::query()->count()],
                    ['label' => __('Holy sites'), 'value' => Place::query()->count()],
                    ['label' => __('Pilgrims'), 'value' => Pilgrim::query()->count()],
                ],
                'upcoming' => [],
                'quiet' => [],
            ]);
        }

        $staleAfter = now()->subMinutes(30);

        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => __('Trips'), 'value' => FlightGroup::query()->visibleTo($user)->count()],
                ['label' => __('Groups'), 'value' => Group::query()->visibleTo($user)->count()],
                ['label' => __('Pilgrims'), 'value' => Pilgrim::query()->visibleTo($user)->count()],
                ['label' => __('Devices seen in 30 min'), 'value' => Device::query()->visibleTo($user)->where('last_seen_at', '>=', $staleAfter)->count()],
            ],
            'upcoming' => Schedule::query()->visibleTo($user)->with('place:id,name')
                ->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get(['id', 'title', 'starts_at', 'place_id']),
            'quiet' => Device::query()->visibleTo($user)->with('pilgrim:id,name')
                ->where(fn ($query) => $query->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $staleAfter))
                ->orderBy('last_seen_at')->limit(5)->get(['id', 'pilgrim_id', 'identifier', 'last_seen_at']),
        ]);
    }
}
