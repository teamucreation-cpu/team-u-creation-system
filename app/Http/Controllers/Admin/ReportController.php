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
    private const PERIODS = ['week', 'month', 'year'];

    private function periodDates(string $period): array
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'month';
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
        };
        $endDate = match ($period) {
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'year' => now()->endOfYear(),
        };

        return [$period, $startDate, $endDate];
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'week' => 'This Week',
            'year' => 'This Year',
            default => 'This Month',
        };
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $availableYears = Booking::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();
        $reportYear = (int) $request->get('year', now()->year);
        if (! $availableYears->contains($reportYear)) {
            $reportYear = (int) now()->year;
        }

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
            ->whereYear('created_at', $reportYear)
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
            'bookingsByStatus', 'bookingsByMonth', 'topPackages', 'stats', 'period', 'reportYear', 'availableYears',
            'inventoryAvailability', 'lowStockItems', 'mostUsedItems', 'damagedItems', 'usagePerEvent'
        ));
    }

    public function bookings(Request $request)
    {
        [$period, $startDate, $endDate] = $this->periodDates($request->get('period', 'month'));
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, \App\Http\Controllers\AdminBookingController::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $query = Booking::with(['client.user', 'package'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhereHas('client.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate($perPage)->withQueryString();
        $periodLabel = $this->periodLabel($period);

        return view('admin.reports.bookings', compact('bookings', 'period', 'periodLabel', 'search', 'perPage'));
    }

    public function clients(Request $request)
    {
        [$period, $startDate, $endDate] = $this->periodDates($request->get('period', 'month'));
        $search = $request->get('search');
        $query = Client::with('user', 'bookings')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest();

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(10)->withQueryString();
        $periodLabel = $this->periodLabel($period);

        return view('admin.reports.clients', compact('clients', 'period', 'periodLabel', 'search'));
    }

    public function upcomingEvents(Request $request)
    {
        [$period] = $this->periodDates($request->get('period', 'month'));
        $search = $request->get('search');
        $query = Booking::with(['client.user', 'package'])
            ->where('status', 'Approved')
            ->where('event_date', '>=', now())
            ->orderBy('event_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhereHas('client.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('package', function ($q2) use ($search) {
                        $q2->where('package_name', 'like', "%{$search}%");
                    });
            });
        }

        $upcomingEvents = $query->paginate(10)->withQueryString();

        return view('admin.reports.upcoming-events', compact('upcomingEvents', 'search', 'period'));
    }
}