@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
    .breadcrumb-gold {
        font-size: 0.85rem;
        color: #8a93a8;
    }
    .breadcrumb-gold .current {
        color: var(--tu-gold);
        font-weight: 600;
    }
    .page-heading {
        color: #fff;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    .page-heading-underline {
        width: 54px;
        height: 3px;
        background: var(--tu-gold);
        border-radius: 2px;
        margin-bottom: 1.5rem;
    }

    .profile-card {
        background: linear-gradient(180deg, #16213e 0%, var(--tu-navy) 100%);
        border: 1px solid rgba(212, 160, 23, 0.25);
        border-radius: 18px;
        padding: 1.75rem 1.5rem;
        text-align: center;
        box-shadow: 0 10px 28px rgba(0,0,0,0.35);
    }

    .avatar-xl-wrap {
        position: relative;
        width: 110px;
        height: 110px;
        margin: 0 auto 1rem;
    }

    .avatar-xl {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 3px solid var(--tu-gold);
        background: #0b1120;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.6rem;
        font-weight: 700;
        color: var(--tu-gold);
        font-family: Georgia, 'Times New Roman', serif;
    }

    .profile-card .full-name {
        color: #fff;
        font-weight: 700;
        margin-bottom: 0.1rem;
    }

    .profile-card .role-badge {
        color: var(--tu-gold);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .profile-card .id-divider {
        border-top: 1px solid rgba(212, 160, 23, 0.2);
        margin: 1.1rem 0;
    }

    .profile-card .meta-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        text-align: left;
        background: rgba(255,255,255,0.04);
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        margin-bottom: 0.6rem;
    }

    .profile-card .meta-row .meta-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(212, 160, 23, 0.15);
        color: var(--tu-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .profile-card .meta-row .meta-label {
        color: #8a93a8;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-card .meta-row .meta-value {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .motivational-card {
        background: linear-gradient(135deg, #16213e 0%, var(--tu-navy) 100%);
        border: 1px solid rgba(212, 160, 23, 0.2);
        border-radius: 18px;
        padding: 1.5rem;
        margin-top: 1.25rem;
        position: relative;
        overflow: hidden;
    }

    .motivational-card .deco-icon {
        position: absolute;
        color: rgba(212, 160, 23, 0.18);
        font-size: 2.5rem;
    }

    .motivational-card .deco-1 { top: 10px; right: 18px; }
    .motivational-card .deco-2 { bottom: 8px; right: 60px; font-size: 1.4rem; }
    .motivational-card .deco-3 { top: 50%; right: 10px; font-size: 1rem; }

    .motivational-card h6 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .motivational-card .gold-script {
        color: var(--tu-gold);
        font-style: italic;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .motivational-card p.desc {
        color: #8a93a8;
        font-size: 0.85rem;
        margin-bottom: 0;
        max-width: 75%;
    }

    .gold-heading {
        color: var(--tu-black);
        font-weight: 700;
    }

    .gold-heading .fa-icon-badge {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: rgba(212, 160, 23, 0.12);
        color: var(--tu-gold);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.5rem;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 0.25rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child { border-bottom: none; }

    .info-row .info-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: rgba(212, 160, 23, 0.1);
        color: var(--tu-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .info-row .info-label {
        color: #999;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .info-row .info-value {
        color: var(--tu-black);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .settings-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 0.5rem;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: background 0.15s;
    }

    .settings-row:hover {
        background: #fbf6e9;
    }

    .settings-row .settings-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: rgba(212, 160, 23, 0.1);
        color: var(--tu-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .settings-row .settings-title {
        color: var(--tu-black);
        font-weight: 600;
        font-size: 0.92rem;
        margin-bottom: 0;
    }

    .settings-row .settings-desc {
        color: #999;
        font-size: 0.78rem;
    }

    .settings-row .settings-arrow {
        margin-left: auto;
        color: #ccc;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap mb-1">
    <div>
        <h2 class="page-heading">My Profile</h2>
        <div class="page-heading-underline"></div>
    </div>
    <div class="breadcrumb-gold mb-3">
        <a href="{{ route('client.dashboard') }}" class="text-decoration-none" style="color: #8a93a8;">Dashboard</a>
        <span class="mx-1">&gt;</span>
        <span class="current">My Profile</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-4">
    {{-- Left column --}}
    <div class="col-lg-4">
        <div class="profile-card">
            <div class="avatar-xl-wrap">
                <div class="avatar-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            </div>
            <div class="full-name">{{ $user->name }}</div>
            <div class="role-badge mb-3">Client</div>

            <div class="id-divider"></div>

            <div class="meta-row">
                <div class="meta-icon"><i class="fas fa-id-badge"></i></div>
                <div>
                    <div class="meta-label">Client ID</div>
                    <div class="meta-value">CLT-{{ $client->created_at->format('Y') }}-{{ str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="meta-label">Member Since</div>
                    <div class="meta-value">{{ $client->created_at->format('F j, Y') }}</div>
                </div>
            </div>

            <button type="button" class="btn btn-gold w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#personalInfoEdit">
                <i class="fas fa-pen me-1"></i> Edit Profile
            </button>
        </div>

        <div class="motivational-card">
            <i class="fas fa-calendar-day deco-icon deco-1"></i>
            <i class="fas fa-star deco-icon deco-2"></i>
            <i class="fas fa-star deco-icon deco-3"></i>
            <h6>Great events</h6>
            <div class="gold-script mb-2">start with you!</div>
            <p class="desc">Let's create unforgettable memories together.</p>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-8">
        <form method="POST" action="{{ route('client.profile.update') }}">
            @csrf @method('PUT')

            <div class="stat-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="gold-heading mb-0"><span class="fa-icon-badge"><i class="fas fa-user"></i></span>Personal Information</h6>
                    <button type="button" class="btn btn-sm btn-outline-gold" data-bs-toggle="collapse" data-bs-target="#personalInfoEdit">
                        <i class="fas fa-pen me-1"></i> Edit
                    </button>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $user->phone }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $client->address ?? 'Not set' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-birthday-cake"></i></div>
                    <div>
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">{{ $client->date_of_birth?->format('F j, Y') ?? 'Not set' }}</div>
                    </div>
                </div>

                <div class="collapse mt-3" id="personalInfoEdit">
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', optional($client->date_of_birth)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="{{ old('address', $client->address) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gold mt-3"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </div>

            <div class="stat-card">
                <h6 class="gold-heading mb-3"><span class="fa-icon-badge"><i class="fas fa-cog"></i></span>Account Settings</h6>

                <div class="settings-row" data-bs-toggle="collapse" data-bs-target="#changePasswordCollapse" role="button">
                    <div class="settings-icon"><i class="fas fa-lock"></i></div>
                    <div>
                        <p class="settings-title">Change Password</p>
                        <div class="settings-desc">Update your account password</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </div>

                <div class="collapse" id="changePasswordCollapse">
                    <div class="p-3">
                        <p class="text-muted small">Leave blank to keep your current password.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gold mt-3"><i class="fas fa-save me-1"></i> Update Password</button>
                    </div>
                </div>

                <a href="{{ route('client.notifications.index') }}" class="settings-row">
                    <div class="settings-icon"><i class="fas fa-bell"></i></div>
                    <div>
                        <p class="settings-title">Notification Preferences</p>
                        <div class="settings-desc">Choose how you want to receive updates</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>

                <a href="#" class="settings-row" data-bs-toggle="tooltip" title="Coming soon" onclick="return false;">
                    <div class="settings-icon"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <p class="settings-title">Privacy Settings</p>
                        <div class="settings-desc">Manage your privacy and data settings</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
