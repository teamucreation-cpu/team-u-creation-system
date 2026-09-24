@extends('layouts.app')

@section('title', 'Login')

@section('content')
<style>
    .password-field { position: relative; }
    .password-field .password-toggle {
        position: absolute;
        top: 50%;
        right: 0.75rem;
        transform: translateY(-50%);
        border: 0;
        padding: 0.25rem;
        background: transparent;
        color: #6c757d;
        line-height: 1;
    }
    .password-field .password-toggle:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }
    .password-field .form-control { padding-right: 2.75rem; }
    .password-field input::-ms-reveal,
    .password-field input::-ms-clear { display: none; }
</style>
<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4">Log In</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-field">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-dark w-100">Log In</button>
        </form>

        <p class="mt-3 text-center">
            Don't have an account? <a href="{{ route('register') }}">Register</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var password = document.getElementById(toggle.dataset.passwordToggle);
            var isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!isVisible));
            toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            toggle.querySelector('i').className = isVisible ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    });
</script>
@endpush
