<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Auth::user()->client->appointments()->with('booking')->latest()->get();
        return view('client.appointments.index', compact('appointments'));
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

        Appointment::create([
            'client_id' => $client->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'Pending',
        ]);

        app(NotificationService::class)->notifyAppointmentSubmitted(Auth::user());

        return redirect()->route('client.appointments.index')->with('success', 'Appointment request submitted.');
    }
}