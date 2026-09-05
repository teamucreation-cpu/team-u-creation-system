<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * "My Reviews" — every review this client has submitted, any status.
     */
    public function index()
    {
        $reviews = Auth::user()->client->reviews()
            ->with('booking')
            ->latest()
            ->paginate(10);

        return view('client.reviews.index', compact('reviews'));
    }

    public function create(Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (! $booking->isReviewable()) {
            abort(404);
        }

        return view('client.reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (! $booking->isReviewable()) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        Review::create([
            'client_id' => $booking->client_id,
            'booking_id' => $booking->id,
            'title' => $validated['title'],
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'],
            'status' => 'Pending',
        ]);

        return redirect()->route('client.bookings.show', $booking)
            ->with('success', 'Thank you for your feedback! Your review will appear publicly once approved.');
    }

    public function edit(Review $review)
    {
        $this->authorizeReview($review);

        if (! $review->isEditable()) {
            abort(404);
        }

        return view('client.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $this->authorizeReview($review);

        if (! $review->isEditable()) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        $review->update($validated);

        return redirect()->route('client.reviews.index')
            ->with('success', 'Your review has been updated.');
    }

    protected function authorizeBooking(Booking $booking): void
    {
        if ($booking->client_id !== Auth::user()->client->id) {
            abort(403);
        }
    }

    protected function authorizeReview(Review $review): void
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
    }
}
