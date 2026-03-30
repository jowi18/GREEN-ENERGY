@extends('layouts.auth')

@section('title', 'Vendor Sign In')

@section('panel-subtitle')
    Sign in to manage your shop, inventory, orders, and POS system.
@endsection

@section('panel-features')
    <li><i class="bi bi-speedometer2 fi"></i> Live sales dashboard</li>
    <li><i class="bi bi-receipt fi"></i> POS for walk-in customers</li>
    <li><i class="bi bi-box-seam fi"></i> Inventory & stock alerts</li>
    <li><i class="bi bi-truck fi"></i> Order & delivery tracking</li>
@endsection

@section('form-content')

    <div class="auth-form-header" style="margin-bottom:1.75rem;">
        <h1>Vendor Sign In</h1>
        <p>Access your vendor dashboard and tools.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error-custom mb-3">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.login') }}" id="loginForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="you@business.com" autofocus required>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-1">
            <label class="form-label">Password</label>
            <div class="input-group" style="position:relative;">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="loginPw"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Your password" required>
                <button type="button"
                    onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'; this.querySelector('i').className = this.previousElementSibling.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';"
                    style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                           background:none;border:none;cursor:pointer;color:var(--text-muted,#9aada0);
                           font-size:1rem;z-index:5;">
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

        <button type="submit" class="btn-primary-green w-100" id="loginBtn"
            style="justify-content:center;padding:0.7rem;">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </form>

    <div class="auth-divider">New vendor?</div>

    <a href="{{ route('vendor.register') }}" class="btn-outline-green w-100"
        style="justify-content:center;padding:0.65rem;">
        <i class="bi bi-shop"></i> Register your business
    </a>

    <p style="text-align:center;font-size:0.78rem;color:#8fa695;margin-top:1.5rem;">
        Are you a customer?
        <a href="{{ route('customer.login') }}" style="color:var(--green-600);font-weight:500;">Sign in here</a>
    </p>

@endsection

@push('scripts')
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
        });
    </script>
@endpush
