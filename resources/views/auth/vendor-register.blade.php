@extends('layouts.auth')

@section('title', 'Vendor Registration')

@section('panel-subtitle')
    Join thousands of solar vendors already growing their business on our platform.
@endsection

@section('panel-features')
    <li><i class="bi bi-shop fi"></i> Your own branded solar storefront</li>
    <li><i class="bi bi-pos2 fi"></i> POS system for walk-in sales</li>
    <li><i class="bi bi-box-seam fi"></i> Full inventory management</li>
    <li><i class="bi bi-people fi"></i> Multi-role employee access</li>
    <li><i class="bi bi-chat-dots fi"></i> Real-time customer chat</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .step-nav {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 2rem;
        }

        .step-nav__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            cursor: default;
        }

        .step-nav__item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px;
            left: calc(50% + 18px);
            right: calc(-50% + 18px);
            height: 2px;
            background: var(--border);
            z-index: 0;
            transition: background 0.3s;
        }

        .step-nav__item.done:not(:last-child)::after {
            background: var(--green-400);
        }

        .step-nav__circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted, #9aada0);
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }

        .step-nav__item.active .step-nav__circle {
            border-color: var(--green-500);
            background: var(--green-500);
            color: #fff;
            box-shadow: 0 0 0 4px var(--green-50);
        }

        .step-nav__item.done .step-nav__circle {
            border-color: var(--green-400);
            background: var(--green-400);
            color: #fff;
        }

        .step-nav__label {
            font-size: 0.65rem;
            font-weight: 600;
            margin-top: 0.35rem;
            color: var(--text-muted, #9aada0);
            text-align: center;
            white-space: nowrap;
        }

        .step-nav__item.active .step-nav__label {
            color: var(--green-700);
        }

        .step-nav__item.done .step-nav__label {
            color: var(--green-600);
        }

        /* Step panels */
        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        /* Document upload row */
        .doc-row {
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.65rem;
            background: #fff;
            transition: border-color 0.2s;
        }

        .doc-row:hover {
            border-color: var(--green-400);
        }

        .doc-row__head {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.65rem;
        }

        .doc-row__icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: var(--green-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green-600);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .btn-add-doc {
            border: 1.5px dashed var(--green-400);
            border-radius: 10px;
            background: var(--green-50);
            color: var(--green-600);
            padding: 0.65rem;
            width: 100%;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-family: inherit;
        }

        .btn-add-doc:hover {
            background: var(--green-100);
            border-color: var(--green-600);
        }

        /* Map */
        #map {
            height: 280px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
        }

        .map-pin-info {
            background: var(--green-50);
            border: 1px solid var(--green-100);
            border-radius: 8px;
            padding: 0.55rem 0.85rem;
            font-size: 0.78rem;
            color: var(--green-800);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Password strength */
        .pw-strength {
            height: 4px;
            border-radius: 2px;
            background: var(--border);
            margin-top: 0.4rem;
            overflow: hidden;
        }

        .pw-strength__bar {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
            width: 0;
        }

        /* Navigation buttons */
        .step-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }
    </style>
@endpush

{{--
    CHANGES vs original:
    1. SweetAlert2 CDN added in @push('scripts')
    2. Each doc-row file input calls validateDocumentFile() on change
    3. validateDocumentFile() → AJAX to /vendor/validate-document → Swal result
    4. Validated doc indices tracked; form submit blocked if any pending/failed
    5. First doc row already wired; dynamically added rows auto-wired too
--}}

