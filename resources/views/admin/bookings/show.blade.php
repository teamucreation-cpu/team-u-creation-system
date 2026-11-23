@extends('layouts.app')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@push('styles')
<style>
    .admin-booking-details {
        --booking-bg: #121212;
        --booking-gold: #F4C95D;
        --booking-text: #FFFFFF;
        --booking-muted: #D1D5DB;
        color: var(--booking-text);
        max-width: 1180px;
        margin: 0 auto;
        padding: 1rem;
        background: #0A0A0A;
        border-radius: 16px;
    }
    .booking-details-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .booking-details-header h2 { color: var(--booking-text); font-size: 1.5rem; font-weight: 700; margin: 0; }
    .booking-details-header p { color: var(--booking-muted); margin: 0.3rem 0 0; font-size: 0.9rem; }
    .booking-details-card {
        background: var(--booking-bg);
        border: 1px solid rgba(244, 201, 93, 0.22);
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.16);
        padding: 1.15rem 1.25rem;
        margin-bottom: 1rem;
    }
    .booking-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: var(--booking-gold);
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 1rem;
    }
    .booking-section-title i { font-size: 0.9rem; }
    .booking-info-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
    .booking-info-column { min-width: 0; }
    .booking-info-column + .booking-info-column {
        border-left: 1px solid rgba(244, 201, 93, 0.2);
        padding-left: 1.5rem;
    }
    .booking-info-heading {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: var(--booking-gold);
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0 0 0.9rem;
    }
    .booking-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem 1.5rem; }
    .booking-overview-fields { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .booking-field { min-width: 0; }
    .booking-field-label {
        display: block;
        color: var(--booking-muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        margin-bottom: 0.28rem;
        text-transform: uppercase;
    }
    .booking-field-value {
        color: var(--booking-text);
        font-size: 0.95rem;
        font-weight: 500;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }
    .booking-field-value--gold { color: var(--booking-gold); font-weight: 700; }
    .booking-package-list { color: var(--booking-muted); margin: 0; padding-left: 1.2rem; }
    .booking-package-list li + li { margin-top: 0.35rem; }
    .booking-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
    }
    .booking-status--pending { background: #F4C95D; color: #171717; }
    .booking-status--approved, .booking-status--completed { background: #166534; color: #FFFFFF; }
    .booking-status--declined, .booking-status--cancelled { background: #991B1B; color: #FFFFFF; }
    .booking-status--rescheduled { background: #155E75; color: #FFFFFF; }
    .booking-status--other { background: #374151; color: #FFFFFF; }
    .booking-conflict {
        background: rgba(153, 27, 27, 0.18);
        border: 1px solid rgba(248, 113, 113, 0.55);
        border-radius: 10px;
        color: #FEE2E2;
        padding: 0.85rem 0.95rem;
        margin-top: 1rem;
    }
    .booking-review {
        border-top: 1px solid rgba(244, 201, 93, 0.2);
        margin-top: 1rem;
        padding-top: 0.9rem;
    }
    .booking-review-title { color: var(--booking-gold); font-size: 0.9rem; font-weight: 700; margin: 0 0 0.8rem; }
    .booking-review-stars { color: var(--booking-gold); }
    .booking-review-stars .is-empty { color: #6B7280; }
    .booking-review-link { margin-top: 0.85rem; }
    .booking-action-form { display: inline-block; margin: 0.85rem 0.5rem 0 0; }
    .btn-mark-completed,
    .btn-mark-completed:hover,
    .btn-mark-completed:focus,
    .btn-mark-completed:active,
    .btn-mark-completed:disabled {
        background-color: #F4C95D;
        border-color: #F4C95D;
        color: #000;
    }
    .btn-mark-completed:disabled { opacity: 1; }

    @media (max-width: 767.98px) {
        .booking-info-columns { grid-template-columns: 1fr; gap: 1rem; }
        .booking-info-column + .booking-info-column {
            border-left: 0;
            border-top: 1px solid rgba(244, 201, 93, 0.2);
            padding: 1rem 0 0;
        }
    }
    @media (max-width: 575.98px) {
        .admin-booking-details { padding: 0.6rem; }
        .booking-details-header { align-items: flex-start; flex-direction: column-reverse; }
        .booking-details-header h2 { font-size: 1.3rem; }
        .booking-details-card { padding: 1rem; }
        .booking-fields, .booking-overview-fields { grid-template-columns: 1fr; gap: 0.8rem; }
    }
</style>
@endpush

@section('content')
@php
    $statusClass = match($booking->status) {
        'Pending' => 'pending',
        'Approved' => 'approved',
        'Declined' => 'declined',
        'Rescheduled' => 'rescheduled',
        'Completed' => 'completed',
        'Cancelled' => 'cancelled',
        default => 'other',
    };
    $packageInclusions = $booking->package?->inclusions
        ? collect(preg_split('/[\r\n,]+/', trim($booking->package->inclusions)))->map(fn ($item) => trim($item))->filter()->values()
        : collect();
@endphp

<div class="admin-booking-details">
    <header class="booking-details-header">
        <div>
            <h2>Booking Details</h2>
            <p>Complete information and management actions for this booking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-gold"><i class="fas fa-arrow-left me-1"></i> Back to Bookings</a>
    </header>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="booking-details-card" aria-label="Client and event information">
        <div class="booking-info-columns">
            <section class="booking-info-column" aria-labelledby="clientInformationTitle">
                <h3 class="booking-info-heading" id="clientInformationTitle"><i class="fas fa-user"></i> Client Information</h3>
                <div class="booking-fields">
                    <div class="booking-field">
                        <span class="booking-field-label">Client Name</span>
                        <span class="booking-field-value">{{ $booking->client?->user?->name ?? 'Not available' }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Email Address</span>
                        <span class="booking-field-value">{{ $booking->client?->user?->email ?? 'Not available' }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Phone Number</span>
                        <span class="booking-field-value">{{ $booking->client?->user?->phone ?? 'Not available' }}</span>
                    </div>
                </div>
            </section>

            <section class="booking-info-column" aria-labelledby="eventInformationTitle">
                <h3 class="booking-info-heading" id="eventInformationTitle"><i class="fas fa-calendar-days"></i> Event Information</h3>
                <div class="booking-fields">
                    <div class="booking-field">
                        <span class="booking-field-label">Event Type</span>
                        <span class="booking-field-value">{{ $booking->event_type }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Event Date</span>
                        <span class="booking-field-value">{{ $booking->event_date->format('F j, Y') }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Event Venue</span>
                        <span class="booking-field-value">{{ $booking->venue }}</span>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <section class="booking-details-card" aria-labelledby="selectedPackageTitle">
        <h3 class="booking-section-title" id="selectedPackageTitle"><i class="fas fa-box-open"></i> Selected Event Package</h3>
        @if($booking->package)
            <div class="booking-fields">
                <div class="booking-field">
                    <span class="booking-field-label">Package Type</span>
                    <span class="booking-field-value">Event Package</span>
                </div>
                <div class="booking-field">
                    <span class="booking-field-label">Package Name</span>
                    <span class="booking-field-value">{{ $booking->package->package_name }}</span>
                </div>
                <div class="booking-field">
                    <span class="booking-field-label">Package Price</span>
                    <span class="booking-field-value booking-field-value--gold">PHP {{ number_format((float) $booking->package->price, 2) }}</span>
                </div>
                @if($booking->package->description)
                    <div class="booking-field">
                        <span class="booking-field-label">Package Description</span>
                        <span class="booking-field-value">{{ $booking->package->description }}</span>
                    </div>
                @endif
                @if($packageInclusions->isNotEmpty())
                    <div class="booking-field">
                        <span class="booking-field-label">Package Inclusions</span>
                        <ul class="booking-package-list">
                            @foreach($packageInclusions as $inclusion)
                                <li>{{ $inclusion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @else
            <div class="booking-fields">
                <div class="booking-field">
                    <span class="booking-field-label">Package Type</span>
                    <span class="booking-field-value">Custom Event</span>
                </div>
                <div class="booking-field">
                    <span class="booking-field-label">Package Name</span>
                    <span class="booking-field-value">No Package – Custom Event</span>
                </div>
            </div>
        @endif
    </section>

    <section class="booking-details-card" aria-labelledby="bookingOverviewTitle">
        <h3 class="booking-section-title" id="bookingOverviewTitle"><i class="fas fa-clipboard-check"></i> Booking Overview</h3>
        <div class="booking-fields booking-overview-fields">
            <div class="booking-field">
                <span class="booking-field-label">Booking Status</span>
                <span class="booking-status booking-status--{{ $statusClass }}">{{ $booking->status }}</span>
            </div>
            <div class="booking-field">
                <span class="booking-field-label">Date Requested</span>
                <span class="booking-field-value">{{ \App\Support\PhilippineTime::format($booking->created_at) }}</span>
            </div>
            <div class="booking-field">
                <span class="booking-field-label">Last Updated</span>
                <span class="booking-field-value">{{ \App\Support\PhilippineTime::format($booking->updated_at) }}</span>
            </div>
        </div>

        @if($conflict)
            <div class="booking-conflict" role="alert">
                <strong>Scheduling conflict:</strong> Another booking is already Approved for {{ $booking->event_date->format('F j, Y') }}.
                <div class="booking-fields mt-3">
                    <div class="booking-field">
                        <span class="booking-field-label">Conflicting Event Type</span>
                        <span class="booking-field-value">{{ $conflict->event_type }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Conflicting Event Venue</span>
                        <span class="booking-field-value">{{ $conflict->venue }}</span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Conflicting Client Name</span>
                        <span class="booking-field-value">{{ $conflict->client?->user?->name ?? 'Not available' }}</span>
                    </div>
                </div>
                <p class="mb-0 mt-3">Decline one of these bookings before approving this date.</p>
            </div>
        @endif

        @if($booking->status === 'Completed' && $booking->review)
            <div class="booking-review">
                <h4 class="booking-review-title"><i class="fas fa-star me-1"></i> Client Review</h4>
                <div class="booking-fields">
                    <div class="booking-field">
                        <span class="booking-field-label">Rating</span>
                        <span class="booking-review-stars" aria-label="{{ $booking->review->rating }} out of 5 stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $booking->review->rating ? '' : 'is-empty' }}"></i>
                            @endfor
                        </span>
                    </div>
                    <div class="booking-field">
                        <span class="booking-field-label">Review Status</span>
                        <span class="booking-field-value">{{ $booking->review->status }}</span>
                    </div>
                    @if($booking->review->title)
                        <div class="booking-field">
                            <span class="booking-field-label">Review Title</span>
                            <span class="booking-field-value">{{ $booking->review->title }}</span>
                        </div>
                    @endif
                    @if($booking->review->feedback)
                        <div class="booking-field">
                            <span class="booking-field-label">Review Feedback</span>
                            <span class="booking-field-value">{{ $booking->review->feedback }}</span>
                        </div>
                    @endif
                </div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-gold booking-review-link">Manage in Reviews</a>
            </div>
        @endif
    </section>

    @if($booking->status === 'Pending')
        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" class="booking-action-form">
            @csrf
            <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Approve Booking</button>
        </form>

        <form method="POST" action="{{ route('admin.bookings.decline', $booking) }}" class="booking-action-form">
            @csrf
            <button type="submit" class="btn btn-danger"><i class="fas fa-xmark me-1"></i> Decline Booking</button>
        </form>
    @elseif($booking->status === 'Approved' && ($booking->event_date->isPast() || $booking->event_date->isToday()))
        <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}" class="booking-action-form">
            @csrf
            <button type="submit" class="btn btn-mark-completed"><i class="fas fa-check-double me-1"></i> Mark Completed</button>
        </form>
    @endif
</div>
@endsection
