@extends('layouts.app')

@section('title', 'Inventory Assignments')
@section('page-title', 'Inventory Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Inventory Assigned to Events</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-gold"><i class="fas fa-boxes-stacked me-1"></i> Inventory</a>
        <a href="{{ route('admin.inventory.assignments.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Assign Item</a>
    </div>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Quantity</th><th>Event</th><th>Client</th><th>Qty</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($assignments as $assignment)
            <tr>
                <td><strong>{{ $assignment->inventoryItem->quantity_total ?? '—' }}</strong></td>
                <td>{{ $assignment->booking->event_type ?? 'N/A' }}</td>
                <td>{{ $assignment->booking->client->user->name ?? 'N/A' }}</td>
                <td>{{ $assignment->quantity_assigned }}</td>
                <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $assignment->status)) }}">{{ $assignment->status }}</span></td>
                <td>
                    @if($assignment->status === 'In Use')
                        <form method="POST" action="{{ route('admin.inventory.assignments.resolve', $assignment) }}" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="Returned">
                            <button class="btn btn-sm btn-outline-gold" onclick="return confirm('Mark this item as returned?')">Mark Returned</button>
                        </form>
                        <form method="POST" action="{{ route('admin.inventory.assignments.resolve', $assignment) }}" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="Damaged">
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Mark this item as damaged?')">Mark Damaged</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.inventory.assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('Remove this inventory assignment?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-center py-3">No inventory assignments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $assignments->links() }}</div>
@endsection
