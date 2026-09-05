@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">My Notifications</h5>
    <form method="POST" action="{{ route('client.notifications.markAllRead') }}">
        @csrf
        <button class="btn btn-sm btn-outline-gold"><i class="fas fa-check-double me-1"></i> Mark All Read</button>
    </form>
</div>

@if($notifications->isEmpty())
    <div class="stat-card text-center py-4">
        <p class="text-muted mb-0"><i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>No notifications.</p>
    </div>
@else
    @foreach($notifications as $notif)
    <div class="stat-card mb-2 {{ $notif->status === 'unread' ? 'border-start border-4' : '' }}" style="{{ $notif->status === 'unread' ? 'border-color: var(--tu-gold) !important;' : '' }}">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <p class="mb-1">{{ $notif->message }}</p>
                <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $notif->relative_time }} &middot; {{ $notif->notification_type }}</small>
            </div>
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                @if($notif->status === 'unread')
                <form method="POST" action="{{ route('client.notifications.read', $notif) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" title="Mark read"><i class="fas fa-check"></i></button>
                </form>
                @endif
                <div class="dropdown notif-menu">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notification options">
                        <i class="fas fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" action="{{ route('client.notifications.destroy', $notif) }}" onsubmit="return confirm('Are you sure you want to remove this notification?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete Notification</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="mt-3">{{ $notifications->links() }}</div>
@endif
@endsection