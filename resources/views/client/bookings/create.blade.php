@extends('layouts.app')

@section('title', isset($booking) ? 'Edit Booking' : 'Book an Event')
@section('page-title', isset($booking) ? 'Edit Booking' : 'Book an Event')

@push('styles')
<style>
    .book-event-lux {
        /* Matches the Request Appointment page's background exactly — that
           page has no background override of its own, so it renders on the
           site-wide navy (--tu-navy, defined on :root in layouts/app.blade.php). */
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
        /* Fixed to the viewport, flex column, exactly like the Event
           Calendar page's .evcal-bright (resources/views/client/calendar/
           index.blade.php) — everything below has to fit inside this one
           height, on desktop and mobile alike, with no page scroll.
           74px is the .top-navbar's own real rendered height (measured:
           1.1rem+0.5rem padding around its 40px avatar row) — the negative
           margin below cancels .page-content's padding, so this box's top
           edge lands exactly at the navbar's bottom edge, and subtracting
           anything more than that just wastes space at the bottom instead
           of giving it to the calendar. */
        height: calc(100vh - 74px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 1rem 1.5rem;
        background: var(--be-black);
        overflow: hidden;
        display: flex;
        flex-direction: column;
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

    .be-wrap { position: relative; z-index: 1; width: 100%; max-width: 720px; margin: 0 auto; flex: 1; min-height: 0; display: flex; flex-direction: column; }

    @keyframes beFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    .be-fade { opacity: 0; animation: beFadeUp 0.55s ease forwards; }

    .be-heading-block { flex: 0 0 auto; text-align: center; margin-bottom: 0.75rem; }
    .be-heading-underline { width: 56px; height: 3px; background: var(--be-gold); border-radius: 2px; margin: 0 auto 0.6rem; }
    .be-heading-block p { color: var(--be-muted); font-size: 0.92rem; margin: 0; }

    /* ---- Inner form card ----
       White, matching the Request Appointment page's .stat-card. Hosts both
       steps of the flow (#bookingStepCalendar, #bookingStepDetails) — only
       one is visible at a time, toggled in the script below. */
    .be-card {
        background: #FFFFFF;
        border: 1px solid var(--be-gold);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 0 40px rgba(244, 201, 93, 0.08), 0 20px 45px rgba(0, 0, 0, 0.5);
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    /* Both steps fill the card's full height. Step 1 (the calendar) must
       never need the page to scroll; Step 2 (a plain form) gets its own
       internal scrollbar as a fallback on a very short viewport, which is
       fine — only the calendar has a hard no-scroll requirement. */
    #bookingStepCalendar, #bookingStepDetails {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }
    #bookingStepDetails { overflow-y: auto; }

    .be-step-title {
        flex: 0 0 auto;
        display: flex; align-items: center; gap: 0.6rem;
        color: #1F2937; font-weight: 700; font-size: 0.92rem;
        margin-bottom: 0.6rem;
    }
    .be-step-title i { color: var(--be-gold); }

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

    /* ---- Selected-date summary (Step 2) ---- */
    .be-selected-date-row {
        display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
        flex-wrap: wrap;
    }
    .be-selected-date-row .chosen { color: #1F2937; font-weight: 600; }

    /* ---- Event Booking Calendar (Step 1) ----
       Restyled to match the Event Calendar page's compact layout
       (resources/views/client/calendar/index.blade.php): a fixed-height
       flex column topped by a centered header + legend, then one single
       bordered grid holding both the weekday labels and the day cells —
       the same background-shows-through-a-1px-gap trick draws the grid
       lines with no extra nested "card" around it. Its row template is
       set in JS on every render() as "auto repeat(N, 1fr)" (N = however
       many week-rows the month needs), so it always fills exactly the
       space left after the header/legend, on any screen, with no scroll. */
    .be-calendar-pop {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .be-cal-header { flex: 0 0 auto; display: flex; align-items: center; justify-content: center; gap: 1.25rem; margin-bottom: 0.85rem; }
    .be-cal-nav-btn {
        width: 40px; height: 40px; border-radius: 50%;
        background: #FFFFFF; border: 1.5px solid #E5E7EB; color: #1F2937;
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
        flex: 0 0 auto;
    }
    .be-cal-nav-btn:hover { background: #F4C95D; border-color: #F4C95D; color: #1F2937; transform: translateY(-2px); }

    /* The month/year title is itself the editor's trigger — clicking it
       swaps the grid below for an inline month+year picker. */
    .be-cal-title-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        background: none; border: none; cursor: pointer;
        color: #1F2937; font-weight: 700; font-size: 1.3rem; font-family: inherit;
        margin: 0; min-width: 210px; padding: 0.3rem 0.85rem; border-radius: 10px;
        transition: background 0.2s ease;
    }
    .be-cal-title-btn:hover, .be-cal-title-btn:focus-visible { background: #F9FAFB; outline: none; }
    .be-cal-title-caret { font-size: 0.62rem; color: #9CA3AF; transition: transform 0.2s ease, color 0.2s ease; }
    .be-cal-title-btn:hover .be-cal-title-caret, .be-cal-title-btn.is-open .be-cal-title-caret { color: #D4AF37; }
    .be-cal-title-btn.is-open .be-cal-title-caret { transform: rotate(180deg); }

    /* Inline month+year picker — replaces the grid in place while open. */
    .be-cal-picker { display: none; flex: 1; min-height: 0; overflow-y: auto; }
    .be-cal-picker.is-active { display: block; }
    .be-cal-picker-row { display: flex; gap: 0.6rem; margin-bottom: 0.75rem; }
    .be-cal-picker-select {
        flex: 1 1 0; min-width: 0;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        border: 1px solid #E5E7EB; border-radius: 8px; background-color: #F9FAFB;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231F2937' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat; background-position: right 0.6rem center; background-size: 11px 11px;
        color: #1F2937; font-weight: 600; font-size: 0.85rem;
        padding: 0.5rem 1.8rem 0.5rem 0.7rem;
        cursor: pointer;
    }
    .be-cal-picker-select:hover, .be-cal-picker-select:focus { border-color: #F4C95D; outline: none; }
    .be-cal-picker-go {
        display: block; width: 100%; background: #F4C95D; color: #1F2937; border: none;
        font-weight: 700; font-size: 0.85rem; border-radius: 8px; padding: 0.6rem;
        cursor: pointer; transition: background 0.2s ease;
    }
    .be-cal-picker-go:hover { background: #D4AF37; }

    .be-cal-legend {
        flex: 0 0 auto;
        display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;
        margin-bottom: 0.85rem; font-size: 0.82rem; color: #4B5563; font-weight: 500;
    }
    .be-cal-legend span { display: inline-flex; align-items: center; gap: 0.4rem; }
    .be-legend-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; flex: 0 0 auto; }

    #calGridView { flex: 1; min-height: 0; display: flex; flex-direction: column; }

    /* Single merged grid — weekday header cells and day cells together
       (rebuilt every render(), same as the Event Calendar's .calendar-grid):
       a light-gray background shows through a 1px gap between white cells,
       drawing thin grid lines with no separate bordered sections. Always
       7 columns, so it stays a real calendar grid on mobile too — it just
       shrinks, it never collapses into a list. */
    .be-cal-grid {
        flex: 1;
        min-height: 0;
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #E5E7EB;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        gap: 1px;
        overflow: hidden;
    }
    .be-cal-weekday {
        background: #FFFFFF;
        color: #6B7280;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0;
    }

    .be-day {
        position: relative;
        min-height: 0;
        border: none;
        background: #FFFFFF;
        color: #1F2937;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        transition: background 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
    }
    .be-day--empty { background: #FCFCFD; cursor: default; }
    .be-day--past { color: #D1D5DB; background: #FCFCFD; cursor: not-allowed; }
    .be-day--available:not(:disabled):hover { box-shadow: inset 0 0 0 1.5px #F4C95D; background: #FFFBEF; }
    /* Pending is a hard stop exactly like Booked — disabled in JS below, so
       no hover feedback here (it would wrongly suggest the date is
       clickable). */
    .be-day--pending { background: #FFFDF3; cursor: not-allowed; }
    .be-day--booked { background: #FEF6F6; color: #9CA3AF; cursor: not-allowed; }
    .be-day--selected { box-shadow: inset 0 0 0 2px #F4C95D !important; background: #FFF8E7 !important; color: #1F2937 !important; font-weight: 800; }
    .be-day-num { display: inline-flex; align-items: center; justify-content: center; }
    .be-day--today .be-day-num {
        background: #F4C95D; color: #1F2937; border-radius: 50%;
        width: 26px; height: 26px; font-weight: 800;
    }
    .be-day .be-dot { width: 6px; height: 6px; border-radius: 50%; }
    .be-day--available .be-dot { background: #22C55E; }
    .be-day--pending .be-dot { background: #FBBF24; }
    .be-day--booked .be-dot { background: #EF4444; }
    .be-day--selected .be-dot { background: #D4AF37; }

    /* ---- Date Availability panel ---- */
    .be-avail-card {
        flex: 0 0 auto;
        background: #F9FAFB;
        border: 1px solid var(--be-gold-dim);
        border-radius: 12px;
        padding: 0.6rem 0.85rem;
        margin: 0.6rem 0 0;
        transition: border-color 0.25s ease, background 0.25s ease;
    }
    .be-avail-title {
        color: #1F2937;
        font-weight: 700;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .be-avail-title i { color: var(--be-gold); }
    .be-avail-body { display: flex; align-items: flex-start; gap: 0.65rem; }
    .be-avail-icon {
        width: 28px; height: 28px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; flex: 0 0 auto;
    }
    .be-avail-date { color: #1F2937; font-weight: 700; font-size: 0.98rem; margin-bottom: 0.2rem; }
    .be-avail-status { font-weight: 700; font-size: 0.78rem; margin-bottom: 0.1rem; }
    .be-avail-meta { color: #6B7280; font-size: 0.82rem; }
    .be-avail-msg { color: #6B7280; font-size: 0.76rem; margin: 0; }

    .be-avail-card.state-available { border-color: rgba(21, 128, 61, 0.35); }
    .be-avail-card.state-pending { border-color: rgba(180, 83, 9, 0.35); }
    .be-avail-card.state-booked { border-color: rgba(185, 28, 28, 0.35); }
    .state-available .be-avail-icon { background: rgba(21, 128, 61, 0.12); color: #15803D; }
    .state-pending .be-avail-icon { background: rgba(180, 83, 9, 0.12); color: #B45309; }
    .state-booked .be-avail-icon { background: rgba(185, 28, 28, 0.12); color: #B91C1C; }
    .state-available .be-avail-status { color: #15803D; }
    .state-pending .be-avail-status { color: #B45309; }
    .state-booked .be-avail-status { color: #B91C1C; }

    .be-btn-gold {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--be-gold); color: #000; border: none;
        font-weight: 700; font-size: 0.95rem; border-radius: 10px;
        padding: 0.75rem 1.6rem; transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, opacity 0.2s ease;
    }
    .be-btn-gold:hover:not(:disabled) { background: #ffd873; color: #000; transform: translateY(-2px); box-shadow: 0 10px 26px rgba(244, 201, 93, 0.4); }
    .be-btn-gold:disabled { opacity: 0.45; cursor: not-allowed; }

    /* Mobile — same technique as the Event Calendar's own mobile block:
       shrink paddings/fonts/nav-button size, but the grid itself keeps
       "grid-template-columns: repeat(7, 1fr)" no matter what, so it's
       always a real 7-column calendar, never a stacked list. */
    @media (max-width: 575.98px) {
        .book-event-lux { height: calc(100vh - 64px); padding: 0.65rem 0.75rem; margin: -1rem -1rem -1.5rem; }
        .be-card { padding: 0.85rem 0.9rem; border-radius: 18px; }
        .be-heading-block { margin-bottom: 0.5rem; }
        .be-cal-header { gap: 0.6rem; margin-bottom: 0.5rem; }
        .be-cal-title-btn { min-width: 0; font-size: 0.88rem; padding: 0.2rem 0.5rem; }
        .be-cal-nav-btn { width: 28px; height: 28px; }
        .be-cal-legend { gap: 0.55rem; font-size: 0.6rem; margin-bottom: 0.45rem; }
        .be-day { font-size: 0.66rem; }
        .be-day--today .be-day-num { width: 17px; height: 17px; font-size: 0.62rem; }
        .be-avail-card { padding: 0.5rem 0.65rem; }
    }
</style>
@endpush

@section('content')
@php
    // Editing an existing booking, or redisplaying the form after a failed
    // submission, both start on the Booking Details step (the date is
    // already known in both cases) — a brand-new booking starts on the
    // Calendar step, per "the Calendar must be the first page shown after
    // clicking Book an Event".
    $startOnDetails = isset($booking) || $errors->any();
@endphp
<div class="book-event-lux">
    <div class="be-wrap">
        <div class="be-heading-block be-fade" style="animation-delay:.03s;">
            <div class="be-heading-underline"></div>
            <p>
                @if(isset($booking))
                    Update your event details — it will need to be reviewed by the admin again.
                @else
                    Pick your preferred event date, then tell us about your event — availability updates live.
                @endif
            </p>
        </div>

        <div class="be-card be-fade" style="animation-delay:.14s;">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            {{-- ============ STEP 1: Event Booking Calendar ============ --}}
            <div id="bookingStepCalendar" style="{{ $startOnDetails ? 'display:none;' : '' }}">
                <div class="be-step-title"><i class="fas fa-calendar-days"></i> Choose Your Event Date</div>

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
                            <span><span class="be-legend-dot" style="background: #FBBF24;"></span> Pending</span>
                            <span><span class="be-legend-dot" style="background: #EF4444;"></span> Already Booked</span>
                        </div>
                        {{-- Weekday header cells + day cells are rendered together into
                             this one grid in JS (render() below) — same single-grid
                             technique the Event Calendar page uses, so the row template
                             ("auto" for the header row + N equal rows for the weeks) can
                             be recomputed on every month change and the whole thing
                             always exactly fills the space left in the card. --}}
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

                <div class="be-avail-card" id="availCard">
                    <div class="be-avail-title"><i class="fas fa-circle-info"></i> Date Availability</div>
                    <div class="be-avail-body">
                        <div class="be-avail-icon" id="availIcon" style="background: rgba(244,201,93,0.1); color: var(--be-gold);"><i class="fas fa-calendar"></i></div>
                        <div id="availTextWrap">
                            <p class="be-avail-msg" id="availMsg">Select an available date above to continue.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ STEP 2: Booking Details ============ --}}
            <div id="bookingStepDetails" style="{{ $startOnDetails ? '' : 'display:none;' }}">
                <div class="be-step-title"><i class="fas fa-clipboard-list"></i> Booking Details</div>

                <div class="be-field">
                    <label class="form-label">Event Date</label>
                    <div class="be-input-group">
                        <i class="fas fa-calendar-days be-field-icon"></i>
                        <div class="form-control be-selected-date-row">
                            <span id="selectedDateDisplay" class="chosen"></span>
                            <button type="button" class="btn btn-sm btn-outline-gold" id="changeDateBtn">Change Date</button>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ isset($booking) ? route('client.bookings.update', $booking) : route('client.bookings.store') }}" id="bookingForm">
                    @csrf
                    @if(isset($booking)) @method('PUT') @endif
                    <input type="hidden" name="event_date" id="eventDateInput" value="{{ old('event_date', isset($booking) ? $booking->event_date->format('Y-m-d') : '') }}">

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
    var weekdayLabels = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    var state = {
        month: {{ $month }},
        year: {{ $year }},
        availability: @json($availability),
        cache: {},
    };
    state.cache[state.year + '-' + state.month] = state.availability;

    var stepCalendar = document.getElementById('bookingStepCalendar');
    var stepDetails = document.getElementById('bookingStepDetails');
    var changeDateBtn = document.getElementById('changeDateBtn');
    var selectedDateDisplay = document.getElementById('selectedDateDisplay');

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
    var submitBtn = document.getElementById('submitBtn');

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

    function setSubmitEnabled(enabled) {
        submitBtn.disabled = !enabled;
    }

    function updateSelectedDateDisplay() {
        selectedDateDisplay.textContent = hiddenInput.value ? formatLong(hiddenInput.value) : 'No date selected';
    }

    // ---- Step navigation ----
    function showCalendarStep() {
        stepDetails.style.display = 'none';
        stepCalendar.style.display = '';
    }
    function showDetailsStep() {
        updateSelectedDateDisplay();
        stepCalendar.style.display = 'none';
        stepDetails.style.display = '';
    }
    changeDateBtn.addEventListener('click', showCalendarStep);

    function selectDate(dateStr, info) {
        var dateLabel = formatLong(dateStr);

        if (info && info.status === 'booked') {
            hiddenInput.value = '';
            setSubmitEnabled(false);
            setAvailState('booked', 'fa-circle-xmark',
                dateLabel + ' — Already Booked',
                'This date is already booked for a ' + info.event_type + ' event. Please select another date.');
            return;
        }

        if (info && info.status === 'pending') {
            // Pending dates are a hard stop, same as Already Booked — the
            // client cannot select one or continue with the booking.
            hiddenInput.value = '';
            setSubmitEnabled(false);
            setAvailState('pending', 'fa-hourglass-half',
                dateLabel + ' — Pending Approval' + (info.event_type ? ' (' + info.event_type + ')' : ''),
                'This date currently has a pending reservation and cannot be selected. Please choose another available date.');
            return;
        }

        // Available — carry the date over and jump straight into the
        // existing Booking Details flow. No manual "Continue" click needed;
        // picking a valid date IS the confirmation.
        hiddenInput.value = dateStr;
        setSubmitEnabled(true);
        setAvailState('available', 'fa-circle-check',
            dateLabel + ' — Available',
            'This date is available for booking.');
        render();
        if (stepCalendar.style.display !== 'none') {
            showDetailsStep();
        } else {
            updateSelectedDateDisplay();
        }
    }

    function render() {
        calTitle.textContent = monthNames[state.month - 1] + ' ' + state.year;
        calDays.innerHTML = '';

        var first = new Date(state.year, state.month - 1, 1);
        var startDow = first.getDay();
        var daysInMonth = new Date(state.year, state.month, 0).getDate();

        // The grid fills a fixed-height area (#calDays is flex:1 inside a
        // height-constrained column), so every month — 4, 5, or 6 week-rows
        // — has to divide that same space evenly. "auto" reserves just
        // enough height for the weekday-label row; the rest is split evenly
        // across the week rows. Same technique as the Event Calendar page.
        var totalRows = Math.ceil((startDow + daysInMonth) / 7);
        calDays.style.gridTemplateRows = 'auto repeat(' + totalRows + ', 1fr)';

        for (var w = 0; w < 7; w++) {
            var wd = document.createElement('div');
            wd.className = 'be-cal-weekday';
            wd.textContent = weekdayLabels[w];
            calDays.appendChild(wd);
        }

        for (var i = 0; i < startDow; i++) {
            var empty = document.createElement('button');
            empty.type = 'button';
            empty.disabled = true;
            empty.className = 'be-day be-day--empty';
            calDays.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var dateStr = state.year + '-' + pad(state.month) + '-' + pad(day);
            var info = state.availability[dateStr];
            var status = info ? info.status : 'available';
            var isPast = dateStr < todayStr;
            var isToday = dateStr === todayStr;

            var cell = document.createElement('button');
            cell.type = 'button';
            cell.dataset.date = dateStr;
            var cls = 'be-day';
            if (isPast) {
                cls += ' be-day--past';
            } else {
                cls += ' be-day--' + status;
            }
            if (isToday) { cls += ' be-day--today'; }
            if (dateStr === hiddenInput.value) { cls += ' be-day--selected'; }
            cell.className = cls;

            var dot = isPast ? '' : '<span class="be-dot"></span>';
            cell.innerHTML = '<span class="be-day-num">' + day + '</span>' + dot;

            if (isPast || status === 'booked' || status === 'pending') {
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
    // month+year picker. Hitting Go jumps straight to any month/year — any
    // year, not just the current one — via the same loadMonth() the
    // Prev/Next month arrows already use.
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

    setSubmitEnabled(!!hiddenInput.value);
    updateSelectedDateDisplay();
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
