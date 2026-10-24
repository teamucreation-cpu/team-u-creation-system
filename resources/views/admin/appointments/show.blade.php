@extends('layouts.app')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')

@section('content')
<a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="stat-card">
    <div class="row">
        <div class="col-md-6">
            <p><strong>Client:</strong> {{ $appointment->client->user->name }}</p>
            <p><strong>Email:</strong> {{ $appointment->client->user->email }}</p>
            <p><strong>Phone:</strong> {{ $appointment->client->user->phone }}</p>
            <p><strong>Purpose:</strong> {{ $appointment->purpose }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
            <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
            <p><strong>Status:</strong> <span class="badge badge-{{ strtolower($appointment->status) }}">{{ $appointment->status }}</span></p>
            <p><strong>Notes:</strong> {{ $appointment->notes ?? 'None' }}</p>
            <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
            <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
        </div>
    </div>

    <hr>
    <div class="d-flex gap-2 flex-wrap">
        @if($appointment->status === 'Pending')
            <form method="POST" action="{{ route('admin.appointments.approve', $appointment) }}">@csrf
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.appointments.decline', $appointment) }}">@csrf
                <button class="btn btn-danger"><i class="fas fa-times me-1"></i> Decline</button>
            </form>
            <button type="button" class="btn btn-outline-gold" data-bs-toggle="collapse" data-bs-target="#rescheduleForm">Reschedule</button>
        @elseif(in_array($appointment->status, ['Confirmed', 'Rescheduled'], true))
            <form method="POST" action="{{ route('admin.appointments.complete', $appointment) }}">@csrf
                <button class="btn btn-outline-success"><i class="fas fa-check-double me-1"></i> Complete</button>
            </form>
            @if($appointment->status === 'Rescheduled')
                <form method="POST" action="{{ route('admin.appointments.cancel', $appointment) }}">@csrf
                    <button class="btn btn-outline-danger"><i class="fas fa-ban me-1"></i> Cancel</button>
                </form>
            @endif
        @endif
    </div>

    @if($appointment->status === 'Pending')
        <div class="collapse mt-3" id="rescheduleForm">
            <form method="POST" action="{{ route('admin.appointments.reschedule', $appointment) }}" class="border p-3 rounded">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">New Date</label>
                        <input type="date" class="form-control" name="appointment_date" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Time</label>
                        <input type="time" class="form-control" name="appointment_time" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold mt-3">Confirm Reschedule</button>
            </form>
        </div>
    @endif
</div>
@endsection