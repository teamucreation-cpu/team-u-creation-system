@extends('layouts.app')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')

@section('content')
<a href="{{ route('client.appointments.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back to My Appointments</a>

@php($status = $appointment->status === 'Approved' ? 'Confirmed' : $appointment->status)
@php($awaitingReschedule = $appointment->status === \App\Models\Appointment::RESCHEDULE_AWAITING_CLIENT)
@php($displayStatus = $awaitingReschedule ? 'Rescheduled – Awaiting Your Confirmation' : $status)
@php($statusBadge = str_contains($status, 'Rescheduled') ? 'rescheduled' : strtolower($status))
@php($newAppointmentDate = $awaitingReschedule || $appointment->reschedule_response === 'Declined' ? $appointment->proposed_appointment_date : $appointment->appointment_date)
@php($newAppointmentTime = $awaitingReschedule || $appointment->reschedule_response === 'Declined' ? $appointment->proposed_appointment_time : $appointment->appointment_time)
<div class="stat-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $appointment->purpose }}</h5>
            <span class="badge badge-{{ $statusBadge }}">{{ $displayStatus }}</span>
        </div>
        <a href="{{ route('client.appointments.create') }}" class="btn btn-gold"><i class="fas fa-calendar-plus me-1"></i> Book Appointment</a>
    </div>

    @if($awaitingReschedule)
        <div class="alert alert-warning" role="status">
            <p class="mb-1">Your appointment with the administrator has been rescheduled.</p>
            <strong>Appointment Status:</strong> {{ $displayStatus }}
        </div>
    @elseif($appointment->reschedule_response === 'Declined')
        <div class="alert alert-warning" role="status">
            You have declined the rescheduled appointment. Please book a new appointment to select your preferred date and time.
            <a href="{{ route('client.appointments.create') }}" class="btn btn-gold btn-sm ms-2">Book New Appointment</a>
        </div>
    @endif

    @if($appointment->original_appointment_date)
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <p><strong>Original Appointment Date:</strong> {{ $appointment->original_appointment_date->format('F d, Y') }}</p>
                <p><strong>Original Appointment Time:</strong> {{ \Carbon\Carbon::parse($appointment->original_appointment_time)->format('g:i A') }}</p>
                <p><strong>Purpose:</strong> {{ $appointment->purpose }}</p>
                <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
                <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
                <p><strong>Notes:</strong> {{ $appointment->notes ?: 'No additional notes.' }}</p>
                <p><strong>Related Booking:</strong> {{ $appointment->booking?->event_type ?? 'None' }}</p>
            </div>
            <div class="col-12 col-md-6">
                @if($appointment->reschedule_reason)
                    <p><strong>Reason for Rescheduling (Admin Notes):</strong></p>
                    <p class="mb-3" style="white-space: pre-line;">{{ $appointment->reschedule_reason }}</p>
                @endif
                <p><strong>New Appointment Date:</strong> {{ $newAppointmentDate->format('F d, Y') }}</p>
                <p><strong>New Appointment Time:</strong> {{ \Carbon\Carbon::parse($newAppointmentTime)->format('g:i A') }}</p>
                <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
                <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <p><strong>Appointment Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
                <p><strong>Appointment Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                <p><strong>Purpose:</strong> {{ $appointment->purpose }}</p>
            </div>
            <div class="col-12 col-md-6">
                <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
                <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
                <p><strong>Related Booking:</strong> {{ $appointment->booking?->event_type ?? 'None' }}</p>
            </div>
            <div class="col-12">
                <strong>Notes:</strong>
                <p class="mb-0">{{ $appointment->notes ?: 'No additional notes.' }}</p>
            </div>
        </div>
    @endif

    @if($awaitingReschedule)
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmRescheduleModal">Confirm</button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#declineRescheduleModal">Decline</button>
        </div>
    @endif
</div>

@if($awaitingReschedule)
    <div class="modal fade" id="confirmRescheduleModal" tabindex="-1" aria-labelledby="confirmRescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmRescheduleModalLabel">Confirm Rescheduled Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Do you want to confirm the rescheduled appointment?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('client.appointments.reschedule.confirm', $appointment) }}">@csrf
                        <button class="btn btn-success" type="submit">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="declineRescheduleModal" tabindex="-1" aria-labelledby="declineRescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineRescheduleModalLabel">Decline Rescheduled Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Do you want to decline the rescheduled appointment? If the proposed schedule is not convenient, please book a new appointment.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('client.appointments.reschedule.decline', $appointment) }}">@csrf
                        <button class="btn btn-danger" type="submit">Yes, Decline</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection