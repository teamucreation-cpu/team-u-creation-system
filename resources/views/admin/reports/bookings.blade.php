@extends('layouts.app')

@section('title', 'Bookings Report - ' . $periodLabel)
@section('page-title', 'Bookings Report - ' . $periodLabel)

@section('content')
<div class="admin-bookings-compact">
    <a href="{{ route('admin.reports.index', ['period' => $period]) }}" class="btn btn-sm btn-outline-gold mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Bookings Report - {{ $periodLabel }}</h5>
    </div>

    <div class="list-toolbar">
        <form method="GET" action="{{ route('admin.reports.bookings') }}" class="row g-2 align-items-center">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="col-12 col-md-7">
                <input type="text" name="search" class="form-control" placeholder="Search by client, event type, or venue..." value="{{ $search }}">
            </div>
            <div class="col-6 col-md-3">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach(\App\Http\Controllers\AdminBookingController::PER_PAGE_OPTIONS as $option)
                        <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }} / page</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-gold"><i class="fas fa-search me-1"></i> Search</button>
                @if($search)
                    <a href="{{ route('admin.reports.bookings', ['period' => $period]) }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
        @if($bookings->total() > 0)
            <div class="mt-2 pt-2 border-top" style="overflow-x: auto; max-width: 100%;">
                {{ $bookings->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    <div class="table-custom table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Client</th><th>Event Type</th><th>Venue</th><th>Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->client->user->name }}</td>
                        <td>{{ $booking->event_type }}</td>
                        <td>{{ $booking->venue }}</td>
                        <td>{{ $booking->event_date->format('M d, Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                        <td><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-gold">View Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No bookings found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection