@extends('layouts.app')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
<div class="table-custom table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Client</th><th>Purpose</th><th>Date</th><th>Time</th><th>Status</th><th>Date Requested</th><th>Last Updated</th><th></th></tr></thead>
        <tbody>
            @forelse($appointments as $apt)
            @php($statusBadge = str_contains($apt->status, 'Rescheduled') ? 'rescheduled' : strtolower($apt->status))
            <tr>
                <td><strong>{{ $apt->client->user->name }}</strong></td>
                <td>{{ $apt->purpose }}</td>
                <td>{{ ($apt->status === \App\Models\Appointment::RESCHEDULE_AWAITING_CLIENT ? $apt->proposed_appointment_date : $apt->appointment_date)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($apt->status === \App\Models\Appointment::RESCHEDULE_AWAITING_CLIENT ? $apt->proposed_appointment_time : $apt->appointment_time)->format('g:i A') }}</td>
                <td><span class="badge badge-{{ $statusBadge }}">{{ $apt->status }}</span></td>
                <td>{{ \App\Support\PhilippineTime::format($apt->created_at, compact: true) }}</td>
                <td>{{ \App\Support\PhilippineTime::format($apt->updated_at, compact: true) }}</td>
                <td><a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-sm btn-outline-gold">View Details</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-muted text-center py-3">No appointments.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection