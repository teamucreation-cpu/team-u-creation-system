<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->appNotifications()->with(['booking', 'appointment'])->latest()->take(50)->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        return response()->json(['count' => Auth::user()->appNotifications()->where('status', 'unread')->count()]);
    }

    public function markAllRead()
    {
        Auth::user()->appNotifications()->where('status', 'unread')->update(['status' => 'read']);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(int $notification)
    {
        $notification = Auth::user()->appNotifications()
            ->with(['booking', 'appointment'])
            ->findOrFail($notification);

        if ($notification->status === 'unread') {
            $notification->update(['status' => 'read']);
        }

        if ($notification->booking) {
            return redirect()->route('admin.bookings.show', $notification->booking);
        }

        if ($notification->appointment) {
            return redirect()->route('admin.appointments.show', $notification->appointment);
        }

        return redirect()->route('admin.notifications.index');
    }

    public function create()
    {
        $users = User::where('role', 'client')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:500'],
            'notification_type' => ['required', 'string', 'max:50'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        app(NotificationService::class)->notify($user, $validated['message'], $validated['notification_type']);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification sent.');
    }

    /**
     * This page is a shared log of every notification sent to every user
     * (see index()), but deleting one is still a personal action — an admin
     * may only remove a notification that was sent to their own account
     * (e.g. a low-stock alert), never a client's. The view only ever shows
     * the delete option on the admin's own rows in the first place; this
     * ownership check is the actual enforcement, in case that's ever
     * bypassed.
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