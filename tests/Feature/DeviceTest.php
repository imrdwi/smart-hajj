<?php

namespace Tests\Feature;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function recording_a_location_moves_last_seen_forward(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create();

        $device->recordLocation(21.42, 39.82, 5.0, now()->subMinute());

        $this->assertTrue($device->fresh()->last_seen_at->equalTo(now()->subMinute()));
    }

    #[Test]
    public function a_late_arriving_older_report_does_not_move_last_seen_back(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create();

        $device->recordLocation(21.42, 39.82, recordedAt: now());
        $device->recordLocation(21.43, 39.83, recordedAt: now()->subHour());

        $this->assertTrue($device->fresh()->last_seen_at->equalTo(now()));
        $this->assertSame(2, $device->locationReports()->count());
    }

    #[Test]
    public function a_device_token_can_be_revoked_without_touching_other_devices(): void
    {
        $phone = Device::factory()->create();
        $tablet = Device::factory()->create();

        $phone->createToken('app');
        $tablet->createToken('app');

        $phone->tokens()->delete();

        $this->assertSame(0, $phone->tokens()->count());
        $this->assertSame(1, $tablet->tokens()->count());
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_type' => 'device', 'tokenable_id' => $tablet->id]);
        $this->assertSame(1, PersonalAccessToken::count());
    }
}
