@extends('layouts.app')

@section('title', 'Remind Clients')
@section('page-title', 'Remind Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">My Notifications</h5>
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
            <tr>
                <td>
                    <form method="POST" action="{{ route('admin.notifications.open', $n) }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-dark text-decoration-none p-0 text-start">{{ Str::limit($n->message, 90) }}</button>
                    </form>
                </td>
                <td><span class="badge bg-secondary">{{ $n->notification_type }}</span></td>
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