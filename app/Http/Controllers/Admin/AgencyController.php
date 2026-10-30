<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgencyRequest;
use App\Models\Agency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgencyController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('agencies/Index', [
            'agencies' => Agency::query()
                ->withCount(['flightGroups', 'users'])
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('agencies/Form', ['agency' => null]);
    }

    public function store(AgencyRequest $request): RedirectResponse
    {
        Agency::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Agency created.')]);

        return to_route('agencies.index');
    }

    public function edit(Agency $agency): Response
    {
        return Inertia::render('agencies/Form', ['agency' => $agency]);
    }

    public function update(AgencyRequest $request, Agency $agency): RedirectResponse
    {
        $agency->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Agency updated.')]);

        return to_route('agencies.index');
    }

    public function destroy(Agency $agency): RedirectResponse
    {
        $agency->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Agency archived.')]);

        return to_route('agencies.index');
    }
}
