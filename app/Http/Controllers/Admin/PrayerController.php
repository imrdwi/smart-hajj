<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrayerRequest;
use App\Models\Place;
use App\Models\Prayer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PrayerController extends Controller
{
    public function store(PrayerRequest $request, Place $place): RedirectResponse
    {
        $place->prayers()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Prayer added.')]);

        return to_route('places.edit', $place);
    }

    public function update(PrayerRequest $request, Prayer $prayer): RedirectResponse
    {
        $prayer->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Prayer updated.')]);

        return to_route('places.edit', $prayer->place_id);
    }

    public function destroy(Prayer $prayer): RedirectResponse
    {
        $prayer->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Prayer removed.')]);

        return to_route('places.edit', $prayer->place_id);
    }
}
