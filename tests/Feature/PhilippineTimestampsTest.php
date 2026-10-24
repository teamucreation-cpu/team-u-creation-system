<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use App\Support\PhilippineTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhilippineTimestampsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_display_time_converts_across_midnight_in_manila(): void
    {
        $timestamp = Carbon::parse('2026-10-19 16:30:00', 'UTC');

        $this->assertSame('October 20, 2026 – 12:30 AM', PhilippineTime::format($timestamp));
        $this->assertSame('Oct 20, 2026 – 12:30 AM', PhilippineTime::format($timestamp, compact: true));
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('Asia/Manila', config('app.display_timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
        $this->assertSame('+00:00', config('database.connections.mysql.timezone'));
    }

    public function test_booking_submission_time_matches_on_client_and_admin_pages(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-19 14:04:00', 'UTC'));
        [$clientUser] = $this->createClient();

        $this->actingAs($clientUser)
            ->post(route('client.bookings.store'), [
                'event_type' => 'Birthday Party',
                'venue' => 'Main Hall',
                'event_date' => '2026-10-22',
            ])
            ->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame('2026-10-19 14:04:00', $booking->created_at->format('Y-m-d H:i:s'));

        $this->actingAs($clientUser)
            ->get(route('client.bookings.show', $booking))
            ->assertOk()
            ->assertSee('October 19, 2026 – 10:04 PM');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('October 19, 2026 – 10:04 PM');
    }

    public function test_appointment_submission_and_admin_update_keep_requested_time_and_match_views(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-19 14:15:00', 'UTC'));
        [$clientUser] = $this->createClient();

        $this->actingAs($clientUser)
            ->post(route('client.appointments.store'), [
                'booking_id' => null,
                'appointment_date' => '2026-10-22',
                'appointment_time' => '10:00',
                'purpose' => 'Initial Consultation',
            ])
            ->assertRedirect(route('client.appointments.index'));

        $appointment = Appointment::firstOrFail();
        $originalCreatedAt = $appointment->created_at->copy();
        $this->assertSame('2026-10-19 14:15:00', $originalCreatedAt->format('Y-m-d H:i:s'));

        $this->actingAs($clientUser)
            ->get(route('client.appointments.index'))
            ->assertOk()
            ->assertSee('Oct 19, 2026 – 10:15 PM');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.appointments.index'))
            ->assertOk()
            ->assertSee('Oct 19, 2026 – 10:15 PM');

        Carbon::setTestNow(Carbon::parse('2026-10-19 14:30:00', 'UTC'));
        $this->actingAs($admin)
            ->post(route('admin.appointments.approve', $appointment))
            ->assertRedirect();

        $appointment->refresh();
        $this->assertSame($originalCreatedAt->format('Y-m-d H:i:s'), $appointment->created_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-19 14:30:00', $appointment->updated_at->format('Y-m-d H:i:s'));

        $this->actingAs($clientUser)
            ->get(route('client.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('October 19, 2026 – 10:15 PM')
            ->assertSee('October 19, 2026 – 10:30 PM');

        $this->actingAs($admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('October 19, 2026 – 10:15 PM')
            ->assertSee('October 19, 2026 – 10:30 PM');
    }

    private function createClient(): array
    {
        $user = User::factory()->create(['role' => 'client']);

        return [$user, Client::create(['user_id' => $user->id])];
    }
}
