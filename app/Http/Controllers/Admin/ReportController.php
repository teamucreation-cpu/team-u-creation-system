<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $bookingsByStatus = Booking::where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $bookingsByMonth = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month');

        $topPackages = Booking::with('package')
            ->whereNotNull('package_id')
            ->selectRaw('package_id, COUNT(*) as count')
            ->groupBy('package_id')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $stats = [
            'total_bookings_period' => Booking::where('created_at', '>=', $startDate)->count(),
            'new_clients_period' => Client::where('created_at', '>=', $startDate)->count(),
            'upcoming_events' => Booking::where('status', 'Approved')->where('event_date', '>=', now())->count(),
        ];

        // ─── Inventory Reports ─────────────────────────────────────────
        $inventoryAvailability = InventoryItem::orderBy('item_name')->get();

        // Low stock and out-of-stock items both belong on the restock-attention report.
        $lowStockItems = InventoryItem::where(function ($q) {
                $q->where('quantity_available', 0)
                    ->orWhere(function ($q2) {
                        $q2->where('quantity_available', '>', 0)
                            ->whereColumn('quantity_available', '<=', 'low_stock_threshold');
                    });
            })
            ->orderBy('quantity_available')
            ->get();

        $mostUsedItems = InventoryAssignment::with('inventoryItem')
            ->selectRaw('inventory_item_id, SUM(quantity_assigned) as total_used')
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_used')
            ->take(5)
            ->get();

        $damagedItems = InventoryAssignment::with(['inventoryItem', 'booking.client.user'])
            ->where('status', 'Damaged')
            ->latest()
            ->take(10)
            ->get();

        $usagePerEvent = InventoryAssignment::with(['inventoryItem', 'booking'])
            ->selectRaw('booking_id, COUNT(*) as item_count, SUM(quantity_assigned) as total_quantity')
            ->groupBy('booking_id')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'bookingsByStatus', 'bookingsByMonth', 'topPackages', 'stats', 'period',
            'inventoryAvailability', 'lowStockItems', 'mostUsedItems', 'damagedItems', 'usagePerEvent'
        ));
    }
}