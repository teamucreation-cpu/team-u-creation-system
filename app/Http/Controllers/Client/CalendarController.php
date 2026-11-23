<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $client = Auth::user()->client;

        $appointments = $client->appointments()
            ->whereIn('status', ['Approved', 'Confirmed'])
            ->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->get(['appointment_date', 'appointment_time', 'purpose']);
        $appointmentsByDate = [];
        foreach ($appointments as $appointment) {
            $date = $appointment->appointment_date->format('Y-m-d');
            $appointmentsByDate[$date][] = Carbon::parse($appointment->appointment_time)->format('g:i A').' – '.$appointment->purpose;
        }

        // Status only — deliberately not event type, package, or anyone's
        // identity. This calendar must never reveal another client's booking
        // details, only whether a date is booked/pending/available.
        $statusMap = collect(Booking::monthAvailability($year, $month))
            ->map(fn ($info) => $info['status'])
            ->all();

        return view('client.calendar.index', compact('appointments', 'appointmentsByDate', 'month', 'year', 'startOfMonth', 'endOfMonth', 'statusMap'));
    }
}