@extends('layouts.app')

@section('title', 'Client Details')
@section('page-title', 'Client Details')

@section('content')
<a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="row g-4">
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Client Info</h6>
            <p><strong>Name:</strong> {{ $client->user->name }}</p>
            <p><strong>Email:</strong> {{ $client->user->email }}</p>
            <p><strong>Phone:</strong> {{ $client->user->phone }}</p>
            <p><strong>Address:</strong> {{ $client->address ?? 'N/A' }}</p>
            <p><strong>Joined:</strong> {{ \App\Support\PhilippineTime::date($client->created_at) }}</p>
        </div>
    </div>
    <div class="col-md-8">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Bookings ({{ $client->bookings->count() }})</h6>
            @if($client->bookings->isEmpty())
                <p class="text-muted">No bookings.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Event</th><th>Date</th><th>Package</th><th>Status</th><th>Date Requested</th><th>Last Updated</th></tr></thead>
                        <tbody>
                            @foreach($client->bookings as $booking)
                            <tr>
                                <td>{{ $booking->event_type }}</td>
                                <td>{{ $booking->event_date->format('M d, Y') }}</td>
                                <td>{{ $booking->package->package_name ?? 'Custom' }}</td>
                                <td><span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span></td>
                                <td>{{ \App\Support\PhilippineTime::format($booking->created_at, compact: true) }}</td>
                                <td>{{ \App\Support\PhilippineTime::format($booking->updated_at, compact: true) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection