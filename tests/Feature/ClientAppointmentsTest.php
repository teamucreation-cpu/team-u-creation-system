<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAppointmentsTest extends TestCase
{
    use RefreshDatabase;

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
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Rescheduled',
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
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Rescheduled',
            'appointment_time' => '15:00:00',
        ]);
    }

    private function createClient(): array
    {
        $user = User::factory()->create(['role' => 'client']);

        return [$user, Client::create(['user_id' => $user->id])];
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