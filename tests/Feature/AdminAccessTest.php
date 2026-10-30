<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Contact;
use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Pilgrim;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $admin;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->agencyAdmin()->for($this->agency)->create();
        $this->group = Group::factory()->for(FlightGroup::factory()->for($this->agency))->create();
    }

    #[Test]
    public function every_admin_page_renders_for_an_agency_admin(): void
    {
        $pilgrim = Pilgrim::factory()->for($this->group)->create();

        foreach (['/dashboard', '/flight-groups', '/flight-groups/create', '/groups', '/groups/create', '/pilgrims', '/pilgrims/create',
            "/pilgrims/{$pilgrim->id}", "/pilgrims/{$pilgrim->id}/edit", '/schedules', '/schedules/create', '/meals', '/meals/create',
            '/contacts', '/contacts/create', '/devices', '/locations', '/staff', '/staff/create'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    #[Test]
    public function system_pages_are_for_system_admins_only(): void
    {
        $this->actingAs($this->admin)->get('/agencies')->assertForbidden();
        $this->actingAs($this->admin)->get('/places')->assertForbidden();

        $systemAdmin = User::factory()->systemAdmin()->create();

        foreach (['/dashboard', '/agencies', '/agencies/create', '/places', '/places/create', '/staff'] as $url) {
            $this->actingAs($systemAdmin)->get($url)->assertOk();
        }
    }

    #[Test]
    public function an_admin_cannot_see_or_change_another_agencys_records(): void
    {
        $otherGroup = Group::factory()->create();
        $otherPilgrim = Pilgrim::factory()->for($otherGroup)->create();

        $this->actingAs($this->admin)->get("/pilgrims/{$otherPilgrim->id}")->assertForbidden();
        $this->actingAs($this->admin)->put("/groups/{$otherGroup->id}", ['flight_group_id' => $otherGroup->flight_group_id, 'name' => 'Taken'])->assertForbidden();
        $this->actingAs($this->admin)->delete("/pilgrims/{$otherPilgrim->id}")->assertForbidden();

        $this->actingAs($this->admin)->get('/pilgrims')
            ->assertInertia(fn ($page) => $page->where('pilgrims.total', 0));
    }

    #[Test]
    public function foreign_keys_must_point_inside_the_admins_agency(): void
    {
        $otherGroup = Group::factory()->create();

        $this->actingAs($this->admin)->post('/pilgrims', [
            'group_id' => $otherGroup->id,
            'name' => 'Aminah',
            'passport_number' => 'X1234567',
            'gender' => 'female',
        ])->assertSessionHasErrors('group_id');

        $this->assertDatabaseMissing('pilgrims', ['passport_number' => 'X1234567']);
    }

    #[Test]
    public function a_schedule_is_created_with_its_assignments(): void
    {
        $pilgrim = Pilgrim::factory()->for($this->group)->create();

        $this->actingAs($this->admin)->post('/schedules', [
            'title' => 'Tawaf Qudum',
            'starts_at' => now()->addDay()->format('Y-m-d H:i'),
            'group_ids' => [$this->group->id],
        ])->assertRedirect('/schedules');

        $schedule = Schedule::query()->sole();
        $this->assertSame($this->agency->id, $schedule->agency_id);
        $this->assertSame([$schedule->id], $pilgrim->effectiveSchedules()->pluck('id')->all());
    }

    #[Test]
    public function group_leaders_only_see_their_own_groups_and_cannot_edit(): void
    {
        $leader = User::factory()->groupLeader()->for($this->agency)->create();
        $this->group->update(['leader_id' => $leader->id]);
        $mine = Pilgrim::factory()->for($this->group)->create();
        $notMine = Pilgrim::factory()->for(Group::factory()->for($this->group->flightGroup))->create();

        $this->actingAs($leader)->get("/pilgrims/{$mine->id}")->assertOk();
        $this->actingAs($leader)->get("/pilgrims/{$notMine->id}")->assertForbidden();
        $this->actingAs($leader)->get("/pilgrims/{$mine->id}/edit")->assertForbidden();
        $this->actingAs($leader)->get('/pilgrims/create')->assertForbidden();
        $this->actingAs($leader)->get('/staff')->assertForbidden();
        $this->actingAs($leader)->get('/pilgrims')->assertInertia(fn ($page) => $page->where('pilgrims.total', 1));
    }

    #[Test]
    public function an_agency_admin_cannot_create_a_system_admin(): void
    {
        $this->actingAs($this->admin)->post('/staff', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'role' => 'system_admin',
            'password' => 'password',
        ])->assertSessionHasErrors('role');
    }

    #[Test]
    public function contacts_can_belong_to_the_agency_or_one_group(): void
    {
        $this->actingAs($this->admin)->post('/contacts', ['name' => 'Clinic', 'phone' => '+966100'])->assertRedirect('/contacts');
        $this->actingAs($this->admin)->post('/contacts', ['name' => 'Leader', 'phone' => '+966200', 'group_id' => $this->group->id])->assertRedirect('/contacts');

        $this->assertTrue(Contact::query()->where('name', 'Clinic')->sole()->contactable->is($this->agency));
        $this->assertTrue(Contact::query()->where('name', 'Leader')->sole()->contactable->is($this->group));
    }

    #[Test]
    public function guests_are_sent_to_login(): void
    {
        $this->get('/pilgrims')->assertRedirect('/login');
    }
}
