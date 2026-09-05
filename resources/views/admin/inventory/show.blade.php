@extends('layouts.app')

@section('title', $inventoryItem->item_name)
@section('page-title', 'Inventory Item Details')

@section('content')
<a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="stat-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $inventoryItem->item_name }}</h4>
            <span class="text-muted">{{ $inventoryItem->category }}</span>
        </div>
        <span class="badge badge-{{ strtolower(str_replace(' ', '-', $inventoryItem->status)) }} fs-6">{{ $inventoryItem->status }}</span>
    </div>

    <div class="row mt-4 text-center g-3">
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->quantity_total }}</div>
            <small class="text-muted">Total</small>
        </div>
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->quantity_available }}</div>
            <small class="text-muted">Available</small>
        </div>
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->quantity_in_use }}</div>
            <small class="text-muted">In Use</small>
        </div>
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->quantity_damaged }}</div>
            <small class="text-muted">Damaged</small>
        </div>
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->quantity_returned }}</div>
            <small class="text-muted">Returned (lifetime)</small>
        </div>
        <div class="col-6 col-md-2">
            <div class="fw-bold fs-4">{{ $inventoryItem->low_stock_threshold }}</div>
            <small class="text-muted">Low Stock At</small>
        </div>
    </div>

    @if($inventoryItem->notes)
        <hr>
        <h6 class="fw-bold">Notes</h6>
        <p class="mb-0">{{ $inventoryItem->notes }}</p>
    @endif

    <p class="text-muted mt-3 mb-0"><small>Last updated {{ $inventoryItem->updated_at->format('F d, Y g:i A') }}</small></p>

    <div class="mt-3">
        <a href="{{ route('admin.inventory.edit', $inventoryItem) }}" class="btn btn-sm btn-outline-gold">Edit Item</a>
        <a href="{{ route('admin.inventory.assignments.create', ['item_id' => $inventoryItem->id]) }}" class="btn btn-sm btn-gold">Assign to Event</a>
    </div>
</div>

<div class="stat-card">
    <h6 class="fw-bold mb-3">Assignment History</h6>
    @if($inventoryItem->assignments->isEmpty())
        <p class="text-muted mb-0">This item has never been assigned to an event.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Event</th><th>Client</th><th>Qty</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($inventoryItem->assignments->sortByDesc('created_at') as $assignment)
                    <tr>
                        <td>{{ $assignment->booking->event_type ?? 'N/A' }}</td>
                        <td>{{ $assignment->booking->client->user->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->quantity_assigned }}</td>
                        <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $assignment->status)) }}">{{ $assignment->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
