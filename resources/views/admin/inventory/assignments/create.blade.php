@extends('layouts.app')

@section('title', 'Assign Inventory')
@section('page-title', 'Assign Inventory to Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="stat-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            @if($bookings->isEmpty())
                <p class="text-muted mb-0">There are no approved events yet. Approve a booking first before assigning inventory.</p>
            @elseif($items->isEmpty())
                <p class="text-muted mb-0">There are no inventory items with available stock. <a href="{{ route('admin.inventory.create') }}">Add an item</a> first.</p>
            @else
            <form method="POST" action="{{ route('admin.inventory.assignments.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Event (Approved Booking)</label>
                    <select class="form-select" name="booking_id" required>
                        <option value="">Select an event...</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}" @selected(old('booking_id', $selectedBookingId) == $booking->id)>
                                {{ $booking->event_type }} - {{ $booking->client->user->name }} ({{ $booking->event_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Inventory Item</label>
                    <select class="form-select" name="inventory_item_id" required>
                        <option value="">Select an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected(old('inventory_item_id', $selectedItemId) == $item->id)>
                                {{ $item->item_name }} ({{ $item->quantity_available }} available)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" min="1" class="form-control" name="quantity_assigned" value="{{ old('quantity_assigned', 1) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold">Assign Item</button>
                <a href="{{ route('admin.inventory.assignments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
