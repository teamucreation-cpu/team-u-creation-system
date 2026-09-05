@extends('layouts.app')

@section('title', 'Client Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* Scoped to this page only — breaks the content area out to the brand's
       black background while leaving the sidebar, logo, and header/profile
       area (everything outside the content section) completely untouched. */
    .client-dash-hero {
        position: relative;
        min-height: calc(100vh - 140px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 3rem 1.5rem;
        background: #0A0A0A;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cdh-inner { width: 100%; max-width: 960px; margin: 0 auto; }

    .cdh-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.75rem;
    }

    .cdh-card {
        background: #FFFFFF;
        border: 2px solid rgba(244, 201, 93, 0.35);
        border-radius: 20px;
        padding: 2.75rem 2rem;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .cdh-card:hover, .cdh-card:focus-visible {
        transform: translateY(-6px);
        border-color: #F4C95D;
        box-shadow: 0 22px 46px rgba(244, 201, 93, 0.24);
        outline: none;
    }

    .cdh-icon {
        width: 78px; height: 78px; border-radius: 50%;
        background: rgba(244, 201, 93, 0.14);
        color: #F4C95D;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.1rem;
        margin-bottom: 1.35rem;
        flex: 0 0 auto;
    }

    .cdh-title { color: #000000; font-weight: 700; font-size: 1.35rem; margin-bottom: 0.65rem; }
    .cdh-desc { color: #374151; font-size: 0.93rem; line-height: 1.65; margin-bottom: 1.85rem; flex: 1 1 auto; }

    .cdh-btn {
        display: inline-flex; align-items: center; gap: 0.55rem;
        background: #F4C95D; color: #0A0A0A; border: none;
        font-weight: 700; font-size: 0.95rem;
        padding: 0.85rem 2.1rem; border-radius: 12px;
        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .cdh-card:hover .cdh-btn {
        background: #D4AF37;
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(244, 201, 93, 0.4);
    }

    @media (max-width: 575.98px) {
        .client-dash-hero { padding: 2rem 1rem; margin: -1rem -1rem -1.5rem; min-height: calc(100vh - 118px); }
        .cdh-card { padding: 2.25rem 1.5rem; }
        .cdh-icon { width: 66px; height: 66px; font-size: 1.7rem; margin-bottom: 1.1rem; }
        .cdh-title { font-size: 1.2rem; }
    }
</style>
@endpush

@section('content')
<div class="client-dash-hero">
    <div class="cdh-inner">
        <div class="cdh-grid">
            <a href="{{ route('client.bookings.create') }}" class="cdh-card">
                <div class="cdh-icon"><i class="fas fa-gift"></i></div>
                <h3 class="cdh-title">Book an Event</h3>
                <p class="cdh-desc">Create a new booking for your upcoming celebration, wedding, birthday, year end party, or special occasion.</p>
                <span class="cdh-btn"><i class="fas fa-arrow-right"></i> Start Booking</span>
            </a>

            <a href="{{ route('client.appointments.create') }}" class="cdh-card">
                <div class="cdh-icon"><i class="fas fa-calendar-check"></i></div>
                <h3 class="cdh-title">Schedule an Appointment</h3>
                <p class="cdh-desc">Reserve a consultation with our team to discuss event requirements, planning, and coordination details.</p>
                <span class="cdh-btn"><i class="fas fa-arrow-right"></i> Book Appointment</span>
            </a>
        </div>
    </div>
</div>
@endsection
