@extends('layouts.app')

@section('title', 'Ratings Analytics')
@section('page-title', 'Ratings Analytics')

@push('styles')
<style>
    .rating-bar-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .rating-bar-row:last-child { margin-bottom: 0; }
    .rating-bar-row .stars-label {
        flex: 0 0 74px;
        font-size: 0.82rem;
        color: var(--tu-black);
        white-space: nowrap;
    }
    .rating-bar-row .stars-label i { color: var(--tu-gold); font-size: 0.7rem; }
    .rating-bar-track {
        flex: 1 1 auto;
        background: #f0f0f0;
        border-radius: 999px;
        height: 14px;
        overflow: hidden;
    }
    .rating-bar-fill {
        height: 100%;
        background: var(--tu-gold);
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .rating-bar-row .bar-count {
        flex: 0 0 64px;
        text-align: right;
        font-size: 0.82rem;
        color: #666;
    }

    .type-bar-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.9rem;
    }
    .type-bar-row:last-child { margin-bottom: 0; }
    .type-bar-row .type-label {
        flex: 0 0 140px;
        font-size: 0.85rem;
        color: var(--tu-black);
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-bar-track {
        flex: 1 1 auto;
        background: #f0f0f0;
        border-radius: 999px;
        height: 12px;
        overflow: hidden;
    }
    .type-bar-fill {
        height: 100%;
        background: var(--tu-gold);
        border-radius: 999px;
    }
    .type-bar-row .type-meta {
        flex: 0 0 90px;
        text-align: right;
        font-size: 0.78rem;
        color: #666;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Reviews</a>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: rgba(212,160,23,0.12); color: var(--tu-gold);"><i class="fas fa-star"></i></div>
            <div>
                <div class="stat-value">{{ $averageRating }} <span style="font-size: 1rem; color: #999;">/ 5</span></div>
                <div class="stat-label">Overall Average Rating</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: rgba(212,160,23,0.12); color: var(--tu-gold);"><i class="fas fa-comments"></i></div>
            <div>
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total Reviews Received</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: #d4edda; color: #155724;"><i class="fas fa-check-double"></i></div>
            <div>
                <div class="stat-value">{{ $approvalRate !== null ? $approvalRate . '%' : '—' }}</div>
                <div class="stat-label">Approval Rate</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: #fff3cd; color: #856404;"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: #d4edda; color: #155724;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['approved'] }}</div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: #f8d7da; color: #721c24;"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['rejected'] }}</div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="stat-card h-100">
            <h6 class="fw-bold mb-3">Rating Distribution</h6>
            @if($total === 0)
                <p class="text-muted mb-0">No reviews yet.</p>
            @else
                @foreach($distribution as $star => $d)
                    <div class="rating-bar-row">
                        <div class="stars-label">
                            {{ $star }} <i class="fas fa-star"></i>
                        </div>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill" style="width: {{ $d['percent'] }}%;"></div>
                        </div>
                        <div class="bar-count">{{ $d['count'] }} ({{ $d['percent'] }}%)</div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="stat-card h-100">
            <h6 class="fw-bold mb-3">Approved Reviews by Event Type</h6>
            @if($byEventType->isEmpty())
                <p class="text-muted mb-0">No approved reviews yet.</p>
            @else
                @php $maxCount = $byEventType->max('total'); @endphp
                @foreach($byEventType as $row)
                    <div class="type-bar-row">
                        <div class="type-label">{{ $row->event_type }}</div>
                        <div class="type-bar-track">
                            <div class="type-bar-fill" style="width: {{ $maxCount > 0 ? round(($row->total / $maxCount) * 100) : 0 }}%;"></div>
                        </div>
                        <div class="type-meta">{{ $row->total }} &middot; {{ round($row->avg_rating, 1) }} <i class="fas fa-star" style="color: var(--tu-gold);"></i></div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
