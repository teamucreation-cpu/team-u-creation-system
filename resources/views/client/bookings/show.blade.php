@extends('layouts.app')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@section('content')
<a href="{{ route('client.bookings.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="stat-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">{{ $booking->event_type }}</h5>
        <span class="badge badge-{{ strtolower($booking->status) }} fs-6">{{ $booking->status }}</span>
    </div>

    <div class="row">
        <div class="col-md-6">
            <p><strong><i class="fas fa-map-marker-alt me-2" style="color: var(--tu-gold);"></i>Venue:</strong> {{ $booking->venue }}</p>
            <p><strong><i class="fas fa-calendar me-2" style="color: var(--tu-gold);"></i>Event Date:</strong> {{ $booking->event_date->format('F d, Y') }}</p>
        </div>
        <div class="col-md-6">
            <p><strong><i class="fas fa-box-open me-2" style="color: var(--tu-gold);"></i>Package:</strong> {{ $booking->package->package_name ?? 'Custom Event (No Package)' }}</p>
            <p><strong><i class="fas fa-clock me-2" style="color: var(--tu-gold);"></i>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($booking->created_at) }}</p>
            <p><strong><i class="fas fa-clock me-2" style="color: var(--tu-gold);"></i>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($booking->updated_at) }}</p>
        </div>
    </div>

    @if($booking->isEditable())
        {{-- Pending: still awaiting admin review, so the client can adjust or withdraw it. --}}
        <div class="d-flex gap-2 mt-2">
            <a href="{{ route('client.bookings.edit', $booking) }}" class="btn btn-outline-gold"><i class="fas fa-pen me-1"></i> Edit Booking</a>
            <form method="POST" action="{{ route('client.bookings.destroy', $booking) }}"
                  onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt me-1"></i> Cancel Booking</button>
            </form>
        </div>
    @elseif($booking->status === 'Approved')
        <p class="text-muted small mt-2 mb-0"><i class="fas fa-lock me-1"></i> This booking has been approved and can no longer be edited or cancelled.</p>
    @elseif($booking->status === 'Declined')
        <div class="d-flex justify-content-between align-items-center mt-2">
            <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i> This booking was declined. Feel free to submit a new booking request.</p>
            <a href="{{ route('client.bookings.create') }}" class="btn btn-gold btn-sm"><i class="fas fa-plus me-1"></i> Book a New Event</a>
        </div>
    @elseif($booking->status === 'Cancelled')
        <p class="text-muted small mt-2 mb-0"><i class="fas fa-ban me-1"></i> You cancelled this booking.</p>
    @endif

    @if($booking->status === 'Completed')
        <hr>
        @if($booking->review)
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="fw-bold mb-2"><i class="fas fa-star me-2" style="color: var(--tu-gold);"></i>Your Review</h6>
                @if($booking->review->isEditable())
                    <a href="{{ route('client.reviews.edit', $booking->review) }}" class="btn btn-sm btn-outline-gold">Edit</a>
                @endif
            </div>
            <div>
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" style="color: {{ $i <= $booking->review->rating ? 'var(--tu-gold)' : '#e0e0e0' }};"></i>
                @endfor
                <span class="badge badge-{{ strtolower($booking->review->status) }} ms-2">{{ $booking->review->status }}</span>
            </div>
            <p class="fw-bold mt-2 mb-0">{{ $booking->review->title }}</p>
            <p class="text-muted mb-0 fst-italic">"{{ $booking->review->feedback }}"</p>
            @if($booking->review->status === 'Pending')
                <p class="text-muted small mt-1 mb-0">Your review is awaiting approval before it appears on the public website.</p>
            @endif
        @else
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1">How was your event?</h6>
                    <p class="text-muted mb-0">Share your experience — approved reviews are featured on our website.</p>
                </div>
                <a href="{{ route('client.reviews.create', $booking) }}" class="btn btn-gold"><i class="fas fa-star me-1"></i> Leave a Review</a>
            </div>
        @endif
    @endif
</div>
@endsection