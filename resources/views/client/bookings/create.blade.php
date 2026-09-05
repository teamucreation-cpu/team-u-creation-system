@extends('layouts.app')

@section('title', isset($booking) ? 'Edit Booking' : 'Book an Event')
@section('page-title', isset($booking) ? 'Edit Booking' : 'Book an Event')

@push('styles')
<style>
    .book-event-lux {
        /* Matches the Request Appointment page's background exactly — that
           page has no background override of its own, so it renders on the
           site-wide navy (--tu-navy, defined on :root in layouts/app.blade.php).
           This page previously used a near-black (#0A0A0A) instead, which
           read as a different, inconsistent tone next to Appointment. */
        --be-black: var(--tu-navy);
        --be-charcoal: #121212;
        --be-gold: #F4C95D;
        --be-gold-dim: rgba(244, 201, 93, 0.25);
        --be-white: #ffffff;
        --be-muted: #D1D5DB;
        --be-green: #4cd97b;
        --be-amber: #ffc107;
        --be-red: #ff6b6b;
        position: relative;
        min-height: calc(100vh - 140px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 2.5rem 1.5rem 3rem;
        background: var(--be-black);
        overflow: hidden;
    }
    .book-event-lux::before {
        content: '';
        position: absolute;
        top: -140px;
        left: 50%;
        transform: translateX(-50%);
        width: 640px;
        height: 380px;
        background: radial-gradient(ellipse at center, rgba(244, 201, 93, 0.14) 0%, transparent 70%);
        pointer-events: none;
    }

    .be-wrap { position: relative; z-index: 1; width: 100%; max-width: 720px; margin: 0 auto; }

    @keyframes beFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    .be-fade { opacity: 0; animation: beFadeUp 0.55s ease forwards; }

    .be-heading-block { text-align: center; margin-bottom: 2rem; }
    .be-heading-underline { width: 56px; height: 3px; background: var(--be-gold); border-radius: 2px; margin: 0 auto 0.9rem; }
    .be-heading-block p { color: var(--be-muted); font-size: 0.92rem; margin: 0; }

    /* ---- Inner form card ----
       Deliberately light/white, matching the Request Appointment page's
       .stat-card — everything below this point that used to read light
       text on a dark glass card (var(--be-white)/var(--be-muted)) is
       repainted with literal dark-on-light values instead. Only the
       elements OUTSIDE this card (the page's own dark background, and the
       intro paragraph above the card) keep using var(--be-white)/
       var(--be-muted) as before, since that surface is still dark. */
    .be-card {
        background: #FFFFFF;
        border: 1px solid var(--be-gold);
        border-radius: 20px;
        padding: 2rem;
        /* Now fully opaque, so this has nothing left to blur — kept anyway
           (harmless either way) so the "why popups are portaled to <body>"
           comments elsewhere in this file, which cite it, stay accurate. */
        backdrop-filter: blur(14px) saturate(140%);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        box-shadow: 0 0 40px rgba(244, 201, 93, 0.08), 0 20px 45px rgba(0, 0, 0, 0.5);
    }

    .book-event-lux .form-label {
        color: #1F2937;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .be-field { margin-bottom: 1.5rem; position: relative; }
    .be-input-group { position: relative; }
    .be-input-group > i.be-field-icon {
        position: absolute; left: 0.95rem; top: 50%; transform: translateY(-50%);
        color: var(--be-gold); font-size: 0.9rem; pointer-events: none; z-index: 2;
    }
    .book-event-lux .form-control,
    .book-event-lux .form-select {
        background: #F9FAFB;
        border: 1px solid rgba(244, 201, 93, 0.35);
        color: #1F2937;
        border-radius: 10px;
        padding: 0.7rem 0.95rem 0.7rem 2.6rem;
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .book-event-lux .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23F4C95D' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        background-size: 14px 14px;
    }
    .book-event-lux .form-control::placeholder { color: #9CA3AF; }
    .book-event-lux .form-control:focus,
    .book-event-lux .form-select:focus {
        background: #FFFFFF;
        border-color: var(--be-gold);
        color: #1F2937;
        box-shadow: 0 0 0 0.2rem rgba(244, 201, 93, 0.22);
    }
    .book-event-lux option { background: #FFFFFF; color: #1F2937; }

    /* ---- Smart Booking Date Selector ---- */
    .be-date-trigger {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    /* Named .be-placeholder rather than the generic "placeholder" — that
       name collides with Bootstrap 5's own reserved .placeholder utility
       class (a loading-skeleton effect whose background-color: currentcolor
       silently painted this text's own background the same color as the
       text itself, hiding it completely). */
    .be-date-trigger .be-placeholder { color: #9CA3AF; }
    .be-date-trigger .chosen { color: #1F2937; font-weight: 600; }
    .be-date-trigger .be-chevron { color: var(--be-gold); transition: transform 0.2s ease; }
    .be-date-trigger.is-open .be-chevron { transform: rotate(180deg); }

    /* The popup (and its backdrop) is moved to a direct child of <body> at
       runtime (see the script below) and styled with literal colors rather
       than var(--be-*) — intentional, for two reasons:
       1. .be-card uses backdrop-filter, which (per spec) makes it the
          containing block for any position:fixed *descendant*, so a fixed
          popup left nested inside it is still bounded — and clipped — by
          .book-event-lux's overflow:hidden exactly like an absolutely
          positioned one would be. Relocating it to <body> removes every
          ancestor that could constrain it, which is the only fully robust
          fix for that.
       2. Once relocated it sits outside .book-event-lux entirely, so it no
          longer inherits that scope's custom properties — and it's meant to
          look different anyway: a light, readable panel over the page's
          dark form, per the "white/light calendar" requirement.
       It's centered horizontally and pinned near the top of the viewport —
       fixed there regardless of the trigger's position or page scroll —
       rather than anchored to the field, which is what made it necessary to
       fit the whole thing inside a shrinking pop before. */
    .be-calendar-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99998;
        background: rgba(10, 10, 10, 0.55);
        opacity: 0;
        transition: opacity 0.18s ease;
    }
    .be-calendar-backdrop.is-mounted { display: block; }
    .be-calendar-backdrop.is-shown { opacity: 1; }

    .be-calendar-pop {
        display: none;
        position: fixed;
        top: max(1rem, 7vh);
        left: 50%;
        z-index: 99999;
        width: min(300px, 90vw);
        max-height: min(400px, calc(100vh - 3rem));
        overflow-y: auto;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-top: 3px solid #F4C95D;
        border-radius: 14px;
        padding: 0.85rem;
        box-shadow: 0 24px 48px rgba(17, 24, 39, 0.28), 0 0 0 1px rgba(244, 201, 93, 0.08);
        opacity: 0;
        transform: translateX(-50%) translateY(-10px) scale(0.96);
        transition: opacity 0.16s ease, transform 0.16s ease;
    }
    .be-calendar-pop.is-mounted { display: block; }
    .be-calendar-pop.is-shown { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }

    .be-cal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.65rem; }
    /* The month/year title is itself the editor's trigger — clicking it
       swaps the day grid below for an inline month+year picker (see
       .be-cal-picker), rather than opening a second stacked popup on top
       of this already-compact 300px one. */
    .be-cal-title-btn {
        display: flex; align-items: center; gap: 0.3rem;
        background: none; border: none; cursor: pointer; padding: 0.1rem 0.3rem; border-radius: 6px;
        color: #1F2937; font-weight: 700; font-size: 0.85rem; font-family: inherit;
        transition: background 0.2s ease;
    }
    .be-cal-title-btn:hover, .be-cal-title-btn:focus-visible { background: #F9FAFB; outline: none; }
    .be-cal-title-caret { font-size: 0.55rem; color: #9CA3AF; transition: transform 0.2s ease, color 0.2s ease; }
    .be-cal-title-btn:hover .be-cal-title-caret, .be-cal-title-btn.is-open .be-cal-title-caret { color: #D4AF37; }
    .be-cal-title-btn.is-open .be-cal-title-caret { transform: rotate(180deg); }
    .be-cal-nav-btn {
        width: 25px; height: 25px; border-radius: 7px;
        background: #F9FAFB; border: 1px solid #E5E7EB; color: #1F2937;
        display: flex; align-items: center; justify-content: center; font-size: 0.68rem;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }
    .be-cal-nav-btn:hover { background: #F4C95D; border-color: #F4C95D; color: #1F2937; transform: translateY(-1px); }

    /* Inline month+year picker — replaces the weekday header + day grid in
       place (same popup, same size) while open, instead of stacking a
       second popup on top of this already-compact one. */
    .be-cal-picker { display: none; }
    .be-cal-picker.is-active { display: block; }
    .be-cal-picker-row { display: flex; gap: 0.4rem; margin-bottom: 0.6rem; }
    .be-cal-picker-select {
        flex: 1 1 0; min-width: 0;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        border: 1px solid #E5E7EB; border-radius: 8px; background-color: #F9FAFB;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231F2937' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat; background-position: right 0.45rem center; background-size: 9px 9px;
        color: #1F2937; font-weight: 600; font-size: 0.72rem;
        padding: 0.4rem 1.4rem 0.4rem 0.55rem;
        cursor: pointer;
    }
    .be-cal-picker-select:hover, .be-cal-picker-select:focus { border-color: #F4C95D; outline: none; }
    .be-cal-picker-go {
        display: block; width: 100%; background: #F4C95D; color: #1F2937; border: none;
        font-weight: 700; font-size: 0.72rem; border-radius: 8px; padding: 0.5rem;
        cursor: pointer; transition: background 0.2s ease;
    }
    .be-cal-picker-go:hover { background: #D4AF37; }

    .be-cal-legend { display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.6rem; font-size: 0.62rem; color: #6B7280; }
    .be-cal-legend span { display: inline-flex; align-items: center; gap: 0.28rem; }
    .be-legend-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

    .be-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.2rem; }
    .be-cal-weekday { text-align: center; color: #6B7280; font-size: 0.58rem; font-weight: 700; text-transform: uppercase; padding-bottom: 0.3rem; }

    .be-day {
        position: relative;
        aspect-ratio: 1 / 1;
        border-radius: 7px;
        border: 1px solid transparent;
        background: #F9FAFB;
        color: #1F2937;
        font-size: 0.68rem;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    }
    .be-day--empty { visibility: hidden; }
    .be-day--past { color: #D1D5DB; background: transparent; cursor: not-allowed; }
    .be-day--available:hover { border-color: #F4C95D; transform: translateY(-1px); background: #FFF8E7; }
    .be-day--pending { background: rgba(244, 201, 93, 0.12); }
    .be-day--pending:hover { border-color: #F4C95D; transform: translateY(-1px); background: #FFF8E7; }
    .be-day--booked { background: rgba(239, 68, 68, 0.08); color: #9CA3AF; cursor: not-allowed; }
    /* Available per the booking-status data, but earlier than the currently
       typed event type's minimum-advance-notice rule allows. Recomputed live
       as the client types (see the eventTypeInput listener below) — unlike
       --booked/--past, it deliberately stays clickable so selecting one still
       surfaces the "booking cannot proceed" rule message, same as --pending. */
    .be-day--too-soon { background: rgba(156, 163, 175, 0.12); color: #9CA3AF; }
    .be-day--too-soon:hover { border-color: #9CA3AF; transform: translateY(-1px); background: rgba(156, 163, 175, 0.22); }
    .be-day--selected { border-color: #F4C95D !important; background: #F4C95D !important; color: #1F2937 !important; font-weight: 800; box-shadow: 0 3px 10px rgba(244, 201, 93, 0.45); }
    .be-day .be-dot { width: 5px; height: 5px; border-radius: 50%; }
    .be-day--available .be-dot { background: #22C55E; }
    .be-day--pending .be-dot { background: #F4C95D; }
    .be-day--booked .be-dot { background: #EF4444; }
    .be-day--too-soon .be-dot { background: #9CA3AF; }
    .be-day--selected .be-dot { background: #1F2937; }

    /* ---- Date Availability panel ----
       Also part of the light card now — was a barely-visible translucent
       white wash (rgba(255,255,255,0.02)) meant to sit on the dark glass
       card behind it; needs to be an actually-visible light panel of its
       own now that the card itself is opaque white. Status colors are
       darkened from the page's original light-on-dark accents
       (var(--be-green)/--be-amber/--be-red) to the same darker shades this
       app already uses for status text on light backgrounds elsewhere. */
    .be-avail-card {
        background: #F9FAFB;
        border: 1px solid var(--be-gold-dim);
        border-radius: 14px;
        padding: 1.2rem 1.35rem;
        margin-bottom: 1.75rem;
        transition: border-color 0.25s ease, background 0.25s ease;
    }
    .be-avail-title {
        color: #1F2937;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .be-avail-title i { color: var(--be-gold); }
    .be-avail-body { display: flex; align-items: flex-start; gap: 0.9rem; }
    .be-avail-icon {
        width: 40px; height: 40px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; flex: 0 0 auto;
    }
    .be-avail-date { color: #1F2937; font-weight: 700; font-size: 0.98rem; margin-bottom: 0.2rem; }
    .be-avail-status { font-weight: 700; font-size: 0.85rem; margin-bottom: 0.15rem; }
    .be-avail-meta { color: #6B7280; font-size: 0.82rem; }
    .be-avail-msg { color: #6B7280; font-size: 0.85rem; margin: 0; }

    .be-avail-card.state-available { border-color: rgba(21, 128, 61, 0.35); }
    .be-avail-card.state-pending { border-color: rgba(146, 97, 10, 0.35); }
    .be-avail-card.state-booked { border-color: rgba(185, 28, 28, 0.35); }
    /* Distinct from state-booked (someone else already has the date) even
       though it reuses the same red accent — this one means the date itself
       is free, but the client's own event type requires more advance notice
       than this date gives it. */
    .be-avail-card.state-rule-blocked { border-color: rgba(185, 28, 28, 0.35); }
    .state-available .be-avail-icon { background: rgba(21, 128, 61, 0.12); color: #15803D; }
    .state-pending .be-avail-icon { background: rgba(146, 97, 10, 0.12); color: #92610A; }
    .state-booked .be-avail-icon { background: rgba(185, 28, 28, 0.12); color: #B91C1C; }
    .state-rule-blocked .be-avail-icon { background: rgba(185, 28, 28, 0.12); color: #B91C1C; }
    .state-available .be-avail-status { color: #15803D; }
    .state-pending .be-avail-status { color: #92610A; }
    .state-booked .be-avail-status { color: #B91C1C; }
    .state-rule-blocked .be-avail-status { color: #B91C1C; }
    .be-rule-note { display: block; margin-top: 0.35rem; color: #6B7280; font-size: 0.78rem; font-style: italic; }

    .be-btn-gold {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--be-gold); color: #000; border: none;
        font-weight: 700; font-size: 0.95rem; border-radius: 10px;
        padding: 0.75rem 1.6rem; transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, opacity 0.2s ease;
    }
    .be-btn-gold:hover:not(:disabled) { background: #ffd873; color: #000; transform: translateY(-2px); box-shadow: 0 10px 26px rgba(244, 201, 93, 0.4); }
    .be-btn-gold:disabled { opacity: 0.45; cursor: not-allowed; }

    @media (max-width: 575.98px) {
        .book-event-lux { padding: 2rem 1rem 2.5rem; margin: -1rem -1rem -1.5rem; }
        .be-card { padding: 1.5rem; border-radius: 18px; }
    }

    /* ---- Pending Reservation notice ----
       A dedicated popup, separate from the compact calendar dropdown and
       the inline "Date Availability" panel — it must sit in front of
       everything else on the page, including the still-open calendar
       popup, so its z-index is a step above .be-calendar-pop's (99999).
       Same portal pattern as every other popup in this app: relocated to a
       direct child of <body> at runtime so no ancestor's overflow or
       backdrop-filter can clip or hide it, and every color below is a
       literal value rather than var(--be-*) — those custom properties are
       scoped to .book-event-lux and resolve to nothing once detached. */
    .be-pending-backdrop {
        display: none; position: fixed; inset: 0; z-index: 100000;
        background: #000000; opacity: 0; transition: opacity 0.2s ease;
    }
    .be-pending-backdrop.is-mounted { display: block; }
    .be-pending-backdrop.is-shown { opacity: 0.6; }

    .be-pending-modal {
        display: none; position: fixed; top: max(1rem, 12vh); left: 50%; z-index: 100001;
        width: min(360px, 92vw);
        background-color: #FFFFFF; border: 1px solid #E5E7EB; border-top: 4px solid #F4C95D;
        border-radius: 16px; padding: 1.75rem 1.5rem 1.5rem;
        box-shadow: 0 24px 48px rgba(17, 24, 39, 0.32);
        opacity: 0; transform: translateX(-50%) translateY(-14px) scale(0.95);
        transition: opacity 0.2s ease, transform 0.2s ease;
        text-align: center;
    }
    .be-pending-modal.is-mounted { display: block; }
    .be-pending-modal.is-shown { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }

    .be-pending-close {
        position: absolute; top: 0.75rem; right: 0.75rem;
        width: 28px; height: 28px; border-radius: 50%; border: none;
        background-color: #F3F4F6; color: #000000; font-size: 0.8rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .be-pending-close:hover { background-color: #E5E7EB; }

    .be-pending-icon {
        width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 1rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        background-color: #FEF3C7; color: #B45309;
    }
    .be-pending-title { font-weight: 800; font-size: 1.15rem; color: #000000; margin-bottom: 1rem; }
    .be-pending-row { display: flex; justify-content: space-between; gap: 0.75rem; font-size: 0.85rem; padding: 0.5rem 0; border-top: 1px solid #E5E7EB; text-align: left; }
    .be-pending-row:first-of-type { border-top: none; }
    .be-pending-row .label { color: #6B7280; font-weight: 600; }
    .be-pending-row .value { color: #000000; font-weight: 700; text-align: right; }
    .be-pending-row .value.status-pending { color: #B45309; }
    .be-pending-msg { color: #374151; font-size: 0.85rem; line-height: 1.5; margin: 1.1rem 0 1.35rem; text-align: left; }
    .be-pending-btn-ok {
        display: block; width: 100%; background-color: #F4C95D; color: #000000; border: none;
        font-weight: 700; font-size: 0.9rem; border-radius: 10px; padding: 0.7rem;
        cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .be-pending-btn-ok:hover { background-color: #D4AF37; transform: translateY(-2px); }

    @media (max-width: 575.98px) {
        .be-pending-modal { padding: 1.4rem 1.15rem 1.15rem; }
        .be-pending-icon { width: 48px; height: 48px; font-size: 1.2rem; }
        .be-pending-title { font-size: 1.05rem; }
    }
</style>
@endpush

@section('content')
<div class="book-event-lux">
    <div class="be-wrap">
        <div class="be-heading-block be-fade" style="animation-delay:.03s;">
            <div class="be-heading-underline"></div>
            <p>
                @if(isset($booking))
                    Update your event details — it will need to be reviewed by the admin again.
                @else
                    Tell us about your event and pick a date — availability updates live.
                @endif
            </p>
        </div>

        <div class="be-card be-fade" style="animation-delay:.14s;">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ isset($booking) ? route('client.bookings.update', $booking) : route('client.bookings.store') }}" id="bookingForm">
                @csrf
                @if(isset($booking)) @method('PUT') @endif

                <div class="be-field">
                    <label class="form-label">Event Package <span class="text-muted normal-case" style="text-transform:none; font-weight:400;">(optional)</span></label>
                    <div class="be-input-group">
                        <i class="fas fa-gift be-field-icon"></i>
                        <select class="form-select" name="package_id">
                            <option value="">No package - custom event</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id', $booking->package_id ?? ($selectedPackage->id ?? '')) == $package->id ? 'selected' : '' }}>
                                    {{ $package->package_name }} - PHP {{ number_format($package->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="be-field">
                    <label class="form-label">Event Type</label>
                    <div class="be-input-group">
                        <i class="fas fa-champagne-glasses be-field-icon"></i>
                        <input type="text" class="form-control" name="event_type" id="eventTypeInput" value="{{ old('event_type', $booking->event_type ?? '') }}" placeholder="e.g. Wedding, Birthday, Corporate" required>
                    </div>
                </div>

                <div class="be-field">
                    <label class="form-label">Venue</label>
                    <div class="be-input-group">
                        <i class="fas fa-map-marker-alt be-field-icon"></i>
                        <input type="text" class="form-control" name="venue" value="{{ old('venue', $booking->venue ?? '') }}" placeholder="Event venue location" required>
                    </div>
                </div>

                <div class="be-field" id="dateFieldWrap">
                    <label class="form-label">Event Date</label>
                    <div class="be-input-group">
                        <i class="fas fa-calendar-days be-field-icon"></i>
                        <div class="form-control be-date-trigger" id="dateTrigger" tabindex="0">
                            <span id="dateTriggerText" class="be-placeholder">Select a date</span>
                            <i class="fas fa-chevron-down be-chevron"></i>
                        </div>

                        <div class="be-calendar-backdrop" id="calendarBackdrop"></div>
                        <div class="be-calendar-pop" id="calendarPop">
                            <div class="be-cal-header">
                                <button type="button" class="be-cal-nav-btn" id="calPrev" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
                                <button type="button" class="be-cal-title-btn" id="calTitleBtn" aria-haspopup="true" aria-expanded="false">
                                    <span id="calTitle"></span>
                                    <i class="fas fa-chevron-down be-cal-title-caret"></i>
                                </button>
                                <button type="button" class="be-cal-nav-btn" id="calNext" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
                            </div>

                            {{-- Grid view — the normal day-picker. Toggled off (in favor of
                                 .be-cal-picker below) while the month/year picker is open. --}}
                            <div id="calGridView">
                                <div class="be-cal-legend">
                                    <span><span class="be-legend-dot" style="background: #22C55E;"></span> Available</span>
                                    <span><span class="be-legend-dot" style="background: #F4C95D;"></span> Pending Reservation</span>
                                    <span><span class="be-legend-dot" style="background: #EF4444;"></span> Booked / Approved</span>
                                    <span><span class="be-legend-dot" style="background: #9CA3AF;"></span> Too Soon for This Event Type</span>
                                </div>
                                <div class="be-cal-grid" id="calWeekdays">
                                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $wd)
                                        <div class="be-cal-weekday">{{ $wd }}</div>
                                    @endforeach
                                </div>
                                <div class="be-cal-grid" id="calDays"></div>
                            </div>

                            {{-- Picker view — shown in place of the grid above when the
                                 month/year title is clicked. --}}
                            <div class="be-cal-picker" id="calPicker">
                                <div class="be-cal-picker-row">
                                    <select class="be-cal-picker-select" id="calPickerMonth" aria-label="Month">
                                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $name)
                                            <option value="{{ $i + 1 }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="be-cal-picker-select" id="calPickerYear" aria-label="Year"></select>
                                </div>
                                <button type="button" class="be-cal-picker-go" id="calPickerGo">Go</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="event_date" id="eventDateInput" value="{{ old('event_date', isset($booking) ? $booking->event_date->format('Y-m-d') : '') }}">
                </div>

                <div class="be-avail-card" id="availCard">
                    <div class="be-avail-title"><i class="fas fa-circle-info"></i> Date Availability</div>
                    <div class="be-avail-body">
                        <div class="be-avail-icon" id="availIcon" style="background: rgba(244,201,93,0.1); color: var(--be-gold);"><i class="fas fa-calendar"></i></div>
                        <div id="availTextWrap">
                            <p class="be-avail-msg" id="availMsg">Select a date above to check its availability.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="be-btn-gold" id="submitBtn">
                        <i class="fas {{ isset($booking) ? 'fa-check' : 'fa-paper-plane' }}"></i>
                        {{ isset($booking) ? 'Save Changes' : 'Submit Booking' }}
                    </button>
                    @if(isset($booking))
                        <a href="{{ route('client.bookings.show', $booking) }}" class="btn btn-outline-gold"><i class="fas fa-arrow-left me-1"></i> Back</a>
                    @endif
                </div>
            </form>

            @if(isset($booking))
                <form method="POST" action="{{ route('client.bookings.destroy', $booking) }}" class="mt-3 pt-3" style="border-top: 1px solid rgba(244, 201, 93, 0.18);"
                      onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Cancel This Booking</button>
                </form>
            @endif
        </div>
    </div>

    <div class="be-pending-backdrop" id="bePendingBackdrop"></div>
    <div class="be-pending-modal" id="bePendingModal" role="alertdialog" aria-modal="true" aria-labelledby="bePendingTitle">
        <button type="button" class="be-pending-close" id="bePendingClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
        <div class="be-pending-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="be-pending-title" id="bePendingTitle">Pending Reservation</div>
        <div class="be-pending-row"><span class="label">Selected Date</span><span class="value" id="bePendingDate"></span></div>
        <div class="be-pending-row"><span class="label">Status</span><span class="value status-pending">Pending Approval</span></div>
        <p class="be-pending-msg">This date currently has a pending reservation and may become unavailable if approved. You may proceed at your own risk or choose another available date.</p>
        <button type="button" class="be-pending-btn-ok" id="bePendingOk">OK</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var availabilityUrl = @json(route('client.bookings.availability'));
    // Editing an existing booking: exclude its own id from conflict checks so
    // its current date reads as available instead of "booked by itself".
    var excludeBookingId = @json(isset($booking) ? $booking->id : null);
    var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    // Minimum-advance-notice booking rules, per event type — sourced from
    // Booking::BOOKING_RULES (see app/Models/Booking.php) so the client-side
    // preview here can never drift out of sync with what the server actually
    // enforces on submit.
    var BOOKING_RULES = @json(\App\Models\Booking::bookingRulesForJs());
    var BOOKING_RULE_DEFAULT = @json(\App\Models\Booking::BOOKING_RULE_DEFAULT);

    function ruleFor(eventType) {
        var needle = (eventType || '').toLowerCase().trim();
        if (needle) {
            for (var i = 0; i < BOOKING_RULES.length; i++) {
                if (needle.indexOf(BOOKING_RULES[i].keyword) !== -1) { return BOOKING_RULES[i]; }
            }
        }
        return BOOKING_RULE_DEFAULT;
    }

    function ruleNoticeLabel(rule) {
        var unit = rule.unit + (rule.amount === 1 ? '' : 's');
        return rule.amount + ' ' + unit;
    }

    function ruleSentence(rule) {
        return rule.label + ' events must be booked at least ' + ruleNoticeLabel(rule) + ' before the event date.';
    }

    // The earliest event_date ('Y-m-d') the given rule allows, counting from
    // today — mirrors Booking::earliestBookableDate() on the server.
    function earliestDateStr(rule) {
        var d = new Date(todayStr + 'T00:00:00');
        if (rule.unit === 'month') {
            d.setMonth(d.getMonth() + rule.amount);
        } else {
            d.setDate(d.getDate() + rule.amount);
        }
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    var state = {
        month: {{ $month }},
        year: {{ $year }},
        availability: @json($availability),
        cache: {},
    };
    state.cache[state.year + '-' + state.month] = state.availability;

    var trigger = document.getElementById('dateTrigger');
    var triggerText = document.getElementById('dateTriggerText');
    var pop = document.getElementById('calendarPop');
    var backdrop = document.getElementById('calendarBackdrop');
    var dateFieldWrap = document.getElementById('dateFieldWrap');

    // Move the popup (and its backdrop) to a direct child of <body>. It
    // otherwise sits inside .be-card, whose backdrop-filter makes it the
    // containing block for any position:fixed descendant — so a fixed popup
    // left in place would still be bounded (and clipped) by
    // .book-event-lux's overflow:hidden. This relocation is the only fully
    // robust way to guarantee it can never be clipped by any container,
    // card, or scroll area on the page.
    document.body.appendChild(backdrop);
    document.body.appendChild(pop);

    var pendingBackdrop = document.getElementById('bePendingBackdrop');
    var pendingModal = document.getElementById('bePendingModal');
    var pendingClose = document.getElementById('bePendingClose');
    var pendingOk = document.getElementById('bePendingOk');
    var pendingDateEl = document.getElementById('bePendingDate');
    document.body.appendChild(pendingBackdrop);
    document.body.appendChild(pendingModal);

    var calTitle = document.getElementById('calTitle');
    var calDays = document.getElementById('calDays');
    var calPrev = document.getElementById('calPrev');
    var calNext = document.getElementById('calNext');
    var calTitleBtn = document.getElementById('calTitleBtn');
    var calGridView = document.getElementById('calGridView');
    var calPicker = document.getElementById('calPicker');
    var calPickerMonth = document.getElementById('calPickerMonth');
    var calPickerYear = document.getElementById('calPickerYear');
    var calPickerGo = document.getElementById('calPickerGo');
    var hiddenInput = document.getElementById('eventDateInput');
    var availCard = document.getElementById('availCard');
    var availIcon = document.getElementById('availIcon');
    var availMsg = document.getElementById('availMsg');
    var submitBtn = document.getElementById('submitBtn');
    var eventTypeInput = document.getElementById('eventTypeInput');

    var todayStr = new Date().toISOString().slice(0, 10);

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function formatLong(dateStr) {
        var parts = dateStr.split('-').map(Number);
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        return monthNames[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    function setAvailState(kind, iconClass, title, body) {
        availCard.className = 'be-avail-card state-' + kind;
        availIcon.className = 'be-avail-icon';
        availIcon.innerHTML = '<i class="fas ' + iconClass + '"></i>';
        var html = '';
        if (title) { html += '<div class="be-avail-status">' + title + '</div>'; }
        html += '<p class="be-avail-msg mb-0">' + body + '</p>';
        document.getElementById('availTextWrap').innerHTML = html;
    }

    // ---- Pending Reservation notice ----
    var pendingCloseTimer = null;
    var pendingDateStr = null; // the date OK should actually select, if clicked
    function isPendingModalOpen() { return pendingModal.classList.contains('is-mounted'); }

    function openPendingModal(dateStr) {
        pendingDateStr = dateStr;
        pendingDateEl.textContent = formatLong(dateStr);
        if (pendingCloseTimer) { clearTimeout(pendingCloseTimer); pendingCloseTimer = null; }
        pendingModal.classList.add('is-mounted');
        pendingBackdrop.classList.add('is-mounted');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (isPendingModalOpen()) { pendingModal.classList.add('is-shown'); pendingBackdrop.classList.add('is-shown'); }
            });
        });
    }

    function closePendingModal() {
        if (!isPendingModalOpen()) { return; }
        pendingModal.classList.remove('is-shown');
        pendingBackdrop.classList.remove('is-shown');
        pendingCloseTimer = setTimeout(function () {
            pendingModal.classList.remove('is-mounted');
            pendingBackdrop.classList.remove('is-mounted');
        }, 200);
    }

    // Only reached via the pending modal's OK button — the client
    // explicitly chose to proceed despite the pending conflict, so this
    // actually selects the date (server-side validation allows submitting
    // against a Pending — but not an Approved — conflicting booking; see
    // BookingController::store()).
    function confirmPendingSelection(dateStr) {
        var dateLabel = formatLong(dateStr);
        hiddenInput.value = dateStr;
        triggerText.textContent = dateLabel;
        triggerText.className = 'chosen';
        submitBtn.disabled = false;
        setAvailState('pending', 'fa-hourglass-half',
            dateLabel + ' — Proceeding on a Pending Date',
            'You chose to proceed with this date despite its pending reservation. It may still become unavailable if that request is approved first.');
        closePop();
    }

    pendingClose.addEventListener('click', function () { closePendingModal(); pendingDateStr = null; });
    pendingBackdrop.addEventListener('click', function () { closePendingModal(); pendingDateStr = null; });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isPendingModalOpen()) { closePendingModal(); pendingDateStr = null; }
    });
    pendingOk.addEventListener('click', function () {
        var dateStr = pendingDateStr;
        closePendingModal();
        pendingDateStr = null;
        if (dateStr) { confirmPendingSelection(dateStr); }
    });

    function selectDate(dateStr, info) {
        var dateLabel = formatLong(dateStr);
        var rule = ruleFor(eventTypeInput.value);
        var ruleNote = '<span class="be-rule-note">' + ruleSentence(rule) + '</span>';

        if (info && info.status === 'booked') {
            hiddenInput.value = '';
            triggerText.textContent = 'Select a date';
            triggerText.className = 'be-placeholder';
            submitBtn.disabled = true;
            setAvailState('booked', 'fa-circle-xmark',
                dateLabel + ' — Booked',
                'This date is already booked for a ' + info.event_type + ' event. Please select another date.' + ruleNote);
            return;
        }

        if (info && info.status === 'pending') {
            hiddenInput.value = '';
            triggerText.textContent = 'Select a date';
            triggerText.className = 'be-placeholder';
            submitBtn.disabled = true;
            setAvailState('pending', 'fa-hourglass-half',
                dateLabel + ' — Pending Approval' + (info.event_type ? ' (' + info.event_type + ')' : ''),
                'This date currently has a pending reservation. Please select another date, or use the popup to proceed at your own risk.' + ruleNote);
            openPendingModal(dateStr);
            return;
        }

        // The date has no conflicting booking — but it may still be too soon
        // for the currently typed event type's minimum-advance-notice rule
        // (e.g. a Wedding needs 3 months out, a Birthday only needs 3 days).
        var earliest = earliestDateStr(rule);
        if (dateStr < earliest) {
            hiddenInput.value = '';
            triggerText.textContent = 'Select a date';
            triggerText.className = 'be-placeholder';
            submitBtn.disabled = true;
            setAvailState('rule-blocked', 'fa-triangle-exclamation',
                dateLabel + ' — Booking Cannot Proceed',
                'Booking cannot proceed for this date. ' + ruleSentence(rule)
                + ' The earliest eligible date for a ' + rule.label + ' event is ' + formatLong(earliest) + '.');
            return;
        }

        // Available and eligible
        hiddenInput.value = dateStr;
        triggerText.textContent = dateLabel;
        triggerText.className = 'chosen';
        submitBtn.disabled = false;
        setAvailState('available', 'fa-circle-check',
            dateLabel + ' — Available',
            'This date is available for booking.' + ruleNote);
        closePop();
    }

    function render() {
        calTitle.textContent = monthNames[state.month - 1] + ' ' + state.year;
        calDays.innerHTML = '';

        var first = new Date(state.year, state.month - 1, 1);
        var startDow = first.getDay();
        var daysInMonth = new Date(state.year, state.month, 0).getDate();

        // Recomputed on every render — the field is free text, so the rule
        // (and therefore which dates count as "too soon") can change with
        // every keystroke in the Event Type input.
        var rule = ruleFor(eventTypeInput.value);
        var earliest = earliestDateStr(rule);

        for (var i = 0; i < startDow; i++) {
            var empty = document.createElement('div');
            empty.className = 'be-day be-day--empty';
            calDays.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var dateStr = state.year + '-' + pad(state.month) + '-' + pad(day);
            var info = state.availability[dateStr];
            var status = info ? info.status : 'available';
            var isPast = dateStr < todayStr;
            var isTooSoon = !isPast && status === 'available' && dateStr < earliest;

            var cell = document.createElement('button');
            cell.type = 'button';
            cell.dataset.date = dateStr;
            var cls = 'be-day';
            if (isPast) {
                cls += ' be-day--past';
            } else if (isTooSoon) {
                cls += ' be-day--too-soon';
            } else {
                cls += ' be-day--' + status;
            }
            if (dateStr === hiddenInput.value) { cls += ' be-day--selected'; }
            cell.className = cls;

            var dot = isPast ? '' : '<span class="be-dot"></span>';
            cell.innerHTML = '<span>' + day + '</span>' + dot;

            if (isPast || status === 'booked') {
                cell.disabled = true;
            } else {
                cell.addEventListener('click', (function (ds, inf) {
                    return function () { selectDate(ds, inf); };
                })(dateStr, info));
            }

            calDays.appendChild(cell);
        }

    }

    function loadMonth(year, month) {
        var key = year + '-' + month;
        if (state.cache[key]) {
            state.availability = state.cache[key];
            state.year = year;
            state.month = month;
            render();
            return;
        }

        var url = availabilityUrl + '?year=' + year + '&month=' + month;
        if (excludeBookingId) { url += '&exclude=' + excludeBookingId; }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                state.cache[key] = data;
                state.availability = data;
                state.year = year;
                state.month = month;
                render();
            })
            .catch(function () { /* leave calendar as-is on network failure */ });
    }

    // Fixed to the top-center of the viewport by CSS alone (not anchored to
    // the trigger's position), so there's no positioning math needed here at
    // all — it's always in the same predictable spot regardless of where the
    // field sits on the page or how far the page is scrolled.
    var CLOSE_MS = 160;
    var closeTimer = null;

    function isOpen() { return pop.classList.contains('is-mounted'); }

    function openPop() {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        pop.classList.add('is-mounted');
        backdrop.classList.add('is-mounted');
        trigger.classList.add('is-open');
        // display:none -> block must land in a paint before the opacity/transform
        // transition starts, or the browser collapses it into a single jump.
        // Guarded by isOpen() in case closePop() ran in the meantime (e.g. an
        // instant open-then-select) — otherwise this stale callback would add
        // 'is-shown' back after the popup already closed.
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (isOpen()) { pop.classList.add('is-shown'); backdrop.classList.add('is-shown'); }
            });
        });
    }
    function closePop() {
        if (!isOpen()) { return; }
        pop.classList.remove('is-shown');
        backdrop.classList.remove('is-shown');
        trigger.classList.remove('is-open');
        closeTimer = setTimeout(function () {
            pop.classList.remove('is-mounted');
            backdrop.classList.remove('is-mounted');
            closePicker(); // reset to the day grid so it doesn't reopen showing the picker
        }, CLOSE_MS);
    }
    function togglePop() {
        if (isOpen()) { closePop(); } else { openPop(); }
    }

    trigger.addEventListener('click', togglePop);
    trigger.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePop(); }
        if (e.key === 'Escape') { closePop(); }
    });
    backdrop.addEventListener('click', closePop);

    calPrev.addEventListener('click', function () {
        var m = state.month - 1, y = state.year;
        if (m < 1) { m = 12; y -= 1; }
        loadMonth(y, m);
    });
    calNext.addEventListener('click', function () {
        var m = state.month + 1, y = state.year;
        if (m > 12) { m = 1; y += 1; }
        loadMonth(y, m);
    });
    // ---- Month & Year picker ----
    // Clicking the "September 2026" title swaps the day grid for a compact
    // month+year picker in the same popup (rather than stacking a second
    // popup on top of this already-compact 300px one). Hitting Go jumps
    // straight to any month/year — any year, not just the current one —
    // via the same loadMonth() the Prev/Next month arrows already use.
    function openPicker() {
        calPickerMonth.value = state.month;
        var startY = Math.min(state.year, new Date().getFullYear()) - 5;
        var endY = Math.max(state.year, new Date().getFullYear()) + 10;
        calPickerYear.innerHTML = '';
        for (var y = startY; y <= endY; y++) {
            var opt = document.createElement('option');
            opt.value = y;
            opt.textContent = y;
            if (y === state.year) { opt.selected = true; }
            calPickerYear.appendChild(opt);
        }
        calGridView.style.display = 'none';
        calPicker.classList.add('is-active');
        calTitleBtn.classList.add('is-open');
        calTitleBtn.setAttribute('aria-expanded', 'true');
    }
    function closePicker() {
        calGridView.style.display = '';
        calPicker.classList.remove('is-active');
        calTitleBtn.classList.remove('is-open');
        calTitleBtn.setAttribute('aria-expanded', 'false');
    }
    calTitleBtn.addEventListener('click', function () {
        if (calPicker.classList.contains('is-active')) { closePicker(); } else { openPicker(); }
    });
    calPickerGo.addEventListener('click', function () {
        var y = parseInt(calPickerYear.value, 10);
        var m = parseInt(calPickerMonth.value, 10);
        closePicker();
        loadMonth(y, m);
    });

    // The popup now lives outside #dateFieldWrap (moved to <body> above), so
    // a click inside it must also count as "inside" here — otherwise every
    // click on a day cell or the prev/next buttons would immediately close
    // the popup as an "outside click" before its own handler ever ran.
    document.addEventListener('click', function (e) {
        if (!dateFieldWrap.contains(e.target) && !pop.contains(e.target)) { closePop(); }
    });

    // The booking rule depends on the (free-text) Event Type field, so it
    // must re-apply live as the client types — both to re-grey "too soon"
    // days on the calendar and, if a date is already selected, to re-check
    // it against the newly-applicable rule (e.g. switching from "Birthday"
    // to "Wedding" after already picking a date only a week out).
    eventTypeInput.addEventListener('input', function () {
        if (hiddenInput.value) {
            selectDate(hiddenInput.value, state.availability[hiddenInput.value]);
        }
        render();
    });

    submitBtn.disabled = !hiddenInput.value;
    if (hiddenInput.value) {
        triggerText.textContent = formatLong(hiddenInput.value);
        triggerText.className = 'chosen';
    }

    render();

    // Deep-linked from the Event Calendar's "Book This Date" button. Re-checked
    // against the live availability data (not just trusted from the URL) —
    // selectDate() itself falls back to the booked/pending messaging if the
    // date turned out not to be free after all.
    var prefillDate = @json($prefillDate);
    if (prefillDate && !hiddenInput.value) {
        selectDate(prefillDate, state.availability[prefillDate]);
    }
})();
</script>
@endpush
