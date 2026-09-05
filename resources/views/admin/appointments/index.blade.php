@extends('layouts.app')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Client</th><th>Purpose</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($appointments as $apt)
            <tr>
                <td><strong>{{ $apt->client->user->name }}</strong></td>
                <td>{{ $apt->purpose }}</td>
                <td>{{ $apt->appointment_date->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}</td>
                <td><span class="badge badge-{{ strtolower($apt->status) }}">{{ $apt->status }}</span></td>
                <td><a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-gold">View Details</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-center py-3">No appointments.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection