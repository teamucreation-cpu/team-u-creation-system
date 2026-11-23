@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@push('styles')
<style>
    .admin-notification-row { cursor: pointer; transition: background-color 0.18s ease; }
    .admin-notification-row:hover > td { background-color: rgba(244, 201, 93, 0.12); }
    .admin-notification-row:focus-visible { outline: 2px solid #F4C95D; outline-offset: -2px; }
    .notification-type-badge { background: #121212; border: 1px solid #F4C95D; color: #F4C95D; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Notifications</h5>
    <div class="d-flex align-items-center gap-2">
        <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
            @csrf
            <button class="btn btn-sm btn-outline-gold"><i class="fas fa-check-double me-1"></i> Mark All Read</button>
        </form>
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-gold"><i class="fas fa-paper-plane me-1"></i> Send Reminders</a>
    </div>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Message</th><th>Type</th><th>Status</th><th>Sent</th><th></th></tr></thead>
        <tbody>
            @forelse($notifications as $n)
            @php
                if ($n->appointment_id !== null || $n->appointment) {
                    $typeLabel = 'Appointment';
                } elseif ($n->booking_id !== null || $n->booking) {
                    $typeLabel = 'Booking';
                } elseif (\Illuminate\Support\Str::startsWith($n->notification_type, 'appointment')) {
                    $typeLabel = 'Appointment';
                } elseif (\Illuminate\Support\Str::startsWith($n->notification_type, 'booking')) {
                    $typeLabel = 'Booking';
                } else {
                    $typeLabel = 'General';
                }
            @endphp
            <tr class="admin-notification-row" tabindex="0" aria-label="Open {{ $typeLabel }} notification: {{ $n->message }}">
                <td>
                    <form method="POST" action="{{ route('admin.notifications.open', $n) }}" class="notification-open-form">
                        @csrf
                        <button type="submit" class="btn btn-link text-dark text-decoration-none p-0 text-start">{{ Str::limit($n->message, 90) }}</button>
                    </form>
                </td>
                <td><span class="badge notification-type-badge">{{ $typeLabel }}</span></td>
                <td>
                    @if($n->status === 'unread')
                        <span class="badge badge-pending">Unread</span>
                    @else
                        <span class="badge badge-completed">Read</span>
                    @endif
                </td>
                <td>{{ $n->relative_time }} ({{ \App\Support\PhilippineTime::format($n->created_at, compact: true) }})</td>
                <td class="text-end">
                    <div class="dropdown notif-menu">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notification options">
                            <i class="fas fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Are you sure you want to remove this notification?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete Notification</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-center py-3">No notifications.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.admin-notification-row').forEach(function (row) {
    function isInteractiveTarget(target) {
        return target.closest('button, a, form, .dropdown') !== null;
    }

    function openNotification() {
        row.querySelector('.notification-open-form').submit();
    }

    row.addEventListener('click', function (event) {
        if (!isInteractiveTarget(event.target)) {
            openNotification();
        }
    });

    row.addEventListener('keydown', function (event) {
        if (isInteractiveTarget(event.target)) { return; }
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openNotification();
        }
    });
});
</script>
@endpush