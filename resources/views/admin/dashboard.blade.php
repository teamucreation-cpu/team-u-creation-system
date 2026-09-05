@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* Scoped to this page only — breaks the content area out to a pure black
       background while leaving the sidebar and every other admin page's
       navy .page-content background untouched. Reuses the site-wide
       .stat-card / .stat-icon / .stat-value / .stat-label classes as-is —
       they're already white cards with dark text, exactly as required. */
    .admin-dash-lux {
        position: relative;
        min-height: calc(100vh - 140px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 2rem 1.5rem 3rem;
        background: #0A0A0A;
    }

    .ad-heading { color: #FFFFFF; font-weight: 700; margin-bottom: 0.4rem; }
    .ad-heading-underline { width: 54px; height: 3px; background: #F4C95D; border-radius: 2px; margin-bottom: 0.6rem; }
    .ad-sub { color: #D1D5DB; font-size: 0.9rem; margin-bottom: 1.75rem; }

    .ad-stats-row { margin-bottom: 1.75rem; }

    /* Bootstrap's row/col already stretches every column in the row to equal
       height, but a.stat-card-link is display:block with no explicit height,
       so it shrink-wraps to its own text instead of filling that stretched
       column — and .stat-card's height:100% then only matches THAT. Invisible
       on short one-line labels, but "Pending Bookings" / "Upcoming Events"
       wrap to two lines and exposed it. Just stretching the anchor itself
       fixes it without touching the shared global rule. */
    .admin-dash-lux .ad-stats-row .stat-card-link { display: flex; height: 100%; }

    /* One consistent gold accent for every card icon — no per-card rainbow. */
    .admin-dash-lux .stat-icon.ad-icon-gold {
        background: rgba(244, 201, 93, 0.14);
        color: #B4881F;
    }

    .admin-dash-lux .stat-value { color: #0A0A0A; }
    .admin-dash-lux .stat-label { color: #555555; }

    .ad-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: #0A0A0A;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 1.1rem;
    }
    .ad-section-title i { color: #F4C95D; }

    .admin-dash-lux .table thead th {
        color: #555555;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 700;
        border-bottom: 2px solid #F4C95D;
    }
    .admin-dash-lux .table td { color: #0A0A0A; vertical-align: middle; }
    .admin-dash-lux .table-sm td, .admin-dash-lux .table-sm th { padding: 0.65rem 0.5rem; }

    @media (max-width: 575.98px) {
        .admin-dash-lux { padding: 1.5rem 1rem 2.5rem; margin: -1rem -1rem -1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="admin-dash-lux">
    <h2 class="ad-heading">Dashboard</h2>
    <div class="ad-heading-underline"></div>
    <p class="ad-sub">A quick overview of bookings and what needs your attention.</p>

    <div class="row g-3 ad-stats-row">
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.bookings.index') }}" class="stat-card-link">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon ad-icon-gold"><i class="fas fa-book"></i></div>
                        <div>
                            <div class="stat-value">{{ $stats['total_bookings'] }}</div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.bookings.index', ['status' => 'Pending']) }}" class="stat-card-link">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon ad-icon-gold"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="stat-value">{{ $stats['pending_bookings'] }}</div>
                            <div class="stat-label">Pending Bookings</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.bookings.index', ['filter' => 'upcoming']) }}" class="stat-card-link">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon ad-icon-gold"><i class="fas fa-calendar"></i></div>
                        <div>
                            <div class="stat-value">{{ $stats['upcoming_events'] }}</div>
                            <div class="stat-label">Upcoming Events</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="stat-card">
        <div class="ad-section-title"><i class="fas fa-clock"></i> Recent Booking Requests</div>
        @if($recentBookings->isEmpty())
            <p class="text-muted mb-0">No bookings yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Client</th><th>Event</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                        <tr>
                            <td>{{ $booking->client->user->name }}</td>
                            <td>{{ $booking->event_type }}</td>
                            <td>{{ $booking->event_date->format('M d, Y') }}</td>
                            <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
