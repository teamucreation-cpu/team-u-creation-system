<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['client.user', 'booking'])->latest()->get();
        return view('admin.appointments.index', compact('appointments'));
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['client.user', 'booking']);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function approve(Appointment $appointment)
    {
        if (! $this->transition($appointment, ['Pending'], ['status' => 'Confirmed'])) {
            return back()->with('error', 'Only pending appointments can be approved.');
        }

        $appointment->refresh();
        $clientUser = $appointment->client->user;
        app(NotificationService::class)->notifyAppointmentConfirmed($clientUser, $appointment);

        return back()->with('success', 'Appointment approved.');
    }

    public function decline(Appointment $appointment)
    {
        if (! $this->transition($appointment, ['Pending'], ['status' => 'Declined'])) {
            return back()->with('error', 'Only pending appointments can be declined.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAppointmentDeclined($appointment->client->user, $appointment);

        return back()->with('success', 'Appointment declined.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $appointment->refresh();
        if ($appointment->status !== 'Pending') {
            return back()->with('error', 'Only pending appointments can be rescheduled.');
        }

        $updatedDate = Carbon::parse($validated['appointment_date'])->toDateString();
        $updatedTime = Carbon::parse($validated['appointment_time'])->format('H:i:s');
        $timezone = config('app.display_timezone', 'Asia/Manila');
        $proposedSchedule = Carbon::parse($updatedDate.' '.$updatedTime, $timezone);
        if ($proposedSchedule->lessThanOrEqualTo(Carbon::now($timezone))) {
            return back()->withErrors(['appointment_date' => 'Choose a future appointment date and time.'])->withInput();
        }

        $oldDate = $appointment->appointment_date->toDateString();
        $oldTime = Carbon::parse($appointment->appointment_time)->format('H:i:s');

        if ($oldDate === $updatedDate && $oldTime === $updatedTime) {
            return back()
                ->withErrors(['appointment_date' => 'Choose a different appointment date or time to reschedule.'])
                ->withInput();
        }

        if (Appointment::hasScheduleConflict($updatedDate, $updatedTime, $appointment->id)) {
            return back()->withErrors(['appointment_time' => 'Another appointment is already scheduled for that date and time.'])->withInput();
        }

        $previousSchedule = $appointment->appointment_date->format('F j, Y').' at '.Carbon::parse($appointment->appointment_time)->format('g:i A');
        if (! $this->transition($appointment, ['Pending'], [
            'original_appointment_date' => $oldDate,
            'original_appointment_time' => $oldTime,
            'proposed_appointment_date' => $updatedDate,
            'proposed_appointment_time' => $updatedTime,
            'reschedule_reason' => $validated['reason'],
            'status' => Appointment::RESCHEDULE_AWAITING_CLIENT,
        ])) {
            return back()->with('error', 'Only pending appointments can be rescheduled.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAppointmentRescheduled($appointment->client->user, $appointment, $previousSchedule);

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function complete(Appointment $appointment)
    {
        if (! $this->transition($appointment, ['Confirmed', 'Rescheduled'], ['status' => 'Completed'])) {
            return back()->with('error', 'Only confirmed or rescheduled appointments can be completed.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAppointmentCompleted($appointment->client->user, $appointment);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function cancel(Appointment $appointment)
    {
        if (! $this->transition($appointment, ['Rescheduled'], ['status' => 'Cancelled'])) {
            return back()->with('error', 'Only rescheduled appointments can be cancelled.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAppointmentCancelled($appointment->client->user, $appointment);

        return back()->with('success', 'Appointment cancelled.');
    }

    private function transition(Appointment $appointment, array $allowedStatuses, array $attributes): bool
    {
        return Appointment::query()
            ->whereKey($appointment->getKey())
            ->whereIn('status', $allowedStatuses)
            ->update($attributes) > 0;
    }
}