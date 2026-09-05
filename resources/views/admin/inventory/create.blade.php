@extends('layouts.app')

@section('title', 'Add Inventory Item')
@section('page-title', 'Add Inventory Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="stat-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.inventory.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" name="item_name" value="{{ old('item_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="">Select a category...</option>
                        @foreach(\App\Models\InventoryItem::CATEGORIES as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Quantity</label>
                        <input type="number" min="0" class="form-control" name="quantity_total" value="{{ old('quantity_total', 0) }}" required>
                        <small class="text-muted">All units start as Available.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" required>
                        <small class="text-muted">Flagged "Low Stock" at or below this available count.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold">Add Item</button>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
