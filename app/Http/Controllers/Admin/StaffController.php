<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Agency;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff accounts. System admins manage everyone; agency admins manage their own agency's staff.
 */
class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $this->actor($request);

        return Inertia::render('staff/Index', [
            'staff' => $this->scope($actor)
                ->with('agency:id,name')
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where(fn ($matches) => $matches
                    ->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('staff/Form', ['staffMember' => null, ...$this->options($this->actor($request))]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $actor = $this->actor($request);
        $staff = new User($request->safe()->only('name', 'email', 'password'));
        $this->assignRole($staff, $request, $actor);
        $staff->forceFill(['email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account created.')]);

        return to_route('staff.index');
    }

    public function edit(Request $request, User $user): Response
    {
        $actor = $this->actor($request);
        $this->ensureManageable($actor, $user);

        return Inertia::render('staff/Form', ['staffMember' => $user, ...$this->options($actor)]);
    }

    public function update(StaffRequest $request, User $user): RedirectResponse
    {
        $actor = $this->actor($request);
        $this->ensureManageable($actor, $user);

        $user->fill($request->safe()->only('name', 'email'));

        if ($request->filled('password')) {
            $user->password = $request->string('password')->toString();
        }

        if ($user->isNot($actor)) {
            $this->assignRole($user, $request, $actor);
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account updated.')]);

        return to_route('staff.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $this->actor($request);
        $this->ensureManageable($actor, $user);
        abort_if($user->is($actor), 422, __('You cannot delete your own account here.'));

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account deleted.')]);

        return to_route('staff.index');
    }

    private function actor(Request $request): User
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->isSystemAdmin() || $actor->hasRole(UserRole::AgencyAdmin), 403);

        return $actor;
    }

    /**
     * @return Builder<User>
     */
    private function scope(User $actor): Builder
    {
        return User::query()->when(! $actor->isSystemAdmin(), fn ($query) => $query->where('agency_id', $actor->agency_id));
    }

    private function ensureManageable(User $actor, User $staff): void
    {
        abort_unless($actor->isSystemAdmin() || $staff->agency_id === $actor->agency_id, 403);
    }

    private function assignRole(User $staff, StaffRequest $request, User $actor): void
    {
        $staff->role = UserRole::from($request->string('role')->toString());
        $staff->agency_id = $actor->isSystemAdmin()
            ? ($staff->role === UserRole::SystemAdmin ? null : $request->integer('agency_id'))
            : $actor->agency_id;
    }

    /**
     * @return array<string, mixed>
     */
    private function options(User $actor): array
    {
        $roles = $actor->isSystemAdmin() ? UserRole::cases() : [UserRole::AgencyAdmin, UserRole::GroupLeader];

        return [
            'roles' => array_map(fn (UserRole $role) => $role->value, $roles),
            'agencies' => $actor->isSystemAdmin() ? Agency::query()->orderBy('name')->get(['id', 'name']) : [],
        ];
    }
}
