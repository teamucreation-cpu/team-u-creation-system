@extends('layouts.app')

@section('title', $package->package_name)
@section('page-title', 'Package Details')

@section('content')
<a href="{{ route('client.packages.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="stat-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h4 class="fw-bold">{{ $package->package_name }}</h4>
            <p class="fs-4 fw-bold" style="color: var(--tu-gold);">PHP {{ number_format($package->price, 2) }}</p>
        </div>
        <a href="{{ route('client.bookings.create', ['package' => $package->id]) }}" class="btn btn-gold"><i class="fas fa-calendar-plus me-1"></i> Book This Package</a>
    </div>

    <hr>
    <h6 class="fw-bold">Description</h6>
    <p>{{ $package->description ?: 'No description provided.' }}</p>

    <h6 class="fw-bold mt-4">Inclusions</h6>
    @if($package->inclusions)
        <ul class="list-unstyled">
            @foreach(explode("\n", $package->inclusions) as $item)
                @if(trim($item))
                    <li><i class="fas fa-check-circle me-2" style="color: var(--tu-gold);"></i>{{ trim($item) }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <p class="text-muted">No inclusions listed.</p>
    @endif
</div>
@endsection