@extends('layouts.app')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')

@section('content')
<a href="{{ route('client.appointments.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back to My Appointments</a>

@php($status = $appointment->status === 'Approved' ? 'Confirmed' : $appointment->status)
<div class="stat-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $appointment->purpose }}</h5>
            <span class="badge badge-{{ strtolower($status) }}">{{ $status }}</span>
        </div>
        <a href="{{ route('client.appointments.create') }}" class="btn btn-gold"><i class="fas fa-calendar-plus me-1"></i> Book Appointment</a>
    </div>

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
</div>
@endsection