<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    /**
     * Rows-per-page choices offered on the Admin Bookings table.
     */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $status = $request->get('status');
        $filter = $request->get('filter');
        $search = $request->get('search');

        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $query = Booking::with(['client.user', 'package']);

        if ($filter === 'upcoming') {
            $query->where('status', 'Approved')->where('event_date', '>=', now())->orderBy('event_date');
        } else {
            if ($status) {
                $query->where('status', $status);
            }
            $query->latest();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhereHas('client.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate($perPage)->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'status', 'filter', 'search', 'perPage'));
    }

    public function show(Booking $booking)
    {
        $conflict = null;

        if ($booking->status !== 'Approved') {
            $conflict = Booking::where('event_date', $booking->event_date)
                ->where('status', 'Approved')
                ->where('id', '!=', $booking->id)
                ->first();
        }

        return view('admin.bookings.show', compact('booking', 'conflict'));
    }

    public function approve(Booking $booking)
    {
        $conflict = Booking::where('event_date', $booking->event_date)
            ->where('status', 'Approved')
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Cannot approve — another booking is already approved for this date. Decline one of them first.');
        }

        $booking->update(['status' => 'Approved']);

        app(NotificationService::class)->notifyBookingApproved($booking->client->user, $booking->event_type);

        return back()->with('success', 'Booking approved.');
    }

    public function decline(Booking $booking)
    {
        $booking->update(['status' => 'Declined']);

        app(NotificationService::class)->notifyBookingDeclined($booking->client->user, $booking->event_type);

        return back()->with('success', 'Booking declined.');
    }

    /**
     * Mark an approved, already-happened event as Completed. This is what
     * makes the booking eligible for the client to leave a review.
     */
    public function complete(Booking $booking)
    {
        if ($booking->status !== 'Approved') {
            return back()->with('error', 'Only an approved booking can be marked completed.');
        }

        if ($booking->event_date->isFuture()) {
            return back()->with('error', 'This event hasn\'t happened yet — it can only be marked completed on or after its event date.');
        }

        $booking->update(['status' => 'Completed']);

        return back()->with('success', 'Booking marked as completed. The client can now leave a review.');
    }
}