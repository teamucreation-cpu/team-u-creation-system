@extends('layouts.app')

@section('title', 'Team U Creation — Event Coordination')

@section('content')
<style>
    /* ============ Landing page — true luxury black & metallic gold ============
       Palette is restricted to #F4C95D / #D4AF37 / #C9A227 on #0A0A0A / #121212.
       No neon/lemon yellow, no colored glow — shadows stay neutral (black),
       borders and gradient text carry the gold. */
    .lp-heading { font-family: 'Poppins', sans-serif; }

    .lp-gold-text {
        background: linear-gradient(90deg, var(--lux-gold) 0%, var(--lux-gold-mid) 55%, var(--lux-gold-deep) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: var(--lux-gold-mid); /* fallback for browsers without background-clip:text */
        -webkit-text-fill-color: transparent;
    }

    /* ---- Hero ---- */
    .lp-hero {
        text-align: center;
        padding: 4.5rem 1rem 3.5rem;
    }

    .lp-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--lux-gold);
        text-transform: uppercase;
        letter-spacing: 2.5px;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid rgba(201, 162, 39, 0.4);
        background: rgba(201, 162, 39, 0.06);
        padding: 0.4rem 1.1rem;
        border-radius: 999px;
        margin-bottom: 1.75rem;
    }
    .lp-eyebrow i { font-size: 0.5rem; }

    .lp-hero h1 {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: clamp(2.3rem, 5.5vw, 3.9rem);
        line-height: 1.12;
        letter-spacing: -0.5px;
        margin: 0 0 1.5rem;
        color: var(--lux-white);
    }
    .lp-hero p.lead {
        color: var(--lux-gray);
        max-width: 600px;
        font-size: 1.05rem;
        margin: 0 auto 2.25rem;
    }

    .lp-divider {
        width: 56px;
        height: 2px;
        background: linear-gradient(90deg, var(--lux-gold), var(--lux-gold-deep));
        margin: 0 auto 2rem;
        border-radius: 2px;
    }

    .lp-btn-lg { padding: 0.75rem 1.9rem; font-size: 1rem; border-radius: 8px; }

    /* ---- Scroll reveal (opacity/position only — no glow) ---- */
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.in-view { opacity: 1; transform: translateY(0); }

    @keyframes lpFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .lp-fade-1, .lp-fade-2, .lp-fade-3, .lp-fade-4 {
        opacity: 0;
        animation: lpFadeUp 0.7s ease forwards;
    }
    .lp-fade-1 { animation-delay: 0.05s; }
    .lp-fade-2 { animation-delay: 0.18s; }
    .lp-fade-3 { animation-delay: 0.32s; }
    .lp-fade-4 { animation-delay: 0.46s; }

    /* ---- Section chrome ---- */
    .lp-section { padding: 5.5rem 0 0; }
    .lp-section-title {
        text-align: center;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: clamp(1.5rem, 3vw, 2rem);
        color: var(--lux-white);
        margin-bottom: 0.6rem;
    }
    .lp-section-sub {
        text-align: center;
        color: var(--lux-gray);
        max-width: 560px;
        margin: 0 auto 3rem;
    }
    .lp-eyebrow-mini {
        display: block;
        text-align: center;
        color: var(--lux-gold-mid);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.72rem;
        font-weight: 700;
        margin-bottom: 0.6rem;
    }

    /* ---- Feature cards ---- */
    .lp-feature-card {
        background: var(--lux-bg2);
        border: 1px solid rgba(201, 162, 39, 0.22);
        border-radius: 14px;
        padding: 2rem 1.4rem;
        text-align: center;
        height: 100%;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .lp-feature-card:hover {
        transform: translateY(-6px);
        border-color: var(--lux-gold-mid);
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.45);
    }
    .lp-feature-card .icon {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: 1.5px solid rgba(201, 162, 39, 0.55);
        background: rgba(201, 162, 39, 0.07);
        color: var(--lux-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin: 0 auto 1.1rem;
    }
    .lp-feature-card h6 { color: var(--lux-white); font-weight: 700; margin-bottom: 0.5rem; }
    .lp-feature-card p { color: var(--lux-gray); font-size: 0.88rem; margin: 0; }

    /* ---- How it works — connected timeline ---- */
    .lp-steps-row { position: relative; }
    .lp-steps-row::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 12%;
        right: 12%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(201, 162, 39, 0.45) 15%, rgba(201, 162, 39, 0.45) 85%, transparent);
        z-index: 0;
    }
    .lp-step-card { text-align: center; padding: 0 1rem; position: relative; z-index: 1; }
    .lp-step-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--lux-gold) 0%, var(--lux-gold-deep) 100%);
        color: #0A0A0A;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0 auto 1.1rem;
        border: 3px solid var(--lux-bg);
        box-shadow: 0 0 0 1px rgba(201, 162, 39, 0.45);
    }
    .lp-step-card h6 { color: var(--lux-white); font-weight: 700; margin-bottom: 0.4rem; }
    .lp-step-card p { color: var(--lux-gray); font-size: 0.85rem; margin: 0; }

    /* ---- Packages ---- */
    .lp-package-card {
        background: var(--lux-bg2);
        border: 1px solid rgba(201, 162, 39, 0.22);
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .lp-package-card:hover {
        transform: translateY(-5px);
        border-color: var(--lux-gold-mid);
        box-shadow: 0 20px 38px rgba(0, 0, 0, 0.5);
    }
    .lp-package-accent { height: 3px; background: linear-gradient(90deg, var(--lux-gold), var(--lux-gold-deep)); }

    /* Placeholder "package image" panel — no photo library yet, so a
       branded gold-on-black motif fills the same visual real estate. */
    .lp-package-media {
        position: relative;
        height: 150px;
        overflow: hidden;
        background:
            repeating-linear-gradient(135deg, rgba(201, 162, 39, 0.05) 0px, rgba(201, 162, 39, 0.05) 1px, transparent 1px, transparent 26px),
            linear-gradient(160deg, #171717 0%, #0d0d0d 100%);
        border-bottom: 1px solid rgba(201, 162, 39, 0.18);
    }
    .lp-package-media .icon-wrap {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.6rem;
        color: var(--lux-gold-mid);
        opacity: 0.9;
        transition: transform 0.4s ease;
    }
    .lp-package-card:hover .lp-package-media .icon-wrap { transform: scale(1.1); }

    .lp-package-body { padding: 1.5rem 1.5rem 1.6rem; display: flex; flex-direction: column; flex: 1; }
    .lp-package-body h5 { color: var(--lux-white); font-family: 'Poppins', sans-serif; font-weight: 700; margin-bottom: 0.6rem; }
    .lp-package-body .desc { color: var(--lux-gray); font-size: 0.87rem; margin-bottom: 1.1rem; }
    .lp-package-body .inclusions { list-style: none; padding: 0; margin: 0 0 1.4rem; flex: 1; }
    .lp-package-body .inclusions li {
        color: var(--lux-gray);
        font-size: 0.85rem;
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        margin-bottom: 0.5rem;
    }
    .lp-package-body .inclusions li i { color: var(--lux-gold-mid); margin-top: 0.2rem; font-size: 0.78rem; }
    .lp-package-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid rgba(201, 162, 39, 0.18);
        padding-top: 1.1rem;
    }
    .lp-package-price { font-weight: 800; font-size: 1.3rem; font-family: 'Poppins', sans-serif; }

    /* ---- Testimonials ---- */
    .lp-rating-summary { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
    .lp-rating-summary .avg-number { color: var(--lux-white); font-weight: 800; font-size: 1.5rem; font-family: 'Poppins', sans-serif; }
    .lp-rating-summary .stars i { color: var(--lux-gold); font-size: 1rem; }
    .lp-rating-summary .total-count { color: var(--lux-gray); font-size: 0.88rem; }

    .lp-testimonial-card {
        background: var(--lux-bg2);
        border: 1px solid rgba(201, 162, 39, 0.2);
        border-radius: 14px;
        padding: 1.85rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .lp-testimonial-card:hover {
        transform: translateY(-5px);
        border-color: var(--lux-gold-mid);
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.4);
    }
    .lp-testimonial-card .quote-icon { color: rgba(201, 162, 39, 0.35); font-size: 1.5rem; margin-bottom: 0.5rem; }
    .lp-testimonial-card .stars i { color: var(--lux-gold); font-size: 0.9rem; }
    .lp-testimonial-card .feedback { color: var(--lux-gray); font-size: 0.92rem; flex-grow: 1; margin: 0.85rem 0 1.35rem; }
    .lp-testimonial-card .reviewer { display: flex; align-items: center; gap: 0.75rem; border-top: 1px solid rgba(201, 162, 39, 0.18); padding-top: 1rem; }
    .lp-testimonial-card .avatar-sm {
        width: 42px; height: 42px; border-radius: 50%;
        background: rgba(201, 162, 39, 0.1);
        border: 1.5px solid rgba(201, 162, 39, 0.5);
        color: var(--lux-gold);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Poppins', sans-serif;
        font-weight: 700; flex: 0 0 auto;
    }
    .lp-testimonial-card .name { color: var(--lux-white); font-weight: 700; font-size: 0.9rem; }
    .lp-testimonial-card .meta { color: var(--lux-gold-mid); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .lp-testimonial-card .review-date { color: var(--lux-gray); font-size: 0.72rem; margin-left: auto; align-self: flex-start; opacity: 0.7; }

    /* ---- CTA band ---- */
    .lp-cta {
        background: var(--lux-bg2);
        border: 1px solid rgba(201, 162, 39, 0.3);
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        margin: 5.5rem 0 4.5rem;
    }
    .lp-cta-icon {
        width: 60px; height: 60px; border-radius: 50%;
        border: 1.5px solid rgba(201, 162, 39, 0.5);
        background: rgba(201, 162, 39, 0.07);
        color: var(--lux-gold);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin: 0 auto 1.25rem;
    }
    .lp-cta h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: clamp(1.5rem, 3.5vw, 2.1rem);
        color: var(--lux-white);
        margin-bottom: 0.5rem;
    }
    .lp-cta p { color: var(--lux-gray); margin-bottom: 2rem; }

    @media (max-width: 767.98px) {
        .lp-steps-row::before { display: none; }
        .lp-section { padding-top: 4rem; }
    }

    /* ---- Footer ---- */
    .lp-footer-wrap { margin: 5.5rem 0 0; }

    .lp-footer {
        position: relative;
        background: linear-gradient(180deg, rgba(18, 18, 18, 0.92) 0%, rgba(10, 10, 10, 0.98) 100%);
        border: 1px solid var(--lux-gold-soft);
        border-radius: 22px;
        padding: 3rem 2.25rem 2.25rem;
        overflow: hidden;
        backdrop-filter: blur(14px) saturate(140%);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        box-shadow: 0 0 50px rgba(244, 201, 93, 0.06), 0 20px 45px rgba(0, 0, 0, 0.5);
    }

    .lp-footer-glow {
        position: absolute;
        top: -120px;
        left: 50%;
        transform: translateX(-50%);
        width: 480px;
        height: 240px;
        background: radial-gradient(ellipse at center, rgba(244, 201, 93, 0.14) 0%, transparent 70%);
        pointer-events: none;
    }

    .lp-footer-col { position: relative; z-index: 1; }

    @media (min-width: 992px) {
        .lp-footer-col.has-divider { border-left: 1px solid rgba(201, 162, 39, 0.18); padding-left: 2rem; }
    }

    .lp-footer-logo-ring {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        overflow: hidden;
        border: 1.5px solid rgba(201, 162, 39, 0.55);
        margin-bottom: 1rem;
    }

    .lp-footer-brand-name {
        color: var(--lux-gold);
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        letter-spacing: 1.5px;
        font-size: 1.05rem;
        margin-bottom: 0.15rem;
    }

    .lp-footer-tagline {
        color: var(--lux-gold-mid);
        font-style: italic;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 0.9rem;
    }

    .lp-footer-desc {
        color: var(--lux-gray);
        font-size: 0.86rem;
        line-height: 1.6;
        margin-bottom: 1.4rem;
        max-width: 320px;
    }

    .lp-footer-socials { display: flex; gap: 0.65rem; }

    .lp-footer-social-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1.5px solid rgba(201, 162, 39, 0.4);
        background: rgba(244, 201, 93, 0.05);
        color: var(--lux-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        text-decoration: none;
        transition: transform 0.22s ease, background 0.22s ease, box-shadow 0.22s ease, color 0.22s ease;
    }

    .lp-footer-social-btn:hover {
        background: var(--lux-gold);
        color: #0A0A0A;
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(244, 201, 93, 0.25);
    }

    .lp-footer-heading {
        color: var(--lux-white);
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 1.25rem;
        position: relative;
        padding-bottom: 0.7rem;
    }

    .lp-footer-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 32px;
        height: 2px;
        background: linear-gradient(90deg, var(--lux-gold), var(--lux-gold-deep));
        border-radius: 2px;
    }

    .lp-footer-links { list-style: none; padding: 0; margin: 0; }
    .lp-footer-links li { margin-bottom: 0.75rem; }
    .lp-footer-links li:last-child { margin-bottom: 0; }
    .lp-footer-links a {
        color: var(--lux-gray);
        text-decoration: none;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: color 0.22s ease, transform 0.22s ease;
    }
    .lp-footer-links a:hover { color: var(--lux-gold); transform: translateX(4px); }

    .lp-footer-contact-list { list-style: none; padding: 0; margin: 0; }
    .lp-footer-contact-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .lp-footer-contact-list li:last-child { margin-bottom: 0; }
    .lp-footer-contact-list .fi-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(244, 201, 93, 0.1);
        color: var(--lux-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex: 0 0 auto;
        margin-top: 0.05rem;
    }
    .lp-footer-contact-list a,
    .lp-footer-contact-list span.plain {
        color: var(--lux-gray);
        text-decoration: none;
        font-size: 0.86rem;
        line-height: 1.5;
        transition: color 0.22s ease;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .lp-footer-contact-list a:hover { color: var(--lux-gold); }

    .lp-footer-hours-desc {
        color: var(--lux-gray);
        font-size: 0.85rem;
        line-height: 1.6;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(201, 162, 39, 0.15);
    }

    /* Bottom bar breaks out of the centered container to sit truly full-width. */
    .lp-footer-bottom-wrap {
        margin: 0 calc(50% - 50vw);
        margin-top: 1.75rem;
        border-top: 1px solid var(--lux-gold-soft);
        padding: 1.25rem 0;
    }
    .lp-footer-bottom {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 1.5rem;
        color: var(--lux-gray);
        font-size: 0.82rem;
        text-align: center;
    }
    .lp-footer-bottom .heart { color: #e0577a; }

    @media (max-width: 767.98px) {
        .lp-footer { padding: 2.25rem 1.5rem 1.75rem; border-radius: 18px; }
        .lp-footer-col.has-divider { border-left: none; padding-left: 0; }
        .lp-footer-bottom { justify-content: center; text-align: center; }
    }
</style>

<div class="lp-hero" id="top">
    <div class="lp-eyebrow lp-fade-1"><i class="fas fa-circle"></i> Iriga City, Camarines Sur</div>
    <h1 class="lp-fade-2">Your Event, <span class="lp-gold-text">Perfectly Coordinated</span></h1>
    <div class="lp-divider lp-fade-2"></div>
    <p class="lead lp-fade-3">
        Transform your event planning experience with a smarter way to book, schedule, and coordinate.
        We help make every event memorable, from intimate celebrations to grand occasions.
    </p>
    <div class="d-flex gap-2 justify-content-center flex-wrap lp-fade-4">
        <a href="{{ route('login') }}" class="btn tu-btn-gold-lux lp-btn-lg">Get Started</a>
    </div>
</div>

@if($approvedReviews->isNotEmpty())
<div class="lp-section" id="testimonials">
    <span class="lp-eyebrow-mini reveal">Client Love</span>
    <h4 class="lp-section-title reveal">What Our Clients Say</h4>
    <div class="lp-rating-summary reveal">
        <span class="avg-number">{{ $reviewStats['average'] }}</span>
        <span class="stars">
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star" style="{{ $i > round($reviewStats['average']) ? 'color: rgba(201,162,39,0.25);' : '' }}"></i>
            @endfor
        </span>
        <span class="total-count">out of 5 &middot; based on {{ $reviewStats['total'] }} {{ Str::plural('review', $reviewStats['total']) }}</span>
    </div>
    <p class="lp-section-sub reveal">Real feedback from clients whose events we've coordinated.</p>

    <div class="row g-4 reveal">
        @foreach($approvedReviews as $review)
        <div class="col-lg-4 col-md-6">
            <div class="lp-testimonial-card">
                <i class="fas fa-quote-left quote-icon"></i>
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="{{ $i > $review->rating ? 'color: rgba(201,162,39,0.25);' : '' }}"></i>
                    @endfor
                </div>
                @if($review->title)
                    <h6 style="color: var(--lux-white); font-weight: 700; font-size: 0.95rem; margin: 0.5rem 0 0;">{{ $review->title }}</h6>
                @endif
                <p class="feedback">"{{ Str::limit($review->feedback, 180) }}"</p>
                <div class="reviewer">
                    <div class="avatar-sm">{{ strtoupper(substr($review->client->user->name, 0, 1)) }}</div>
                    <div>
                        <div class="name">{{ $review->client->user->name }}</div>
                        <div class="meta">{{ $review->booking->event_type }} Client</div>
                    </div>
                    <div class="review-date">{{ $review->created_at->format('M Y') }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="lp-footer-wrap">
    <footer class="lp-footer reveal">
        <div class="lp-footer-glow"></div>
        <div class="row g-4">
            {{-- Column 1 — Company Information --}}
            <div class="col-lg-4 col-md-6 lp-footer-col">
                <div class="lp-footer-logo-ring">@include('layouts.partials.brand-mark')</div>
                <div class="lp-footer-brand-name">TEAM U CREATION</div>
                <div class="lp-footer-tagline">"We Create, You Celebrate."</div>
                <p class="lp-footer-desc">We help turn your vision into reality by providing seamless event planning, coordination, and management services for unforgettable celebrations.</p>
            </div>

            {{-- Column 2 — Contact Information --}}
            <div class="col-lg-4 col-md-6 lp-footer-col has-divider" id="footer-contact">
                <div class="lp-footer-heading">Contact Us</div>
                <ul class="lp-footer-contact-list">
                    <li>
                        <span class="fi-icon"><i class="fas fa-location-dot"></i></span>
                        <a href="https://www.google.com/maps/search/?api=1&query=Iriga+City,+Camarines+Sur" target="_blank" rel="noopener">Iriga City, Camarines Sur</a>
                    </li>
                    <li>
                        <span class="fi-icon"><i class="fas fa-phone"></i></span>
                        <a href="tel:+639123456789">+63 912 345 6789</a>
                    </li>
                    <li>
                        <span class="fi-icon"><i class="fas fa-envelope"></i></span>
                        <a href="mailto:teamucreation@gmail.com">teamucreation@gmail.com</a>
                    </li>
                    <li>
                        <span class="fi-icon"><i class="fas fa-globe"></i></span>
                        <a href="https://www.facebook.com/TEAMUCreations" target="_blank" rel="noopener">facebook.com/TEAMUCreations</a>
                    </li>
                </ul>
            </div>

            {{-- Column 3 — Business Hours --}}
            <div class="col-lg-4 col-md-6 lp-footer-col has-divider">
                <div class="lp-footer-heading">Business Hours</div>
                <ul class="lp-footer-contact-list">
                    <li>
                        <span class="fi-icon"><i class="fas fa-calendar-days"></i></span>
                        <span class="plain">Monday &ndash; Sunday</span>
                    </li>
                    <li>
                        <span class="fi-icon"><i class="fas fa-clock"></i></span>
                        <span class="plain">8:00 AM &ndash; 8:00 PM</span>
                    </li>
                </ul>
                <p class="lp-footer-hours-desc">We're here to help you plan your perfect event.</p>
            </div>
        </div>
    </footer>

    <div class="lp-footer-bottom-wrap">
        <div class="lp-footer-bottom">
            <span>&copy; {{ date('Y') }} Team U Creation. All Rights Reserved.</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var targets = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window) || !targets.length) {
            targets.forEach(function (el) { el.classList.add('in-view'); });
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
        targets.forEach(function (el) { observer.observe(el); });
    })();
</script>
@endpush
