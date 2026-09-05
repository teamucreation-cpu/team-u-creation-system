@extends('layouts.app')

@section('title', 'Event Packages')

@section('content')
<h2 class="mb-4">Event Packages</h2>

@if ($packages->isEmpty())
    <p class="text-muted">No packages are available right now. Please check back later.</p>
@else
    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($packages as $package)
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $package->package_name }}</h5>
                        <p class="card-text text-muted">{{ $package->description ? \Illuminate\Support\Str::limit($package->description, 100) : 'No description available.' }}</p>
                        <p class="fw-bold">₱{{ number_format($package->price, 2) }}</p>
                        <a href="{{ route('client.packages.show', $package) }}" class="btn btn-outline-dark">View Details</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
