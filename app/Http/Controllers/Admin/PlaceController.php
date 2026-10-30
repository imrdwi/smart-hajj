<?php

namespace App\Http\Controllers\Admin;

use App\City;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlaceRequest;
use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaceController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('places/Index', [
            'places' => Place::query()
                ->withCount('prayers')
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('city')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('places/Form', ['place' => null, 'cities' => $this->cities()]);
    }

    public function store(PlaceRequest $request): RedirectResponse
    {
        $place = Place::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Holy site created.')]);

        return to_route('places.edit', $place);
    }

    public function edit(Place $place): Response
    {
        return Inertia::render('places/Form', [
            'place' => $place->load('prayers'),
            'cities' => $this->cities(),
        ]);
    }

    public function update(PlaceRequest $request, Place $place): RedirectResponse
    {
        $place->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Holy site updated.')]);

        return to_route('places.edit', $place);
    }

    public function destroy(Place $place): RedirectResponse
    {
        $place->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Holy site deleted.')]);

        return to_route('places.index');
    }

    /**
     * @return list<string>
     */
    private function cities(): array
    {
        return array_map(fn (City $city) => $city->value, City::cases());
    }
}
