<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Team U Creation')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --tu-black: #1a1a1a;
            --tu-gold: #d4a017;
            --tu-gold-light: #f0d060;
            --tu-gold-dark: #b8960c;
            --tu-navy: #0f172a;
            --tu-navy-light: #1a2540;
            --tu-dark: #0f172a;
            --tu-gray: #24304d;
            --tu-light: #f8f9fa;
            --tu-gold-premium: #FFD54D;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--tu-navy);
            color: #e2e6f0;
            --bs-body-color: #e2e6f0;
            --bs-body-color-rgb: 226, 230, 240;
            --bs-secondary-color: #8a93a8;
            min-height: 100vh;
        }

        /* Bootstrap components (form fields, tables, dropdowns, .text-muted)
           read --bs-body-color/--bs-secondary-color directly rather than plain
           inheritance, so every white surface re-pins them back to dark text. */
        .stat-card, .table-custom, .package-card, .feature-card,
        .dropdown-menu, .modal-content, .list-group-item,
        .form-control, .form-select, textarea {
            color: var(--tu-black);
            --bs-body-color: #1a1a1a;
            --bs-secondary-color: rgba(33, 37, 41, 0.75);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--tu-navy);
            color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            border-right: 1px solid rgba(212, 160, 23, 0.15);
        }

        .sidebar-brand {
            flex: 0 0 auto;
            padding: 1.75rem 1.25rem 1.5rem;
            border-bottom: 1px solid var(--tu-gray);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar-brand .brand-mark {
            width: 96px;
            height: 96px;
            margin: 0 auto 0.85rem;
            border-radius: 50%;
            overflow: hidden;
            background: #fff;
            border: 2px solid rgba(255, 213, 77, 0.55);
            box-shadow: 0 0 0 4px rgba(255, 213, 77, 0.08), 0 4px 14px rgba(0, 0, 0, 0.35);
            flex-shrink: 0;
        }

        .sidebar-brand .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .sidebar-brand h4 {
            color: var(--tu-gold-premium);
            font-weight: 800;
            margin: 0;
            font-size: 1.3rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .sidebar-brand .brand-tagline {
            margin-top: 0.65rem;
        }

        .sidebar-brand .tagline-line {
            margin: 0;
            line-height: 1.35;
        }

        .sidebar-brand .tagline-white {
            color: #fff;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .sidebar-brand .tagline-gold {
            color: var(--tu-gold-premium);
            font-style: italic;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .sidebar-nav {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem 0;
            /* Scrollbar hidden, scrolling behavior (wheel/touch/keyboard)
               untouched — only overflow-y:auto above controls that, and it's
               unchanged. Layout, width, and nav items are untouched too. */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE / legacy Edge */
        }
        .sidebar-nav::-webkit-scrollbar { /* Chrome, Safari, modern Edge */
            display: none;
        }

        .sidebar-nav .nav-label {
            color: var(--tu-gold);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0.75rem 1.25rem 0.25rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 0.6rem 1.25rem;
            color: #ccc;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: var(--tu-gray);
            color: var(--tu-gold);
            border-left-color: var(--tu-gold);
        }

        .sidebar-nav a.active {
            background: var(--tu-gray);
            color: var(--tu-gold);
            border-left-color: var(--tu-gold);
            font-weight: 600;
        }

        .sidebar-nav a i {
            width: 24px;
            margin-right: 10px;
            text-align: center;
            font-size: 0.85rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Navbar */
        .top-navbar {
            background: transparent;
            padding: 1.1rem 1.5rem 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            max-width: 100%;
        }

        /* Flex items default to min-width:auto, so unbreakable text (a long
           title, an email/role string) refuses to shrink and pushes the
           whole navbar past the viewport. Let them shrink and truncate instead. */
        .top-navbar > div {
            min-width: 0;
        }

        .top-navbar h5 {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .top-navbar h5 {
            color: #fff !important;
        }

        .top-navbar .sidebar-toggle {
            background: transparent;
            color: var(--tu-gold);
        }

        .top-navbar .notif-bell {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .top-navbar .notif-bell:hover {
            background: rgba(212, 160, 23, 0.18);
            color: var(--tu-gold);
        }

        .top-navbar .notif-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--tu-gold);
            color: var(--tu-dark);
            font-size: 0.65rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
        }

        .top-navbar .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .top-navbar .user-info .user-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .top-navbar .user-info .user-text .name {
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .top-navbar .user-info .user-text .role {
            color: var(--tu-gold);
            font-size: 0.72rem;
            text-transform: capitalize;
        }

        .top-navbar .user-info .avatar {
            width: 40px;
            height: 40px;
            background: var(--tu-gold);
            color: var(--tu-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            border: 2px solid var(--tu-gold-light);
        }

        .top-navbar .user-dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.25rem 0.5rem 0.25rem 0.25rem;
            border-radius: 999px;
        }

        .top-navbar .user-dropdown-toggle:hover {
            background: rgba(255,255,255,0.06);
        }

        .top-navbar .user-dropdown-toggle::after {
            color: #8a93a8;
        }

        .page-content {
            padding: 0.5rem 1.5rem 1.5rem;
        }

        /* Cards */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem;
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--tu-black);
        }

        .stat-card .stat-label {
            color: #777;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Clickable dashboard stat cards */
        a.stat-card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            border-radius: 16px;
        }

        a.stat-card-link .stat-card {
            cursor: pointer;
            height: 100%;
        }

        a.stat-card-link:hover .stat-card {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.28);
        }

        a.stat-card-link:active .stat-card {
            transform: translateY(-1px) scale(1.01);
        }

        a.stat-card-link:focus-visible {
            outline: 2px solid var(--tu-gold);
            outline-offset: 3px;
        }

        /* List toolbars (search + filters) above data tables */
        .list-toolbar {
            background: #fff;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            margin-bottom: 1rem;
        }

        .list-toolbar .form-control,
        .list-toolbar .form-select {
            color: var(--tu-black);
        }

        .pagination .page-link {
            color: var(--tu-gold-dark);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--tu-gold);
            border-color: var(--tu-gold);
            color: var(--tu-dark);
        }

        .pagination .page-link:focus {
            box-shadow: 0 0 0 0.2rem rgba(212, 160, 23, 0.25);
        }

        /* Status badges */
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-declined { background: #f8d7da; color: #721c24; }
        .badge-rescheduled { background: #d1ecf1; color: #0c5460; }
        .badge-in-progress { background: #e2d9f3; color: #4a1d96; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #e2e3e5; color: #383d41; }
        .badge-available { background: #d4edda; color: #155724; }
        .badge-low-stock { background: #fff3cd; color: #856404; }
        .badge-out-of-stock { background: #f8d7da; color: #721c24; }
        .badge-in-use { background: #d1ecf1; color: #0c5460; }
        .badge-damaged { background: #f5c6cb; color: #58151c; }
        .badge-returned { background: #e2e3e5; color: #383d41; }
        .badge-rejected { background: #f8d7da; color: #721c24; }

        /* Buttons */
        .btn-gold {
            background: var(--tu-gold);
            color: var(--tu-dark);
            border: none;
            font-weight: 600;
        }

        .btn-gold:hover {
            background: var(--tu-gold-dark);
            color: #fff;
        }

        .btn-outline-gold {
            border: 2px solid var(--tu-gold);
            color: var(--tu-gold);
            background: transparent;
            font-weight: 600;
        }

        .btn-outline-gold:hover {
            background: var(--tu-gold);
            color: var(--tu-dark);
        }

        /* Tables */
        .table-custom {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }

        .table-custom thead {
            background: var(--tu-dark);
            color: var(--tu-gold);
        }

        .table-custom thead th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            border: none;
        }

        .table-custom tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Mobile sidebar toggle */
        .sidebar-toggle {
            display: none;
            background: var(--tu-dark);
            color: var(--tu-gold);
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 5px;
            font-size: 1.2rem;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: inline-block;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Small screens: the top navbar's title + notif bell + user info row
           doesn't wrap, so it overflows and forces the whole page to scroll
           horizontally. Trim it down before that point is reached. */
        @media (max-width: 575.98px) {
            .top-navbar {
                padding: 1rem 1rem 0.5rem;
                gap: 0.5rem;
            }

            .top-navbar .user-info .user-text {
                display: none;
            }
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Hide the visible scrollbar on the main page (Client and Admin both
           share this layout, so one rule covers both) while leaving
           normal wheel/touch/keyboard scrolling fully intact — this only
           affects the scrollbar's own visual chrome, not whether the page
           can scroll. The sidebar's own internal scrollbar (.sidebar-nav)
           gets the identical treatment where it's defined above — its
           layout, width, and nav items are untouched either way. */
        html, body {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE / legacy Edge */
        }
        html::-webkit-scrollbar, body::-webkit-scrollbar { /* Chrome, Safari, modern Edge */
            display: none;
        }

        /* Calendar styles */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }

        .calendar-header-cell {
            background: var(--tu-dark);
            color: var(--tu-gold);
            padding: 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .calendar-cell {
            background: #fff;
            min-height: 80px;
            padding: 0.25rem;
            position: relative;
        }

        .calendar-cell.today {
            background: #fffef5;
            border: 2px solid var(--tu-gold);
        }

        .calendar-cell.other-month {
            background: #f9f9f9;
            color: #ccc;
        }

        .calendar-cell .day-number {
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.15rem 0.35rem;
        }

        .calendar-event {
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
            border-radius: 3px;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== Guest marketing shell (landing / login / register) =====
           Scoped to .tu-guest-shell so the pure-black/gold luxury palette
           never bleeds into the authenticated navy dashboard. */
        .tu-guest-shell {
            --lux-bg: #0A0A0A;
            --lux-bg2: #121212;
            --lux-gold: #F4C95D;
            --lux-gold-mid: #D4AF37;
            --lux-gold-deep: #C9A227;
            --lux-gold-soft: rgba(244, 201, 93, 0.35);
            --lux-gold-dim: rgba(244, 201, 93, 0.12);
            --lux-white: #FFFFFF;
            --lux-gray: #D1D5DB;
            background: var(--lux-bg);
            color: var(--lux-white);
            font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
        }

        .tu-guest-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(10, 10, 10, 0.72);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border-bottom: 1px solid rgba(201, 162, 39, 0.22);
        }

        .tu-guest-nav .container {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
        }

        .tu-guest-nav .navbar-brand { justify-self: start; }
        .tu-guest-nav .tu-guest-nav-links { justify-self: center; }
        .tu-guest-nav .tu-guest-nav-actions { justify-self: end; }

        /* Below lg the centered nav links are hidden anyway (d-none d-lg-flex),
           so fall back to a wrap-friendly flex row instead of a rigid 3-col grid. */
        @media (max-width: 991.98px) {
            .tu-guest-nav .container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
            }
        }

        .tu-guest-brand-ring {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: inline-block;
            border: 1.5px solid rgba(201, 162, 39, 0.5);
        }

        .tu-guest-brand-text {
            color: var(--lux-gold);
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            letter-spacing: 1.5px;
            font-size: 0.95rem;
        }

        .tu-guest-nav .nav-link-lux {
            color: var(--lux-gray);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            padding: 0.4rem 0.1rem;
            position: relative;
            transition: color 0.2s ease;
        }

        .tu-guest-nav .nav-link-lux::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 0;
            height: 2px;
            background: var(--lux-gold);
            transition: width 0.25s ease;
        }

        .tu-guest-nav .nav-link-lux:hover {
            color: var(--lux-gold);
        }

        .tu-guest-nav .nav-link-lux:hover::after {
            width: 100%;
        }

        .tu-btn-gold-lux {
            background: linear-gradient(135deg, var(--lux-gold) 0%, var(--lux-gold-mid) 100%);
            color: #0A0A0A !important;
            border: 1px solid var(--lux-gold-mid);
            font-weight: 700;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
        }

        .tu-btn-gold-lux:hover {
            background: linear-gradient(135deg, var(--lux-gold-mid) 0%, var(--lux-gold-deep) 100%);
            border-color: var(--lux-gold-deep);
            color: #0A0A0A !important;
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.4);
            transform: translateY(-2px);
        }

        .tu-btn-outline-lux {
            border: 1.5px solid rgba(201, 162, 39, 0.55);
            color: var(--lux-gold) !important;
            background: #0A0A0A;
            font-weight: 600;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease, background 0.22s ease;
        }

        .tu-btn-outline-lux:hover {
            border-color: var(--lux-gold);
            background: rgba(244, 201, 93, 0.07);
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.4);
            transform: translateY(-2px);
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
        @php
            $__role = Auth::user()->role;
            $__profileRoute = match($__role) { 'admin' => 'admin.profile.edit', default => 'client.profile.edit' };
            $__notifRoute = match($__role) { 'admin' => 'admin.notifications.index', default => 'client.notifications.index' };
            $__unreadCount = Auth::user()->appNotifications()->where('status', 'unread')->count();
        @endphp
        {{-- Sidebar --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark">
                    @include('layouts.partials.brand-mark')
                </div>
                <h4>Team U Creation</h4>
                <div class="brand-tagline">
                    <p class="tagline-line tagline-white">We Create,</p>
                    <p class="tagline-line tagline-gold">You Celebrate.</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                @if(Auth::user()->isAdmin())
                    @include('layouts.partials.admin-nav')
                @else
                    @include('layouts.partials.client-nav')
                @endif
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="main-content">
            <div class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0" style="font-weight: 600;">@yield('page-title', 'Dashboard')</h5>
                </div>
                <div class="user-info">
                    <a href="{{ route($__notifRoute) }}" class="notif-bell" title="Notifications">
                        <i class="fas fa-bell"></i>
                        @if($__unreadCount > 0)
                            <span class="notif-badge">{{ $__unreadCount > 9 ? '9+' : $__unreadCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown">
                        <button class="user-dropdown-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            <span class="user-text d-none d-md-flex">
                                <span class="name">{{ Auth::user()->name }}</span>
                                <span class="role">{{ $__role }}</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route($__profileRoute) }}"><i class="fas fa-user me-2 text-muted"></i> My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    @else
        {{-- Guest layout (landing / login / register pages) --}}
        <div class="min-vh-100 d-flex flex-column tu-guest-shell">
            <nav class="navbar tu-guest-nav">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center gap-2 mb-0" href="{{ url('/') }}">
                        <span class="tu-guest-brand-ring">@include('layouts.partials.brand-mark')</span>
                        <span class="tu-guest-brand-text">TEAM U CREATION</span>
                    </a>
                    <div class="tu-guest-nav-links">
                        @if(request()->is('/'))
                            <div class="d-none d-lg-flex align-items-center gap-4">
                                <a href="#top" class="nav-link-lux">Home</a>
                                <a href="#testimonials" class="nav-link-lux">Testimonials</a>
                                <a href="#footer-contact" class="nav-link-lux">Contact Us</a>
                            </div>
                        @endif
                    </div>
                    <div class="tu-guest-nav-actions d-flex align-items-center gap-2">
                        <a href="{{ route('login') }}" class="btn btn-sm tu-btn-outline-lux">Log In</a>
                        <a href="{{ route('register') }}" class="btn btn-sm tu-btn-gold-lux">Register</a>
                    </div>
                </div>
            </nav>
            <div class="flex-grow-1 d-flex align-items-center">
                <div class="container py-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    @endauth

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
    @stack('scripts')
</body>
</html>
