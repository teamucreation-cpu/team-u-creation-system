<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = Review::with(['client.user', 'booking']);

        if ($status) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'pending' => Review::where('status', 'Pending')->count(),
            'approved' => Review::where('status', 'Approved')->count(),
            'rejected' => Review::where('status', 'Rejected')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'status', 'stats'));
    }

    /**
     * Ratings analytics: overall average, rating distribution, moderation
     * funnel, and a breakdown by event type.
     */
    public function analytics()
    {
        $total = Review::count();

        $stats = [
            'pending' => Review::where('status', 'Pending')->count(),
            'approved' => Review::where('status', 'Approved')->count(),
            'rejected' => Review::where('status', 'Rejected')->count(),
        ];

        $moderated = $stats['approved'] + $stats['rejected'];
        $approvalRate = $moderated > 0 ? round(($stats['approved'] / $moderated) * 100) : null;

        $averageRating = round(Review::avg('rating') ?? 0, 1);

        // Star distribution, 5 down to 1, across every review regardless of status.
        $distribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = Review::where('rating', $star)->count();
            $distribution[$star] = [
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        // Approved-only breakdown by event type (what actually reflects public perception).
        $byEventType = Review::where('reviews.status', 'Approved')
            ->join('bookings', 'reviews.booking_id', '=', 'bookings.id')
            ->selectRaw('bookings.event_type as event_type, COUNT(*) as total, AVG(reviews.rating) as avg_rating')
            ->groupBy('bookings.event_type')
            ->orderByDesc('total')
            ->get();

        return view('admin.reviews.analytics', compact(
            'total', 'stats', 'approvalRate', 'averageRating', 'distribution', 'byEventType'
        ));
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'Approved']);

        app(NotificationService::class)->notifyReviewApproved($review->client->user);

        return back()->with('success', 'Review approved and now visible on the public website.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'Rejected']);

        return back()->with('success', 'Review rejected.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
