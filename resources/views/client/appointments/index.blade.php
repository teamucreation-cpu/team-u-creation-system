@extends('layouts.app')

@section('title', 'My Appointments')
@section('page-title', 'Appointments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">My Appointments</h5>
    <a href="{{ route('client.appointments.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Request Appointment</a>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Purpose</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($appointments as $apt)
            <tr>
                <td><strong>{{ $apt->purpose }}</strong></td>
                <td>{{ $apt->appointment_date->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}</td>
                <td><span class="badge badge-{{ strtolower($apt->status) }}">{{ $apt->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted text-center py-3">No appointments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection