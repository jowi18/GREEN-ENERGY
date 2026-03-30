@extends('layouts.auth')

@section('title', 'Create Customer Account')

@section('panel-subtitle')
    Discover nearby solar vendors, order products, and request installation services.
@endsection

@section('panel-features')
    <li><i class="bi bi-geo-alt fi"></i> Find vendors near you instantly</li>
    <li><i class="bi bi-cart fi"></i> Order solar products online</li>
    <li><i class="bi bi-tools fi"></i> Request installation & maintenance</li>
    <li><i class="bi bi-shield-check fi"></i> Warranty management made easy</li>
@endsection

@section('form-content')

    <div class="auth-form-header" style="margin-bottom:1.75rem;">
        <h1>Create Account</h1>
        <p>Join the platform and start your solar journey.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error-custom mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-1 ps-3" style="font-size:0.82rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.register') }}" enctype="multipart/form-data" id="custRegForm"
        novalidate>
        @csrf

        {{-- Name row --}}
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">First name <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                    value="{{ old('first_name') }}" placeholder="Juan" required autofocus>
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label class="form-label">Last name <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                    value="{{ old('last_name') }}" placeholder="Reyes" required>
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email address <span style="color:#e74c3c;">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="you@email.com" required>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mobile number <span style="color:#e74c3c;">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" required>
            </div>
            @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">Password <span style="color:#e74c3c;">*</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="custPw"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters"
                        required>
                    <button type="button"
                        onclick="const i=document.getElementById('custPw');const ic=this.querySelector('i');i.type=i.type==='password'?'text':'password';ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';"
                        style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:#9aada0;font-size:1rem;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label class="form-label">Confirm password <span style="color:#e74c3c;">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password"
                    required>
            </div>
        </div>

        {{-- Address (optional) --}}
        <div style="background:var(--green-50);border-radius:10px;padding:1rem;margin-bottom:1rem;">
            <p style="font-size:0.78rem;font-weight:600;color:var(--green-800);margin-bottom:0.75rem;">
                <i class="bi bi-geo-alt me-1"></i>Home Address
                <span style="font-weight:400;color:#5a7060;"> — Optional, helps find nearby vendors</span>
            </p>
            <div class="row g-2">
                <div class="col-12">
                    <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1') }}"
                        placeholder="Street address" style="font-size:0.85rem;">
                </div>
                <div class="col-6">
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}"
                        placeholder="City" style="font-size:0.85rem;">
                </div>
                <div class="col-6">
                    <input type="text" name="province_state" class="form-control" value="{{ old('province_state') }}"
                        placeholder="Province" style="font-size:0.85rem;">
                </div>
                <div class="col-6">
                    <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}"
                        placeholder="Postal code" style="font-size:0.85rem;">
                </div>
            </div>
        </div>

        {{-- Government ID --}}
        <div class="mb-3">
            <label class="form-label">
                Government ID <span style="color:#e74c3c;">*</span>
                <span style="font-weight:400;color:#5a7060;font-size:0.78rem;"> — Required for account verification</span>
            </label>
            <div class="row g-2">
                <div class="col-5">
                    <select name="government_id_type"
                        class="form-select @error('government_id_type') is-invalid @enderror" required>
                        <option value="">ID type…</option>
                        <option value="PhilSys ID"
                            {{ old('government_id_type') === 'PhilSys ID' ? 'selected' : '' }}>PhilSys ID</option>
                        <option value="Driver's License"
                            {{ old('government_id_type') === "Driver's License" ? 'selected' : '' }}>Driver's License
                        </option>
                        <option value="Passport" {{ old('government_id_type') === 'Passport' ? 'selected' : '' }}>
                            Passport</option>
                        <option value="SSS ID" {{ old('government_id_type') === 'SSS ID' ? 'selected' : '' }}>SSS
                            ID</option>
                        <option value="UMID" {{ old('government_id_type') === 'UMID' ? 'selected' : '' }}>
                            UMID</option>
                        <option value="Voter's ID"
                            {{ old('government_id_type') === "Voter's ID" ? 'selected' : '' }}>Voter's ID</option>
                        <option value="Postal ID" {{ old('government_id_type') === 'Postal ID' ? 'selected' : '' }}>
                            Postal ID</option>
                    </select>
                    @error('government_id_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-7">
                    <input type="file" name="government_id"
                        class="form-control @error('government_id') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png"
                        required>
                    @error('government_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <p style="font-size:0.73rem;color:#8fa695;margin-top:0.3rem;">
                PDF, JPG, or PNG — max 5 MB. Your ID is stored securely and used only for verification.
            </p>
        </div>

        {{-- Terms --}}
        <div class="mb-3">
            <label
                style="display:flex;align-items:flex-start;gap:0.6rem;cursor:pointer;
                      font-size:0.83rem;color:#4d6356;line-height:1.5;">
                <input type="checkbox" name="terms" class="@error('terms') is-invalid @enderror"
                    style="accent-color:var(--green-600);width:16px;height:16px;margin-top:2px;flex-shrink:0;" required>
                <span>
                    I agree to the
                    <a href="#" style="color:var(--green-600);font-weight:500;">Terms of Service</a>
                    and
                    <a href="#" style="color:var(--green-600);font-weight:500;">Privacy Policy</a>
                </span>
            </label>
            @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-primary-green w-100" id="submitCustBtn"
            style="justify-content:center;padding:0.72rem;">
            <i class="bi bi-person-check"></i> Create Account
        </button>
    </form>

    <div class="auth-divider">Already have an account?</div>

    <a href="{{ route('customer.login') }}" class="btn-outline-green w-100"
        style="justify-content:center;padding:0.65rem;">
        <i class="bi bi-box-arrow-in-right"></i> Sign in
    </a>

    <p style="text-align:center;font-size:0.78rem;color:#8fa695;margin-top:1.25rem;">
        Are you a vendor?
        <a href="{{ route('vendor.register') }}" style="color:var(--green-600);font-weight:500;">
            Register your business
        </a>
    </p>

@endsection

@push('scripts')
    <script>
        document.getElementById('custRegForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitCustBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account…';
        });
    </script>
@endpush
