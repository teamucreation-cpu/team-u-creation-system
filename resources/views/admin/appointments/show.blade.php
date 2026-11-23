@extends('layouts.app')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')

@section('content')
<a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="stat-card">
    @php($statusBadge = str_contains($appointment->status, 'Rescheduled') ? 'rescheduled' : strtolower($appointment->status))
    @if($appointment->original_appointment_date)
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <p><strong>Client:</strong> {{ $appointment->client->user->name }}</p>
                <p><strong>Email:</strong> {{ $appointment->client->user->email }}</p>
                <p><strong>Phone:</strong> {{ $appointment->client->user->phone }}</p>
                <p><strong>Purpose:</strong> {{ $appointment->purpose }}</p>
                <p><strong>Related Booking:</strong> {{ $appointment->booking?->event_type ?? 'None' }}</p>
                <p><strong>Client Notes:</strong> {{ $appointment->notes ?? 'None' }}</p>
                <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
            </div>
            <div class="col-12 col-md-6">
                <p><strong>Status:</strong> <span class="badge badge-{{ $statusBadge }}">{{ $appointment->status }}</span></p>
                @if($appointment->proposed_appointment_date)
                    <p><strong>Proposed Date:</strong> {{ $appointment->proposed_appointment_date->format('F d, Y') }}</p>
                    <p><strong>Proposed Time:</strong> {{ \Carbon\Carbon::parse($appointment->proposed_appointment_time)->format('g:i A') }}</p>
                @endif
                <p><strong>Notes:</strong></p>
                <p class="mb-3" style="white-space: pre-line;">{{ $appointment->reschedule_reason ?? 'None' }}</p>
                <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-6">
                <p><strong>Client:</strong> {{ $appointment->client->user->name }}</p>
                <p><strong>Email:</strong> {{ $appointment->client->user->email }}</p>
                <p><strong>Phone:</strong> {{ $appointment->client->user->phone }}</p>
                <p><strong>Purpose:</strong> {{ $appointment->purpose }}</p>
                <p><strong>Related Booking:</strong> {{ $appointment->booking?->event_type ?? 'None' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                @php($statusBadge = str_contains($appointment->status, 'Rescheduled') ? 'rescheduled' : strtolower($appointment->status))
                <p><strong>Status:</strong> <span class="badge badge-{{ $statusBadge }}">{{ $appointment->status }}</span></p>
                <p><strong>Notes:</strong> {{ $appointment->notes ?? 'None' }}</p>
                <p><strong>Date Requested:</strong> {{ \App\Support\PhilippineTime::format($appointment->created_at) }}</p>
                <p><strong>Last Updated:</strong> {{ \App\Support\PhilippineTime::format($appointment->updated_at) }}</p>
            </div>
        </div>
    @endif

    <hr>
    <div class="d-flex gap-2 flex-wrap">
        @if($appointment->status === 'Pending')
            <form method="POST" action="{{ route('admin.appointments.approve', $appointment) }}">@csrf
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.appointments.decline', $appointment) }}">@csrf
                <button class="btn btn-danger"><i class="fas fa-times me-1"></i> Decline</button>
            </form>
            <button type="button" class="btn btn-outline-gold" data-bs-toggle="modal" data-bs-target="#rescheduleModal">Reschedule</button>
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
</div>

@if($appointment->status === 'Pending')
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.appointments.reschedule', $appointment) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Do you want to reschedule this appointment?</p>
                        <div class="mb-3">
                            <label class="form-label" for="newAppointmentDate">New Date</label>
                            <input type="date" class="form-control" id="newAppointmentDate" name="appointment_date" min="{{ \Carbon\Carbon::today(config('app.display_timezone', 'Asia/Manila'))->toDateString() }}" value="{{ old('appointment_date') }}" required>
                            @error('appointment_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="newAppointmentTime">New Time</label>
                            <input type="time" class="form-control" id="newAppointmentTime" name="appointment_time" value="{{ old('appointment_time') }}" data-today-time="{{ now(config('app.display_timezone', 'Asia/Manila'))->format('H:i') }}" required>
                            @error('appointment_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="rescheduleReason">Reason for Rescheduling</label>
                            <textarea class="form-control" id="rescheduleReason" name="reason" rows="4" maxlength="2000" required>{{ old('reason') }}</textarea>
                            @error('reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gold">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dateInput = document.getElementById('newAppointmentDate');
            var timeInput = document.getElementById('newAppointmentTime');
            function updateMinimumTime() {
                if (dateInput.value === dateInput.min) {
                    timeInput.min = timeInput.dataset.todayTime;
                } else {
                    timeInput.removeAttribute('min');
                }
            }
            dateInput.addEventListener('change', updateMinimumTime);
            updateMinimumTime();
            @if($errors->has('appointment_date') || $errors->has('appointment_time') || $errors->has('reason'))
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rescheduleModal')).show();
            @endif
        });
    </script>
@endif
@endsection