<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\EventPackage;
use App\Models\InventoryItem;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'Pending')->count(),
            'approved_bookings' => Booking::where('status', 'Approved')->count(),
            'declined_bookings' => Booking::where('status', 'Declined')->count(),
            'rescheduled_bookings' => Booking::where('status', 'Rescheduled')->count(),
            'upcoming_events' => Booking::where('status', 'Approved')->where('event_date', '>=', now())->count(),
            'total_clients' => Client::count(),
            'total_packages' => EventPackage::count(),
            'pending_appointments' => Appointment::where('status', 'Pending')->count(),
            'total_inventory_items' => InventoryItem::count(),
            'available_inventory_items' => InventoryItem::whereColumn('quantity_available', '>', 'low_stock_threshold')->count(),
            'low_stock_items' => InventoryItem::where('quantity_available', '>', 0)->whereColumn('quantity_available', '<=', 'low_stock_threshold')->count(),
            'out_of_stock_items' => InventoryItem::where('quantity_available', 0)->count(),
        ];

        $recentBookings = Booking::with(['client.user', 'package'])->latest()->take(5)->get();
        $upcomingEvents = Booking::with(['client.user'])->where('status', 'Approved')->where('event_date', '>=', now())->orderBy('event_date')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'upcomingEvents'));
    }
}