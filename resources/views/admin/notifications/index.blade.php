@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">All Notifications</h5>
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-gold"><i class="fas fa-paper-plane me-1"></i> Send Notification</a>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Recipient</th><th>Message</th><th>Type</th><th>Status</th><th>Sent</th><th></th></tr></thead>
        <tbody>
            @forelse($notifications as $n)
            <tr>
                <td>{{ $n->user->name ?? 'Unknown' }}</td>
                <td>{{ Str::limit($n->message, 60) }}</td>
                <td><span class="badge bg-secondary">{{ $n->notification_type }}</span></td>
                <td>
                    @if($n->status === 'unread')
                        <span class="badge badge-pending">Unread</span>
                    @else
                        <span class="badge badge-completed">Read</span>
                    @endif
                </td>
                <td>{{ $n->relative_time }}</td>
                <td class="text-end">
                    {{-- This log lists every notification sent to every user, but
                         deleting is still a personal action — only shown, and only
                         allowed, on notifications addressed to the admin's own
                         account (e.g. a low-stock alert), never a client's. --}}
                    @if($n->user_id === auth()->id())
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
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-center py-3">No notifications sent.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection