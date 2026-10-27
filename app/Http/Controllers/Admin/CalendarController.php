<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Admin sees the full moderation picture — both Approved (booked) and
        // Pending bookings, not just the confirmed ones.
        $bookings = Booking::with(['client.user', 'package'])
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderByRaw("CASE WHEN status = 'Approved' THEN 0 ELSE 1 END")
            ->get();

        $availability = Booking::monthAvailability($year, $month);

        // Full detail per date for the admin's click-to-view popup — the one
        // calendar allowed to show client identity and take action.
        $eventDetails = [];
        foreach ($bookings as $booking) {
            $key = $booking->event_date->format('Y-m-d');
            if (isset($eventDetails[$key])) {
                continue; // an Approved row for this date was already recorded first
            }
            $eventDetails[$key] = [
                'booking_id' => $booking->id,
                'event_type' => $booking->event_type,
                'status' => $booking->status,
                'client_name' => $booking->client->user->name ?? 'Unknown',
                'phone' => $booking->client->user->phone ?? '—',
                'venue' => $booking->venue,
                'package' => $booking->package->package_name ?? 'Custom Event (no package selected)',
                'submitted' => $booking->created_at->format('F d, Y'),
            ];
        }

        return view('admin.calendar.index', compact('month', 'year', 'startOfMonth', 'endOfMonth', 'availability', 'eventDetails'));
    }
}