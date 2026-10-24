<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingActionRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_decision_hides_booking_actions_and_prevents_client_changes(): void
    {
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['approve' => 'Approved', 'decline' => 'Declined'] as $action => $status) {
            $booking = $this->createBooking($client);

            $this->actingAs($admin)
                ->post(route("admin.bookings.{$action}", $booking))
                ->assertRedirect();

            $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => $status,
            ]);

            $this->actingAs($admin)
                ->get(route('admin.bookings.show', $booking))
                ->assertOk()
                ->assertDontSee(route('admin.bookings.approve', $booking))
                ->assertDontSee(route('admin.bookings.decline', $booking));

            $this->actingAs($clientUser)
                ->get(route('client.bookings.show', $booking))
                ->assertOk()
                ->assertDontSee('Edit Booking')
                ->assertDontSee('Cancel Booking');

            $this->actingAs($clientUser)
                ->put(route('client.bookings.update', $booking), [
                    'event_type' => 'Changed Event',
                    'venue' => 'Changed Venue',
                    'event_date' => now()->addDays(20)->toDateString(),
                ])
                ->assertRedirect();

            $this->actingAs($clientUser)
                ->delete(route('client.bookings.destroy', $booking))
                ->assertRedirect();

            $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => $status,
                'event_type' => 'Birthday Party',
                'venue' => 'Main Hall',
            ]);
        }
    }

    private function createClient(): array
    {
        $user = User::factory()->create(['role' => 'client']);

        return [$user, Client::create(['user_id' => $user->id])];
    }

    private function createBooking(Client $client): Booking
    {
        return Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->addDays(20)->toDateString(),
            'event_type' => 'Birthday Party',
            'venue' => 'Main Hall',
            'status' => 'Pending',
        ]);
    }
}