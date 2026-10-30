<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Device;
use App\Models\Group;
use App\Models\Pilgrim;
use App\Models\Place;
use App\Models\Prayer;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PilgrimApiTest extends TestCase
{
    use RefreshDatabase;

    private Pilgrim $pilgrim;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pilgrim = Pilgrim::factory()->create();
        $device = Device::factory()->for($this->pilgrim)->create();
        $this->token = $device->createToken('pilgrim-app')->plainTextToken;
    }

    #[Test]
    public function pairing_a_device_shows_its_token_once(): void
    {
        $admin = User::factory()->agencyAdmin()->create(['agency_id' => $this->pilgrim->owningAgencyId()]);

        $this->actingAs($admin)->post("/pilgrims/{$this->pilgrim->id}/devices", ['platform' => 'android'])
            ->assertRedirect("/pilgrims/{$this->pilgrim->id}")
            ->assertSessionHas('pairedToken');

        $this->assertSame(2, $this->pilgrim->devices()->count());
    }

    #[Test]
    public function it_returns_the_pilgrims_profile_and_itinerary(): void
    {
        $schedule = Schedule::factory()->create(['agency_id' => $this->pilgrim->owningAgencyId()]);
        $schedule->assignTo($this->pilgrim->group);

        $this->withToken($this->token)->getJson('/api/v1/me')
            ->assertOk()->assertJsonPath('data.name', $this->pilgrim->name);

        $this->withToken($this->token)->getJson('/api/v1/schedules')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', $schedule->title);
    }

    #[Test]
    public function contacts_include_the_agency_and_own_group_but_not_other_groups(): void
    {
        $group = $this->pilgrim->group;
        $agency = $group->flightGroup->agency;
        $agencyContact = Contact::factory()->create(['contactable_type' => 'agency', 'contactable_id' => $agency->id]);
        $groupContact = Contact::factory()->create(['contactable_type' => 'group', 'contactable_id' => $group->id]);
        $sibling = Group::factory()->for($group->flightGroup)->create();
        Contact::factory()->create(['contactable_type' => 'group', 'contactable_id' => $sibling->id]);

        $ids = collect($this->withToken($this->token)->getJson('/api/v1/contacts')->assertOk()->json('data'))->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$agencyContact->id, $groupContact->id], $ids);
    }

    #[Test]
    public function posting_a_location_returns_newly_entered_places_with_prayers(): void
    {
        $haram = Place::factory()->create(['latitude' => 21.4228764, 'longitude' => 39.8235460, 'radius_meters' => 500]);
        Prayer::factory()->for($haram)->create(['title' => 'Entering the mosque']);

        $this->withToken($this->token)->postJson('/api/v1/locations', ['latitude' => 21.4228764, 'longitude' => 39.8235460])
            ->assertCreated()
            ->assertJsonPath('data.entered_places.0.id', $haram->id)
            ->assertJsonPath('data.entered_places.0.prayers.0.title', 'Entering the mosque');

        $this->withToken($this->token)->postJson('/api/v1/locations', ['latitude' => 21.4229, 'longitude' => 39.8235])
            ->assertCreated()
            ->assertJsonCount(0, 'data.entered_places');
    }

    #[Test]
    public function signing_out_revokes_the_token(): void
    {
        $this->withToken($this->token)->deleteJson('/api/v1/token')->assertNoContent();

        $this->app['auth']->forgetGuards();

        $this->withToken($this->token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    #[Test]
    public function staff_accounts_and_missing_tokens_are_rejected(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();

        $this->actingAs(User::factory()->create(), 'sanctum')->getJson('/api/v1/me')->assertForbidden();
    }

    #[Test]
    public function an_unpaired_device_loses_access(): void
    {
        $admin = User::factory()->agencyAdmin()->create(['agency_id' => $this->pilgrim->owningAgencyId()]);
        $device = $this->pilgrim->devices()->sole();

        $this->actingAs($admin)->delete("/devices/{$device->id}")->assertRedirect();

        $this->app['auth']->forgetGuards();

        $this->withToken($this->token)->getJson('/api/v1/me')->assertUnauthorized();
    }
}
