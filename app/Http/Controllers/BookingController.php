<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\EventPackage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $packages = EventPackage::where('is_available', true)->get();
        $selectedPackage = $request->filled('package')
            ? EventPackage::find($request->query('package'))
            : null;

        // Optional deep-link from the Event Calendar's "Book This Date" button.
        // Only honored if it's a genuinely valid, not-in-the-past date — the
        // Smart Date Selector itself is still the source of truth on whether
        // it's actually available, and re-checks it against live data on load.
        $prefillDate = null;
        if ($request->filled('date')) {
            try {
                $parsed = \Carbon\Carbon::parse($request->query('date'))->startOfDay();
                if (! $parsed->isPast() || $parsed->isToday()) {
                    $prefillDate = $parsed->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $prefillDate = null;
            }
        }

        $month = $prefillDate ? (int) substr($prefillDate, 5, 2) : (int) now()->month;
        $year = $prefillDate ? (int) substr($prefillDate, 0, 4) : (int) now()->year;
        $availability = Booking::monthAvailability($year, $month);

        return view('client.bookings.create', compact('packages', 'selectedPackage', 'availability', 'month', 'year', 'prefillDate'));
    }

    /**
     * AJAX endpoint backing the Smart Booking Date Selector's month navigation.
     * `exclude` (an existing booking id) is passed by the Edit Booking page so
     * the booking's own current date reads as available rather than
     * conflicting with itself.
     */
    public function availability(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $exclude = $request->filled('exclude') ? (int) $request->get('exclude') : null;

        return response()->json(Booking::monthAvailability($year, $month, $exclude));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => ['nullable', 'exists:event_packages,id'],
            'event_type' => ['required', 'string', 'max:255'],
            'venue' => ['required', 'string', 'max:255'],
            'event_date' => [
                'required', 'date', 'after_or_equal:today',
                function ($attribute, $value, $fail) use ($request) {
                    $rule = Booking::bookingRuleFor($request->input('event_type'));
                    $earliest = Booking::earliestBookableDate($request->input('event_type'));

                    if (\Carbon\Carbon::parse($value)->lt($earliest)) {
                        $fail(
                            'Booking cannot proceed: '.$rule['label'].' events must be booked at least '
                            .Booking::bookingRuleNoticeLabel($rule).' before the event date. '
                            .'The earliest eligible date for this event type is '.$earliest->format('F d, Y').'.'
                        );
                    }
                },
                function ($attribute, $value, $fail) {
                    // Only a confirmed (Approved) booking hard-blocks the date. A
                    // Pending one does not — the Smart Date Selector already warns
                    // the client and lets them proceed at their own risk, so the
                    // server must honor that rather than rejecting the submission
                    // out from under them. Two Pending bookings can coexist on the
                    // same date; AdminBookingController::approve() is what actually
                    // enforces exclusivity, refusing to approve one while another is
                    // already Approved for that date.
                    $existing = Booking::where('event_date', $value)
                        ->where('status', 'Approved')
                        ->first();

                    if ($existing) {
                        $fail("This date is already booked for a {$existing->event_type} event. Please select another date.");
                    }
                },
            ],
        ]);

        $booking = Booking::create([
            'client_id' => Auth::user()->client->id,
            'package_id' => $validated['package_id'] ?? null,
            'event_type' => $validated['event_type'],
            'venue' => $validated['venue'],
            'event_date' => $validated['event_date'],
            'status' => 'Pending',
        ]);

        app(NotificationService::class)->notifyBookingSubmitted(Auth::user(), $booking->event_type);

        return redirect()->route('client.bookings.show', $booking)
            ->with('success', 'Booking submitted! We will review it and update you soon.');
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $filter = $request->get('filter');
        $search = $request->get('search');

        $query = Auth::user()->client->bookings()->with('package');

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
                    ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('client.bookings.index', compact('bookings', 'status', 'filter', 'search'));
    }

    public function show(Booking $booking)
    {
        $this->authorizeOwner($booking);

        return view('client.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $this->authorizeOwner($booking);

        if (! $booking->isEditable()) {
            return redirect()->route('client.bookings.show', $booking)
                ->with('error', 'This booking has already been reviewed and can no longer be edited.');
        }

        $packages = EventPackage::where('is_available', true)->get();
        $selectedPackage = null;
        $prefillDate = null;

        $month = (int) $booking->event_date->month;
        $year = (int) $booking->event_date->year;
        $availability = Booking::monthAvailability($year, $month, $booking->id);

        return view('client.bookings.create', compact(
            'packages', 'selectedPackage', 'availability', 'month', 'year', 'prefillDate', 'booking'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorizeOwner($booking);

        if (! $booking->isEditable()) {
            return redirect()->route('client.bookings.show', $booking)
                ->with('error', 'This booking has already been reviewed and can no longer be edited.');
        }

        $validated = $request->validate([
            'package_id' => ['nullable', 'exists:event_packages,id'],
            'event_type' => ['required', 'string', 'max:255'],
            'venue' => ['required', 'string', 'max:255'],
            'event_date' => [
                'required', 'date', 'after_or_equal:today',
                function ($attribute, $value, $fail) use ($request) {
                    $rule = Booking::bookingRuleFor($request->input('event_type'));
                    $earliest = Booking::earliestBookableDate($request->input('event_type'));

                    if (\Carbon\Carbon::parse($value)->lt($earliest)) {
                        $fail(
                            'Booking cannot proceed: '.$rule['label'].' events must be booked at least '
                            .Booking::bookingRuleNoticeLabel($rule).' before the event date. '
                            .'The earliest eligible date for this event type is '.$earliest->format('F d, Y').'.'
                        );
                    }
                },
                function ($attribute, $value, $fail) use ($booking) {
                    // See the identical comment in store() — only Approved blocks.
                    $existing = Booking::where('event_date', $value)
                        ->where('status', 'Approved')
                        ->where('id', '!=', $booking->id)
                        ->first();

                    if ($existing) {
                        $fail("This date is already booked for a {$existing->event_type} event. Please select another date.");
                    }
                },
            ],
        ]);

        // Status is deliberately left untouched — an edit does not
        // re-approve itself, it still needs to be (re-)reviewed as Pending,
        // which it already is (isEditable() above guarantees that).
        $booking->update([
            'package_id' => $validated['package_id'] ?? null,
            'event_type' => $validated['event_type'],
            'venue' => $validated['venue'],
            'event_date' => $validated['event_date'],
        ]);

        return redirect()->route('client.bookings.show', $booking)
            ->with('success', 'Booking updated. It will need to be reviewed by the admin again before it\'s approved.');
    }

    public function destroy(Booking $booking)
    {
        $this->authorizeOwner($booking);

        if (! $booking->isCancellable()) {
            return redirect()->route('client.bookings.show', $booking)
                ->with('error', 'This booking has already been reviewed and can no longer be cancelled.');
        }

        $booking->update(['status' => 'Cancelled']);

        return redirect()->route('client.bookings.index')
            ->with('success', 'Booking cancelled. The date is now available again.');
    }

    protected function authorizeOwner(Booking $booking): void
    {
        if ($booking->client_id !== Auth::user()->client->id) {
            abort(403);
        }
    }
}