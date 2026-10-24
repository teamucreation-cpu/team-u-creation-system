<?php

namespace Tests\Feature;

use App\Mail\WorkflowNotificationMail;
use App\Models\Appointment;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\PhilippineTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkflowNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_booking_and_appointment_notify_every_admin(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($clientUser)->post(route('client.bookings.store'), [
            'event_type' => 'Birthday Party',
            'venue' => 'Main Hall',
            'event_date' => now()->addDays(20)->toDateString(),
        ])->assertRedirect();

        $booking = Booking::where('client_id', $client->id)->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_submitted',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $secondAdmin->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_submitted',
        ]);

        $this->actingAs($clientUser)->post(route('client.appointments.store'), [
            'booking_id' => null,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '14:30',
            'purpose' => 'Initial Consultation',
        ])->assertRedirect();

        $appointment = Appointment::where('client_id', $client->id)->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'appointment_id' => $appointment->id,
            'notification_type' => 'appointment_submitted',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $secondAdmin->id,
            'appointment_id' => $appointment->id,
            'notification_type' => 'appointment_submitted',
        ]);

        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'New Event Booking Request – Team U Creations' && $mail->hasTo($admin->email));
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'New Appointment Request – Team U Creations' && $mail->hasTo($secondAdmin->email));
        Mail::assertSentTimes(WorkflowNotificationMail::class, 4);
        Mail::assertNotQueued(WorkflowNotificationMail::class);
    }

    public function test_smtp_failure_is_logged_and_keeps_the_in_system_notification(): void
    {
        Log::spy();
        Mail::shouldReceive('mailer')
            ->once()
            ->with('smtp')
            ->andThrow(new \RuntimeException('SMTP transport unavailable.'));

        [$clientUser, $client] = $this->createClient();
        $booking = $this->createBooking($client);

        app(NotificationService::class)->notifyBookingApproved($booking);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_approved',
            'status' => 'unread',
        ]);
        Log::shouldHaveReceived('error')->once()->with(
            'Workflow notification email delivery failed.',
            \Mockery::on(fn (array $context) => $context['user_id'] === $clientUser->id && $context['mailer'] === 'smtp')
        );
    }

    public function test_booking_status_changes_notify_only_the_owner_and_retries_do_not_duplicate(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        [$otherClientUser] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createBooking($client);

        $this->actingAs($admin)->post(route('admin.bookings.approve', $booking))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.bookings.approve', $booking))->assertRedirect();

        Mail::assertSentTimes(WorkflowNotificationMail::class, 1);
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Event Booking Approved – Team U Creations' && $mail->hasTo($clientUser->email) && ! $mail->hasTo($otherClientUser->email));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'booking_id' => $booking->id,
            'notification_type' => 'booking_approved',
        ]);
    }

    public function test_booking_decline_and_reschedule_send_the_matching_emails(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $declined = $this->createBooking($client);
        $previousDate = now()->addDays(25)->toDateString();
        $rescheduled = $this->createBooking($client, ['event_date' => $previousDate]);

        $this->actingAs($admin)->post(route('admin.bookings.decline', $declined))->assertRedirect();
        $newDate = now()->addDays(35)->toDateString();
        $this->actingAs($admin)->post(route('admin.bookings.reschedule', $rescheduled), ['event_date' => $newDate])->assertRedirect();

        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Event Booking Declined – Team U Creations' && $mail->hasTo($clientUser->email));
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Event Booking Rescheduled – Team U Creations' && $mail->hasTo($clientUser->email) && str_contains($mail->details['Previous event date'], Carbon::parse($previousDate)->format('F j, Y')));
        $this->assertDatabaseHas('bookings', ['id' => $rescheduled->id, 'status' => 'Rescheduled', 'event_date' => $newDate]);
    }

    public function test_booking_submission_approval_and_decline_email_triggers_use_booking_records(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($clientUser)->post(route('client.bookings.store'), [
            'event_type' => 'Birthday Party',
            'venue' => 'Main Hall',
            'event_date' => now()->addDays(20)->toDateString(),
        ])->assertRedirect();

        $booking = Booking::where('client_id', $client->id)->firstOrFail();
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'New Event Booking Request – Team U Creations' && $mail->hasTo($admin->email));

        $this->actingAs($admin)->post(route('admin.bookings.approve', $booking))->assertRedirect();
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Event Booking Approved – Team U Creations' && $mail->hasTo($clientUser->email));

        $declined = $this->createBooking($client);
        $this->actingAs($admin)->post(route('admin.bookings.decline', $declined))->assertRedirect();
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Event Booking Declined – Team U Creations' && $mail->hasTo($clientUser->email));
    }

    public function test_appointment_approval_decline_and_reschedule_notify_the_owner(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $approved = $this->createAppointment($client);
        $declined = $this->createAppointment($client, ['appointment_time' => '15:00:00']);
        $rescheduled = $this->createAppointment($client, ['appointment_time' => '16:00:00']);

        $this->actingAs($admin)->post(route('admin.appointments.approve', $approved))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.appointments.approve', $approved))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.appointments.decline', $declined))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.appointments.reschedule', $rescheduled), [
            'appointment_date' => now()->addDays(8)->toDateString(),
            'appointment_time' => '17:30',
        ])->assertRedirect();

        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Appointment Approved – Team U Creations' && $mail->hasTo($clientUser->email));
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Appointment Declined – Team U Creations' && $mail->hasTo($clientUser->email));
        Mail::assertSent(WorkflowNotificationMail::class, fn ($mail) => $mail->subjectLine === 'Appointment Rescheduled – Team U Creations' && $mail->hasTo($clientUser->email) && isset($mail->details['Previous schedule']));
        Mail::assertSentTimes(WorkflowNotificationMail::class, 3);
    }

    public function test_unchanged_reschedules_and_failed_status_updates_send_nothing(): void
    {
        Mail::fake();
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createBooking($client, ['status' => 'Approved']);
        $appointment = $this->createAppointment($client);

        $this->actingAs($admin)->post(route('admin.bookings.approve', $booking))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $appointment->appointment_date->toDateString(),
            'appointment_time' => $appointment->appointment_time,
        ])->assertRedirect();

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('notifications', ['user_id' => $clientUser->id, 'booking_id' => $booking->id, 'notification_type' => 'booking_approved']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $clientUser->id, 'appointment_id' => $appointment->id, 'notification_type' => 'appointment_rescheduled']);
    }

    public function test_notification_opening_is_scoped_and_marks_only_the_owners_notification_read(): void
    {
        [$clientUser, $client] = $this->createClient();
        [$otherUser] = $this->createClient();
        $booking = $this->createBooking($client);
        $ownNotification = Notification::create([
            'user_id' => $clientUser->id,
            'booking_id' => $booking->id,
            'message' => 'Booking updated.',
            'notification_type' => 'booking_approved',
            'status' => 'unread',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $otherUser->id,
            'message' => 'Private notice.',
            'notification_type' => 'general',
            'status' => 'unread',
        ]);

        $this->actingAs($clientUser)->post(route('client.notifications.open', $ownNotification))
            ->assertRedirect(route('client.bookings.show', $booking));
        $this->assertDatabaseHas('notifications', ['id' => $ownNotification->id, 'status' => 'read']);

        $this->actingAs($clientUser)->post(route('client.notifications.open', $otherNotification))->assertNotFound();
        $this->actingAs($clientUser)->post(route('client.notifications.markAllRead'))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['id' => $otherNotification->id, 'status' => 'unread']);
    }

    public function test_admin_notification_feed_and_mark_all_read_are_scoped_to_that_admin(): void
    {
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createBooking($client);
        $ownNotification = Notification::create([
            'user_id' => $admin->id,
            'booking_id' => $booking->id,
            'message' => 'New booking request.',
            'notification_type' => 'booking_submitted',
            'status' => 'unread',
        ]);
        Notification::create([
            'user_id' => $clientUser->id,
            'booking_id' => $booking->id,
            'message' => 'Client-only notification.',
            'notification_type' => 'booking_approved',
            'status' => 'unread',
        ]);
        $otherAdminNotification = Notification::create([
            'user_id' => $otherAdmin->id,
            'message' => 'Other admin alert.',
            'notification_type' => 'general',
            'status' => 'unread',
        ]);

        $this->actingAs($admin)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('New booking request.')
            ->assertDontSee('Client-only notification.')
            ->assertDontSee('Other admin alert.');

        $this->actingAs($admin)->post(route('admin.notifications.open', $ownNotification))
            ->assertRedirect(route('admin.bookings.show', $booking));
        $this->actingAs($admin)->post(route('admin.notifications.markAllRead'))->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $ownNotification->id, 'status' => 'read']);
        $this->assertDatabaseHas('notifications', ['id' => $otherAdminNotification->id, 'status' => 'unread']);
    }

    public function test_notification_timestamps_are_displayed_in_manila_time(): void
    {
        $timestamp = Carbon::parse('2026-10-22 04:00:00', 'UTC');

        $this->assertSame('October 22, 2026 – 12:00 PM', PhilippineTime::format($timestamp));
    }

    private function createClient(): array
    {
        $user = User::factory()->create(['role' => 'client']);

        return [$user, Client::create(['user_id' => $user->id])];
    }

    private function createBooking(Client $client, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'client_id' => $client->id,
            'event_date' => now()->addDays(20)->toDateString(),
            'event_type' => 'Birthday Party',
            'venue' => 'Main Hall',
            'status' => 'Pending',
        ], $attributes));
    }

    private function createAppointment(Client $client, array $attributes = []): Appointment
    {
        return Appointment::create(array_merge([
            'client_id' => $client->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '14:30:00',
            'purpose' => 'Initial Consultation',
            'status' => 'Pending',
        ], $attributes));
    }
}