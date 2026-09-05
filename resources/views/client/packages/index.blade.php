@extends('layouts.app')

@section('title', 'Event Packages')
@section('page-title', 'Event Packages')

@section('content')
@if($packages->isEmpty())
    <div class="stat-card text-center py-5">
        <i class="fas fa-box-open fa-3x mb-3" style="color: var(--tu-gold);"></i>
        <p class="text-muted">No packages available right now. Check back later!</p>
    </div>
@else
    <div class="row g-4">
        @foreach($packages as $package)
        <div class="col-md-6 col-lg-4">
            <div class="stat-card h-100">
                <h5 class="fw-bold">{{ $package->package_name }}</h5>
                <p class="text-muted">{{ $package->description ? Str::limit($package->description, 100) : 'No description.' }}</p>
                <p class="fs-5 fw-bold" style="color: var(--tu-gold);">PHP {{ number_format($package->price, 2) }}</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('client.packages.show', $package) }}" class="btn btn-sm btn-outline-gold">View Details</a>
                    <a href="{{ route('client.bookings.create', ['package' => $package->id]) }}" class="btn btn-sm btn-gold">Book Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection