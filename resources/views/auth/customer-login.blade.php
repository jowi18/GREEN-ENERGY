@extends('layouts.auth')

@section('title', 'Customer Sign In')

@section('panel-subtitle')
    Browse nearby solar vendors, order products, and track your installations.
@endsection

@section('panel-features')
    <li><i class="bi bi-geo-alt fi"></i> Nearby vendor discovery</li>
    <li><i class="bi bi-bag fi"></i> Easy online ordering</li>
    <li><i class="bi bi-chat-dots fi"></i> Chat directly with vendors</li>
    <li><i class="bi bi-shield fi"></i> Warranty & service management</li>
@endsection

@section('form-content')

    <div class="auth-form-header" style="margin-bottom:1.75rem;">
        <h1>Welcome back</h1>
        <p>Sign in to your customer account to continue.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error-custom mb-3">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('customer.login') }}" id="custLoginForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="you@email.com" autofocus required>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-1">
            <label class="form-label">Password</label>
            <div style="position:relative;">
                <input type="password" name="password" id="custLoginPw"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Your password" required>
                <button type="button"
                    onclick="const i=document.getElementById('custLoginPw');const ic=this.querySelector('i');i.type=i.type==='password'?'text':'password';ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';"
                    style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                           background:none;border:none;cursor:pointer;color:#9aada0;font-size:1rem;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3" style="margin-top:0.5rem;">
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;color:#5a7060;">
                <input type="checkbox" name="remember" style="accent-color:var(--green-600);width:15px;height:15px;">
                Remember me
            </label>
            <a href="#" style="font-size:0.82rem;color:var(--green-600);font-weight:500;">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary-green w-100" id="custLoginBtn"
            style="justify-content:center;padding:0.7rem;">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </form>

    <div class="auth-divider">New here?</div>

    <a href="{{ route('customer.register') }}" class="btn-outline-green w-100"
        style="justify-content:center;padding:0.65rem;">
        <i class="bi bi-person-plus"></i> Create a free account
    </a>

    <p style="text-align:center;font-size:0.78rem;color:#8fa695;margin-top:1.25rem;">
        Are you a vendor?
        <a href="{{ route('vendor.login') }}" style="color:var(--green-600);font-weight:500;">
            Sign in to vendor portal
        </a>
    </p>

@endsection

@push('scripts')
    <script>
        document.getElementById('custLoginForm').addEventListener('submit', function() {
            const btn = document.getElementById('custLoginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
        });
    </script>
@endpush
