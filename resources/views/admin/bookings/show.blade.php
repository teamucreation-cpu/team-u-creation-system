@extends('layouts.app')

@section('title', 'Booking Details')

@push('styles')
<style>
    .btn-mark-completed,
    .btn-mark-completed:hover,
    .btn-mark-completed:focus,
    .btn-mark-completed:active,
    .btn-mark-completed:disabled {
        background-color: #F4C95D;
        border-color: #F4C95D;
        color: #000;
    }

    .btn-mark-completed:disabled {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Bookings</a>

<h2 class="mb-3">Booking Details</h2>

@php
    $badgeClass = match($booking->status) {
        'Pending' => 'bg-warning text-dark',
        'Approved' => 'bg-success',
        'Declined' => 'bg-danger',
        'Rescheduled' => 'bg-info text-dark',
        'Completed' => 'bg-success',
        default => 'bg-secondary',
    };
@endphp

<span class="badge {{ $badgeClass }} fs-6 mb-3">{{ $booking->status }}</span>

<table class="table table-borderless">
    <tr>
        <th style="width: 200px;">Client</th>
        <td>{{ $booking->client->user->name }} ({{ $booking->client->user->email }}, {{ $booking->client->user->phone }})</td>
    </tr>
    <tr>
        <th>Event Type</th>
        <td>{{ $booking->event_type }}</td>
    </tr>
    <tr>
        <th>Venue</th>
        <td>{{ $booking->venue }}</td>
    </tr>
    <tr>
        <th>Event Date</th>
        <td>{{ $booking->event_date->format('F d, Y') }}</td>
    </tr>
    <tr>
        <th>Package</th>
        <td>{{ $booking->package->package_name ?? 'No package selected (custom event)' }}</td>
    </tr>
    <tr>
        <th>Date Requested</th>
        <td>{{ \App\Support\PhilippineTime::format($booking->created_at) }}</td>
    </tr>
    <tr>
        <th>Last Updated</th>
        <td>{{ \App\Support\PhilippineTime::format($booking->updated_at) }}</td>
    </tr>
</table>

@if ($conflict)
    <div class="alert alert-danger">
        <strong>Scheduling conflict:</strong> Another booking is already Approved for {{ $booking->event_date->format('F d, Y') }}
        — "{{ $conflict->event_type }}" at {{ $conflict->venue }} for {{ $conflict->client->user->name }}.
        You'll need to decline one of these before either can be approved for this date.
    </div>
@endif

<div class="d-flex gap-2 mt-4">
    @if($booking->status === 'Pending')
        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-success">Approve</button>
        </form>

        <form method="POST" action="{{ route('admin.bookings.decline', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-danger">Decline</button>
        </form>
    @endif

    @if($booking->status === 'Approved')
        @if($booking->event_date->isPast() || $booking->event_date->isToday())
            <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">
                @csrf
                <button type="submit" class="btn btn-mark-completed"><i class="fas fa-check-double me-1"></i> Mark Completed</button>
            </form>
        @endif
    @endif
</div>

@if($booking->status === 'Completed' && $booking->review)
    <div class="mt-4">
        <h6 class="fw-bold">Client Review</h6>
        <div>
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star" style="color: {{ $i <= $booking->review->rating ? 'var(--tu-gold)' : '#e0e0e0' }};"></i>
            @endfor
            <span class="badge badge-{{ strtolower($booking->review->status) }} ms-2">{{ $booking->review->status }}</span>
        </div>
        <p class="fw-bold mt-2 mb-0">{{ $booking->review->title }}</p>
        <p class="text-muted fst-italic">"{{ $booking->review->feedback }}"</p>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-gold">Manage in Reviews</a>
    </div>
@endif
@endsection