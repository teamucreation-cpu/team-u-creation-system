@extends('layouts.app')

@section('title', 'Reviews')
@section('page-title', 'Client Reviews')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.reviews.analytics') }}" class="btn btn-outline-gold"><i class="fas fa-chart-simple me-1"></i> View Ratings Analytics</a>
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

<div class="list-toolbar mb-3">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 align-items-center">
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(['Pending', 'Approved', 'Rejected'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        @if($status)
            <div class="col-auto">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        @endif
    </form>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Client</th>
                <th>Event Type</th>
                <th>Title</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td><strong>{{ $review->client->user->name }}</strong></td>
                <td>{{ $review->booking->event_type }}</td>
                <td>{{ $review->title }}</td>
                <td class="text-nowrap">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color: {{ $i <= $review->rating ? 'var(--tu-gold)' : '#e0e0e0' }};"></i>
                    @endfor
                </td>
                <td style="max-width: 260px;">{{ \Illuminate\Support\Str::limit($review->feedback, 80) }}</td>
                <td>{{ \App\Support\PhilippineTime::date($review->created_at) }}</td>
                <td><span class="badge badge-{{ strtolower($review->status) }}">{{ $review->status }}</span></td>
                <td class="text-nowrap">
                    @if($review->status !== 'Approved')
                        <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-gold" title="Approve"><i class="fas fa-check"></i></button>
                        </form>
                    @endif
                    @if($review->status !== 'Rejected')
                        <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Reject"><i class="fas fa-times"></i></button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="d-inline" onsubmit="return confirm('Delete this review permanently?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-muted text-center py-3">No reviews yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
