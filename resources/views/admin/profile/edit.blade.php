@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Account Settings')

@push('styles')
<style>
    .admin-settings-lux {
        --as-black: #0A0A0A;
        --as-charcoal: #121212;
        --as-gold: #F4C95D;
        --as-gold-dim: rgba(244, 201, 93, 0.25);
        --as-white: #ffffff;
        --as-muted: #D1D5DB;
        position: relative;
        min-height: calc(100vh - 140px);
        margin: -0.5rem -1.5rem -1.5rem;
        padding: 2.5rem 1.5rem 3rem;
        background: var(--as-black);
        overflow: hidden;
    }

    .admin-settings-lux::before {
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

    .as-wrap { position: relative; z-index: 1; width: 100%; max-width: 880px; margin: 0 auto; }

    @keyframes asFadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .as-fade { opacity: 0; animation: asFadeUp 0.55s ease forwards; }
    .as-fade-1 { animation-delay: 0.03s; }
    .as-fade-2 { animation-delay: 0.14s; }
    .as-fade-3 { animation-delay: 0.25s; }

    .as-heading-block { text-align: center; margin-bottom: 2rem; }
    .as-heading-underline {
        width: 56px;
        height: 3px;
        background: var(--as-gold);
        border-radius: 2px;
        margin: 0 auto 0.9rem;
    }
    .as-heading-block p { color: var(--as-muted); font-size: 0.92rem; margin: 0; }

    /* ---- Glassmorphism card ---- */
    .as-card {
        background: rgba(18, 18, 18, 0.85);
        border: 1px solid var(--as-gold);
        border-radius: 20px;
        padding: 2rem;
        backdrop-filter: blur(14px) saturate(140%);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        box-shadow: 0 0 40px rgba(244, 201, 93, 0.08), 0 20px 45px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.75rem;
    }

    /* ---- Profile overview card ---- */
    .as-overview-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .as-overview-identity { display: flex; align-items: center; gap: 1.35rem; min-width: 0; }

    .as-avatar {
        flex: 0 0 auto;
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #ffe6a3, var(--as-gold) 65%);
        color: var(--as-black);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        font-weight: 800;
        font-family: Georgia, 'Times New Roman', serif;
        box-shadow: 0 0 0 4px rgba(244, 201, 93, 0.18), 0 10px 26px rgba(244, 201, 93, 0.28);
    }

    .as-overview-identity .as-name {
        color: var(--as-white);
        font-weight: 700;
        font-size: 1.35rem;
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .as-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(244, 201, 93, 0.12);
        border: 1px solid var(--as-gold-dim);
        color: var(--as-gold);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
    }

    /* ---- Buttons ---- */
    .as-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: transparent;
        color: var(--as-gold);
        border: 1.5px solid var(--as-gold);
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 10px;
        padding: 0.65rem 1.3rem;
        transition: background 0.22s ease, box-shadow 0.22s ease, transform 0.22s ease, color 0.22s ease;
        white-space: nowrap;
    }
    .as-btn-outline:hover {
        background: rgba(244, 201, 93, 0.1);
        box-shadow: 0 0 18px rgba(244, 201, 93, 0.35);
        transform: translateY(-2px);
        color: var(--as-gold);
    }

    .as-btn-gold {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--as-gold);
        color: #000;
        border: none;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 10px;
        padding: 0.65rem 1.3rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
        white-space: nowrap;
    }
    .as-btn-gold:hover {
        background: #ffd873;
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(244, 201, 93, 0.45);
    }

    .as-actions-view { display: inline-flex; }
    .as-actions-edit { display: none; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

    /* ---- Information card ---- */
    .as-card-title {
        color: var(--as-white);
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(244, 201, 93, 0.15);
    }

    /* View-mode read-only detail grid */
    .as-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.75rem 2rem;
    }

    .as-detail-item { display: flex; align-items: flex-start; gap: 0.85rem; min-width: 0; }
    .as-detail-item .as-detail-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(244, 201, 93, 0.1);
        color: var(--as-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex: 0 0 auto;
    }
    .as-detail-item .as-detail-label {
        color: var(--as-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .as-detail-item .as-detail-value {
        color: var(--as-white);
        font-size: 0.98rem;
        font-weight: 600;
        word-break: break-word;
    }
    .as-detail-item .as-detail-value.is-empty { color: var(--as-muted); font-weight: 400; font-style: italic; }

    /* Edit-mode form — floating labels with a leading gold icon */
    #profileForm { display: none; }

    .as-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem 1.5rem;
    }

    .as-floating { position: relative; }
    .as-floating > .form-control {
        background: var(--as-charcoal);
        border: 1px solid rgba(244, 201, 93, 0.22);
        color: var(--as-white);
        border-radius: 12px;
        padding-left: 2.7rem;
        height: calc(3.4rem + 2px);
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .as-floating > .form-control:focus {
        background: #1b1b1b;
        border-color: var(--as-gold);
        color: var(--as-white);
        box-shadow: 0 0 0 0.2rem rgba(244, 201, 93, 0.22);
    }
    .as-floating > label {
        color: var(--as-muted);
        padding-left: 2.7rem;
    }
    .as-floating > .form-control:focus ~ label,
    .as-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--as-gold);
    }
    /* Bootstrap punches a "hole" behind the shrunk label using --bs-body-bg (light theme
       value) — repaint that mask to match this card's dark input background instead. */
    .as-floating > .form-control:focus ~ label::after,
    .as-floating > .form-control:not(:placeholder-shown) ~ label::after {
        background-color: var(--as-charcoal);
    }
    .as-floating .as-field-icon {
        position: absolute;
        left: 0.95rem;
        top: 1.05rem;
        color: var(--as-gold);
        font-size: 0.92rem;
        z-index: 5;
        pointer-events: none;
        transition: color 0.2s ease;
    }
    .as-floating > .form-control:focus ~ .as-field-icon { color: var(--as-gold); }

    .as-password-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(244, 201, 93, 0.15);
    }
    .as-password-section h4 {
        color: var(--as-white);
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    .as-password-hint {
        color: var(--as-muted);
        font-size: 0.82rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .as-detail-grid, .as-edit-grid { grid-template-columns: 1fr; gap: 1.25rem; }
        .as-overview-card { flex-direction: column; align-items: flex-start; }
        .as-actions-view, .as-actions-edit { width: 100%; }
        .as-actions-view .as-btn-outline, .as-actions-edit .as-btn-gold, .as-actions-edit .as-btn-outline { width: 100%; justify-content: center; }
    }

    @media (max-width: 575.98px) {
        .admin-settings-lux { padding: 2rem 1rem 2.5rem; margin: -1rem -1rem -1.5rem; }
        .as-card { padding: 1.5rem; border-radius: 18px; }
    }
</style>
@endpush

@section('content')
<div class="admin-settings-lux">
    <div class="as-wrap" id="accountSettingsRoot">
        <div class="as-heading-block as-fade as-fade-1">
            <div class="as-heading-underline"></div>
            <p>Manage your profile information.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        {{-- Profile Overview Card --}}
        <div class="as-card as-overview-card as-fade as-fade-2">
            <div class="as-overview-identity">
                <div class="as-avatar">{{ strtoupper(substr(trim(explode(' ', $user->name)[0] ?? $user->name), 0, 1)) }}</div>
                <div>
                    <div class="as-name">{{ $user->name }}</div>
                    <span class="as-role-badge"><i class="fas fa-shield-halved"></i> System Administrator</span>
                </div>
            </div>

            <div class="as-actions-view">
                <button type="button" id="editBtn" class="as-btn-outline"><i class="fas fa-pen"></i> Edit Information</button>
            </div>

            <div class="as-actions-edit" id="editActions">
                <button type="submit" form="profileForm" class="as-btn-gold"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" id="cancelBtn" class="as-btn-outline"><i class="fas fa-xmark"></i> Cancel</button>
            </div>
        </div>

        {{-- Information Card --}}
        <div class="as-card as-fade as-fade-3">
            <div class="as-card-title">Personal Information</div>

            {{-- View mode — read-only details --}}
            <div class="as-detail-grid" id="viewDetails">
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="as-detail-label">Full Name</div>
                        <div class="as-detail-value">{{ $user->name }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="as-detail-label">Email Address</div>
                        <div class="as-detail-value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <div class="as-detail-label">Phone Number</div>
                        <div class="as-detail-value">{{ $user->phone }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-location-dot"></i></div>
                    <div>
                        <div class="as-detail-label">Address</div>
                        <div class="as-detail-value {{ $user->address ? '' : 'is-empty' }}">{{ $user->address ?: 'Not set' }}</div>
                    </div>
                </div>
            </div>

            {{-- Edit mode — form --}}
            <form id="profileForm" method="POST" action="{{ route('admin.profile.update') }}">
                @csrf @method('PUT')

                <div class="as-edit-grid">
                    <div class="form-floating as-floating">
                        <i class="fas fa-user as-field-icon"></i>
                        <input type="text" class="form-control" id="as-name" name="name" placeholder=" " value="{{ old('name', $user->name) }}" required>
                        <label for="as-name">Full Name</label>
                    </div>
                    <div class="form-floating as-floating">
                        <i class="fas fa-envelope as-field-icon"></i>
                        <input type="email" class="form-control" id="as-email" name="email" placeholder=" " value="{{ old('email', $user->email) }}" required>
                        <label for="as-email">Email Address</label>
                    </div>
                    <div class="form-floating as-floating">
                        <i class="fas fa-phone as-field-icon"></i>
                        <input type="text" class="form-control" id="as-phone" name="phone" placeholder=" " value="{{ old('phone', $user->phone) }}" required>
                        <label for="as-phone">Phone Number</label>
                    </div>
                    <div class="form-floating as-floating">
                        <i class="fas fa-location-dot as-field-icon"></i>
                        <input type="text" class="form-control" id="as-address" name="address" placeholder=" " value="{{ old('address', $user->address) }}">
                        <label for="as-address">Address</label>
                    </div>
                </div>

                <div class="as-password-section">
                    <h4>Change Password (Optional)</h4>
                    <p class="as-password-hint">Leave password fields blank to keep your current password.</p>
                    <div class="as-edit-grid">
                        <div class="form-floating as-floating">
                            <i class="fas fa-lock as-field-icon"></i>
                            <input type="password" class="form-control" id="as-password" name="password" placeholder=" ">
                            <label for="as-password">New Password</label>
                        </div>
                        <div class="form-floating as-floating">
                            <i class="fas fa-lock as-field-icon"></i>
                            <input type="password" class="form-control" id="as-password-confirm" name="password_confirmation" placeholder=" ">
                            <label for="as-password-confirm">Confirm Password</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="as-card as-fade as-fade-3">
            <div class="as-card-title">Business Contact Information</div>

            <div class="as-detail-grid" id="businessViewDetails">
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-location-dot"></i></div>
                    <div>
                        <div class="as-detail-label">Location</div>
                        <div class="as-detail-value">{{ $businessSettings->location }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <div class="as-detail-label">Contact Number</div>
                        <div class="as-detail-value">{{ $businessSettings->phone }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="as-detail-label">Email Address</div>
                        <div class="as-detail-value">{{ $businessSettings->email }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fab fa-facebook"></i></div>
                    <div>
                        <div class="as-detail-label">Facebook Page</div>
                        <div class="as-detail-value">{{ $businessSettings->facebook_url }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-calendar-days"></i></div>
                    <div>
                        <div class="as-detail-label">Business Days</div>
                        <div class="as-detail-value">{{ $businessSettings->business_days }}</div>
                    </div>
                </div>
                <div class="as-detail-item">
                    <div class="as-detail-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="as-detail-label">Business Hours</div>
                        <div class="as-detail-value">
                            {{ \Carbon\Carbon::createFromFormat('H:i', $businessSettings->opening_time)->format('g:i A') }}
                            &ndash;
                            {{ \Carbon\Carbon::createFromFormat('H:i', $businessSettings->closing_time)->format('g:i A') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="as-edit-grid" id="businessEditDetails" style="display: none;">
                <div class="form-floating as-floating">
                    <i class="fas fa-location-dot as-field-icon"></i>
                    <input type="text" class="form-control" id="as-business-location" name="business_location" form="profileForm" placeholder=" " value="{{ old('business_location', $businessSettings->location) }}" required>
                    <label for="as-business-location">Business Location</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fas fa-phone as-field-icon"></i>
                    <input type="text" class="form-control" id="as-business-phone" name="business_phone" form="profileForm" placeholder=" " value="{{ old('business_phone', $businessSettings->phone) }}" required>
                    <label for="as-business-phone">Business Contact Number</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fas fa-envelope as-field-icon"></i>
                    <input type="email" class="form-control" id="as-business-email" name="business_email" form="profileForm" placeholder=" " value="{{ old('business_email', $businessSettings->email) }}" required>
                    <label for="as-business-email">Business Email Address</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fab fa-facebook as-field-icon"></i>
                    <input type="url" class="form-control" id="as-business-facebook-url" name="business_facebook_url" form="profileForm" placeholder=" " value="{{ old('business_facebook_url', $businessSettings->facebook_url) }}" required>
                    <label for="as-business-facebook-url">Facebook Page Link</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fas fa-calendar-days as-field-icon"></i>
                    <input type="text" class="form-control" id="as-business-days" name="business_days" form="profileForm" placeholder=" " value="{{ old('business_days', $businessSettings->business_days) }}" required>
                    <label for="as-business-days">Business Days</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fas fa-clock as-field-icon"></i>
                    <input type="time" class="form-control" id="as-business-opening-time" name="business_opening_time" form="profileForm" placeholder=" " value="{{ old('business_opening_time', $businessSettings->opening_time) }}" required>
                    <label for="as-business-opening-time">Opening Time</label>
                </div>
                <div class="form-floating as-floating">
                    <i class="fas fa-clock as-field-icon"></i>
                    <input type="time" class="form-control" id="as-business-closing-time" name="business_closing_time" form="profileForm" placeholder=" " value="{{ old('business_closing_time', $businessSettings->closing_time) }}" required>
                    <label for="as-business-closing-time">Closing Time</label>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var root = document.getElementById('accountSettingsRoot');
        var editBtn = document.getElementById('editBtn');
        var cancelBtn = document.getElementById('cancelBtn');
        var form = document.getElementById('profileForm');
        var viewActions = document.querySelector('.as-actions-view');
        var editActions = document.getElementById('editActions');
        var viewDetails = document.getElementById('viewDetails');
        var businessViewDetails = document.getElementById('businessViewDetails');
        var businessEditDetails = document.getElementById('businessEditDetails');

        function enterEditMode() {
            viewActions.style.display = 'none';
            editActions.style.display = 'flex';
            viewDetails.style.display = 'none';
            businessViewDetails.style.display = 'none';
            businessEditDetails.style.display = 'grid';
            form.style.display = 'block';
        }

        function exitEditMode() {
            viewActions.style.display = 'inline-flex';
            editActions.style.display = 'none';
            viewDetails.style.display = 'grid';
            businessViewDetails.style.display = 'grid';
            businessEditDetails.style.display = 'none';
            form.style.display = 'none';
        }

        editBtn.addEventListener('click', enterEditMode);
        cancelBtn.addEventListener('click', function () {
            form.reset();
            exitEditMode();
        });

        @if($errors->any())
            enterEditMode();
        @endif
    })();
</script>
@endpush
