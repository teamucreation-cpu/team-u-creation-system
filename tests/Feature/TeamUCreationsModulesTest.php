<?php

namespace Tests\Feature;

use App\Mail\WorkflowNotificationMail;
use App\Models\Appointment;
use App\Models\Booking;
use App\Models\Client;
use App\Models\EventPackage;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamUCreationsModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['services.sms.enabled' => false]);
        Mail::fake();
    }

    public function test_welcome_page_and_registration_form_render(): void
    {
        $this->get('/')->assertOk()->assertViewIs('welcome');
        $this->get(route('register'))->assertOk()->assertSee('Create an Account');
        $this->get(route('login'))->assertOk()->assertSee('Login');
    }

    public function test_client_can_register_update_profile_logout_and_log_in(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Taylor Client',
            'email' => 'taylor@example.test',
            'phone' => '09171234567',
            'address' => 'Quezon City',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'taylor@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('client', $user->role);
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'address' => 'Quezon City',
        ]);
        $response->assertRedirect(route('client.dashboard'));

        $this->put(route('client.profile.update'), [
            'name' => 'Taylor Updated',
            'email' => 'taylor@example.test',
            'phone' => '09170001111',
            'address' => 'Pasig City',
            'date_of_birth' => '1998-04-12',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Taylor Updated',
            'phone' => '09170001111',
        ]);
        $updatedClient = $user->client()->firstOrFail();
        $this->assertSame('Pasig City', $updatedClient->address);
        $this->assertSame('1998-04-12', $updatedClient->date_of_birth->toDateString());

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_short_and_mismatched_passwords(): void
    {
        $this->from(route('register'))->post(route('register'), [
            'name' => 'Invalid Client',
            'email' => 'invalid@example.test',
            'phone' => '09171234567',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertRedirect(route('register'))->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.test']);
    }

    public function test_admin_authentication_and_role_restrictions(): void
    {
        [$admin] = $this->makeAdmin();
        [$clientUser] = $this->makeClient();

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('client.dashboard'))->assertForbidden();

        $this->actingAs($clientUser)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
        $this->get(route('client.profile.edit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.profile.edit'))->assertOk();
        $this->put(route('admin.profile.update'), [
            'name' => 'Updated Admin',
            'email' => $admin->email,
            'phone' => '09170009999',
            'address' => 'Admin Office',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'address' => 'Admin Office',
        ]);

        $this->post(route('logout'));
        $this->get(route('client.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_booking_request_checks_date_and_creates_notifications(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        $package = $this->makePackage();
        $eventDate = now()->addDays(10)->toDateString();

        $this->actingAs($clientUser)->get(route('client.bookings.create'))->assertOk();
        $response = $this->actingAs($clientUser)->post(route('client.bookings.store'), [
            'package_id' => $package->id,
            'event_type' => 'Birthday Celebration',
            'venue' => 'Test Venue',
            'event_date' => $eventDate,
        ]);

        $booking = Booking::where('client_id', $client->id)->firstOrFail();
        $this->assertSame('Pending', $booking->status);
        $this->assertSame($package->id, $booking->package_id);
        $response->assertRedirect(route('client.bookings.show', $booking));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_submitted',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_submitted',
        ]);
        $this->actingAs($clientUser)->get(route('client.bookings.index'))
            ->assertOk()->assertSee('Birthday Celebration');
        $this->actingAs($admin)->get(route('admin.bookings.index'))
            ->assertOk()->assertSee('Birthday Celebration');
        $this->get(route('admin.bookings.show', $booking))->assertOk();
        $this->get(route('admin.notifications.index'))->assertOk();
        Mail::assertSent(WorkflowNotificationMail::class, fn (WorkflowNotificationMail $mail) =>
            $mail->hasTo($admin->email)
            && $mail->details['Event type'] === 'Birthday Celebration'
            && $mail->details['Status'] === 'Pending'
            && str_contains($mail->actionUrl, '/admin/bookings/'.$booking->id)
        );
    }

    public function test_booking_date_conflicts_and_finalized_booking_ownership_are_enforced(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [, $otherClient] = $this->makeClient('Other Client');
        $eventDate = now()->addDays(12)->toDateString();
        $existing = $this->makeBooking($client, $eventDate, 'Pending');
        $this->assertTrue(Booking::whereKey($existing->id)
            ->whereDate('event_date', $eventDate)
            ->where('status', 'Pending')
            ->exists());

        $this->actingAs($clientUser)->from(route('client.bookings.create'))
            ->post(route('client.bookings.store'), [
                'event_type' => 'Birthday Celebration',
                'venue' => 'Another Venue',
                'event_date' => $eventDate,
            ])
            ->assertRedirect(route('client.bookings.create'))
            ->assertSessionHasErrors('event_date');

        $this->get(route('client.bookings.show', $this->makeBooking($otherClient, now()->addDays(20)->toDateString())))
            ->assertForbidden();

        $this->actingAs($this->makeAdmin()[0])->post(route('admin.bookings.approve', $existing));
        $this->assertDatabaseHas('bookings', ['id' => $existing->id, 'status' => 'Approved']);

        $this->actingAs($clientUser)->put(route('client.bookings.update', $existing), [
            'event_type' => 'Birthday Celebration',
            'venue' => 'Changed Venue',
            'event_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect(route('client.bookings.show', $existing));
        $this->assertDatabaseHas('bookings', [
            'id' => $existing->id,
            'status' => 'Approved',
            'venue' => 'Test Venue',
        ]);
    }

    public function test_admin_can_approve_decline_and_reschedule_pending_bookings(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        $approved = $this->makeBooking($client, now()->addDays(20)->toDateString());
        $declined = $this->makeBooking($client, now()->addDays(25)->toDateString());
        $rescheduled = $this->makeBooking($client, now()->addDays(30)->toDateString());
        $completed = $this->makeBooking($client, now()->subDay()->toDateString(), 'Approved');

        $this->actingAs($admin)->post(route('admin.bookings.approve', $approved));
        $this->post(route('admin.bookings.decline', $declined));
        $this->post(route('admin.bookings.reschedule', $rescheduled), [
            'event_date' => now()->addDays(40)->toDateString(),
        ]);
        $this->post(route('admin.bookings.complete', $completed));

        $this->assertDatabaseHas('bookings', ['id' => $approved->id, 'status' => 'Approved']);
        $this->assertDatabaseHas('bookings', ['id' => $declined->id, 'status' => 'Declined']);
        $this->assertDatabaseHas('bookings', ['id' => $completed->id, 'status' => 'Completed']);
        $this->assertDatabaseHas('bookings', [
            'id' => $rescheduled->id,
            'status' => 'Rescheduled',
            'event_date' => now()->addDays(40)->toDateString(),
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'booking_id' => $approved->id,
            'notification_type' => 'booking_approved',
        ]);
        Mail::assertSent(WorkflowNotificationMail::class, fn (WorkflowNotificationMail $mail) =>
            $mail->hasTo($clientUser->email)
            && $mail->details['Status'] === 'Approved'
            && str_contains($mail->actionUrl, '/client/bookings/'.$approved->id)
        );
    }

    public function test_booking_calendar_availability_marks_pending_and_approved_dates(): void
    {
        [$clientUser, $client] = $this->makeClient();
        $pendingDate = now()->addDays(14)->toDateString();
        $approvedDate = now()->addDays(15)->toDateString();
        $this->makeBooking($client, $pendingDate, 'Pending');
        $this->makeBooking($client, $approvedDate, 'Approved');

        $response = $this->actingAs($clientUser)->getJson(route('client.bookings.availability', [
            'month' => (int) now()->addDays(14)->month,
            'year' => (int) now()->addDays(14)->year,
        ]));

        $response->assertOk()
            ->assertJsonPath($pendingDate.'.status', 'pending')
            ->assertJsonPath($approvedDate.'.status', 'booked');
    }

    public function test_client_can_submit_and_view_an_appointment_with_owner_protection(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        [, $otherClient] = $this->makeClient('Private Client');
        $booking = $this->makeBooking($client, now()->addDays(20)->toDateString());

        $this->actingAs($clientUser)->post(route('client.appointments.store'), [
            'booking_id' => $booking->id,
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '10:30',
            'purpose' => 'Initial Consultation',
            'notes' => 'Discuss event details',
        ])->assertRedirect(route('client.appointments.index'));

        $appointment = Appointment::where('client_id', $client->id)->firstOrFail();
        $this->assertSame('Pending', $appointment->status);
        $this->actingAs($clientUser)->get(route('client.appointments.create'))->assertOk();
        $this->get(route('client.appointments.index'))->assertOk()->assertSee($appointment->purpose);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'appointment_id' => $appointment->id,
            'notification_type' => 'appointment_submitted',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'appointment_id' => $appointment->id,
            'notification_type' => 'appointment_submitted',
        ]);
        Mail::assertSent(WorkflowNotificationMail::class, fn (WorkflowNotificationMail $mail) =>
            $mail->hasTo($admin->email)
            && $mail->details['Purpose'] === 'Initial Consultation'
            && $mail->details['Status'] === 'Pending'
            && str_contains($mail->actionUrl, '/admin/appointments/'.$appointment->id)
        );

        $this->get(route('client.appointments.show', $appointment))->assertOk();
        $foreignAppointment = $this->makeAppointment($otherClient);
        $this->get(route('client.appointments.show', $foreignAppointment))->assertNotFound();
    }

    public function test_admin_can_approve_decline_and_reschedule_appointments(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        $confirmed = $this->makeAppointment($client);
        $declined = $this->makeAppointment($client);
        $rescheduled = $this->makeAppointment($client);
        $toComplete = $this->makeAppointment($client, 'Confirmed');
        $toCancel = $this->makeAppointment($client, 'Rescheduled');

        $this->actingAs($admin)->get(route('admin.appointments.index'))->assertOk();
        $this->get(route('admin.appointments.show', $confirmed))->assertOk();
        $this->actingAs($admin)->post(route('admin.appointments.approve', $confirmed));
        $this->post(route('admin.appointments.decline', $declined));
        $newDate = now()->addDays(16)->toDateString();
        $this->post(route('admin.appointments.reschedule', $rescheduled), [
            'appointment_date' => $newDate,
            'appointment_time' => '14:15',
        ]);
        $this->post(route('admin.appointments.complete', $toComplete));
        $this->post(route('admin.appointments.cancel', $toCancel));

        $this->assertDatabaseHas('appointments', ['id' => $confirmed->id, 'status' => 'Confirmed']);
        $this->assertDatabaseHas('appointments', ['id' => $declined->id, 'status' => 'Declined']);
        $this->assertDatabaseHas('appointments', ['id' => $toComplete->id, 'status' => 'Completed']);
        $this->assertDatabaseHas('appointments', ['id' => $toCancel->id, 'status' => 'Cancelled']);
        $this->assertDatabaseHas('appointments', [
            'id' => $rescheduled->id,
            'status' => 'Rescheduled',
            'appointment_date' => $newDate,
            'appointment_time' => '14:15:00',
        ]);
        Mail::assertSent(WorkflowNotificationMail::class, fn (WorkflowNotificationMail $mail) =>
            $mail->hasTo($clientUser->email)
            && $mail->details['Status'] === 'Rescheduled'
            && $mail->details['Appointment date'] === \Carbon\Carbon::parse($newDate)->format('F j, Y')
            && str_contains($mail->actionUrl, '/client/appointments/'.$rescheduled->id)
        );
    }

    public function test_calendar_routes_respect_client_privacy_and_admin_visibility(): void
    {
        [$clientUser, $client] = $this->makeClient('Calendar Owner');
        [$otherUser, $otherClient] = $this->makeClient('Private Calendar Client');
        [$admin] = $this->makeAdmin();
        $date = now()->addDays(18)->toDateString();
        $privateBooking = $this->makeBooking($otherClient, $date, 'Pending', null, 'Private Corporate Event');
        $confirmedAppointment = $this->makeAppointment($client, 'Confirmed');
        $confirmedAppointment->update(['appointment_date' => $date]);
        $this->assertSame('pending', Booking::monthAvailability(
            (int) now()->addDays(18)->year,
            (int) now()->addDays(18)->month,
        )[$date]['status'] ?? null);

        $clientResponse = $this->actingAs($clientUser)->get(route('client.calendar.index', [
            'month' => (int) now()->addDays(18)->month,
            'year' => (int) now()->addDays(18)->year,
        ]));
        $clientResponse->assertOk()
            ->assertViewHas('statusMap', fn (array $statusMap) => ($statusMap[$date] ?? null) === 'pending')
            ->assertViewHas('appointments', fn ($appointments) => $appointments->contains('id', $confirmedAppointment->id))
            ->assertDontSee('Private Calendar Client')
            ->assertDontSee('Private Corporate Event');

        $adminResponse = $this->actingAs($admin)->get(route('admin.calendar.index', [
            'month' => (int) now()->addDays(18)->month,
            'year' => (int) now()->addDays(18)->year,
        ]));
        $adminResponse->assertOk()->assertViewHas('eventDetails', function (array $details) use ($date, $privateBooking): bool {
            return ($details[$date]['booking_id'] ?? null) === $privateBooking->id
                && ($details[$date]['client_name'] ?? null) === 'Private Calendar Client';
        });
        $this->assertNotSame($otherUser->id, $clientUser->id);
    }

    public function test_client_packages_and_admin_package_management(): void
    {
        [$clientUser] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        $available = $this->makePackage('Garden Reception', true);
        $unavailable = $this->makePackage('Archived Package', false);

        $this->actingAs($clientUser)->get(route('client.packages.index'))
            ->assertOk()->assertSee('Garden Reception')->assertDontSee('Archived Package');
        $this->get(route('client.packages.show', $available))->assertOk()->assertSee($available->description);
        $this->get(route('client.bookings.create', ['package' => $available->id]))
            ->assertOk()->assertSee($available->package_name);
        $this->get(route('client.packages.show', $unavailable))->assertNotFound();

        $this->actingAs($admin)->get(route('admin.packages.index'))->assertOk();
        $this->get(route('admin.packages.create'))->assertOk();
        $this->actingAs($admin)->post(route('admin.packages.store'), [
            'package_name' => 'New Package',
            'description' => 'Package details',
            'price' => '15000.00',
            'inclusions' => 'Venue styling, table setup',
            'is_available' => '1',
        ])->assertRedirect(route('admin.packages.index'));

        $created = EventPackage::where('package_name', 'New Package')->firstOrFail();
        $this->get(route('admin.packages.edit', $created))->assertOk();
        $this->put(route('admin.packages.update', $created), [
            'package_name' => 'Updated Package',
            'description' => 'Updated details',
            'price' => '17500.00',
            'inclusions' => 'Updated inclusions',
            'is_available' => '0',
        ])->assertRedirect(route('admin.packages.index'));
        $this->assertDatabaseHas('event_packages', [
            'id' => $created->id,
            'package_name' => 'Updated Package',
            'price' => '17500.00',
            'is_available' => 0,
        ]);
    }

    public function test_inventory_records_validate_update_and_notify_on_low_stock_transition(): void
    {
        [$admin] = $this->makeAdmin();
        $this->actingAs($admin)->get(route('admin.inventory.create'))->assertOk();
        $this->actingAs($admin)->post(route('admin.inventory.store'), [
            'item_name' => 'Folding Chairs',
            'category' => 'Chairs',
            'quantity_total' => 20,
            'low_stock_threshold' => 5,
            'notes' => 'White resin chairs',
        ])->assertRedirect(route('admin.inventory.index'));

        $item = InventoryItem::where('item_name', 'Folding Chairs')->firstOrFail();
        $this->assertSame(20, $item->quantity_available);
        $this->assertSame('Available', $item->status);
        $this->get(route('admin.inventory.index'))->assertOk()->assertSee('Available');
        $this->get(route('admin.inventory.show', $item))->assertOk();
        $this->get(route('admin.inventory.edit', $item))->assertOk();

        $this->put(route('admin.inventory.update', $item), [
            'item_name' => 'Folding Chairs',
            'category' => 'Chairs',
            'quantity_total' => 20,
            'quantity_available' => 4,
            'quantity_damaged' => 0,
            'low_stock_threshold' => 5,
            'notes' => 'Recounted',
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame('Low Stock', $item->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'notification_type' => 'inventory_low_stock',
        ]);

        $this->from(route('admin.inventory.create'))->post(route('admin.inventory.store'), [
            'item_name' => 'Invalid Inventory',
            'category' => 'Not a category',
            'quantity_total' => -1,
            'low_stock_threshold' => 2,
        ])->assertSessionHasErrors(['category', 'quantity_total']);
        $this->assertDatabaseMissing('inventory_items', ['item_name' => 'Invalid Inventory']);
    }

    public function test_notification_lists_and_actions_are_limited_to_the_recipient(): void
    {
        [$clientUser] = $this->makeClient();
        [$otherUser] = $this->makeClient('Second Client');
        $own = Notification::create([
            'user_id' => $clientUser->id,
            'message' => 'Own notification',
            'notification_type' => 'general',
            'status' => 'unread',
        ]);
        $foreign = Notification::create([
            'user_id' => $otherUser->id,
            'message' => 'Private notification',
            'notification_type' => 'general',
            'status' => 'unread',
        ]);

        $this->actingAs($clientUser)->get(route('client.notifications.index'))
            ->assertOk()->assertSee('Own notification')->assertDontSee('Private notification');
        $this->post(route('client.notifications.read', $own))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['id' => $own->id, 'status' => 'read']);
        $this->post(route('client.notifications.read', $foreign))->assertForbidden();
        $this->delete(route('client.notifications.destroy', $foreign))->assertForbidden();
    }

    public function test_reviews_require_completed_own_booking_and_admin_can_moderate(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        [, $otherClient] = $this->makeClient('Other Reviewer');
        $completed = $this->makeBooking($client, now()->subDays(2)->toDateString(), 'Completed');
        $pending = $this->makeBooking($client, now()->addDays(20)->toDateString(), 'Pending');

        $this->actingAs($clientUser)->get(route('client.reviews.create', $pending))->assertNotFound();
        $this->post(route('client.reviews.store', $completed), [
            'title' => 'Excellent event',
            'rating' => 6,
            'feedback' => 'The team was excellent.',
        ])->assertSessionHasErrors('rating');

        $this->post(route('client.reviews.store', $completed), [
            'title' => 'Excellent event',
            'rating' => 5,
            'feedback' => 'The team was excellent.',
        ])->assertRedirect(route('client.bookings.show', $completed));

        $review = Review::where('booking_id', $completed->id)->firstOrFail();
        $this->assertSame('Pending', $review->status);
        $this->get(route('client.reviews.index'))->assertOk()->assertSee('Excellent event');
        $this->get(route('client.bookings.show', $completed))->assertOk();
        $this->get(route('client.reviews.create', $this->makeBooking($otherClient, now()->subDays(3)->toDateString(), 'Completed')))
            ->assertForbidden();

        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk();
        $this->post(route('admin.reviews.approve', $review));
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'status' => 'Approved']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'notification_type' => 'review_approved',
        ]);
        $this->get(route('admin.reviews.analytics'))->assertOk();
    }

    public function test_reports_are_admin_only_and_include_period_totals(): void
    {
        [$clientUser, $client] = $this->makeClient();
        [$admin] = $this->makeAdmin();
        $booking = $this->makeBooking($client, now()->addDays(24)->toDateString(), 'Approved');

        $this->actingAs($clientUser)->get(route('admin.reports.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'year']))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['total_bookings_period'] === 1
                    && $stats['new_clients_period'] === 1
                    && $stats['upcoming_events'] === 1;
            });

        $this->get(route('admin.reports.bookings', ['period' => 'year']))
            ->assertOk()->assertSee($booking->event_type);
        $this->get(route('admin.reports.clients', ['period' => 'year']))->assertOk();
        $this->get(route('admin.reports.upcoming-events'))->assertOk()->assertSee($booking->event_type);
    }

    public function test_client_management_is_admin_only_and_shows_related_records(): void
    {
        [$clientUser, $client] = $this->makeClient('Managed Client');
        [$admin] = $this->makeAdmin();
        $booking = $this->makeBooking($client, now()->addDays(17)->toDateString());
        $appointment = $this->makeAppointment($client, 'Pending', $booking);

        $this->actingAs($clientUser)->get(route('admin.clients.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.clients.index', ['search' => 'Managed Client']))
            ->assertOk()->assertSee('Managed Client');
        $details = $this->get(route('admin.clients.show', $client));
        $details->assertOk();
        $this->assertTrue(str_contains($details->getContent(), $booking->event_type));
        $this->assertTrue(
            str_contains($details->getContent(), $appointment->purpose),
            'The client details page should display the related appointment record.'
        );
    }

    private function makeClient(string $name = 'Test Client'): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'+'.uniqid().'@example.test',
            'phone' => '09171234567',
            'role' => 'client',
            'password' => Hash::make('Password123!'),
        ]);
        $client = Client::create(['user_id' => $user->id, 'address' => 'Test Address']);

        return [$user, $client];
    }

    private function makeAdmin(string $name = 'Test Admin'): array
    {
        $admin = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'+'.uniqid().'@example.test',
            'phone' => '09179876543',
            'role' => 'admin',
            'password' => Hash::make('Password123!'),
        ]);

        return [$admin];
    }

    private function makePackage(string $name = 'Test Package', bool $available = true): EventPackage
    {
        return EventPackage::create([
            'package_name' => $name.' '.uniqid(),
            'description' => 'A detailed event package description.',
            'price' => 12000,
            'inclusions' => 'Planning, setup, and coordination',
            'is_available' => $available,
        ]);
    }

    private function makeBooking(
        Client $client,
        string $date,
        string $status = 'Pending',
        ?EventPackage $package = null,
        string $eventType = 'Birthday Celebration',
    ): Booking {
        return Booking::create([
            'client_id' => $client->id,
            'package_id' => $package?->id,
            'event_date' => $date,
            'event_type' => $eventType,
            'venue' => 'Test Venue',
            'status' => $status,
        ]);
    }

    private function makeAppointment(
        Client $client,
        string $status = 'Pending',
        ?Booking $booking = null,
    ): Appointment {
        return Appointment::create([
            'client_id' => $client->id,
            'booking_id' => $booking?->id,
            'appointment_date' => now()->addDays(8)->toDateString(),
            'appointment_time' => '10:00:00',
            'purpose' => 'Initial Consultation',
            'status' => $status,
            'notes' => 'Fixture notes',
        ]);
    }
}