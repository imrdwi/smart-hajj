<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;
use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Contact::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('contacts/Index', [
            'contacts' => Contact::query()
                ->visibleTo($user)
                ->with('contactable')
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Contact $contact) => [
                    ...$contact->only('id', 'name', 'role', 'phone', 'address', 'latitude', 'longitude'),
                    'owner' => $contact->contactable instanceof Group ? $contact->contactable->name : __('Whole agency'),
                ]),
            'filters' => $request->only('search'),
            'canManage' => $user->can('create', Contact::class),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Contact::class);

        return Inertia::render('contacts/Form', ['contact' => null, 'groups' => $this->groups($request)]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        Gate::authorize('create', Contact::class);

        /** @var User $user */
        $user = $request->user();
        $contact = new Contact($request->safe()->except('group_id'));
        $contact->contactable()->associate($request->filled('group_id') ? Group::query()->findOrFail($request->integer('group_id')) : $user->agency);
        $contact->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact created.')]);

        return to_route('contacts.index');
    }

    public function edit(Request $request, Contact $contact): Response
    {
        Gate::authorize('update', $contact);

        return Inertia::render('contacts/Form', [
            'contact' => [...$contact->toArray(), 'group_id' => $contact->contactable instanceof Group ? $contact->contactable->id : null],
            'groups' => $this->groups($request),
        ]);
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        Gate::authorize('update', $contact);

        /** @var User $user */
        $user = $request->user();
        $contact->fill($request->safe()->except('group_id'));
        $contact->contactable()->associate($request->filled('group_id') ? Group::query()->findOrFail($request->integer('group_id')) : $user->agency);
        $contact->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact updated.')]);

        return to_route('contacts.index');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        Gate::authorize('delete', $contact);

        $contact->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact deleted.')]);

        return to_route('contacts.index');
    }

    /**
     * @return Collection<int, Group>
     */
    private function groups(Request $request): Collection
    {
        /** @var User $user */
        $user = $request->user();

        return Group::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']);
    }
}
