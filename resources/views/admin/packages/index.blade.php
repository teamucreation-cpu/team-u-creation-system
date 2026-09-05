@extends('layouts.app')

@section('title', 'Event Packages')
@section('page-title', 'Event Packages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        @if($availability === 'available')
            Available Packages
        @elseif($availability === 'unavailable')
            Unavailable Packages
        @else
            All Event Packages
        @endif
    </h5>
    <a href="{{ route('admin.packages.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Add New Package</a>
</div>

<div class="list-toolbar">
    <form method="GET" action="{{ route('admin.packages.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search by package name..." value="{{ $search }}">
        </div>
        <div class="col-6 col-md-3">
            <select name="availability" class="form-select">
                <option value="">All</option>
                <option value="available" {{ $availability === 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ $availability === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-gold"><i class="fas fa-filter me-1"></i> Apply</button>
            @if($search || $availability)
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($packages as $package)
                <tr>
                    <td><strong>{{ $package->package_name }}</strong></td>
                    <td>&#8369;{{ number_format($package->price, 2) }}</td>
                    <td>
                        @if ($package->is_available)
                            <span class="badge badge-approved">Available</span>
                        @else
                            <span class="badge badge-cancelled">Unavailable</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-gold">Edit</a>
                        <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="d-inline" onsubmit="return confirm('Delete this package?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-3">No packages found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $packages->links() }}</div>
@endsection
