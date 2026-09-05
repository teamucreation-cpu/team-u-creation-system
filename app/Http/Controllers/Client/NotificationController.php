<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->appNotifications()->latest()->paginate(20);
        return view('client.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        $notification->update(['status' => 'read']);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Auth::user()->appNotifications()->where('status', 'unread')->update(['status' => 'read']);
        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Deletes exactly one notification — the ownership check means a client
     * can never remove anyone else's notification, even by guessing an id.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification removed.');
    }
}