<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $status = $request->get('status');
        $filter = $request->get('filter');
        $search = $request->get('search');

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

        $bookings = $query->paginate(10)->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'status', 'filter', 'search'));
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
        if ($booking->fresh()->status !== 'Pending') {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        $conflict = Booking::where('event_date', $booking->event_date)
            ->where('status', 'Approved')
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Cannot approve — another booking is already approved for this date. Decline one of them first.');
        }

        $updated = Booking::query()
            ->whereKey($booking->getKey())
            ->where('status', 'Pending')
            ->update(['status' => 'Approved']);

        if (! $updated) {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        $booking->refresh();

        app(NotificationService::class)->notifyBookingApproved($booking);

        return back()->with('success', 'Booking approved.');
    }

    public function decline(Booking $booking)
    {
        $updated = Booking::query()
            ->whereKey($booking->getKey())
            ->where('status', 'Pending')
            ->update(['status' => 'Declined']);

        if (! $updated) {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        $booking->refresh();

        app(NotificationService::class)->notifyBookingDeclined($booking);

        return back()->with('success', 'Booking declined.');
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'event_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function ($attribute, $value, $fail) use ($booking) {
                    $rule = Booking::bookingRuleFor($booking->event_type);
                    $earliest = Booking::earliestBookableDate($booking->event_type);

                    if ($value < $earliest->toDateString()) {
                        $fail(
                            'Booking cannot proceed: '.$rule['label'].' events must be booked at least '
                            .Booking::bookingRuleNoticeLabel($rule).' before the event date. '
                            .'The earliest eligible date for this event type is '.$earliest->format('F d, Y').'.'
                        );
                    }
                },
            ],
        ]);

        $booking->refresh();
        if ($booking->status !== 'Pending') {
            return back()->with('error', 'Only pending bookings can be rescheduled.');
        }

        $updatedDate = Carbon::parse($validated['event_date'])->toDateString();
        $previousDate = $booking->event_date->format('F j, Y');
        if ($booking->event_date->toDateString() === $updatedDate) {
            return back()->with('error', 'Choose a different event date to reschedule this booking.');
        }

        $conflict = Booking::whereDate('event_date', $updatedDate)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'That date already has a pending or approved booking. Choose another date.');
        }

        $updated = Booking::query()
            ->whereKey($booking->getKey())
            ->where('status', 'Pending')
            ->whereDate('event_date', $booking->event_date->toDateString())
            ->update(['event_date' => $updatedDate, 'status' => 'Rescheduled']);

        if (! $updated) {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        $booking->refresh();
        app(NotificationService::class)->notifyBookingRescheduled($booking, $previousDate);

        return back()->with('success', 'Booking rescheduled.');
    }

    /**
     * Mark an approved, already-happened event as Completed. This is what
     * makes the booking eligible for the client to leave a review.
     */
    public function complete(Booking $booking)
    {
        $booking->refresh();

        if ($booking->status !== 'Approved') {
            return back()->with('error', 'Only an approved booking can be marked completed.');
        }

        if ($booking->event_date->isFuture()) {
            return back()->with('error', 'This event hasn\'t happened yet — it can only be marked completed on or after its event date.');
        }

        $updated = Booking::query()
            ->whereKey($booking->getKey())
            ->where('status', 'Approved')
            ->update(['status' => 'Completed']);

        if (! $updated) {
            return back()->with('error', 'Only an approved booking can be marked completed.');
        }

        return back()->with('success', 'Booking marked as completed. The client can now leave a review.');
    }
}