<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_details_show_only_the_appointment_related_booking(): void
    {
        [, $client] = $this->createClient();
        $relatedBooking = $this->createBooking($client, 'Wedding Celebration');
        $this->createBooking($client, 'Birthday Party');
        $appointment = $this->createAppointment($client, ['booking_id' => $relatedBooking->id]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Purpose:</strong> Initial Consultation', false)
            ->assertSee('Related Booking:</strong> Wedding Celebration', false)
            ->assertSee('Reschedule Appointment')
            ->assertSee('Do you want to reschedule this appointment?')
            ->assertSee('name="appointment_date"', false)
            ->assertSee('name="appointment_time"', false)
            ->assertSee('name="reason"', false)
            ->assertDontSee('Confirm Reschedule')
            ->assertDontSee('rescheduleForm')
            ->assertDontSee('Birthday Party');

        $unrelatedAppointment = $this->createAppointment($client);
        $this->get(route('admin.appointments.show', $unrelatedAppointment))
            ->assertOk()
            ->assertSee('Related Booking:</strong> None', false);
    }

    public function test_client_sees_only_their_appointments_and_can_open_details(): void
    {
        [$clientUser, $client] = $this->createClient();
        [, $otherClient] = $this->createClient();
        $appointment = $this->createAppointment($client);
        $otherAppointment = $this->createAppointment($otherClient, ['purpose' => 'Package Discussion']);

        $this->actingAs($clientUser)
            ->get(route('client.appointments.index'))
            ->assertOk()
            ->assertSee('My Appointments')
            ->assertSee('Appointment Date')
            ->assertSee('Date Requested')
            ->assertSee('Last Updated')
            ->assertSee('Pending')
            ->assertDontSee('Package Discussion');

        $this->actingAs($clientUser)
            ->get(route('client.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Initial Consultation');

        $this->actingAs($clientUser)
            ->get(route('client.appointments.show', $otherAppointment))
            ->assertNotFound();
    }

    public function test_admin_status_update_notifies_the_correct_client_and_appointment(): void
    {
        [$clientUser, $client] = $this->createClient();
        $appointment = $this->createAppointment($client);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.appointments.approve', $appointment))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Confirmed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'appointment_id' => $appointment->id,
            'notification_type' => 'appointment_confirmed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertDontSee(route('admin.appointments.approve', $appointment))
            ->assertDontSee(route('admin.appointments.decline', $appointment))
            ->assertDontSee(route('admin.appointments.reschedule', $appointment))
            ->assertSee('Complete')
            ->assertDontSee('Cancel');

        $this->actingAs($admin)
            ->post(route('admin.appointments.cancel', $appointment))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Confirmed',
        ]);

        $this->actingAs($clientUser)
            ->get(route('client.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Confirmed')
            ->assertDontSee('Edit Appointment')
            ->assertDontSee('Cancel Appointment');

        $this->actingAs($admin)
            ->post(route('admin.appointments.decline', $appointment))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Confirmed',
        ]);

        $this->assertInstanceOf(Appointment::class, Notification::where('appointment_id', $appointment->id)->first()->appointment);
    }

    public function test_rescheduled_appointment_cannot_be_approved_declined_or_rescheduled_again(): void
    {
        [, $client] = $this->createClient();
        $appointment = $this->createAppointment($client);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.appointments.reschedule', $appointment), [
                'appointment_date' => now()->addDays(5)->toDateString(),
                'appointment_time' => '15:00',
                'reason' => 'The original time is unavailable.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::RESCHEDULE_AWAITING_CLIENT,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertDontSee(route('admin.appointments.approve', $appointment))
            ->assertDontSee(route('admin.appointments.decline', $appointment))
            ->assertDontSee(route('admin.appointments.reschedule', $appointment));

        $this->actingAs($admin)->post(route('admin.appointments.approve', $appointment));
        $this->actingAs($admin)->post(route('admin.appointments.decline', $appointment));
        $this->actingAs($admin)->post(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => now()->addDays(7)->toDateString(),
            'appointment_time' => '16:00',
            'reason' => 'The original time is unavailable.',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::RESCHEDULE_AWAITING_CLIENT,
            'appointment_time' => '14:30:00',
            'proposed_appointment_time' => '15:00:00',
            'reschedule_reason' => 'The original time is unavailable.',
        ]);
    }

    public function test_client_can_confirm_or_decline_admin_reschedule_proposals(): void
    {
        [$clientUser, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $confirmed = $this->createAppointment($client, ['notes' => "Client's original notes."]);
        $declined = $this->createAppointment($client, ['appointment_time' => '16:00:00']);
        $originalDate = $confirmed->appointment_date->toDateString();

        $this->actingAs($admin)->post(route('admin.appointments.reschedule', $confirmed), [
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '15:00',
            'reason' => 'The administrator is unavailable at the original time.',
        ])->assertRedirect();
        $this->post(route('admin.appointments.reschedule', $declined), [
            'appointment_date' => now()->addDays(7)->toDateString(),
            'appointment_time' => '17:00',
            'reason' => 'A scheduling conflict requires a new time.',
        ])->assertRedirect();

        $this->actingAs($clientUser)
            ->get(route('client.appointments.show', $confirmed))
            ->assertOk()
            ->assertSee('The administrator is unavailable at the original time.')
            ->assertSee('Client&#039;s original notes.', false)
            ->assertSee('Appointment Status:</strong> Rescheduled – Awaiting Your Confirmation', false)
            ->assertSee('Original Appointment Date:')
            ->assertSee('New Appointment Date:')
            ->assertSee('New Appointment Time:')
            ->assertSeeInOrder([
                'Original Appointment Date:',
                'Original Appointment Time:',
                'Purpose:',
                'Date Requested:',
                'Last Updated:',
                'Notes:',
                'Related Booking:',
                'Reason for Rescheduling (Admin Notes):',
                'New Appointment Date:',
                'New Appointment Time:',
                'Date Requested:',
                'Last Updated:',
            ])
            ->assertDontSee('Proposed Appointment Date:')
            ->assertSee('Do you want to confirm the rescheduled appointment?')
            ->assertSee('Do you want to decline the rescheduled appointment? If the proposed schedule is not convenient, please book a new appointment.')
            ->assertSee('Yes, Decline');
        $this->post(route('client.appointments.reschedule.confirm', $confirmed))->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $confirmed->id,
            'status' => 'Confirmed',
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '15:00:00',
            'original_appointment_date' => $originalDate,
            'original_appointment_time' => '14:30:00',
            'proposed_appointment_date' => now()->addDays(6)->toDateString(),
            'reschedule_reason' => 'The administrator is unavailable at the original time.',
            'notes' => "Client's original notes.",
            'reschedule_response' => 'Confirmed',
        ]);
        $this->assertSame($originalDate, $confirmed->fresh()->original_appointment_date->toDateString());
        $this->assertNotNull($confirmed->fresh()->reschedule_responded_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $clientUser->id,
            'appointment_id' => $confirmed->id,
            'notification_type' => 'appointment_rescheduled',
            'message' => 'Your appointment with the administrator has been rescheduled. Please review the updated appointment details and confirm or decline the proposed schedule.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'appointment_id' => $confirmed->id,
            'notification_type' => 'appointment_reschedule_confirmed',
        ]);
        Mail::assertSent(\App\Mail\WorkflowNotificationMail::class, fn ($mail) =>
            $mail->hasTo($admin->email)
            && $mail->subjectLine === 'Client Appointment Reschedule Confirmed – Team U Creations'
        );

        $this->post(route('client.appointments.reschedule.confirm', $confirmed))->assertRedirect();
        $this->assertSame(1, \App\Models\Notification::where('appointment_id', $confirmed->id)
            ->where('notification_type', 'appointment_reschedule_confirmed')->count());

        $this->get(route('client.calendar.index'))
            ->assertOk()
            ->assertViewHas('appointmentsByDate', fn (array $appointments) =>
                in_array('3:00 PM – Initial Consultation', $appointments[now()->addDays(6)->toDateString()] ?? [], true)
            );
        $this->get(route('client.appointments.show', $confirmed))
            ->assertOk()
            ->assertDontSee('confirmRescheduleModal')
            ->assertDontSee('declineRescheduleModal');

        $this->post(route('client.appointments.reschedule.decline', $declined))->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $declined->id,
            'status' => 'Declined',
            'proposed_appointment_time' => '17:00:00',
            'reschedule_response' => 'Declined',
        ]);
        $this->assertSame($originalDate, $declined->fresh()->appointment_date->toDateString());
        $this->assertNotNull($declined->fresh()->reschedule_responded_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'appointment_id' => $declined->id,
            'notification_type' => 'appointment_reschedule_declined',
        ]);
        Mail::assertSent(\App\Mail\WorkflowNotificationMail::class, fn ($mail) =>
            $mail->hasTo($admin->email)
            && $mail->subjectLine === 'Client Appointment Reschedule Declined – Team U Creations'
        );
        $this->post(route('client.appointments.reschedule.decline', $declined))->assertRedirect();
        $this->assertSame(1, \App\Models\Notification::where('appointment_id', $declined->id)
            ->where('notification_type', 'appointment_reschedule_declined')->count());
        $declined->refresh();
        $this->assertFalse(Appointment::hasScheduleConflict(
            $declined->proposed_appointment_date->toDateString(),
            $declined->proposed_appointment_time,
        ));
        $this->get(route('client.appointments.show', $declined))
            ->assertOk()
            ->assertSee('You have declined the rescheduled appointment. Please book a new appointment to select your preferred date and time.')
            ->assertSee('Book New Appointment')
            ->assertDontSee('confirmRescheduleModal');
    }

    public function test_admin_cannot_propose_a_conflicting_schedule_or_omit_the_reason(): void
    {
        [, $client] = $this->createClient();
        $admin = User::factory()->create(['role' => 'admin']);
        $target = $this->createAppointment($client);
        $occupied = $this->createAppointment($client, [
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '15:00:00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.appointments.reschedule', $target), [
                'appointment_date' => $occupied->appointment_date->toDateString(),
                'appointment_time' => '15:00',
                'reason' => 'The original slot is unavailable.',
            ])
            ->assertSessionHasErrors('appointment_time');

        $this->post(route('admin.appointments.reschedule', $target), [
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '15:00',
        ])->assertSessionHasErrors('reason');

        $pastSchedule = Carbon::now(config('app.display_timezone', 'Asia/Manila'))->subMinute();
        $this->post(route('admin.appointments.reschedule', $target), [
            'appointment_date' => $pastSchedule->toDateString(),
            'appointment_time' => $pastSchedule->format('H:i'),
            'reason' => 'The selected schedule has passed.',
        ])->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseHas('appointments', [
            'id' => $target->id,
            'status' => 'Pending',
            'proposed_appointment_date' => null,
            'reschedule_reason' => null,
        ]);
    }

    private function createClient(): array
    {
        $user = User::factory()->create(['role' => 'client']);

        return [$user, Client::create(['user_id' => $user->id])];
    }

    private function createBooking(Client $client, string $eventType): Booking
    {
        return Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->addDays(20)->toDateString(),
            'event_type' => $eventType,
            'venue' => 'Test Venue',
            'status' => 'Pending',
        ]);
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