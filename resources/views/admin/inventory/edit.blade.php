@extends('layouts.app')

@section('title', 'Edit Inventory Item')
@section('page-title', 'Edit Inventory Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="stat-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.inventory.update', $inventoryItem) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" name="item_name" value="{{ old('item_name', $inventoryItem->item_name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        @foreach(\App\Models\InventoryItem::CATEGORIES as $cat)
                            <option value="{{ $cat }}" {{ old('category', $inventoryItem->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Quantity</label>
                        <input type="number" min="0" class="form-control" name="quantity_total" value="{{ old('quantity_total', $inventoryItem->quantity_total) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Available Quantity</label>
                        <input type="number" min="0" class="form-control" name="quantity_available" value="{{ old('quantity_available', $inventoryItem->quantity_available) }}" required>
                        <small class="text-muted">Use this to correct counts after a physical recount.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Damaged Quantity</label>
                        <input type="number" min="0" class="form-control" name="quantity_damaged" value="{{ old('quantity_damaged', $inventoryItem->quantity_damaged) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', $inventoryItem->low_stock_threshold) }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">In Use <small class="text-muted">(system-managed)</small></label>
                        <input type="text" class="form-control" value="{{ $inventoryItem->quantity_in_use }}" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Returned (lifetime) <small class="text-muted">(system-managed)</small></label>
                        <input type="text" class="form-control" value="{{ $inventoryItem->quantity_returned }}" disabled>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes', $inventoryItem->notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold">Save Changes</button>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
