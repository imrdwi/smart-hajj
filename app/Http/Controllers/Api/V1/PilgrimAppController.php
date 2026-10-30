<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ContactResource;
use App\Http\Resources\V1\MealResource;
use App\Http\Resources\V1\PlaceResource;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Agency;
use App\Models\Contact;
use App\Models\Device;
use App\Models\Group;
use App\Models\Pilgrim;
use App\Models\Place;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Endpoints for the pilgrim's phone, authenticated by its device token.
 */
class PilgrimAppController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $pilgrim = $this->pilgrim($request)->load('group.flightGroup', 'group.leader:id,name');
        $group = $pilgrim->group;

        return response()->json(['data' => [
            'id' => $pilgrim->id,
            'name' => $pilgrim->name,
            'group' => $group?->only('id', 'name'),
            'leader' => $group?->leader?->only('id', 'name'),
            'trip' => $group?->flightGroup?->only('id', 'code', 'airline', 'flight_number', 'departs_at', 'returns_at'),
        ]]);
    }

    public function schedules(Request $request): AnonymousResourceCollection
    {
        return ScheduleResource::collection(
            $this->pilgrim($request)->effectiveSchedules()->with('place')->get()
        );
    }

    public function meals(Request $request): AnonymousResourceCollection
    {
        return MealResource::collection($this->pilgrim($request)->effectiveMeals()->get());
    }

    /**
     * The agency's contacts plus the pilgrim's own group's.
     */
    public function contacts(Request $request): AnonymousResourceCollection
    {
        $pilgrim = $this->pilgrim($request);
        $group = $pilgrim->group;

        return ContactResource::collection(Contact::query()
            ->where(fn ($owners) => $owners
                ->whereMorphRelation('contactable', Agency::class, 'id', $pilgrim->owningAgencyId())
                ->when($group, fn ($query) => $query->orWhereMorphRelation('contactable', Group::class, 'id', $group?->id)))
            ->orderBy('name')
            ->get());
    }

    public function places(): AnonymousResourceCollection
    {
        return PlaceResource::collection(Place::query()->with('prayers')->orderBy('name')->get());
    }

    /**
     * Store a location fix and return any holy sites the pilgrim has just entered, with their prayers.
     */
    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'recorded_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $report = $this->device($request)->recordLocation(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy_meters']) ? (float) $validated['accuracy_meters'] : null,
            isset($validated['recorded_at']) ? CarbonImmutable::parse($validated['recorded_at']) : null,
        );

        return response()->json([
            'data' => [
                'id' => $report->id,
                'entered_places' => PlaceResource::collection($report->enteredPlaces()->load('prayers')),
            ],
        ], 201);
    }

    /**
     * Sign the phone out by revoking the token it used.
     */
    public function signOut(Request $request): Response
    {
        $this->device($request)->currentAccessToken()->delete();

        return response()->noContent();
    }

    private function device(Request $request): Device
    {
        /** @var Device $device */
        $device = $request->user();

        return $device;
    }

    private function pilgrim(Request $request): Pilgrim
    {
        /** @var Pilgrim $pilgrim */
        $pilgrim = $this->device($request)->pilgrim;

        return $pilgrim;
    }
}
