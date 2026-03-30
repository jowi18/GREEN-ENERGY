@extends('layouts.vendor')
@section('title', 'Solar Project · ' . $solarProject->project_number)
@section('page-title', 'Solar Project')

@section('breadcrumb')
    <a href="{{ route('vendor.solar.index') }}" class="text-secondary">Solar Projects</a>
    <span class="sep">›</span>
    <span>{{ $solarProject->project_number }}</span>
    <span class="sep">›</span>
    <span class="current">Edit</span>
@endsection

@push('styles')
    <style>
        /* ── Activity log ── */
        .act-log {
            position: relative;
            padding-left: 1.5rem;
        }

        .act-log::before {
            content: '';
            position: absolute;
            left: .45rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--card-border);
        }

        .act-item {
            position: relative;
            margin-bottom: .85rem;
        }

        .act-item:last-child {
            margin-bottom: 0;
        }

        .act-dot {
            position: absolute;
            left: -1.27rem;
            top: .2rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--g-500);
            border: 2px solid var(--card-bg);
        }

        .act-time {
            font-size: .7rem;
            color: var(--tx-muted);
        }

        .act-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        .act-note {
            font-size: .78rem;
            color: var(--tx-secondary);
            margin-top: .1rem;
        }

        /* ── Doc grid ── */
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: .5rem;
        }

        .doc-tile {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s;
            display: block;
        }

        .doc-tile:hover {
            border-color: var(--g-400);
        }

        .doc-tile__img {
            width: 100%;
            height: 72px;
            object-fit: cover;
            background: var(--n-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tx-muted);
            font-size: 1.4rem;
        }

        .doc-tile__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-tile__label {
            padding: .3rem .45rem;
            font-size: .65rem;
            font-weight: 600;
            color: var(--tx-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Requirement checklist ── */
        .req-check {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            padding: .3rem 0;
        }

        .req-check i {
            font-size: .8rem;
            flex-shrink: 0;
        }

        .req-check.met i {
            color: var(--g-500);
        }

        .req-check.unmet i {
            color: #f59e0b;
        }

        .req-check.met span {
            color: var(--tx-secondary);
        }

        .req-check.unmet span {
            color: #92400e;
            font-weight: 600;
        }

        /* ── Blocked btn ── */
        .vd-btn--blocked {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
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
            background: var(--card-bg);
        }

        .option-card:hover,
        .option-card:active {
            border-color: var(--g-400);
            background: var(--n-50);
        }

        .option-card.selected {
            border-color: var(--g-500);
            background: var(--n-50);
        }

        .option-card input[type="radio"] {
            display: none;
        }

        /* ── Locked fields ────────────────────────────────────────────────── */
        .locked-field__label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--tx-muted);
            margin-bottom: .15rem;
        }

        .locked-field__value {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
            line-height: 1.4;
        }

        /* ── form-hint ────────────────────────────────────────────────────── */
        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .25rem;
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('vendor.solar.update', $solarProject) }}" id="editForm">
        @csrf
        @method('PATCH')

        {{-- ── Validation errors ───────────────────────────────────────────── --}}
        @if ($errors->any())
            <div class="vd-card mb-3" style="border-color:#fca5a5;background:#fef2f2;">
                <div class="vd-card__body">
                    <div class="fw-700 mb-1" style="font-size:.82rem;color:#dc2626;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size:.78rem;color:#dc2626;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════
             CARD 1 — Locked Information
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-shield-lock"></i> Locked Information
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="locked-field__label">Vendor</div>
                        <div class="locked-field__value">{{ $solarProject->vendor->business_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="locked-field__label">Property Type</div>
                        <div class="locked-field__value">
                            @php
                                $ptLabels = [
                                    'residential' => '🏠 Residential',
                                    'commercial' => '🏢 Commercial',
                                    'industrial' => '🏭 Industrial',
                                    'agricultural' => '🌾 Agricultural',
                                ];
                            @endphp
                            {{ $ptLabels[$solarProject->property_type] ?? $solarProject->property_type }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="locked-field__label">Project #</div>
                        <div class="locked-field__value">{{ $solarProject->project_number }}</div>
                    </div>
                    <div class="col-12">
                        <div class="locked-field__label">Installation Address</div>
                        <div class="locked-field__value">
                            {{ implode(
                                ', ',
                                array_filter([
                                    $solarProject->installation_address,
                                    $solarProject->installation_city,
                                    $solarProject->installation_province,
                                ]),
                            ) }}
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-2" style="border-top:1px solid var(--n-100);">
                    <span style="font-size:.72rem;color:var(--tx-muted);">
                        <i class="bi bi-lock-fill me-1" style="color:var(--g-500);"></i>
                        These fields cannot be changed after submission.
                    </span>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             CARD 2 — Roof Details
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-house-fill"></i> Roof Details
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="vd-label">Roof Type</label>
                        <select name="roof_type" class="vd-select @error('roof_type') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach ([
            'concrete' => 'Concrete',
            'metal' => 'Metal',
            'asphalt_shingle' => 'Asphalt Shingle',
            'clay_tile' => 'Clay Tile',
            'other' => 'Other',
        ] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('roof_type', $solarProject->roof_type) === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                        @error('roof_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Roof Area (sqm)</label>
                        <input type="number" name="roof_area_sqm"
                            value="{{ old('roof_area_sqm', $solarProject->roof_area_sqm) }}"
                            class="vd-input @error('roof_area_sqm') is-invalid @enderror" placeholder="e.g. 80"
                            min="0" step="1" inputmode="numeric">
                        @error('roof_area_sqm')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             CARD 3 — System Preferences
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-lightning-charge-fill"></i> System Preferences
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">

                    {{-- System type --}}
                    <div class="col-12">
                        <label class="vd-label">System Type</label>
                        <div class="row g-2 mt-0">
                            @foreach ([
            'grid_tie' => ['🔌', 'Grid-Tie', 'Connected to utility grid'],
            'off_grid' => ['🔋', 'Off-Grid', 'Standalone with battery'],
            'hybrid' => ['⚡', 'Hybrid', 'Grid + battery backup'],
        ] as $val => [$emoji, $lbl, $desc])
                                @php $isSelected = old('system_type', $solarProject->system_type) === $val; @endphp
                                <div class="col-md-4 col-6">
                                    <label class="option-card {{ $isSelected ? 'selected' : '' }}"
                                        onclick="selectOption(this, 'system_type', '{{ $val }}')">
                                        <input type="radio" name="system_type" value="{{ $val }}"
                                            {{ $isSelected ? 'checked' : '' }}>
                                        <div style="font-size:1.5rem;margin-bottom:.3rem;">{{ $emoji }}</div>
                                        <div class="fw-700" style="font-size:.82rem;">{{ $lbl }}</div>
                                        <div
                                            style="font-size:.72rem;color:var(--tx-muted);line-height:1.35;margin-top:.1rem;">
                                            {{ $desc }}
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('system_type')
                            <div class="text-danger mt-1" style="font-size:.78rem;">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Select the type of solar system preferred.</div>
                    </div>

                    <div class="col-12">
                        <hr style="border-color:var(--n-100);margin:.25rem 0;">
                    </div>

                    {{-- Monthly bill --}}
                    <div class="col-md-6">
                        <label class="vd-label">Monthly Electric Bill (₱)</label>
                        <input type="number" name="estimated_monthly_bill"
                            value="{{ old('estimated_monthly_bill', $solarProject->estimated_monthly_bill) }}"
                            class="vd-input @error('estimated_monthly_bill') is-invalid @enderror" placeholder="e.g. 5000"
                            min="0" step="0.01" inputmode="decimal">
                        @error('estimated_monthly_bill')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Helps estimate the right system size.</div>
                    </div>

                    {{-- Desired system size --}}
                    <div class="col-md-6">
                        <label class="vd-label">Desired System Size (kW)</label>
                        <input type="number" name="desired_system_size_kw"
                            value="{{ old('desired_system_size_kw', $solarProject->desired_system_size_kw) }}"
                            class="vd-input @error('desired_system_size_kw') is-invalid @enderror" placeholder="e.g. 5"
                            min="0" step="0.1" inputmode="decimal">
                        @error('desired_system_size_kw')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Leave blank if unsure.</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             CARD 4 — Additional Notes
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-chat-text-fill"></i> Additional Notes
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="vd-label">Customer Notes</label>
                        <textarea name="customer_notes" rows="4" class="vd-input @error('customer_notes') is-invalid @enderror"
                            placeholder="Shade issues, roof access, special requirements…" style="resize:vertical;">{{ old('customer_notes', $solarProject->customer_notes) }}</textarea>
                        @error('customer_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Any additional details the vendor should know about the site.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ─────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('vendor.solar.show', $solarProject) }}" class="vd-btn vd-btn--ghost">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="vd-btn vd-btn--primary" id="saveBtn" style="min-width:160px;">
                <i class="bi bi-floppy-fill me-1"></i> Save Changes
            </button>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        function selectOption(el, name, val) {
            el.closest('.row')
                .querySelectorAll('.option-card')
                .forEach(c => c.classList.remove('selected'));

            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;
        }

        document.getElementById('editForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        });
    </script>
@endpush
