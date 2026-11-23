<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Auth::user()->client->appointments()->with('booking')->latest()->get();
        return view('client.appointments.index', compact('appointments'));
    }

    public function show(int $appointment)
    {
        $appointment = Auth::user()->client->appointments()->with('booking')->findOrFail($appointment);

        return view('client.appointments.show', compact('appointment'));
    }

    public function confirmReschedule(int $appointment)
    {
        $appointment = Auth::user()->client->appointments()->findOrFail($appointment);
        if ($appointment->status !== Appointment::RESCHEDULE_AWAITING_CLIENT) {
            return back()->with('error', 'There is no reschedule proposal awaiting your confirmation.');
        }

        $date = $appointment->proposed_appointment_date?->toDateString();
        $time = $appointment->proposed_appointment_time;
        if (! $date || ! $time) {
            return back()->with('error', 'The proposed appointment schedule is incomplete. Please contact us.');
        }

        $timezone = config('app.display_timezone', 'Asia/Manila');
        if (Carbon::parse($date.' '.$time, $timezone)->lessThanOrEqualTo(Carbon::now($timezone))) {
            return back()->with('error', 'The proposed appointment schedule has passed. Please contact us.');
        }

        $updated = DB::transaction(function () use ($appointment, $date, $time) {
            $lockedAppointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            if ($lockedAppointment->status !== Appointment::RESCHEDULE_AWAITING_CLIENT) {
                return false;
            }
            if (Appointment::hasScheduleConflict($date, $time, $lockedAppointment->id)) {
                return false;
            }

            return Appointment::query()
                ->whereKey($lockedAppointment->id)
                ->where('status', Appointment::RESCHEDULE_AWAITING_CLIENT)
                ->update([
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'status' => 'Confirmed',
                    'reschedule_response' => 'Confirmed',
                    'reschedule_responded_at' => now(),
                ]) > 0;
        });

        if (! $updated) {
            if ($appointment->fresh()->status !== Appointment::RESCHEDULE_AWAITING_CLIENT) {
                return back()->with('error', 'There is no reschedule proposal awaiting your confirmation.');
            }

            return back()->with('error', 'That proposed schedule is no longer available. Please contact us.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAdminsAppointmentRescheduleResponse(
            Auth::user(),
            $appointment
        );

        return back()->with('success', 'The proposed appointment schedule has been confirmed.');
    }

    public function declineReschedule(int $appointment)
    {
        $appointment = Auth::user()->client->appointments()->findOrFail($appointment);
        $updated = Appointment::query()
            ->whereKey($appointment->id)
            ->where('status', Appointment::RESCHEDULE_AWAITING_CLIENT)
            ->update([
                'status' => 'Declined',
                'reschedule_response' => 'Declined',
                'reschedule_responded_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'There is no reschedule proposal awaiting your response.');
        }

        $appointment->refresh();
        app(NotificationService::class)->notifyAdminsAppointmentRescheduleResponse(
            Auth::user(),
            $appointment
        );

        return back()->with('success', 'The proposed rescheduled appointment has been declined.');
    }

    public function create()
    {
        $bookings = Auth::user()->client->bookings()->get();
        return view('client.appointments.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
            'purpose' => ['required', 'in:Initial Consultation,Event Planning Meeting,Package Discussion,Event Coordination Meeting'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $client = Auth::user()->client;

        if ($validated['booking_id']) {
            $booking = Booking::find($validated['booking_id']);
            if ($booking->client_id !== $client->id) {
                abort(403);
            }
        }

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'Pending',
        ]);

        app(NotificationService::class)->notifyAppointmentSubmitted(Auth::user(), $appointment);

        return redirect()->route('client.appointments.index')->with('success', 'Appointment request submitted.');
    }
}