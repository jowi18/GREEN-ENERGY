{{-- resources/views/customer/solar/create.blade.php --}}
@extends('layouts.customer')
@section('title', 'New Solar Inquiry')

@push('styles')
    <style>
        /* ── Step nav ─────────────────────────────────────────────────────── */
        .step-nav {
            display: flex;
            gap: 0;
            margin-bottom: 1.75rem;
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1.5px solid var(--card-border);
        }

        .step-nav__item {
            flex: 1;
            padding: .65rem .35rem;
            text-align: center;
            font-size: .72rem;
            font-weight: 700;
            color: var(--tx-muted);
            background: var(--card-bg);
            border-right: 1px solid var(--card-border);
            transition: all .2s;
            cursor: default;
            line-height: 1.3;
        }

        .step-nav__item:last-child {
            border-right: none;
        }

        .step-nav__item.active {
            background: var(--cg-600);
            color: #fff;
        }

        .step-nav__item.done {
            background: var(--cg-50);
            color: var(--cg-700);
        }

        .step-nav__num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .25);
            font-size: .68rem;
            margin-right: .25rem;
        }

        /* Hide labels on very small screens, show only numbers */
        @media (max-width: 380px) {
            .step-nav__label {
                display: none;
            }

            .step-nav__num {
                margin-right: 0;
            }
        }

        /* ── Step panels ──────────────────────────────────────────────────── */
        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        /* ── Option cards ─────────────────────────────────────────────────── */
        .option-card {
            border: 2px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .85rem .75rem;
            cursor: pointer;
            transition: all .15s;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            display: block;
            width: 100%;
        }

        .option-card:hover,
        .option-card:active {
            border-color: var(--cg-300);
            background: var(--cg-50);
        }

        .option-card.selected {
            border-color: var(--cg-500);
            background: var(--cg-50);
        }

        .option-card input[type="radio"] {
            display: none;
        }

        /* 2-col on mobile, keep as-is on desktop */
        .option-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .option-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .5rem;
        }

        @media (max-width: 480px) {
            .option-grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ── Photo upload ─────────────────────────────────────────────────── */
        .photo-upload-zone {
            border: 2px dashed var(--card-border);
            border-radius: var(--r-md);
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .photo-upload-zone:hover,
        .photo-upload-zone:active {
            border-color: var(--cg-400);
            background: var(--cg-50);
        }

        .photo-previews {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .75rem;
        }

        .photo-preview-item {
            width: 68px;
            height: 68px;
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1.5px solid var(--card-border);
            position: relative;
            flex-shrink: 0;
        }

        @media (max-width: 400px) {
            .photo-preview-item {
                width: 60px;
                height: 60px;
            }
        }

        .photo-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-preview-item__rm {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239, 68, 68, .9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: .65rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        /* ── Form rows ────────────────────────────────────────────────────── */
        .field-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        @media (max-width: 480px) {
            .field-row-2 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Nav buttons ──────────────────────────────────────────────────── */
        .step-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-top: 1rem;
        }

        .step-actions--end {
            justify-content: flex-end;
        }

        @media (max-width: 480px) {
            .step-actions {
                flex-direction: column-reverse;
                gap: .5rem;
            }

            .step-actions .cu-btn {
                width: 100%;
                justify-content: center;
            }

            .step-actions--end {
                flex-direction: column;
            }
        }

        /* ── Vendor card ──────────────────────────────────────────────────── */
        .vendor-badge {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            border: 1.5px solid var(--cg-200);
            background: var(--cg-50);
            border-radius: var(--r-md);
            margin-bottom: 1rem;
        }

        .vendor-badge__logo {
            width: 40px;
            height: 40px;
            border-radius: var(--r-sm);
            object-fit: cover;
            flex-shrink: 0;
        }

        /* ── Summary card ─────────────────────────────────────────────────── */
        .ready-card {
            border: 1.5px solid var(--cg-200);
            background: var(--cg-50);
            border-radius: var(--r-md);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        /* ── Misc ─────────────────────────────────────────────────────────── */
        .hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .2rem;
        }

        .req {
            color: #ef4444;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 480px) {
            .page-header {
                gap: .5rem;
                margin-bottom: 1.25rem;
            }
        }
    </style>
@endpush

@section('content')
    <div style="max-width:680px;margin:0 auto;padding:0 .25rem;">

        {{-- Page header --}}
        <div class="page-header">
            <a href="{{ route('customer.solar.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm"
                style="flex-shrink:0;margin-top:.1rem;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;font-size:1.05rem;">
                    ☀️ New Solar Installation Inquiry
                </h5>
                <div class="text-muted" style="font-size:.78rem;margin-top:.15rem;line-height:1.4;">
                    Fill in the details — the vendor will review and contact you.
                </div>
            </div>
        </div>

        {{-- Step nav --}}
        <div class="step-nav" id="stepNav">
            <div class="step-nav__item active" id="nav-1">
                <span class="step-nav__num">1</span><span class="step-nav__label">Property</span>
            </div>
            <div class="step-nav__item" id="nav-2">
                <span class="step-nav__num">2</span><span class="step-nav__label">System</span>
            </div>
            <div class="step-nav__item" id="nav-3">
                <span class="step-nav__num">3</span><span class="step-nav__label">Location</span>
            </div>
            <div class="step-nav__item" id="nav-4">
                <span class="step-nav__num">4</span><span class="step-nav__label">Docs</span>
            </div>
        </div>

        <form method="POST" action="{{ route('customer.solar.store') }}" enctype="multipart/form-data" id="inquiryForm">
            @csrf

            {{-- Vendor selection --}}
            @if ($vendor)
                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                <div class="vendor-badge">
                    @if ($vendor->shop_logo)
                        <img src="{{ Storage::url($vendor->shop_logo) }}" class="vendor-badge__logo">
                    @else
                        <div class="vendor-badge__logo"
                            style="background:var(--cg-100);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-shop" style="color:var(--cg-500);font-size:1.1rem;"></i>
                        </div>
                    @endif
                    <div style="min-width:0;">
                        <div class="fw-700"
                            style="font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $vendor->business_name }}
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">Selected vendor</div>
                    </div>
                    <i class="bi bi-patch-check-fill ms-auto" style="color:var(--cg-500);flex-shrink:0;"></i>
                </div>
            @else
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <label class="cu-label">Select Vendor / Solar Company <span class="req">*</span></label>
                        <select name="vendor_id" class="cu-input @error('vendor_id') is-invalid @enderror" required
                            style="font-size:.875rem;">
                            <option value="">— Choose a vendor —</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->business_name }} — {{ $v->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            {{-- ── Step 1: Property ──────────────────────────────────────── --}}
            <div class="step-panel active" id="step-1">
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-house-fill text-green me-1"></i> Property Details
                        </div>

                        <div class="mb-3">
                            <label class="cu-label">Property Type <span class="req">*</span></label>
                            <div class="option-grid-2">
                                @foreach ([
                                        'residential' => ['🏠', 'Residential', 'House, apartment, condo'],
                                        'commercial' => ['🏢', 'Commercial', 'Office, store, mall'],
                                        'industrial' => ['🏭', 'Industrial', 'Factory, warehouse'],
                                        'agricultural' => ['🌾', 'Agricultural', 'Farm, greenhouse'],
                                    ] as $val => [$emoji, $label, $desc])
                                    <label class="option-card {{ old('property_type') === $val ? 'selected' : '' }}"
                                        onclick="selectOption(this,'property_type','{{ $val }}')">
                                        <input type="radio" name="property_type" value="{{ $val }}"
                                            {{ old('property_type') === $val ? 'checked' : '' }}>
                                        <div style="font-size:1.5rem;margin-bottom:.3rem;">{{ $emoji }}</div>
                                        <div class="fw-700" style="font-size:.82rem;">{{ $label }}</div>
                                        <div class="text-muted" style="font-size:.7rem;line-height:1.3;margin-top:.15rem;">
                                            {{ $desc }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('property_type')
                                <div style="color:#ef4444;font-size:.78rem;margin-top:.3rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- <div class="field-row-2">
                            <div>
                                <label class="cu-label">Roof Type</label>
                                <select name="roof_type" class="cu-input">
                                    <option value="">— Select —</option>
                                    @foreach ([
                                        'concrete' => 'Concrete',
                                        'metal' => 'Metal',
                                        'asphalt_shingle' => 'Asphalt Shingle',
                                        'clay_tile' => 'Clay Tile',
                                        'other' => 'Other',
                                    ] as $val => $lbl)
                                        <option value="{{ $val }}"
                                            {{ old('roof_type') === $val ? 'selected' : '' }}>
                                            {{ $lbl }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="cu-label">Roof Area (sqm)</label>
                                <input type="number" name="roof_area_sqm" value="{{ old('roof_area_sqm') }}"
                                    class="cu-input" placeholder="e.g. 80" min="0" step="1"
                                    inputmode="numeric">
                            </div>
                        </div> --}}
                    </div>
                </div>

                <div class="step-actions step-actions--end">
                    <button type="button" class="cu-btn cu-btn--primary" onclick="goStep(2)">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            {{-- ── Step 2: System ────────────────────────────────────────── --}}
            <div class="step-panel" id="step-2">
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-lightning-charge-fill text-green me-1"></i> System Preferences
                        </div>

                        {{-- <div class="mb-3">
                            <label class="cu-label">System Type</label>
                            <div class="option-grid-3">
                                @foreach ([
                                        'grid_tie' => ['🔌', 'Grid-Tie', 'Connected to utility grid'],
                                        'off_grid' => ['🔋', 'Off-Grid', 'Standalone with battery'],
                                        'hybrid' => ['⚡', 'Hybrid', 'Grid + battery backup'],
                                    ] as $val => [$emoji, $lbl, $desc])
                                    <label class="option-card {{ old('system_type') === $val ? 'selected' : '' }}"
                                        onclick="selectOption(this,'system_type','{{ $val }}')">
                                        <input type="radio" name="system_type" value="{{ $val }}"
                                            {{ old('system_type') === $val ? 'checked' : '' }}>
                                        <div style="font-size:1.4rem;margin-bottom:.3rem;">{{ $emoji }}</div>
                                        <div class="fw-700" style="font-size:.78rem;">{{ $lbl }}</div>
                                        <div class="text-muted"
                                            style="font-size:.68rem;line-height:1.35;margin-top:.15rem;">
                                            {{ $desc }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div> --}}

                        <div class="field-row-2">
                            <div>
                                <label class="cu-label">Monthly Electric Bill (₱)</label>
                                <input type="number" name="estimated_monthly_bill"
                                    value="{{ old('estimated_monthly_bill') }}" class="cu-input" placeholder="e.g. 5000"
                                    min="0" step="0.01" inputmode="decimal">
                                <div class="hint">Helps estimate the right system size</div>
                            </div>
                            <div>
                                <label class="cu-label">Desired System Size (kW)</label>
                                <input type="number" name="desired_system_size_kw"
                                    value="{{ old('desired_system_size_kw') }}" class="cu-input" placeholder="e.g. 5"
                                    min="0" step="0.1" inputmode="decimal">
                                <div class="hint">Leave blank if unsure</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="cu-btn cu-btn--ghost" onclick="goStep(1)">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </button>
                    <button type="button" class="cu-btn cu-btn--primary" onclick="goStep(3)">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            {{-- ── Step 3: Location ──────────────────────────────────────── --}}
            <div class="step-panel" id="step-3">
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-geo-alt-fill text-green me-1"></i> Installation Location
                        </div>

                        <div class="mb-3">
                            <label class="cu-label">Installation Address <span class="req">*</span></label>
                            <input type="text" name="installation_address"
                                value="{{ old('installation_address', auth()->user()->customer->address_line1 ?? '') }}"
                                class="cu-input @error('installation_address') is-invalid @enderror"
                                placeholder="Street address, barangay…" autocomplete="street-address" required>
                            @error('installation_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field-row-2 mb-3">
                            <div>
                                <label class="cu-label">City <span class="req">*</span></label>
                                <input type="text" name="installation_city"
                                    value="{{ old('installation_city', auth()->user()->customer->city ?? '') }}"
                                    class="cu-input @error('installation_city') is-invalid @enderror"
                                    autocomplete="address-level2" required>
                                @error('installation_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="cu-label">Province <span class="req">*</span></label>
                                <input type="text" name="installation_province"
                                    value="{{ old('installation_province', auth()->user()->customer->province_state ?? '') }}"
                                    class="cu-input @error('installation_province') is-invalid @enderror"
                                    autocomplete="address-level1" required>
                                @error('installation_province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="cu-label">Additional Notes</label>
                            <textarea name="customer_notes" rows="3" class="cu-textarea"
                                placeholder="Shade issues, roof access, special requirements…" style="resize:vertical;">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="cu-btn cu-btn--ghost" onclick="goStep(2)">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </button>
                    <button type="button" class="cu-btn cu-btn--primary" onclick="goStep(4)">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            {{-- ── Step 4: Documents ─────────────────────────────────────── --}}
            <div class="step-panel" id="step-4">

                {{-- Site photos --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-camera-fill text-green me-1"></i> Site Photos
                            <span class="text-muted fw-400" style="font-size:.78rem;">(optional, max 6)</span>
                        </div>
                        <div class="text-muted mb-2" style="font-size:.78rem;line-height:1.5;">
                            Photos of your roof, electrical panel, and property help the vendor plan accurately.
                        </div>

                        <div class="photo-upload-zone" onclick="document.getElementById('sitePhotosInput').click()">
                            <i class="bi bi-cloud-upload" style="font-size:1.75rem;color:var(--cg-400);"></i>
                            <div class="fw-600" style="font-size:.85rem;margin-top:.5rem;">
                                Tap to upload site photos
                            </div>
                            <div class="text-muted" style="font-size:.72rem;margin-top:.2rem;">
                                JPG, PNG, WebP · Max 5 MB each
                            </div>
                            <input type="file" id="sitePhotosInput" name="site_photos[]" accept="image/*" multiple
                                capture="environment" style="display:none;" onchange="previewPhotos(this)">
                        </div>
                        <div class="photo-previews" id="photoPreviews"></div>
                    </div>
                </div>

                {{-- Electric bill --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-receipt text-green me-1"></i> Latest Electric Bill
                            <span class="text-muted fw-400" style="font-size:.78rem;">(optional)</span>
                        </div>
                        <div class="text-muted mb-2" style="font-size:.78rem;">
                            Helps the vendor accurately size your solar system.
                        </div>
                        <input type="file" name="electric_bill" class="cu-input" accept=".jpg,.jpeg,.png,.pdf"
                            style="padding:.45rem .75rem;font-size:.82rem;">
                        <div class="hint mt-1">JPG, PNG, or PDF · Max 5 MB</div>
                    </div>
                </div>

                {{-- Ready summary --}}
                <div class="ready-card">
                    <div class="fw-700 mb-1" style="font-size:.85rem;color:var(--cg-700);">
                        <i class="bi bi-check2-circle me-1"></i> Ready to submit
                    </div>
                    <div class="text-muted" style="font-size:.78rem;line-height:1.6;">
                        After submitting, the vendor will review your inquiry and reach out to
                        schedule a consultation. Track progress and respond to quotations in
                        real time from your dashboard.
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="cu-btn cu-btn--ghost" onclick="goStep(3)">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </button>
                    <button type="submit" class="cu-btn cu-btn--primary" id="submitBtn" style="min-width:160px;">
                        <i class="bi bi-send-check me-1"></i> Submit Inquiry
                    </button>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let currentStep = 1;

        function goStep(step) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`nav-${currentStep}`).classList.remove('active');
            document.getElementById(`nav-${currentStep}`).classList.add('done');

            currentStep = step;
            document.getElementById(`step-${step}`).classList.add('active');

            document.querySelectorAll('.step-nav__item').forEach((el, i) => {
                const s = i + 1;
                el.classList.toggle('active', s === step);
                el.classList.toggle('done', s < step);
            });

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function selectOption(el, name, value) {
            el.closest('.option-grid-2, .option-grid-3')
                ?.querySelectorAll('.option-card')
                .forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector(`input[name="${name}"]`).checked = true;
        }

        // ── Photo previews ─────────────────────────────────────────────────
        let pendingPhotos = [];

        function previewPhotos(input) {
            if (pendingPhotos.filter(Boolean).length + input.files.length > 6) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Too many photos',
                    text: 'Maximum 6 photos allowed.',
                    confirmButtonColor: '#15803d',
                });
                input.value = '';
                return;
            }
            [...input.files].forEach(file => {
                pendingPhotos.push(file);
                const idx = pendingPhotos.length - 1;
                const r = new FileReader();
                r.onload = e => {
                    const div = document.createElement('div');
                    div.className = 'photo-preview-item';
                    div.id = `prev-${idx}`;
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Site photo">
                        <button type="button" class="photo-preview-item__rm"
                                onclick="removePhoto(${idx})" aria-label="Remove">
                            <i class="bi bi-x"></i>
                        </button>`;
                    document.getElementById('photoPreviews').appendChild(div);
                };
                r.readAsDataURL(file);
            });
        }

        function removePhoto(idx) {
            document.getElementById(`prev-${idx}`)?.remove();
            pendingPhotos[idx] = null;
        }

        // ── Submit with PayPal confirmation ────────────────────────────────
        document.getElementById('inquiryForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;

            // ── Show site visit package confirmation ───────────────────────
            const confirmed = await Swal.fire({
                title: '☀️ Submit Solar Inquiry',
                html: `
                    <div style="text-align:left;font-size:.875rem;color:#374151;line-height:1.7;">

                        <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:8px;
                                    padding:.85rem 1rem;margin-bottom:1rem;">
                            <div style="font-weight:800;color:#15803d;font-size:.95rem;margin-bottom:.4rem;">
                                🔍 Site Visit & Assessment Package
                            </div>
                            <ul style="padding-left:1.1rem;margin:0;color:#374151;font-size:.82rem;line-height:1.9;">
                                <li>On-site roof inspection & structural check</li>
                                <li>Shading & solar irradiance analysis</li>
                                <li>Electrical panel & energy load evaluation</li>
                                <li>System size & energy yield estimation</li>
                                <li>Installation feasibility report</li>
                                <li>Formal quotation preparation</li>
                            </ul>
                        </div>

                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;
                                    padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;">
                            <strong>📌 Note:</strong> A licensed solar engineer will contact you within
                            <strong>1–2 business days</strong> to schedule the visit.
                            If you proceed to installation, this fee will be
                            <strong>credited</strong> toward your total project cost.
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center;
                                    background:#15803d;color:#fff;border-radius:8px;
                                    padding:.85rem 1rem;">
                            <span style="font-weight:700;">Site Visit Fee</span>
                            <span style="font-size:1.2rem;font-weight:900;font-family:monospace;">
                                ₱1,500.00
                            </span>
                        </div>

                        <div style="text-align:center;margin-top:.75rem;font-size:.78rem;color:#6b7280;">
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg"
                                 style="height:20px;vertical-align:middle;margin-right:4px;"
                                 alt="PayPal">
                            You will be redirected to PayPal to complete the payment securely.
                        </div>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: '💳 Pay ₱1,500 via PayPal',
                cancelButtonText: 'Review Inquiry',
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                width: 520,
                customClass: {
                    confirmButton: 'swal-confirm-paypal',
                },
            });

            if (!confirmed.isConfirmed) return;

            // ── Rebuild file input from pending photos ─────────────────────
            const dt = new DataTransfer();
            pendingPhotos.filter(Boolean).forEach(f => dt.items.add(f));
            document.getElementById('sitePhotosInput').files = dt.files;

            // ── Loading state on button ────────────────────────────────────
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting to PayPal…';

            // ── Show redirecting Swal ──────────────────────────────────────
            Swal.fire({
                title: 'Redirecting to PayPal…',
                html: `<div style="font-size:.875rem;color:#374151;margin-top:.5rem;">
                                       Please wait while we redirect you to PayPal to complete
                                       your payment of <strong>₱1,500.00</strong> securely.
                                   </div>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });

            // Submit the form
            form.submit();
        });

        // ── Restore old() state after validation failure ───────────────────
        @if (old('property_type'))
            document.querySelector(`.option-card [value="{{ old('property_type') }}"]`)
                ?.closest('.option-card')?.classList.add('selected');
        @endif
        @if (old('system_type'))
            document.querySelector(`.option-card [value="{{ old('system_type') }}"]`)
                ?.closest('.option-card')?.classList.add('selected');
        @endif
        @if ($errors->any())
            @if ($errors->has('installation_address') || $errors->has('installation_city') || $errors->has('installation_province'))
                goStep(3);
            @elseif ($errors->has('property_type'))
                goStep(1);
            @endif
        @endif

        // ── Success flash from PayPal return ──────────────────────────────
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Payment Confirmed!',
                html: `<div style="font-size:.875rem;color:#374151;">
                                       {{ session('success') }}
                                   </div>`,
                confirmButtonColor: '#15803d',
                confirmButtonText: 'View Invoice',
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Payment Cancelled',
                html: `<div style="font-size:.875rem;color:#374151;">
                                       {{ session('warning') }}
                                   </div>`,
                confirmButtonColor: '#d97706',
            });
        @endif
    </script>
@endpush
