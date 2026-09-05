@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@push('styles')
<style>
    /* Scoped to this page only — every other admin table keeps its default
       spacing. Goal: fit the heading + filter toolbar + all 10 rows of a
       full page on a standard laptop viewport with no scrolling, by
       trimming padding/margins rather than shrinking any text. Pagination
       is also moved into the toolbar (see the view below) so it's visible
       above the table regardless of how tall the table itself ends up
       being, instead of depending on there being room left underneath it. */
    .admin-bookings-compact .abc-heading { margin-bottom: 0.3rem; }
    .admin-bookings-compact .list-toolbar {
        padding: 0.5rem 1rem;
        margin-bottom: 0.4rem;
    }
    .admin-bookings-compact .abc-pagination-row {
        margin-top: 0.35rem;
        padding-top: 0.35rem;
        border-top: 1px solid #f0f0f0;
    }
    .admin-bookings-compact .pagination { margin-bottom: 0; }
    .admin-bookings-compact .table-custom thead th {
        padding: 0.35rem 1rem;
    }
    .admin-bookings-compact .table-custom tbody td {
        padding: 0.15rem 1rem;
    }
</style>
@endpush

@section('content')
<div class="admin-bookings-compact">
    <div class="d-flex justify-content-between align-items-center abc-heading">
        <h5 class="mb-0">
            @if($filter === 'upcoming')
                Upcoming Events
            @elseif($status)
                {{ $status }} Bookings
            @else
                All Bookings
            @endif
        </h5>
    </div>

    <div class="list-toolbar">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-2 align-items-center">
            @if($filter === 'upcoming')
                <input type="hidden" name="filter" value="upcoming">
            @endif
            <div class="col-12 col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by client, event type, or venue..." value="{{ $search }}">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" @if($filter === 'upcoming') disabled @endif>
                    <option value="">All Statuses</option>
                    @foreach(['Pending', 'Approved', 'Declined', 'Rescheduled', 'Completed', 'Cancelled'] as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach(\App\Http\Controllers\AdminBookingController::PER_PAGE_OPTIONS as $option)
                        <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }} / page</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-gold"><i class="fas fa-filter me-1"></i> Apply</button>
                @if($status || $search || $filter)
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if($bookings->total() > 0)
            {{-- Kept inside the same toolbar card, above the table, so it's
                 always visible regardless of table height — matches the
                 Clients page's pagination placement for consistency. --}}
            <div class="abc-pagination-row" style="overflow-x: auto; max-width: 100%;">
                {{ $bookings->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    <div class="table-custom table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Event Type</th>
                    <th>Venue</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->client->user->name }}</td>
                        <td>{{ $booking->event_type }}</td>
                        <td>{{ $booking->venue }}</td>
                        <td>{{ $booking->event_date->format('M d, Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                        <td><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-gold">View Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
