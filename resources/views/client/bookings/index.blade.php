@extends('layouts.app')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        @if($filter === 'upcoming')
            Upcoming Events
        @elseif($status)
            {{ $status }} Bookings
        @else
            Booking History
        @endif
    </h5>
    <a href="{{ route('client.bookings.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Book an Event</a>
</div>

<div class="list-toolbar">
    <form method="GET" action="{{ route('client.bookings.index') }}" class="row g-2 align-items-center">
        @if($filter === 'upcoming')
            <input type="hidden" name="filter" value="upcoming">
        @endif
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search by event type or venue..." value="{{ $search }}">
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" @if($filter === 'upcoming') disabled @endif>
                <option value="">All Statuses</option>
                @foreach(['Pending', 'Approved', 'Declined', 'Rescheduled', 'Completed', 'Cancelled'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-gold"><i class="fas fa-filter me-1"></i> Apply</button>
            @if($status || $search || $filter)
                <a href="{{ route('client.bookings.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="table-custom table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Event Type</th><th>Venue</th><th>Date</th><th>Package</th><th>Status</th><th>Date Requested</th><th>Last Updated</th><th></th></tr></thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr>
                <td><strong>{{ $booking->event_type }}</strong></td>
                <td>{{ $booking->venue }}</td>
                <td>{{ $booking->event_date->format('M d, Y') }}</td>
                <td>{{ $booking->package->package_name ?? 'Custom' }}</td>
                <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                <td>{{ \App\Support\PhilippineTime::format($booking->created_at, compact: true) }}</td>
                <td>{{ \App\Support\PhilippineTime::format($booking->updated_at, compact: true) }}</td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="{{ route('client.bookings.show', $booking) }}" class="btn btn-sm btn-outline-gold">View</a>
                        @if($booking->isEditable())
                            <a href="{{ route('client.bookings.edit', $booking) }}" class="btn btn-sm btn-outline-gold" title="Edit"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ route('client.bookings.destroy', $booking) }}"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Booking"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-muted text-center py-3">No bookings found. <a href="{{ route('client.bookings.create') }}">Book your first event!</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $bookings->links() }}</div>
@endsection
