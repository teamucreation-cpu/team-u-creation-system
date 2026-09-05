@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.index', ['period' => 'week']) }}" class="btn btn-sm {{ $period === 'week' ? 'btn-gold' : 'btn-outline-gold' }}">This Week</a>
    <a href="{{ route('admin.reports.index', ['period' => 'month']) }}" class="btn btn-sm {{ $period === 'month' ? 'btn-gold' : 'btn-outline-gold' }}">This Month</a>
    <a href="{{ route('admin.reports.index', ['period' => 'year']) }}" class="btn btn-sm {{ $period === 'year' ? 'btn-gold' : 'btn-outline-gold' }}">This Year</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $stats['total_bookings_period'] }}</div>
            <div class="stat-label">Bookings (Period)</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $stats['new_clients_period'] }}</div>
            <div class="stat-label">New Clients</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $stats['upcoming_events'] }}</div>
            <div class="stat-label">Upcoming Events</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Bookings by Status</h6>
            @foreach($bookingsByStatus as $status => $count)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="badge badge-{{ strtolower($status) }}">{{ $status }}</span>
                    <strong>{{ $count }}</strong>
                </div>
            @endforeach
            @if($bookingsByStatus->isEmpty())
                <p class="text-muted">No data for this period.</p>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Bookings by Month ({{ now()->year }})</h6>
            @for($m = 1; $m <= 12; $m++)
                @php $count = $bookingsByMonth[$m] ?? 0; @endphp
                <div class="d-flex justify-content-between align-items-center py-1">
                    <small>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</small>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: {{ $count * 20 }}px; height: 12px; background: var(--tu-gold); border-radius: 3px;"></div>
                        <small>{{ $count }}</small>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

@if($topPackages->isNotEmpty())
<div class="stat-card mt-4">
    <h6 class="fw-bold mb-3">Top Event Packages</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>Package</th><th>Bookings</th></tr></thead>
            <tbody>
                @foreach($topPackages as $pkg)
                <tr>
                    <td>{{ $pkg->package->package_name ?? 'Deleted Package' }}</td>
                    <td><strong>{{ $pkg->count }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<h5 class="fw-bold mt-5 mb-3"><i class="fas fa-boxes-stacked me-2" style="color: var(--tu-gold);"></i>Inventory Reports</h5>

<div class="row g-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Current Inventory Availability</h6>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-gold">View All</a>
            </div>
            @if($inventoryAvailability->isEmpty())
                <p class="text-muted mb-0">No inventory items yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Quantity</th><th>Available</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($inventoryAvailability as $item)
                            <tr>
                                <td>{{ $item->quantity_total }}</td>
                                <td>{{ $item->quantity_available }} / {{ $item->quantity_total }}</td>
                                <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $item->status)) }}">{{ $item->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Low Stock &amp; Out of Stock Items</h6>
            @if($lowStockItems->isEmpty())
                <p class="text-muted mb-0">All items are well stocked.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Quantity</th><th>Available</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($lowStockItems as $item)
                            <tr>
                                <td>{{ $item->quantity_total }}</td>
                                <td>{{ $item->quantity_available }}</td>
                                <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $item->status)) }}">{{ $item->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Most Used Inventory Items</h6>
            @if($mostUsedItems->isEmpty())
                <p class="text-muted mb-0">No usage recorded yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Quantity</th><th>Total Assigned</th></tr></thead>
                        <tbody>
                            @foreach($mostUsedItems as $usage)
                            <tr>
                                <td>{{ $usage->inventoryItem->quantity_total ?? '—' }}</td>
                                <td><strong>{{ $usage->total_used }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <h6 class="fw-bold mb-3">Damaged Inventory Records</h6>
            @if($damagedItems->isEmpty())
                <p class="text-muted mb-0">No damaged items reported.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Quantity</th><th>Event</th><th>Qty</th></tr></thead>
                        <tbody>
                            @foreach($damagedItems as $assignment)
                            <tr>
                                <td>{{ $assignment->inventoryItem->quantity_total ?? '—' }}</td>
                                <td>{{ $assignment->booking->event_type ?? 'N/A' }}</td>
                                <td>{{ $assignment->quantity_assigned }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@if($usagePerEvent->isNotEmpty())
<div class="stat-card mt-4">
    <h6 class="fw-bold mb-3">Inventory Usage Per Event</h6>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Event</th><th>Items Assigned</th><th>Total Units</th></tr></thead>
            <tbody>
                @foreach($usagePerEvent as $usage)
                <tr>
                    <td>{{ $usage->booking->event_type ?? 'Deleted Booking' }}</td>
                    <td>{{ $usage->item_count }}</td>
                    <td><strong>{{ $usage->total_quantity }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection