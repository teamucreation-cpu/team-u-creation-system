@extends('layouts.app')

@section('title', 'Event Calendar')
@section('page-title', 'Event Calendar')

@push('styles')
<style>
    .evcal-bright {
        --eb-bg: #F8F9FA;
        --eb-white: #FFFFFF;
        --eb-gold: #F4C95D;
        --eb-gold-dark: #D4AF37;
        --eb-text: #000000;
        --eb-muted: #000000;
        --eb-border: #E5E7EB;
        --eb-green: #22C55E;
        --eb-yellow: #FBBF24;
        --eb-red: #EF4444;
        position: relative;
        height: calc(100vh - 130px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 1rem 1.5rem;
        background: var(--eb-bg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .evcal-wrap { position: relative; flex: 1; min-height: 0; display: flex; max-width: 780px; width: 100%; margin: 0 auto; }

    .evcal-card {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        background: var(--eb-white);
        border: 1px solid var(--eb-border);
        border-radius: 20px;
        padding: 1.25rem 1.75rem;
        box-shadow: 0 4px 6px rgba(17, 24, 39, 0.04), 0 12px 32px rgba(17, 24, 39, 0.06);
    }

    .evcal-header { flex: 0 0 auto; display: flex; align-items: center; justify-content: center; gap: 1.25rem; margin-bottom: 0.85rem; }
    .evcal-nav-btn {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--eb-white); border: 1.5px solid var(--eb-border); color: var(--eb-text);
        display: inline-flex; align-items: center; justify-content: center;
        text-decoration: none; transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
        flex: 0 0 auto;
    }
    .evcal-nav-btn:hover { background: var(--eb-gold); border-color: var(--eb-gold); color: #000000; transform: translateY(-2px); }

    /* The "September 2026" heading is itself the month/year editor's trigger
       — a real <button> styled to read exactly like the plain heading it
       replaces, plus a small caret and a hover/focus highlight so it's
       discoverable as clickable without changing the calendar's look at
       rest. Clicking it opens .evcal-myp (see below). */
    .evcal-title-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        background: none; border: none; cursor: pointer;
        color: var(--eb-text); font-weight: 700; font-size: 1.35rem; font-family: inherit;
        margin: 0; min-width: 220px; padding: 0.3rem 0.85rem; border-radius: 10px;
        transition: background 0.2s ease;
    }
    .evcal-title-btn:hover, .evcal-title-btn:focus-visible { background: #F3F4F6; outline: none; }
    .evcal-title-caret { font-size: 0.7rem; color: #9CA3AF; transition: transform 0.2s ease, color 0.2s ease; }
    .evcal-title-btn:hover .evcal-title-caret, .evcal-title-btn.is-open .evcal-title-caret { color: var(--eb-gold-dark); }
    .evcal-title-btn.is-open .evcal-title-caret { transform: rotate(180deg); }

    /* ---- Month & Year picker ----
       Portaled to a direct child of <body> at runtime (see script below),
       same reasoning and same literal-colors requirement as the detail
       popup further down: once relocated it's outside .evcal-bright's
       subtree, so var(--eb-*) would resolve to nothing. Pinned to the
       top-center of the viewport rather than anchored under the title, so
       it's never at risk of being clipped or of overflowing a narrow
       screen — consistent with every other popup in this app. */
    .evcal-myp-backdrop {
        display: none; position: fixed; inset: 0; z-index: 99998;
        background: #000000; opacity: 0; transition: opacity 0.18s ease;
    }
    .evcal-myp-backdrop.is-mounted { display: block; }
    .evcal-myp-backdrop.is-shown { opacity: 0.5; }

    .evcal-myp {
        display: none; position: fixed; top: max(1rem, 10vh); left: 50%; z-index: 99999;
        width: min(300px, 90vw);
        background-color: #FFFFFF; border: 1px solid #E5E7EB; border-top: 4px solid #F4C95D;
        border-radius: 16px; padding: 1.25rem;
        box-shadow: 0 24px 48px rgba(17, 24, 39, 0.28);
        opacity: 0; transform: translateX(-50%) translateY(-10px) scale(0.97);
        transition: opacity 0.16s ease, transform 0.16s ease;
    }
    .evcal-myp.is-mounted { display: block; }
    .evcal-myp.is-shown { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    .evcal-myp-title { font-weight: 700; font-size: 0.95rem; color: #000000; margin-bottom: 1rem; text-align: center; }
    .evcal-myp-row { display: flex; gap: 0.6rem; margin-bottom: 1.1rem; }
    .evcal-myp-select {
        flex: 1 1 0; min-width: 0;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        border: 1.5px solid #E5E7EB; border-radius: 10px; background-color: #FFFFFF;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23000000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat; background-position: right 0.6rem center; background-size: 11px 11px;
        color: #000000; font-weight: 600; font-size: 0.9rem;
        padding: 0.55rem 1.9rem 0.55rem 0.8rem;
        cursor: pointer;
    }
    .evcal-myp-select:hover, .evcal-myp-select:focus { border-color: #F4C95D; outline: none; }
    .evcal-myp-go {
        display: block; width: 100%; background-color: #F4C95D; color: #000000; border: none;
        font-weight: 700; font-size: 0.9rem; border-radius: 10px; padding: 0.65rem;
        cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .evcal-myp-go:hover { background-color: #D4AF37; transform: translateY(-2px); }
    .evcal-myp-close {
        position: absolute; top: 0.75rem; right: 0.75rem;
        width: 24px; height: 24px; border-radius: 50%; border: none;
        background-color: #F3F4F6; color: #000000; font-size: 0.7rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .evcal-myp-close:hover { background-color: #E5E7EB; }

    .evcal-legend {
        flex: 0 0 auto;
        display: flex; justify-content: center; gap: 1.75rem; flex-wrap: wrap;
        margin-bottom: 0.85rem; font-size: 0.85rem; color: var(--eb-muted); font-weight: 500;
    }
    .evcal-legend span { display: inline-flex; align-items: center; gap: 0.45rem; }
    .evcal-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; flex: 0 0 auto; }

    .evcal-bright .calendar-grid {
        flex: 1;
        min-height: 0;
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--eb-border);
        border: 1px solid var(--eb-border);
        border-radius: 14px;
        gap: 1px;
        overflow: hidden;
    }
    .evcal-bright .calendar-header-cell {
        background: var(--eb-white);
        color: var(--eb-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .evcal-day-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        width: 100%;
        height: 100%;
        background: var(--eb-white);
        border: none;
        color: var(--eb-text);
        cursor: pointer;
        transition: background 0.18s ease, box-shadow 0.18s ease;
    }
    .evcal-day-btn:disabled { cursor: default; }
    .evcal-day-btn:not(:disabled):hover { background: #FBFAF6; box-shadow: inset 0 0 0 1.5px var(--eb-gold); }
    .evcal-day-btn.is-other-month { background: #FCFCFD; color: #D1D5DB; cursor: default; }
    .evcal-day-btn .day-number { font-weight: 600; font-size: 0.85rem; }
    .evcal-day-btn.is-today .day-number {
        background: var(--eb-gold); color: #000000; border-radius: 50%;
        width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; font-weight: 800;
    }

    /* ---- Click-to-view popup ----
       Portaled to a direct child of <body> at runtime (see script below) so
       it can never be clipped by any card or container. Because of that
       relocation it no longer sits inside .evcal-bright's subtree, so it
       CANNOT use var(--eb-*) — those custom properties are scoped to
       .evcal-bright and resolve to nothing once detached, which is exactly
       why the modal previously rendered with no real background at all
       (the calendar showing straight through it). Every color below is a
       literal, solid value for that reason — this popup must never be
       transparent. The backdrop is solid black at rest (opacity is only
       used for its brief fade-in/out, never a resting translucent state). */
    .evcal-backdrop {
        display: none; position: fixed; inset: 0; z-index: 99998;
        background: #000000; opacity: 0; transition: opacity 0.18s ease;
    }
    .evcal-backdrop.is-mounted { display: block; }
    .evcal-backdrop.is-shown { opacity: 0.6; }

    .evcal-modal {
        display: none; position: fixed; top: max(1rem, 10vh); left: 50%; z-index: 99999;
        width: min(340px, 90vw);
        background-color: #FFFFFF; border: 1px solid #E5E7EB; border-top: 4px solid #F4C95D;
        border-radius: 16px; padding: 1.5rem;
        box-shadow: 0 24px 48px rgba(17, 24, 39, 0.28);
        opacity: 0; transform: translateX(-50%) translateY(-10px) scale(0.97);
        transition: opacity 0.16s ease, transform 0.16s ease;
        text-align: center;
    }
    .evcal-modal.is-mounted { display: block; }
    .evcal-modal.is-shown { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }

    .evcal-modal-icon {
        width: 52px; height: 52px; border-radius: 50%; margin: 0 auto 1rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
    }
    .evcal-modal.state-available .evcal-modal-icon { background-color: #DCFCE7; color: #15803D; }
    .evcal-modal.state-pending .evcal-modal-icon { background-color: #FEF3C7; color: #B45309; }
    .evcal-modal.state-booked .evcal-modal-icon { background-color: #FEE2E2; color: #B91C1C; }

    .evcal-modal-date { font-weight: 700; font-size: 1.05rem; color: #000000; margin-bottom: 0.5rem; }
    .evcal-modal-msg { color: #000000; font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.1rem; }
    .evcal-modal-btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background-color: #F4C95D; color: #000000; border: none; font-weight: 700;
        border-radius: 10px; padding: 0.65rem 1.4rem; text-decoration: none; font-size: 0.9rem;
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .evcal-modal-btn:hover { background-color: #D4AF37; color: #000000; transform: translateY(-2px); box-shadow: 0 10px 22px rgba(212, 175, 55, 0.4); }
    .evcal-modal-close {
        position: absolute; top: 0.75rem; right: 0.75rem;
        width: 26px; height: 26px; border-radius: 50%; border: none;
        background-color: #F3F4F6; color: #000000; font-size: 0.75rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .evcal-modal-close:hover { background-color: #E5E7EB; }

    @media (max-width: 575.98px) {
        .evcal-bright { height: calc(100vh - 118px); padding: 0.65rem 0.6rem; margin: -1rem -1rem -1.5rem; }
        .evcal-card { padding: 0.85rem; border-radius: 16px; }
        .evcal-header { gap: 0.75rem; margin-bottom: 0.6rem; }
        .evcal-title-btn { min-width: 0; font-size: 1.05rem; padding: 0.25rem 0.6rem; }
        .evcal-nav-btn { width: 34px; height: 34px; }
        .evcal-legend { gap: 0.85rem; font-size: 0.7rem; margin-bottom: 0.6rem; }
        .evcal-day-btn .day-number { font-size: 0.75rem; }
    }
</style>
@endpush

@section('content')
@php
    $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
    $prevMonth = $currentDate->copy()->subMonth();
    $nextMonth = $currentDate->copy()->addMonth();
    $daysInMonth = $currentDate->daysInMonth;
    $startDayOfWeek = $currentDate->dayOfWeek;
    $today = now()->format('Y-m-d');
    // How many week-rows this month actually needs (4–6) — used so the grid
    // reserves exactly that many rows and always fills the available height
    // with no leftover gap and no need to scroll to see the rest of the month.
    $totalRows = (int) ceil(($startDayOfWeek + $daysInMonth) / 7);
    // A generous default year window, widened on either end if $year (or
    // "now") happens to fall outside it, so the currently-open year and
    // "today" are always selectable — never artificially capped to "this
    // year only".
    $yearRangeStart = min($year, now()->year) - 5;
    $yearRangeEnd = max($year, now()->year) + 10;
@endphp

<div class="evcal-bright">
    <div class="evcal-wrap">
        <div class="evcal-card">
            <div class="evcal-header">
                <a href="{{ route('client.calendar.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="evcal-nav-btn" aria-label="Previous month"><i class="fas fa-chevron-left"></i></a>
                <button type="button" class="evcal-title-btn" id="evcalTitleBtn" aria-haspopup="true" aria-expanded="false">
                    <span id="evcalTitleText">{{ $currentDate->format('F Y') }}</span>
                    <i class="fas fa-chevron-down evcal-title-caret"></i>
                </button>
                <a href="{{ route('client.calendar.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="evcal-nav-btn" aria-label="Next month"><i class="fas fa-chevron-right"></i></a>
            </div>

            <div class="evcal-legend">
                <span><span class="evcal-dot" style="background: var(--eb-green);"></span> Available</span>
                <span><span class="evcal-dot" style="background: var(--eb-yellow);"></span> Pending</span>
                <span><span class="evcal-dot" style="background: var(--eb-red);"></span> Booked</span>
            </div>

            <div class="calendar-grid" id="evcalGrid" style="grid-template-rows: auto repeat({{ $totalRows }}, 1fr);">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                    <div class="calendar-header-cell">{{ $day }}</div>
                @endforeach

                @for($i = 0; $i < $startDayOfWeek; $i++)
                    <button type="button" class="evcal-day-btn is-other-month" disabled></button>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateStr = $currentDate->copy()->day($day)->format('Y-m-d');
                        $isToday = $dateStr === $today;
                        $isPast = $dateStr < $today;
                        $status = $statusMap[$dateStr] ?? 'available';
                        $dotColor = match($status) {
                            'booked' => 'var(--eb-red)',
                            'pending' => 'var(--eb-yellow)',
                            default => 'var(--eb-green)',
                        };
                    @endphp
                    <button type="button" class="evcal-day-btn {{ $isToday ? 'is-today' : '' }}" {{ $isPast ? 'disabled' : '' }} data-date="{{ $dateStr }}" data-status="{{ $status }}">
                        <span class="day-number">{{ $day }}</span>
                        @unless($isPast)
                            <span class="evcal-dot" style="background: {{ $dotColor }};"></span>
                        @endunless
                    </button>
                @endfor
            </div>
        </div>
    </div>

    <div class="evcal-backdrop" id="evcalBackdrop"></div>
    <div class="evcal-modal" id="evcalModal">
        <button type="button" class="evcal-modal-close" id="evcalModalClose"><i class="fas fa-xmark"></i></button>
        <div class="evcal-modal-icon" id="evcalModalIcon"><i class="fas fa-calendar"></i></div>
        <div class="evcal-modal-date" id="evcalModalDate"></div>
        <p class="evcal-modal-msg" id="evcalModalMsg"></p>
        <a href="#" class="evcal-modal-btn" id="evcalModalBtn" style="display:none;"><i class="fas fa-paper-plane"></i> Book This Date</a>
    </div>

    <div class="evcal-myp-backdrop" id="evcalMypBackdrop"></div>
    <div class="evcal-myp" id="evcalMyp">
        <button type="button" class="evcal-myp-close" id="evcalMypClose"><i class="fas fa-xmark"></i></button>
        <div class="evcal-myp-title">Jump to Month &amp; Year</div>
        <div class="evcal-myp-row">
            <select class="evcal-myp-select" id="evcalMypMonth" aria-label="Month">
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $name)
                    <option value="{{ $i + 1 }}" {{ ($i + 1) === $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select class="evcal-myp-select" id="evcalMypYear" aria-label="Year">
                @for($y = $yearRangeStart; $y <= $yearRangeEnd; $y++)
                    <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="button" class="evcal-myp-go" id="evcalMypGo">Go</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var bookUrlBase = @json(route('client.bookings.create'));

    var grid = document.getElementById('evcalGrid');
    var backdrop = document.getElementById('evcalBackdrop');
    var modal = document.getElementById('evcalModal');
    var closeBtn = document.getElementById('evcalModalClose');
    var modalIcon = document.getElementById('evcalModalIcon');
    var modalDate = document.getElementById('evcalModalDate');
    var modalMsg = document.getElementById('evcalModalMsg');
    var modalBtn = document.getElementById('evcalModalBtn');

    document.body.appendChild(backdrop);
    document.body.appendChild(modal);

    function formatLong(dateStr) {
        var parts = dateStr.split('-').map(Number);
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        return monthNames[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    var CLOSE_MS = 160;
    var closeTimer = null;
    function isOpen() { return modal.classList.contains('is-mounted'); }

    function openModal(dateStr, status) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }

        modal.className = 'evcal-modal state-' + status;
        modalDate.textContent = formatLong(dateStr);

        if (status === 'booked') {
            modalIcon.innerHTML = '<i class="fas fa-circle-xmark"></i>';
            modalMsg.textContent = 'This date is already booked.';
            modalBtn.style.display = 'none';
        } else if (status === 'pending') {
            modalIcon.innerHTML = '<i class="fas fa-hourglass-half"></i>';
            modalMsg.textContent = 'This date currently has a pending reservation. Availability may change.';
            modalBtn.style.display = 'none';
        } else {
            modalIcon.innerHTML = '<i class="fas fa-circle-check"></i>';
            modalMsg.textContent = 'This date is available for booking.';
            modalBtn.href = bookUrlBase + '?date=' + dateStr;
            modalBtn.style.display = 'inline-flex';
        }

        modal.classList.add('is-mounted');
        backdrop.classList.add('is-mounted');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (isOpen()) { modal.classList.add('is-shown'); backdrop.classList.add('is-shown'); }
            });
        });
    }

    function closeModal() {
        if (!isOpen()) { return; }
        modal.classList.remove('is-shown');
        backdrop.classList.remove('is-shown');
        closeTimer = setTimeout(function () {
            modal.classList.remove('is-mounted');
            backdrop.classList.remove('is-mounted');
        }, CLOSE_MS);
    }

    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.evcal-day-btn');
        if (!btn || btn.disabled) { return; }
        openModal(btn.dataset.date, btn.dataset.status);
    });

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeModal(); } });

    // ---- Month & Year picker ----
    // Makes the "September 2026" heading itself the editor: click it, pick
    // any month/year (any year in the dropdown's range, not just the
    // current one), hit Go, and the page navigates there — same route,
    // same query params the Prev/Next arrows already use, so everything
    // (availability, booking status) comes from the normal server render.
    var titleBtn = document.getElementById('evcalTitleBtn');
    var mypBackdrop = document.getElementById('evcalMypBackdrop');
    var myp = document.getElementById('evcalMyp');
    var mypClose = document.getElementById('evcalMypClose');
    var mypMonth = document.getElementById('evcalMypMonth');
    var mypYear = document.getElementById('evcalMypYear');
    var mypGo = document.getElementById('evcalMypGo');
    var calendarBaseUrl = @json(route('client.calendar.index'));

    document.body.appendChild(mypBackdrop);
    document.body.appendChild(myp);

    var mypCloseTimer = null;
    function isMypOpen() { return myp.classList.contains('is-mounted'); }

    function openMyp() {
        if (mypCloseTimer) { clearTimeout(mypCloseTimer); mypCloseTimer = null; }
        titleBtn.classList.add('is-open');
        titleBtn.setAttribute('aria-expanded', 'true');
        myp.classList.add('is-mounted');
        mypBackdrop.classList.add('is-mounted');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (isMypOpen()) { myp.classList.add('is-shown'); mypBackdrop.classList.add('is-shown'); }
            });
        });
    }

    function closeMyp() {
        if (!isMypOpen()) { return; }
        titleBtn.classList.remove('is-open');
        titleBtn.setAttribute('aria-expanded', 'false');
        myp.classList.remove('is-shown');
        mypBackdrop.classList.remove('is-shown');
        mypCloseTimer = setTimeout(function () {
            myp.classList.remove('is-mounted');
            mypBackdrop.classList.remove('is-mounted');
        }, CLOSE_MS);
    }

    titleBtn.addEventListener('click', function () {
        if (isMypOpen()) { closeMyp(); } else { openMyp(); }
    });
    mypClose.addEventListener('click', closeMyp);
    mypBackdrop.addEventListener('click', closeMyp);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeMyp(); } });
    mypGo.addEventListener('click', function () {
        window.location.href = calendarBaseUrl + '?month=' + mypMonth.value + '&year=' + mypYear.value;
    });
})();
</script>
@endpush
