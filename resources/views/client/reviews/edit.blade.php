@extends('layouts.app')

@section('title', 'Edit Review')
@section('page-title', 'Edit Review')

@push('styles')
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.25rem;
        font-size: 2.2rem;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        color: #e0e0e0;
        cursor: pointer;
        transition: color 0.15s;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: var(--tu-gold);
    }
</style>
@endpush

@section('content')
<a href="{{ route('client.reviews.index') }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back to My Reviews</a>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="stat-card">
            <h5 class="fw-bold mb-1">Edit Your Review</h5>
            <p class="text-muted mb-4">{{ $review->booking->event_type }} &middot; {{ $review->booking->event_date->format('F d, Y') }}</p>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('client.reviews.update', $review) }}">
                @csrf @method('PUT')

                <label class="form-label d-block">Your Rating</label>
                <div class="star-rating mb-4">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
                        <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                    @endfor
                </div>

                <div class="mb-3">
                    <label class="form-label">Review Title</label>
                    <input type="text" class="form-control" name="title" maxlength="150" value="{{ old('title', $review->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Your Feedback</label>
                    <textarea class="form-control" name="feedback" rows="5" maxlength="2000" required>{{ old('feedback', $review->feedback) }}</textarea>
                </div>

                <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Update Review</button>
            </form>
        </div>
    </div>
</div>
@endsection
