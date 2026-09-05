<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\NotificationService;
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
        $appointment->update(['status' => 'Approved']);

        $clientUser = $appointment->client->user;
        app(NotificationService::class)->notifyAppointmentConfirmed($clientUser);

        return back()->with('success', 'Appointment approved.');
    }

    public function decline(Appointment $appointment)
    {
        $appointment->update(['status' => 'Declined']);

        app(NotificationService::class)->notifyAppointmentDeclined($appointment->client->user);

        return back()->with('success', 'Appointment declined.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
        ]);

        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => 'Rescheduled',
        ]);

        app(NotificationService::class)->notifyAppointmentRescheduled($appointment->client->user);

        return back()->with('success', 'Appointment rescheduled.');
    }
}