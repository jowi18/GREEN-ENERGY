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
                        <option value="sole_proprietorship"
                            {{ old('business_type') === 'sole_proprietorship' ? 'selected' : '' }}>Sole Proprietorship
                        </option>
                        <option value="partnership"
                            {{ old('business_type') === 'partnership' ? 'selected' : '' }}>Partnership</option>
                        <option value="corporation"
                            {{ old('business_type') === 'corporation' ? 'selected' : '' }}>Corporation</option>
                        <option value="cooperative"
                            {{ old('business_type') === 'cooperative' ? 'selected' : '' }}>Cooperative</option>
                        <option value="sme" {{ old('business_type') === 'sme' ? 'selected' : '' }}>SME
                        </option>
                        <option value="other" {{ old('business_type') === 'other' ? 'selected' : '' }}>
                            Other</option>
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
        {{-- STEP 3 — Business address + Leaflet map   --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="step-panel" id="step3">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Street address <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="address_line1" id="address_line1"
                        class="form-control @error('address_line1') is-invalid @enderror"
                        value="{{ old('address_line1') }}" placeholder="123 Solar Street, Barangay…" required>
                    @error('address_line1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Address line 2</label>
                    <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}"
                        placeholder="Unit / Floor / Building (optional)">
                </div>
                <div class="col-6">
                    <label class="form-label">City / Municipality <span
                            style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="city" id="city"
                        class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}"
                        placeholder="Quezon City" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Province / Region <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="province_state" id="province_state"
                        class="form-control @error('province_state') is-invalid @enderror"
                        value="{{ old('province_state') }}" placeholder="Metro Manila" required>
                    @error('province_state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Postal code <span style="color:var(--danger,#e74c3c)">*</span></label>
                    <input type="text" name="postal_code"
                        class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code') }}"
                        placeholder="1100" required>
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control"
                        value="{{ old('country', 'Philippines') }}" readonly>
                </div>
            </div>

            {{-- Hidden lat/lng fields --}}
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            <div style="margin-top:1rem;">
                <label class="form-label" style="margin-bottom:0.4rem;">
                    <i class="bi bi-geo-alt me-1" style="color:var(--green-600)"></i>
                    Pin your business location
                    <span style="color:var(--danger,#e74c3c)">*</span>
                </label>
                <p style="font-size:0.78rem;color:#5a7060;margin-bottom:0.65rem;">
                    Click anywhere on the map to drop your pin. The marker can be dragged to the exact location.
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
                    <div style="color:#dc3545;font-size:0.82rem;margin-top:0.3rem;">
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

        {{-- ══════════════════════════════════════════ --}}
        {{-- STEP 4 — Document uploads                 --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="step-panel" id="step4">
            <p style="font-size:0.82rem;color:#5a7060;margin-bottom:1rem;">
                Upload at least one document. Accepted formats: PDF, JPG, PNG (max 5 MB each).
            </p>

            <div id="docContainer">
                {{-- First document row (always shown) --}}
                <div class="doc-row" data-index="0">
                    <div class="doc-row__head">
                        <div class="doc-row__icon"><i class="bi bi-file-earmark-text"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:600;margin-bottom:0.3rem;">Document 1</div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-5">
                            <select name="documents[0][type]" class="form-select" required>
                                <option value="">Document type…</option>
                                <option value="business_permit">Business Permit</option>
                                <option value="dti_registration">DTI Registration</option>
                                <option value="sec_registration">SEC Registration</option>
                                <option value="bir_registration">BIR Certificate</option>
                                <option value="government_id">Government ID</option>
                                <option value="proof_of_address">Proof of Address</option>
                                <option value="sme_certificate">SME Certificate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <input type="text" name="documents[0][label]" class="form-control"
                                placeholder="Brief label (e.g. Business Permit 2024)" required>
                        </div>
                        <div class="col-12">
                            <input type="file" name="documents[0][file]" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn-add-doc" id="addDocBtn">
                <i class="bi bi-plus-circle"></i> Add another document
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
                <button type="submit" class="btn-primary-green ms-auto" id="submitBtn">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        /* ── Step navigation ─────────────────────────────────── */
        let currentStep = 1;

        function goToStep(n) {
            if (n > currentStep && !validateStep(currentStep)) return;

            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + n).classList.add('active');

            // Update step nav
            document.querySelectorAll('.step-nav__item').forEach((item, i) => {
                const s = i + 1;
                item.classList.remove('active', 'done');
                if (s === n) item.classList.add('active');
                else if (s < n) {
                    item.classList.add('done');
                    item.querySelector('.step-nav__circle').innerHTML = '<i class="bi bi-check2"></i>';
                } else item.querySelector('.step-nav__circle').textContent = s;
            });

            currentStep = n;

            // Init map when entering step 3
            if (n === 3 && !window._mapInit) initMap();
        }

        /* ── Step validation ─────────────────────────────────── */
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
                        confirmButtonColor: '#218544'
                    });
                    valid = false;
                }
            }

            if (!valid && n < 3) {
                const first = panel.querySelector('.is-invalid');
                first?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return valid;
        }

        /* ── Password visibility toggle ─────────────────────── */
        function togglePw(inputId, btnId) {
            const input = document.getElementById(inputId);
            const icon = document.querySelector('#' + btnId + ' i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        /* ── Password strength ───────────────────────────────── */
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

        /* ── Leaflet map ─────────────────────────────────────── */
        let map, marker;

        function initMap() {
            window._mapInit = true;

            const defaultLat = {{ old('latitude', 14.5995) }};
            const defaultLng = {{ old('longitude', 120.9842) }};

            map = L.map('map').setView([defaultLat, defaultLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const greenIcon = L.divIcon({
                html: `<div style="width:28px;height:28px;background:#218544;border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);border:3px solid #fff;
                    box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                className: '',
            });

            // If old values exist, place marker
            const oldLat = document.getElementById('latitude').value;
            const oldLng = document.getElementById('longitude').value;
            if (oldLat && oldLng) {
                marker = L.marker([oldLat, oldLng], {
                    icon: greenIcon,
                    draggable: true
                }).addTo(map);
                bindMarkerEvents();
            }

            // Click to place / move marker
            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng, {
                    icon: greenIcon,
                    draggable: true
                }).addTo(map);
                updateCoords(e.latlng.lat, e.latlng.lng);
                bindMarkerEvents();
            });

            // Try to geocode address fields → auto-center map
            document.getElementById('address_line1')?.addEventListener('blur', geocodeAddress);
            document.getElementById('city')?.addEventListener('blur', geocodeAddress);
        }

        function bindMarkerEvents() {
            marker.on('dragend', function() {
                const pos = marker.getLatLng();
                updateCoords(pos.lat, pos.lng);
            });
        }

        function updateCoords(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            const display = document.getElementById('coordsDisplay');
            display.style.display = 'flex';
            document.getElementById('coordsText').textContent =
                'Pinned: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            document.getElementById('map').style.borderColor = 'var(--green-400)';
        }

        function geocodeAddress() {
            const q = [
                document.getElementById('address_line1').value,
                document.getElementById('city').value,
                document.getElementById('province_state').value,
                'Philippines'
            ].filter(Boolean).join(', ');

            if (!q || !map) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (data.length) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        map.setView([lat, lng], 16);
                    }
                })
                .catch(() => {});
        }

        /* ── Dynamic document rows ───────────────────────────── */
        let docIndex = 1;

        document.getElementById('addDocBtn').addEventListener('click', function() {
            if (docIndex >= 8) {
                this.disabled = true;
                this.textContent = 'Maximum 8 documents';
                return;
            }

            const template = `
    <div class="doc-row" data-index="${docIndex}" style="animation:fadeIn 0.2s ease;">
        <div class="doc-row__head">
            <div class="doc-row__icon"><i class="bi bi-file-earmark-text"></i></div>
            <div style="flex:1;">
                <div style="font-size:0.82rem;font-weight:600;margin-bottom:0.3rem;">Document ${docIndex + 1}</div>
            </div>
            <button type="button" onclick="this.closest('.doc-row').remove()"
                    style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:1rem;padding:0.2rem;">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-5">
                <select name="documents[${docIndex}][type]" class="form-select" required>
                    <option value="">Document type…</option>
                    <option value="business_permit">Business Permit</option>
                    <option value="dti_registration">DTI Registration</option>
                    <option value="sec_registration">SEC Registration</option>
                    <option value="bir_registration">BIR Certificate</option>
                    <option value="government_id">Government ID</option>
                    <option value="proof_of_address">Proof of Address</option>
                    <option value="sme_certificate">SME Certificate</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-7">
                <input type="text" name="documents[${docIndex}][label]" class="form-control"
                       placeholder="Brief label" required>
            </div>
            <div class="col-12">
                <input type="file" name="documents[${docIndex}][file]" class="form-control"
                       accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
        </div>
    </div>`;

            document.getElementById('docContainer').insertAdjacentHTML('beforeend', template);
            docIndex++;
        });

        /* ── Form submit loading state ───────────────────────── */
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        });

        /* ── If Laravel redirects back with errors, restore step ── */
        @if ($errors->has('latitude') || $errors->has('longitude'))
            document.addEventListener('DOMContentLoaded', () => goToStep(3));
        @elseif ($errors->hasAny(['address_line1', 'city', 'province_state', 'postal_code']))
            document.addEventListener('DOMContentLoaded', () => goToStep(3));
        @elseif ($errors->hasAny(['business_name', 'business_type', 'business_registration_number', 'business_phone']))
            document.addEventListener('DOMContentLoaded', () => goToStep(2));
        @elseif ($errors->hasAny(['documents']))
            document.addEventListener('DOMContentLoaded', () => goToStep(4));
        @endif

        // Animate step panels
        const style = document.createElement('style');
        style.textContent =
            '@keyframes fadeIn { from { opacity:0;transform:translateY(6px) } to { opacity:1;transform:translateY(0) } }';
        document.head.appendChild(style);
    </script>
@endpush
