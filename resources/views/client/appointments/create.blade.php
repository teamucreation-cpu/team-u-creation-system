@extends('layouts.app')

@section('title', 'Request Appointment')
@section('page-title', 'Request Appointment')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="stat-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('client.appointments.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Purpose</label>
                    <select class="form-select" name="purpose" required>
                        <option value="">Select purpose...</option>
                        <option value="Initial Consultation" {{ old('purpose') == 'Initial Consultation' ? 'selected' : '' }}>Initial Consultation</option>
                        <option value="Event Planning Meeting" {{ old('purpose') == 'Event Planning Meeting' ? 'selected' : '' }}>Event Planning Meeting</option>
                        <option value="Package Discussion" {{ old('purpose') == 'Package Discussion' ? 'selected' : '' }}>Package Discussion</option>
                        <option value="Event Coordination Meeting" {{ old('purpose') == 'Event Coordination Meeting' ? 'selected' : '' }}>Event Coordination Meeting</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" name="appointment_date" value="{{ old('appointment_date') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preferred Time</label>
                        <input type="time" class="form-control" name="appointment_time" value="{{ old('appointment_time') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Related Booking <small class="text-muted">(optional)</small></label>
                    <select class="form-select" name="booking_id">
                        <option value="">None</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}">{{ $booking->event_type }} - {{ $booking->event_date->format('M d, Y') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold">Submit Request</button>
                <a href="{{ route('client.appointments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection