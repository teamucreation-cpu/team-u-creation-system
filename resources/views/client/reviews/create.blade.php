@extends('layouts.app')

@section('title', 'Leave a Review')
@section('page-title', 'Leave a Review')

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
<a href="{{ route('client.bookings.show', $booking) }}" class="btn btn-sm btn-outline-gold mb-3"><i class="fas fa-arrow-left me-1"></i> Back</a>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="stat-card">
            <h5 class="fw-bold mb-1">How was your event?</h5>
            <p class="text-muted mb-4">{{ $booking->event_type }} &middot; {{ $booking->event_date->format('F d, Y') }}</p>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('client.reviews.store', $booking) }}">
                @csrf

                <label class="form-label d-block">Your Rating</label>
                <div class="star-rating mb-4">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                        <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                    @endfor
                </div>

                <div class="mb-3">
                    <label class="form-label">Review Title</label>
                    <input type="text" class="form-control" name="title" maxlength="150" placeholder="Sum up your experience in a few words" value="{{ old('title') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Your Feedback</label>
                    <textarea class="form-control" name="feedback" rows="5" maxlength="2000" placeholder="Tell us about your experience with Team U Creation..." required>{{ old('feedback') }}</textarea>
                </div>

                <button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane me-1"></i> Submit Review</button>
            </form>
        </div>
    </div>
</div>
@endsection
