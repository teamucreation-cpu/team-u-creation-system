@extends('layouts.app')

@section('title', 'Upcoming Events Report')
@section('page-title', 'Upcoming Events Report')

@section('content')
<div class="admin-bookings-compact">
    <a href="{{ route('admin.reports.index', ['period' => $period]) }}" class="btn btn-sm btn-outline-gold mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>

    <h5 class="mb-2">Upcoming Events Report</h5>
    <div class="list-toolbar">
        <form method="GET" action="{{ route('admin.reports.upcoming-events') }}" class="row g-2 align-items-center">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="col-12 col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search by client, event type, package, or venue..." value="{{ $search }}">
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-gold w-100"><i class="fas fa-search me-1"></i> Search</button>
            </div>
        </form>
        @if($upcomingEvents->hasPages())
            <div class="mt-2 pt-2 border-top" style="overflow-x: auto; max-width: 100%;">
                {{ $upcomingEvents->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    <div class="table-custom table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Event Name</th><th>Client Name</th><th>Event Type</th><th>Package</th><th>Event Date</th><th>Venue</th><th>Event Status</th></tr></thead>
            <tbody>
                @forelse($upcomingEvents as $booking)
                    <tr>
                        <td><a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none">{{ $booking->event_type }}</a></td>
                        <td>{{ $booking->client->user->name }}</td>
                        <td>{{ $booking->event_type }}</td>
                        <td>{{ $booking->package->package_name ?? 'No package' }}</td>
                        <td>{{ $booking->event_date->format('M d, Y') }}</td>
                        <td>{{ $booking->venue }}</td>
                        <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center py-3">No upcoming approved events found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection