<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Pilgrim;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Device::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('devices/Index', [
            'devices' => Device::query()
                ->visibleTo($user)
                ->with('pilgrim:id,name')
                ->withCount('tokens')
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where(fn ($matches) => $matches
                    ->where('identifier', 'like', "%{$search}%")
                    ->orWhereHas('pilgrim', fn ($pilgrims) => $pilgrims->where('name', 'like', "%{$search}%"))))
                ->latest('last_seen_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Pair a phone with a pilgrim. The token is shown once, for the pilgrim app to store.
     */
    public function store(Request $request, Pilgrim $pilgrim): RedirectResponse
    {
        Gate::authorize('update', $pilgrim);

        $validated = $request->validate([
            'identifier' => ['nullable', 'string', 'max:100', Rule::unique('devices')],
            'platform' => ['nullable', Rule::in(['android', 'ios'])],
        ]);

        $device = new Device([
            'identifier' => $validated['identifier'] ?? (string) Str::uuid(),
            'platform' => $validated['platform'] ?? null,
        ]);
        $device->pilgrim()->associate($pilgrim);
        $device->save();

        $token = $device->createToken('pilgrim-app')->plainTextToken;

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Device paired. Copy the token now; it will not be shown again.')]);

        return to_route('pilgrims.show', $pilgrim)->with('pairedToken', $token);
    }

    /**
     * Unpair a phone: its tokens stop working immediately.
     */
    public function destroy(Device $device): RedirectResponse
    {
        Gate::authorize('delete', $device);

        $device->tokens()->delete();
        $device->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Device unpaired.')]);

        return back();
    }
}