@section('form-content')

    <div class="auth-form-header" style="margin-bottom:1.75rem;">
        <h1>Become a Vendor</h1>
        <p>Complete all 4 steps to submit your application for review.</p>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert-error-custom mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li style="font-size:0.82rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Step indicator --}}
    <div class="step-nav" id="stepNav">
        <div class="step-nav__item active" data-step="1">
            <div class="step-nav__circle">1</div>
            <span class="step-nav__label">Account</span>
        </div>
        <div class="step-nav__item" data-step="2">
            <div class="step-nav__circle">2</div>
            <span class="step-nav__label">Business</span>
        </div>
        <div class="step-nav__item" data-step="3">
            <div class="step-nav__circle">3</div>
            <span class="step-nav__label">Location</span>
        </div>
        <div class="step-nav__item" data-step="4">
            <div class="step-nav__circle">4</div>
            <span class="step-nav__label">Documents</span>
        </div>
    </div>

    <form method="POST" action="{{ route('vendor.register') }}" enctype="multipart/form-data" id="registerForm" novalidate>
        @csrf

        {{-- ══════════════════════════════════════════ --}}
        {{-- STEP 1 — Account credentials              --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="step-panel active" id="step1">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">First name <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="owner_first_name"
                        class="form-control @error('owner_first_name') is-invalid @enderror"
                        value="{{ old('owner_first_name') }}" placeholder="Juan" required>
                    @error('owner_first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Last name <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="owner_last_name"
                        class="form-control @error('owner_last_name') is-invalid @enderror"
                        value="{{ old('owner_last_name') }}" placeholder="dela Cruz" required>
                    @error('owner_last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Email address <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" placeholder="you@business.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Password <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" placeholder="Min. 8 characters" required>
                        <button type="button" onclick="togglePw('password','eyeBtn1')" id="eyeBtn1"
                            style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;color:var(--text-muted,#9aada0);font-size:1rem;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="pw-strength mt-1">
                        <div class="pw-strength__bar" id="pwBar"></div>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Confirm password <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" class="form-control" id="passwordConfirm"
                            placeholder="Repeat password" required>
                        <button type="button" onclick="togglePw('passwordConfirm','eyeBtn2')" id="eyeBtn2"
                            style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;color:var(--text-muted,#9aada0);font-size:1rem;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Mobile number <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input type="tel" name="owner_phone"
                            class="form-control @error('owner_phone') is-invalid @enderror"
                            value="{{ old('owner_phone') }}" placeholder="+63 9XX XXX XXXX" required>
                    </div>
                    @error('owner_phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="step-actions">
                <button type="button" class="btn-primary-green ms-auto" onclick="goToStep(2)">
                    Next: Business Info <i class="bi bi-arrow-right"></i>
                </button>
            </div>
            <div class="auth-divider">Already have an account?</div>
            <div class="text-center">
                <a href="{{ route('vendor.login') }}" style="color:var(--green-600);font-size:0.875rem;font-weight:500;">
                    Sign in to your vendor account
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- STEP 2 — Business info                    --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="step-panel" id="step2">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Business name <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="business_name"
                        class="form-control @error('business_name') is-invalid @enderror"
                        value="{{ old('business_name') }}" placeholder="Sunshine Solar Solutions" required>
                    @error('business_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Business type <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <select name="business_type" class="form-select @error('business_type') is-invalid @enderror"
                        required>
                        <option value="">Select type…</option>
                        <option
                            value="sole_proprietorship"{{ old('business_type') === 'sole_proprietorship' ? 'selected' : '' }}>
                            Sole Proprietorship</option>
                        <option value="partnership"{{ old('business_type') === 'partnership' ? 'selected' : '' }}>
                            Partnership</option>
                        <option value="sme" {{ old('business_type') === 'sme' ? 'selected' : '' }}>SME</option>
                        <option value="other" {{ old('business_type') === 'other' ? 'selected' : '' }}>Other</option>

                    </select>
                    @error('business_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Registration number <span
                            style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="business_registration_number"
                        class="form-control @error('business_registration_number') is-invalid @enderror"
                        value="{{ old('business_registration_number') }}" placeholder="DTI-XXXXX" required>
                    @error('business_registration_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Business phone <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="tel" name="business_phone"
                        class="form-control @error('business_phone') is-invalid @enderror"
                        value="{{ old('business_phone') }}" placeholder="+63 2 XXXX XXXX" required>
                    @error('business_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Business email</label>
                    <input type="email" name="business_email"
                        class="form-control @error('business_email') is-invalid @enderror"
                        value="{{ old('business_email') }}" placeholder="info@yourbusiness.com">
                    @error('business_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Website</label>
                    <div class="input-group">
                        <span class="input-group-text" style="font-size:0.8rem;">https://</span>
                        <input type="url" name="business_website" class="form-control"
                            value="{{ old('business_website') }}" placeholder="www.yourbusiness.com">
                    </div>
                </div>
            </div>
            <div class="step-actions">
                <button type="button" class="btn-outline-green" onclick="goToStep(1)">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-primary-green ms-auto" onclick="goToStep(3)">
                    Next: Location <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- STEP 3 — Business address + Cavite-locked map --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="step-panel" id="step3">
            <div class="row g-3">

                {{-- Street address --}}
                <div class="col-12">
                    <label class="form-label">Street address <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="address_line1" id="address_line1"
                        class="form-control @error('address_line1') is-invalid @enderror"
                        value="{{ old('address_line1') }}" placeholder="123 Solar Street, Barangay…" required>
                    @error('address_line1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Address line 2 --}}
                <div class="col-12">
                    <label class="form-label">Address line 2</label>
                    <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}"
                        placeholder="Unit / Floor / Building (optional)">
                </div>

                {{-- City — Cavite municipalities dropdown --}}
                <div class="col-6">
                    <label class="form-label">
                        City / Municipality <span style="color:var(--danger,#e74c3c)">*</span>
                    </label>
                    <select name="city" id="city" class="form-select @error('city') is-invalid @enderror"
                        required>
                        <option value="">— Select city —</option>
                        @php
                            $caviteCities = [
                                'Alfonso',
                                'Amadeo',
                                'Bacoor',
                                'Carmona',
                                'Cavite City',
                                'Dasmariñas',
                                'General Mariano Alvarez',
                                'General Trias',
                                'Imus',
                                'Indang',
                                'Kawit',
                                'Magallanes',
                                'Maragondon',
                                'Mendez',
                                'Naic',
                                'Noveleta',
                                'Rosario',
                                'Silang',
                                'Tagaytay',
                                'Tanza',
                                'Ternate',
                                'Trece Martires',
                            ];
                        @endphp
                        @foreach ($caviteCities as $c)
                            <option value="{{ $c }}" {{ old('city') === $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Province — locked to Cavite --}}
                <div class="col-6">
                    <label class="form-label">Province / Region <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="province_state" id="province_state" class="form-control" value="Cavite"
                        readonly style="background:#f8f9fa;color:#6c757d;cursor:not-allowed;">
                    <div style="font-size:.7rem;color:#6c757d;margin-top:.25rem;">
                        <i class="bi bi-lock-fill me-1"></i>Restricted to Cavite Province
                    </div>
                    @error('province_state')
                        <div style="color:#dc3545;font-size:.82rem;margin-top:.2rem;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Postal code — auto-filled from map --}}
                <div class="col-6">
                    <label class="form-label">Postal code <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="postal_code" id="postal_code"
                        class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code') }}"
                        placeholder="Auto-filled…" required>
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Country — locked --}}
                <div class="col-6">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control"
                        value="{{ old('country', 'Philippines') }}" readonly
                        style="background:#f8f9fa;color:#6c757d;cursor:not-allowed;">
                </div>

            </div>

            {{-- Hidden lat/lng --}}
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            {{-- Map --}}
            <div style="margin-top:1rem;">
                <label class="form-label" style="margin-bottom:.4rem;">
                    <i class="bi bi-geo-alt me-1" style="color:var(--green-600)"></i>
                    Pin your business location <span style="color:var(--danger,#e74c3c)">*</span>
                </label>
                <p style="font-size:.78rem;color:#5a7060;margin-bottom:.65rem;">
                    Click anywhere within <strong>Cavite Province</strong> to drop your pin.
                    City and address will auto-fill from the pin position.
                </p>
                <div id="map"></div>
                <div class="map-pin-info" id="coordsDisplay" style="{{ old('latitude') ? '' : 'display:none;' }}">
                    <i class="bi bi-check-circle-fill" style="color:var(--green-500);"></i>
                    <span id="coordsText">
                        @if (old('latitude'))
                            Location pinned: {{ old('latitude') }}, {{ old('longitude') }}
                        @endif
                    </span>
                </div>
                @error('latitude')
                    <div style="color:#dc3545;font-size:.82rem;margin-top:.3rem;">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="step-actions">
                <button type="button" class="btn-outline-green" onclick="goToStep(2)">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-primary-green ms-auto" onclick="goToStep(4)">
                    Next: Documents <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- STEP 4 — Document uploads (3 required + optional extras)     --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}

        <div class="step-panel" id="step4">

            {{-- OCR notice banner --}}
            <div
                style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;
                        padding:.75rem 1rem;margin-bottom:1rem;display:flex;gap:.6rem;align-items:flex-start;">
                <i class="bi bi-shield-check" style="color:#2563eb;margin-top:.1rem;flex-shrink:0;"></i>
                <div style="font-size:0.8rem;color:#1e40af;line-height:1.55;">
                    <strong>Cavite Province Verification</strong><br>
                    All uploaded documents will be automatically scanned to verify they are
                    issued within the <strong>Province of Cavite</strong>.
                    Please upload clear, high-quality scans or photos.
                </div>
            </div>

            {{-- Required documents notice --}}
            <div
                style="background:#fefce8;border:1.5px solid #fde047;border-radius:8px;
                        padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;gap:.6rem;align-items:flex-start;">
                <i class="bi bi-exclamation-triangle-fill" style="color:#ca8a04;margin-top:.1rem;flex-shrink:0;"></i>
                <div style="font-size:0.8rem;color:#713f12;line-height:1.6;">
                    <strong>3 Required Documents</strong> — all must be uploaded and verified before submitting:<br>
                    <span style="display:inline-flex;align-items:center;gap:.3rem;margin-top:.3rem;">
                        <i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Business Permit &nbsp;
                        <i class="bi bi-circle-fill" style="font-size:.45rem;"></i> DTI Registration &nbsp;
                        <i class="bi bi-circle-fill" style="font-size:.45rem;"></i> BIR Certificate
                    </span>
                </div>
            </div>

            <div id="docContainer">

                {{-- ── Required Row 1: Business Permit ──────────────────── --}}
                <div class="doc-row required-doc-row" data-index="0" id="docRow-0">
                    <div class="doc-row__head">
                        <div class="doc-row__icon" style="background:#dbeafe;color:#1d4ed8;">
                            <i class="bi bi-building"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:700;margin-bottom:.1rem;">
                                Business Permit
                                <span style="color:#dc2626;font-size:.75rem;">*</span>
                                <span style="font-size:.7rem;font-weight:400;color:#6b7280;margin-left:.35rem;">
                                    (Mayor's Permit / Annual Business Permit)
                                </span>
                            </div>
                        </div>
                        <div id="ocrStatus-0"
                            style="font-size:0.75rem;display:none;align-items:center;gap:.35rem;border-radius:4px;padding:.2rem .5rem;">
                        </div>
                    </div>
                    <div class="row g-2">
                        {{-- Hidden — type is fixed --}}
                        <input type="hidden" name="documents[0][type]" value="business_permit">
                        <input type="hidden" name="documents[0][label]" value="Business Permit">
                        <div class="col-12">
                            <input type="file" name="documents[0][file]" class="form-control doc-file-input"
                                data-index="0" data-doc-type="business_permit" data-required="1"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                            <div style="font-size:.72rem;color:#6b7280;margin-top:.3rem;">
                                PDF, JPG, PNG · Max 5 MB
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Required Row 2: DTI Registration ─────────────────── --}}
                <div class="doc-row required-doc-row" data-index="1" id="docRow-1">
                    <div class="doc-row__head">
                        <div class="doc-row__icon" style="background:#dcfce7;color:#15803d;">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:700;margin-bottom:.1rem;">
                                DTI Registration
                                <span style="color:#dc2626;font-size:.75rem;">*</span>
                                <span style="font-size:.7rem;font-weight:400;color:#6b7280;margin-left:.35rem;">
                                    (Department of Trade & Industry)
                                </span>
                            </div>
                        </div>
                        <div id="ocrStatus-1"
                            style="font-size:0.75rem;display:none;align-items:center;gap:.35rem;border-radius:4px;padding:.2rem .5rem;">
                        </div>
                    </div>
                    <div class="row g-2">
                        <input type="hidden" name="documents[1][type]" value="dti_registration">
                        <input type="hidden" name="documents[1][label]" value="DTI Registration">
                        <div class="col-12">
                            <input type="file" name="documents[1][file]" class="form-control doc-file-input"
                                data-index="1" data-doc-type="dti_registration" data-required="1"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                            <div style="font-size:.72rem;color:#6b7280;margin-top:.3rem;">
                                PDF, JPG, PNG · Max 5 MB
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Required Row 3: BIR Certificate ─────────────────── --}}
                <div class="doc-row required-doc-row" data-index="2" id="docRow-2">
                    <div class="doc-row__head">
                        <div class="doc-row__icon" style="background:#fef3c7;color:#b45309;">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:700;margin-bottom:.1rem;">
                                BIR Certificate
                                <span style="color:#dc2626;font-size:.75rem;">*</span>
                                <span style="font-size:.7rem;font-weight:400;color:#6b7280;margin-left:.35rem;">
                                    (Certificate of Registration / Form 2303)
                                </span>
                            </div>
                        </div>
                        <div id="ocrStatus-2"
                            style="font-size:0.75rem;display:none;align-items:center;gap:.35rem;border-radius:4px;padding:.2rem .5rem;">
                        </div>
                    </div>
                    <div class="row g-2">
                        <input type="hidden" name="documents[2][type]" value="bir_registration">
                        <input type="hidden" name="documents[2][label]" value="BIR Certificate">
                        <div class="col-12">
                            <input type="file" name="documents[2][file]" class="form-control doc-file-input"
                                data-index="2" data-doc-type="bir_registration" data-required="1"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                            <div style="font-size:.72rem;color:#6b7280;margin-top:.3rem;">
                                PDF, JPG, PNG · Max 5 MB
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Optional additional docs start at index 3 ─────────── --}}
            </div>

            {{-- Completion checklist --}}
            <div id="requiredDocsChecklist"
                style="margin-top:1rem;padding:.75rem 1rem;background:var(--card-bg);
                        border:1.5px solid var(--card-border);border-radius:8px;">
                <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.5rem;">
                    Required Documents Status
                </div>
                <div style="display:flex;flex-direction:column;gap:.3rem;">
                    <div id="check-0" class="doc-checklist-item"
                        style="font-size:.78rem;color:#6b7280;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-circle" style="font-size:.7rem;"></i> Business Permit
                    </div>
                    <div id="check-1" class="doc-checklist-item"
                        style="font-size:.78rem;color:#6b7280;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-circle" style="font-size:.7rem;"></i> DTI Registration
                    </div>
                    <div id="check-2" class="doc-checklist-item"
                        style="font-size:.78rem;color:#6b7280;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-circle" style="font-size:.7rem;"></i> BIR Certificate
                    </div>
                </div>
            </div>

            {{-- Add optional extra docs --}}
            <button type="button" class="btn-add-doc" id="addDocBtn" style="margin-top:1rem;">
                <i class="bi bi-plus-circle"></i> Add optional supporting document
            </button>

            @error('documents')
                <div style="color:#dc3545;font-size:0.82rem;margin-top:0.5rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror

            <div class="step-actions">
                <button type="button" class="btn-outline-green" onclick="goToStep(3)">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn-primary-green ms-auto" id="submitBtn" disabled
                    style="opacity:.6;cursor:not-allowed;" title="All 3 required documents must be verified first">
                    <i class="bi bi-send"></i> Submit Application
                </button>
            </div>

            <p style="font-size:0.75rem;color:#8a9e8f;margin-top:1rem;text-align:center;line-height:1.6;">
                By submitting, you agree to our
                <a href="#" style="color:var(--green-600);">Terms of Service</a> and
                <a href="#" style="color:var(--green-600);">Privacy Policy</a>.
                Your application will be reviewed within 3–5 business days.
            </p>
        </div>
    </form>

@endsection

@push('scripts')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Leaflet --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ════════════════════════════════════════════════════════════════
                   OCR DOCUMENT VALIDATION
                   Required: indices 0, 1, 2 → business_permit, dti_registration, bir_registration
                   Optional: index 3+ → any type, OCR runs but does NOT block submit
                   Submit button stays DISABLED until all 3 required are verified ✓
                ════════════════════════════════════════════════════════════════ */

        const OCR_ENDPOINT = '{{ route('vendor.validate.document') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const OCR_TYPES = ['business_permit', 'dti_registration', 'bir_registration',
            'government_id', 'proof_of_address'
        ];
        const REQUIRED_INDICES = [0, 1, 2];
        const REQUIRED_LABELS = {
            0: 'Business Permit',
            1: 'DTI Registration',
            2: 'BIR Certificate',
        };

        // Map: docIndex → { state: 'idle'|'loading'|'valid'|'invalid'|'skipped' }
        const docValidationState = {
            0: {
                state: 'idle'
            },
            1: {
                state: 'idle'
            },
            2: {
                state: 'idle'
            },
        };

         // ── Cavite bounding box ───────────────────────────────────────────
        const CAVITE_BOUNDS = L.latLngBounds(
            L.latLng(13.9000, 120.6000),  // SW
            L.latLng(14.6500, 121.2000)   // NE
        );

        const CAVITE_CENTER = [14.2456, 120.8789];

        // City center coordinates for dropdown → pin sync
        const CITY_COORDS = {
            'Alfonso':                 [14.1167, 120.9333],
            'Amadeo':                  [14.1699, 120.9212],
            'Bacoor':                  [14.4624, 120.9645],
            'Carmona':                 [14.3167, 121.0500],
            'Cavite City':             [14.4791, 120.8970],
            'Dasmariñas':              [14.3294, 120.9367],
            'General Mariano Alvarez': [14.3000, 121.0000],
            'General Trias':           [14.3833, 120.8833],
            'Imus':                    [14.4297, 120.9367],
            'Indang':                  [14.1960, 120.8780],
            'Kawit':                   [14.4395, 120.9009],
            'Magallanes':              [14.1833, 120.7500],
            'Maragondon':              [14.2667, 120.7333],
            'Mendez':                  [14.1333, 120.9000],
            'Naic':                    [14.3167, 120.7667],
            'Noveleta':                [14.4333, 120.8833],
            'Rosario':                 [14.4167, 120.8500],
            'Silang':                  [14.2298, 120.9745],
            'Tagaytay':                [14.0996, 120.9627],
            'Tanza':                   [14.3500, 120.8500],
            'Ternate':                 [14.2833, 120.7167],
            'Trece Martires':          [14.2833, 120.8667],
        };

        const CITY_POSTALS = {
            'Alfonso': '4123', 'Amadeo': '4119', 'Bacoor': '4102',
            'Carmona': '4116', 'Cavite City': '4100', 'Dasmariñas': '4114',
            'General Mariano Alvarez': '4117', 'General Trias': '4107',
            'Imus': '4103', 'Indang': '4122', 'Kawit': '4104',
            'Magallanes': '4126', 'Maragondon': '4124', 'Mendez': '4121',
            'Naic': '4110', 'Noveleta': '4105', 'Rosario': '4106',
            'Silang': '4118', 'Tagaytay': '4120', 'Tanza': '4108',
            'Ternate': '4111', 'Trece Martires': '4109',
        };

        let map, marker;
        let _geocodeTimer = null;

        // ── Wire a doc row ────────────────────────────────────────────────
        function wireDocRow(index) {
            if (!docValidationState[index]) {
                docValidationState[index] = {
                    state: 'idle'
                };
            }

            const fileInput = document.querySelector(`.doc-file-input[data-index="${index}"]`);
            if (!fileInput) return;

            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Fixed rows use data-doc-type; optional rows use a <select>
                const docType = this.dataset.docType ??
                    document.querySelector(`.doc-type-select[data-index="${index}"]`)?.value ??
                    '';

                if (!docType) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select document type first',
                        text: 'Please choose the document type before uploading the file.',
                        confirmButtonColor: '#218544',
                    });
                    this.value = '';
                    return;
                }

                if (!OCR_TYPES.includes(docType)) {
                    setDocState(index, 'skipped', 'No OCR check for this type.');
                    return;
                }

                runOcrValidation(index, file, docType, this);
            });

            // Optional rows: re-validate when type changes and file already chosen
            const typeSelect = document.querySelector(`.doc-type-select[data-index="${index}"]`);
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    const fi = document.querySelector(`.doc-file-input[data-index="${index}"]`);
                    if (!fi?.files?.length) return;
                    if (fi.dataset.docType) fi.dataset.docType = this.value;

                    const docType = this.value;
                    if (!docType) return;

                    if (!OCR_TYPES.includes(docType)) {
                        setDocState(index, 'skipped', 'No OCR check for this type.');
                        return;
                    }
                    runOcrValidation(index, fi.files[0], docType, fi);
                });
            }
        }

        // ── Run OCR via backend ───────────────────────────────────────────
        async function runOcrValidation(index, file, docType, fileInput) {
            setDocState(index, 'loading', 'Scanning…');
            updateChecklist(index, 'loading');
            updateSubmitBtn();

            Swal.fire({
                title: 'Verifying Document…',
                html: `<div style="font-size:.875rem;color:#374151;margin-top:.5rem;">
                           Scanning <strong>${REQUIRED_LABELS[index] ?? docType}</strong>
                           to verify it is issued within the
                           <strong>Province of Cavite</strong>.<br>
                           <span style="font-size:.78rem;color:#6b7280;">
                               This may take a few seconds.
                           </span>
                       </div>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('document_type', docType);
                formData.append('_token', CSRF_TOKEN);

                const response = await fetch(OCR_ENDPOINT, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                Swal.close();

                if (result.valid) {
                    setDocState(index, 'valid', result.message);
                    updateChecklist(index, 'valid');
                    updateSubmitBtn();

                    await Swal.fire({
                        icon: 'success',
                        title: '✅ Document Verified',
                        html: `<div style="font-size:.875rem;color:#374151;">${result.message}</div>`,
                        confirmButtonColor: '#218544',
                        confirmButtonText: 'Great!',
                        timer: 3000,
                        timerProgressBar: true,
                    });

                } else {
                    setDocState(index, 'invalid', result.message);
                    updateChecklist(index, 'invalid');
                    updateSubmitBtn();
                    fileInput.value = '';

                    await Swal.fire({
                        icon: 'error',
                        title: '❌ Document Rejected',
                        html: `<div style="font-size:.875rem;color:#374151;line-height:1.6;">
                                   ${result.message}
                               </div>
                               <div style="margin-top:.75rem;padding:.6rem .85rem;
                                           background:#fef2f2;border-radius:6px;
                                           font-size:.78rem;color:#991b1b;">
                                   <i class="bi bi-info-circle me-1"></i>
                                   Only vendors operating within the
                                   <strong>Province of Cavite</strong>
                                   may register on this platform.
                               </div>`,
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Upload a Different Document',
                    });
                }

            } catch (err) {
                Swal.close();
                setDocState(index, 'invalid', 'Network error during validation.');
                updateChecklist(index, 'invalid');
                updateSubmitBtn();
                fileInput.value = '';

                await Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    html: `<div style="font-size:.875rem;color:#374151;">
                                           Could not connect to the verification service.
                                           Please check your connection and try again.
                                       </div>`,
                    confirmButtonColor: '#dc2626',
                });
            }
        }

        // ── Per-row OCR status badge ───────────────────────────────────────
        function setDocState(index, state, message) {
            docValidationState[index] = {
                state,
                message
            };

            const badge = document.getElementById(`ocrStatus-${index}`);
            if (!badge) return;

            const configs = {
                loading: {
                    color: '#1d4ed8',
                    bg: '#eff6ff',
                    icon: 'bi-hourglass-split',
                    label: 'Scanning…'
                },
                valid: {
                    color: '#15803d',
                    bg: '#f0fdf4',
                    icon: 'bi-check-circle-fill',
                    label: 'Verified ✓'
                },
                invalid: {
                    color: '#dc2626',
                    bg: '#fef2f2',
                    icon: 'bi-x-circle-fill',
                    label: 'Rejected ✗'
                },
                skipped: {
                    color: '#6b7280',
                    bg: '#f9fafb',
                    icon: 'bi-dash-circle',
                    label: 'No check'
                },
            };

            const cfg = configs[state];
            if (!cfg) {
                badge.style.display = 'none';
                return;
            }

            badge.style.cssText = `
                display:flex;align-items:center;gap:.35rem;font-size:.75rem;
                border-radius:4px;padding:.2rem .5rem;
                background:${cfg.bg};color:${cfg.color};`;
            badge.innerHTML = `<i class="bi ${cfg.icon} me-1"></i>${cfg.label}`;
        }

        // ── Required docs checklist panel ─────────────────────────────────
        function updateChecklist(index, state) {
            if (!REQUIRED_INDICES.includes(index)) return;

            const item = document.getElementById(`check-${index}`);
            if (!item) return;

            const label = REQUIRED_LABELS[index];

            if (state === 'valid') {
                item.style.color = '#15803d';
                item.innerHTML = `<i class="bi bi-check-circle-fill"
                                       style="font-size:.8rem;color:#15803d;"></i>
                                    <strong>${label}</strong>`;
            } else if (state === 'invalid') {
                item.style.color = '#dc2626';
                item.innerHTML = `<i class="bi bi-x-circle-fill"
                                       style="font-size:.8rem;color:#dc2626;"></i>
                                    ${label} <span style="font-size:.72rem;">— rejected</span>`;
            } else if (state === 'loading') {
                item.style.color = '#1d4ed8';
                item.innerHTML = `<i class="bi bi-hourglass-split"
                                       style="font-size:.8rem;color:#1d4ed8;"></i>
                                    ${label} <span style="font-size:.72rem;">— scanning…</span>`;
            } else {
                item.style.color = '#6b7280';
                item.innerHTML = `<i class="bi bi-circle" style="font-size:.7rem;"></i> ${label}`;
            }
        }

        // ── Enable submit only when all 3 required are verified ───────────
        function updateSubmitBtn() {
            const allVerified = REQUIRED_INDICES.every(
                i => docValidationState[i]?.state === 'valid'
            );
            const btn = document.getElementById('submitBtn');
            if (!btn) return;

            btn.disabled = !allVerified;
            btn.style.opacity = allVerified ? '1' : '.6';
            btn.style.cursor = allVerified ? 'pointer' : 'not-allowed';
            btn.title = allVerified ? '' :
                'All 3 required documents must be verified first';
        }

        /* ════════════════════════════════════════════════════════════════
           STEP NAVIGATION
        ════════════════════════════════════════════════════════════════ */
        let currentStep = 1;

        function goToStep(n) {
            if (n > currentStep && !validateStep(currentStep)) return;

            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + n).classList.add('active');

            document.querySelectorAll('.step-nav__item').forEach((item, i) => {
                const s = i + 1;
                item.classList.remove('active', 'done');
                if (s === n) item.classList.add('active');
                else if (s < n) {
                    item.classList.add('done');
                    item.querySelector('.step-nav__circle').innerHTML = '<i class="bi bi-check2"></i>';
                } else {
                    item.querySelector('.step-nav__circle').textContent = s;
                }
            });

            currentStep = n;
            if (n === 3 && !window._mapInit) initMap();
        }

        function validateStep(n) {
            const panel = document.getElementById('step' + n);
            const required = panel.querySelectorAll('[required]');
            let valid = true;

            required.forEach(el => {
                el.classList.remove('is-invalid');
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (n === 3) {
                const lat = document.getElementById('latitude').value;
                if (!lat) {
                    document.getElementById('map').style.borderColor = '#dc3545';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Location required',
                        text: 'Please click on the map to pin your business location.',
                        confirmButtonColor: '#218544',
                    });
                    valid = false;
                }
            }

            if (!valid && n < 3) {
                panel.querySelector('.is-invalid')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return valid;
        }

        /* ════════════════════════════════════════════════════════════════
           PASSWORD TOGGLE + STRENGTH
        ════════════════════════════════════════════════════════════════ */
        function togglePw(inputId, btnId) {
            const input = document.getElementById(inputId);
            const icon = document.querySelector('#' + btnId + ' i');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.getElementById('password')?.addEventListener('input', function() {
            const v = this.value;
            const bar = document.getElementById('pwBar');
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const colors = ['#e74c3c', '#e67e22', '#f1c40f', '#2ecc71'];
            const widths = ['25%', '50%', '75%', '100%'];
            bar.style.width = score > 0 ? widths[score - 1] : '0';
            bar.style.background = score > 0 ? colors[score - 1] : 'transparent';
        });

        /* ════════════════════════════════════════════════════════════════
           LEAFLET MAP
        ════════════════════════════════════════════════════════════════ */
        function initMap() {
            window._mapInit = true;

            const oldLat = parseFloat(document.getElementById('latitude').value);
            const oldLng = parseFloat(document.getElementById('longitude').value);
            const hasOld = oldLat && oldLng && CAVITE_BOUNDS.contains([oldLat, oldLng]);

            const initLat = hasOld ? oldLat : CAVITE_CENTER[0];
            const initLng = hasOld ? oldLng : CAVITE_CENTER[1];
            const initZoom = hasOld ? 15 : 11;

            // ── Create map locked to Cavite ───────────────────────────────
            map = L.map('map', {
                center:              [initLat, initLng],
                zoom:                initZoom,
                minZoom:             10,
                maxZoom:             18,
                maxBounds:           CAVITE_BOUNDS,
                maxBoundsViscosity:  1.0,   // hard lock — no panning outside
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(map);

            // ── Province badge overlay ────────────────────────────────────
            const badge = L.control({ position: 'topright' });
            badge.onAdd = () => {
                const d = L.DomUtil.create('div');
                d.innerHTML = `<div style="background:rgba(255,255,255,.88);
                                           border:1.5px solid #218544;border-radius:6px;
                                           padding:.3rem .65rem;font-size:.72rem;
                                           font-weight:700;color:#218544;">
                                   📍 Province of Cavite
                               </div>`;
                return d;
            };
            badge.addTo(map);

            // ── Green pin ─────────────────────────────────────────────────
            const greenIcon = L.divIcon({
                html: `<div style="width:28px;height:28px;background:#218544;
                        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                        border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);"></div>`,
                iconSize:   [28, 28],
                iconAnchor: [14, 28],
                className:  '',
            });

            // Restore old pin if exists
            if (hasOld) {
                marker = L.marker([initLat, initLng], { icon: greenIcon, draggable: true }).addTo(map);
                bindMarkerEvents();
            }

            // ── Map click → place / move pin ──────────────────────────────
            map.on('click', function (e) {
                if (!CAVITE_BOUNDS.contains(e.latlng)) return; // safety guard

                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng, { icon: greenIcon, draggable: true }).addTo(map);
                updateCoords(e.latlng.lat, e.latlng.lng);
                bindMarkerEvents();
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // ── City dropdown → move pin ──────────────────────────────────
            document.getElementById('city')?.addEventListener('change', function () {
                const coords = CITY_COORDS[this.value];
                if (!coords) return;

                map.setView(coords, 14);

                if (marker) map.removeLayer(marker);
                marker = L.marker(coords, { icon: greenIcon, draggable: true }).addTo(map);
                updateCoords(coords[0], coords[1]);
                bindMarkerEvents();
                reverseGeocode(coords[0], coords[1]);

                if (CITY_POSTALS[this.value]) {
                    document.getElementById('postal_code').value = CITY_POSTALS[this.value];
                }
            });
        }

        function bindMarkerEvents() {
            marker.on('dragend', function () {
                const pos = marker.getLatLng();

                // Bounce back if dragged outside Cavite
                if (!CAVITE_BOUNDS.contains(pos)) {
                    const clamped = clampToBounds(pos);
                    marker.setLatLng(clamped);
                    updateCoords(clamped.lat, clamped.lng);
                    reverseGeocode(clamped.lat, clamped.lng);
                    return;
                }

                updateCoords(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });
        }

        function clampToBounds(latlng) {
            return L.latLng(
                Math.max(CAVITE_BOUNDS.getSouth(), Math.min(CAVITE_BOUNDS.getNorth(), latlng.lat)),
                Math.max(CAVITE_BOUNDS.getWest(),  Math.min(CAVITE_BOUNDS.getEast(),  latlng.lng))
            );
        }

         function updateCoords(lat, lng) {
            document.getElementById('latitude').value  = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);

            const display = document.getElementById('coordsDisplay');
            display.style.display = 'flex';
            document.getElementById('coordsText').textContent =
                'Pinned: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            document.getElementById('map').style.borderColor = 'var(--green-400)';
        }

        function reverseGeocode(lat, lng) {
            clearTimeout(_geocodeTimer);
            _geocodeTimer = setTimeout(async () => {
                try {
                    const res  = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`,
                        { headers: { 'Accept-Language': 'en' } }
                    );
                    const data = await res.json();
                    if (!data?.address) return;

                    const addr = data.address;

                    // ── Match city dropdown ───────────────────────────────
                    const nominatimCity =
                        addr.city || addr.town || addr.municipality ||
                        addr.village || addr.suburb || '';

                    const matched = matchCaviteCity(nominatimCity, addr);
                    if (matched) {
                        document.getElementById('city').value = matched;
                        if (CITY_POSTALS[matched]) {
                            document.getElementById('postal_code').value = CITY_POSTALS[matched];
                        }
                    }

                    // ── Address line 1 ────────────────────────────────────
                    const streetParts = [
                        addr.house_number,
                        addr.road || addr.pedestrian || addr.footway,
                        addr.neighbourhood || addr.quarter || addr.suburb,
                    ].filter(Boolean);

                    if (streetParts.length) {
                        document.getElementById('address_line1').value = streetParts.join(', ');
                    }

                } catch (_) {
                    // Silently fail — user can type manually
                }
            }, 600);
        }

        function matchCaviteCity(nominatimCity, addr) {
            const haystack = [
                nominatimCity,
                addr.city_district   ?? '',
                addr.county          ?? '',
                addr.state_district  ?? '',
            ].join(' ').toLowerCase();

            for (const city of Object.keys(CITY_COORDS)) {
                if (haystack.includes(city.toLowerCase())) return city;
                if (city === 'Dasmariñas' && haystack.includes('dasmarinas')) return city;
                if (city === 'General Mariano Alvarez' &&
                    (haystack.includes('gma') || haystack.includes('mariano alvarez'))) return city;
            }
            return null;
        }

       function geocodeAddress() {
            const q = [
                document.getElementById('address_line1').value,
                document.getElementById('city').value,
                'Cavite', 'Philippines',
            ].filter(Boolean).join(', ');

            if (!q || !map) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) return;
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    if (CAVITE_BOUNDS.contains([lat, lng])) {
                        map.setView([lat, lng], 16);
                    }
                })
                .catch(() => {});
        }

        /* ════════════════════════════════════════════════════════════════
           DYNAMIC OPTIONAL DOC ROWS (index 3+)
        ════════════════════════════════════════════════════════════════ */
        let docIndex = 3; // 0–2 are required fixed rows

        document.getElementById('addDocBtn').addEventListener('click', function() {
            if (docIndex >= 11) {
                this.disabled = true;
                this.textContent = 'Maximum additional documents reached';
                return;
            }

            const idx = docIndex;
            const template = `
                <div class="doc-row" data-index="${idx}" id="docRow-${idx}"
                     style="animation:fadeIn .2s ease;">
                    <div class="doc-row__head">
                        <div class="doc-row__icon"><i class="bi bi-file-earmark-text"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:.82rem;font-weight:600;margin-bottom:.3rem;">
                                Supporting Document ${idx - 2}
                            </div>
                        </div>
                        <div id="ocrStatus-${idx}"
                             style="font-size:.75rem;display:none;align-items:center;
                                    gap:.35rem;border-radius:4px;padding:.2rem .5rem;">
                        </div>
                        <button type="button" onclick="removeOptionalDocRow(${idx})"
                                style="background:none;border:none;color:#e74c3c;
                                       cursor:pointer;font-size:1rem;padding:.2rem;margin-left:.5rem;">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-5">
                            <select name="documents[${idx}][type]"
                                    class="form-select doc-type-select"
                                    data-index="${idx}" required>
                                <option value="">Document type…</option>
                                <option value="sec_registration">SEC Registration</option>
                                <option value="government_id">Government ID</option>
                                <option value="proof_of_address">Proof of Address</option>
                                <option value="sme_certificate">SME Certificate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <input type="text" name="documents[${idx}][label]"
                                   class="form-control" placeholder="Brief label" required>
                        </div>
                        <div class="col-12">
                            <input type="file" name="documents[${idx}][file]"
                                   class="form-control doc-file-input"
                                   data-index="${idx}"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                </div>`;

            document.getElementById('docContainer').insertAdjacentHTML('beforeend', template);
            wireDocRow(idx);
            docIndex++;
        });

        function removeOptionalDocRow(idx) {
            document.getElementById(`docRow-${idx}`)?.remove();
            delete docValidationState[idx];
        }

        /* ════════════════════════════════════════════════════════════════
           FORM SUBMIT — blocked until all 3 required are verified
        ════════════════════════════════════════════════════════════════ */
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const anyLoading = Object.values(docValidationState).some(s => s.state === 'loading');
            if (anyLoading) {
                await Swal.fire({
                    icon: 'info',
                    title: 'Please wait',
                    text: 'Document verification is still in progress.',
                    confirmButtonColor: '#218544',
                });
                return;
            }

            const allVerified = REQUIRED_INDICES.every(
                i => docValidationState[i]?.state === 'valid'
            );

            if (!allVerified) {
                // Find which ones are still missing
                const missing = REQUIRED_INDICES
                    .filter(i => docValidationState[i]?.state !== 'valid')
                    .map(i => `<li>${REQUIRED_LABELS[i]}</li>`)
                    .join('');

                await Swal.fire({
                    icon: 'error',
                    title: 'Required Documents Incomplete',
                    html: `<div style="font-size:.875rem;color:#374151;line-height:1.7;">
                                The following documents are still required:<br>
                                <ul style="text-align:left;margin-top:.5rem;padding-left:1.25rem;color:#dc2626;">
                                    ${missing}
                                </ul>
                                Please upload and verify all required documents before submitting.
                            </div>`,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Go Back & Upload',
                });
                return;
            }

            // All verified — submit
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
            this.submit();
        });

        /* ════════════════════════════════════════════════════════════════
           INIT
        ════════════════════════════════════════════════════════════════ */

        // Wire the 3 fixed required rows
        [0, 1, 2].forEach(i => wireDocRow(i));

        // Submit disabled until all 3 verified
        updateSubmitBtn();

        // Restore step on Laravel validation error
        @if ($errors->has('latitude') || $errors->has('longitude'))
            document.addEventListener('DOMContentLoaded', () => goToStep(3));
        @elseif ($errors->hasAny(['address_line1', 'city', 'province_state', 'postal_code']))
            document.addEventListener('DOMContentLoaded', () => goToStep(3));
        @elseif ($errors->hasAny(['business_name', 'business_type', 'business_registration_number', 'business_phone']))
            document.addEventListener('DOMContentLoaded', () => goToStep(2));
        @elseif ($errors->has('documents'))
            document.addEventListener('DOMContentLoaded', () => goToStep(4));
        @endif

        // Fade-in animation for dynamically added rows
        const style = document.createElement('style');
        style.textContent =
            '@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(style);
    </script>
@endpush
