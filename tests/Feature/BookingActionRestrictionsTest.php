<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\EventPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingActionRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_booking_details_show_each_stored_client_event_and_package_field(): void
    {
        $clientUser = User::factory()->create([
            'name' => 'Jeddafaith Tumbado',
            'email' => 'jeddafaith@example.test',
            'phone' => '09123456789',
            'role' => 'client',
        ]);
        $client = Client::create(['user_id' => $clientUser->id]);
        $package = EventPackage::create([
            'package_name' => 'Basic Birthday Package',
            'description' => 'A complete celebration package for birthdays.',
            'price' => 20000,
            'inclusions' => "Event coordination\nVenue styling\nOn-site support",
            'is_available' => true,
        ]);
        $booking = Booking::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'event_date' => now()->addDays(20)->toDateString(),
            'event_type' => 'Birthday',
            'venue' => 'La Purisima, Sto. Domingo, Nabua',
            'status' => 'Pending',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.show', $booking));
        $response
            ->assertOk()
            ->assertSee('Booking Status')
            ->assertSee('Date Requested')
            ->assertSee('Last Updated')
            ->assertDontSee('Booking Reference Number')
            ->assertSee('Client Information')
            ->assertSee('Client Name')
            ->assertSee('Jeddafaith Tumbado')
            ->assertSee('Email Address')
            ->assertSee('jeddafaith@example.test')
            ->assertSee('Phone Number')
            ->assertSee('09123456789')
            ->assertSee('Event Information')
            ->assertSee('Event Type')
            ->assertSee('Birthday')
            ->assertSee('Event Date')
            ->assertSee('Event Venue')
            ->assertSee('La Purisima, Sto. Domingo, Nabua')
            ->assertSee('Selected Event Package')
            ->assertSee('Package Type')
            ->assertSee('Package Name')
            ->assertSee('Basic Birthday Package')
            ->assertSee('Package Price')
            ->assertSee('PHP 20,000.00')
            ->assertSee('Package Description')
            ->assertSee($package->description)
            ->assertSee('Package Inclusions')
            ->assertSee('Event coordination')
            ->assertSee('Venue styling')
            ->assertDontSee('Event Name')
            ->assertDontSee('Event Time')
            ->assertSee(route('admin.bookings.approve', $booking))
            ->assertSee(route('admin.bookings.decline', $booking))
            ->assertDontSee(route('admin.bookings.reschedule', $booking))
            ->assertDontSee('Reschedule Booking')
            ->assertDontSee('New Event Date');
        $html = $response->getContent();
        $this->assertTrue(strpos($html, 'Client Information') < strpos($html, 'Selected Event Package'));
        $this->assertTrue(strpos($html, 'Selected Event Package') < strpos($html, 'Booking Overview'));
        $this->assertTrue(strpos($html, 'Booking Overview') < strpos($html, 'Approve Booking'));
        $this->assertTrue(strpos($html, 'Approve Booking') < strpos($html, 'Decline Booking'));
    }

    public function test_custom_booking_details_do_not_show_package_price_or_inclusions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $client] = $this->createClient();
        $booking = $this->createBooking($client);

        $this->actingAs($admin)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Package Type')
            ->assertSee('Custom Event')
            ->assertSee('Package Name')
            ->assertSee('No Package – Custom Event')
            ->assertDontSee('Package Price')
            ->assertDontSee('Package Inclusions');
    }

    public function test_admin_decision_hides_booking_actions_and_prevents_client_changes(): void
    {
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['approve' => 'Approved', 'decline' => 'Declined'] as $action => $status) {
            $booking = $this->createBooking($client);

            $this->actingAs($admin)
                ->get(route('admin.bookings.show', $booking))
                ->assertOk()
                ->assertSee(route('admin.bookings.approve', $booking))
                ->assertSee(route('admin.bookings.decline', $booking))
                ->assertDontSee(route('admin.bookings.reschedule', $booking))
                ->assertDontSee('Reschedule Booking')
                ->assertDontSee('New Event Date');

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
                ->assertDontSee(route('admin.bookings.decline', $booking))
                ->assertDontSee(route('admin.bookings.reschedule', $booking));

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