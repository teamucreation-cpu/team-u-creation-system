@extends('layouts.app')

@section('title', 'My Reviews')
@section('page-title', 'My Reviews')

@section('content')
<div class="table-custom">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Event</th>
                <th>Title</th>
                <th>Rating</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td><strong>{{ $review->booking->event_type }}</strong></td>
                <td>{{ $review->title }}</td>
                <td class="text-nowrap">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color: {{ $i <= $review->rating ? 'var(--tu-gold)' : '#e0e0e0' }};"></i>
                    @endfor
                </td>
                <td>{{ \App\Support\PhilippineTime::date($review->created_at) }}</td>
                <td><span class="badge badge-{{ strtolower($review->status) }}">{{ $review->status }}</span></td>
                <td>
                    <a href="{{ route('client.bookings.show', $review->booking) }}" class="btn btn-sm btn-outline-gold">View</a>
                    @if($review->isEditable())
                        <a href="{{ route('client.reviews.edit', $review) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-center py-3">You haven't submitted any reviews yet. Reviews can be left from a completed event's booking page.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
