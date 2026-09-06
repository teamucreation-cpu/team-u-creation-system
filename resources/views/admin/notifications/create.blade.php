@extends('layouts.app')

@section('title', 'Send Reminders')
@section('page-title', 'Send Reminders')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="stat-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.notifications.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Recipient (Client)</label>
                    <select class="form-select" name="user_id" required>
                        <option value="">Select client...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notification Type</label>
                    <select class="form-select" name="notification_type" required>
                        <option value="general">General</option>
                        <option value="booking_update">Booking Update</option>
                        <option value="event_reminder">Event Reminder</option>
                        <option value="appointment_reminder">Appointment Reminder</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="4" required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane me-1"></i> Send</button>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection