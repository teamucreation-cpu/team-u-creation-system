@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory Monitoring')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        @if($status)
            {{ $status }} Items
        @else
            All Inventory Items
        @endif
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inventory.assignments.index') }}" class="btn btn-outline-gold"><i class="fas fa-people-carry-box me-1"></i> Assignments</a>
        <a href="{{ route('admin.inventory.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Add Item</a>
    </div>
</div>

<div class="list-toolbar">
    <form method="GET" action="{{ route('admin.inventory.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by item name..." value="{{ $search }}">
        </div>
        <div class="col-6 col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach(\App\Models\InventoryItem::CATEGORIES as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['Available', 'In Use', 'Low Stock', 'Out of Stock', 'Damaged'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-gold"><i class="fas fa-filter me-1"></i> Apply</button>
            @if($status || $search || $category)
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Quantity</th>
                <th>Category</th>
                <th>Available</th>
                <th>In Use</th>
                <th>Damaged</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td><strong>{{ $item->quantity_total }}</strong></td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->quantity_available }} / {{ $item->quantity_total }}</td>
                    <td>{{ $item->quantity_in_use }}</td>
                    <td>{{ $item->quantity_damaged }}</td>
                    <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $item->status)) }}">{{ $item->status }}</span></td>
                    <td>{{ $item->updated_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-outline-gold">View</a>
                        <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-sm btn-outline-gold">Edit</a>
                        <form method="POST" action="{{ route('admin.inventory.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this inventory item?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-muted text-center py-3">No inventory items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
