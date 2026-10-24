@extends('layouts.app')

@section('title', 'My Appointments')
@section('page-title', 'Appointments')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="mb-0">My Appointments</h5>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-gold"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
        <a href="{{ route('client.appointments.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Book Appointment</a>
    </div>
</div>

@if($appointments->isEmpty())
    <div class="table-custom p-4 text-center text-muted">You have no appointments scheduled yet.</div>
@else
    <div class="table-custom table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Appointment Date</th><th>Appointment Time</th><th>Purpose</th><th>Status</th><th>Date Requested</th><th>Last Updated</th><th></th></tr></thead>
            <tbody>
                @foreach($appointments as $appointment)
                @php($status = $appointment->status === 'Approved' ? 'Confirmed' : $appointment->status)
                <tr>
                    <td>{{ $appointment->appointment_date->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                    <td><strong>{{ $appointment->purpose }}</strong></td>
                    <td><span class="badge badge-{{ strtolower($status) }}">{{ $status }}</span></td>
                    <td>{{ \App\Support\PhilippineTime::format($appointment->created_at, compact: true) }}</td>
                    <td>{{ \App\Support\PhilippineTime::format($appointment->updated_at, compact: true) }}</td>
                    <td><a href="{{ route('client.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-gold">View Details</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection