@extends('layouts.vendor')
@section('title', 'Store Settings')
@section('page-title', 'Store Settings')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* ── Tab sidebar layout ──────────────────────── */
        .settings-shell {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 860px) {
            .settings-shell {
                grid-template-columns: 1fr;
            }
        }

        .settings-nav {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            position: sticky;
            top: calc(var(--topbar-h, 64px) + 1rem);
        }

        .settings-nav__item {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1.1rem;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 600;
            color: var(--tx-secondary);
            border-left: 3px solid transparent;
            transition: all .12s;
        }

        .settings-nav__item:hover {
            background: var(--g-50);
            color: var(--g-700);
        }

        .settings-nav__item.active {
            background: var(--g-50);
            color: var(--g-700);
            font-weight: 800;
            border-left-color: var(--g-500);
        }

        .settings-nav__item i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
        }

        .settings-nav__divider {
            padding: .4rem 1.1rem .1rem;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--tx-muted);
        }

        /* ── Section panels ──────────────────────────── */
        .settings-section {
            display: none;
        }

        .settings-section.active {
            display: block;
        }

        /* ── Branding preview area ───────────────────── */
        .brand-preview {
            border-radius: var(--r-xl);
            overflow: hidden;
            border: 1.5px solid var(--card-border);
            background: var(--n-50);
            margin-bottom: 1.25rem;
        }

        .brand-preview__cover {
            height: 140px;
            background: linear-gradient(135deg, var(--g-200), var(--g-400));
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-preview__cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-preview__cover-placeholder {
            color: rgba(255, 255, 255, .5);
            font-size: .82rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .35rem;
        }

        .brand-preview__cover-placeholder i {
            font-size: 2rem;
        }

        .brand-preview__logo-wrap {
            position: absolute;
            bottom: -24px;
            left: 1.25rem;
            width: 60px;
            height: 60px;
            border-radius: var(--r-md);
            background: var(--card-bg);
            border: 3px solid var(--card-bg);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .14);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--g-700);
        }

        .brand-preview__logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-preview__body {
            padding: 2.2rem 1.25rem .85rem;
        }

        .brand-preview__name {
            font-weight: 800;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
        }

        .brand-preview__tagline {
            font-size: .82rem;
            color: var(--tx-muted);
            margin-top: .15rem;
        }

        /* ── Upload zones ────────────────────────────── */
        .upload-zone {
            border: 2px dashed var(--card-border);
            border-radius: var(--r-md);
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all .15s;
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--g-400);
            background: var(--g-50);
        }

        .upload-zone input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-zone i {
            font-size: 1.5rem;
            color: var(--tx-muted);
            display: block;
            margin-bottom: .35rem;
        }

        .upload-zone.has-file {
            border-color: var(--g-400);
            background: var(--g-50);
        }

        .img-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--r-md);
            border: 1.5px solid var(--card-border);
        }

        /* ── Hours grid ──────────────────────────────── */
        .hours-grid {
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .hours-row {
            display: grid;
            grid-template-columns: 90px auto 1fr 12px 1fr;
            align-items: center;
            gap: .65rem;
        }

        .hours-row__day {
            font-weight: 700;
            font-size: .82rem;
        }

        .toggle-switch {
            position: relative;
            width: 38px;
            height: 20px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-switch__track {
            position: absolute;
            inset: 0;
            background: var(--n-200);
            border-radius: 999px;
            cursor: pointer;
            transition: background .2s;
        }

        .toggle-switch input:checked+.toggle-switch__track {
            background: var(--g-500);
        }

        .toggle-switch__thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            transition: transform .2s;
        }

        .toggle-switch input:checked~.toggle-switch__thumb {
            transform: translateX(18px);
        }

        /* ── Highlights chips editor ─────────────────── */
        .highlight-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .45rem;
        }

        .highlight-item input {
            flex: 1;
        }

        /* ── Social icon colors ──────────────────────── */
        .social-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .85rem;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--r-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            color: #fff;
            flex-shrink: 0;
        }

        .social-icon--fb {
            background: #1877f2;
        }

        .social-icon--ig {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        }

        .social-icon--yt {
            background: #ff0000;
        }

        .social-icon--tt {
            background: #000;
        }

        .social-icon--ws {
            background: #25d366;
        }

        .social-icon--vb {
            background: #7360f2;
        }

        .social-icon--web {
            background: var(--g-600);
        }

        /* ── Map ─────────────────────────────────────── */
        #settingsMap {
            height: 240px;
            border-radius: var(--r-md);
            border: 1.5px solid var(--card-border);
        }

        /* ── Toggle checkbox custom ──────────────────── */
        .pref-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 0;
            border-bottom: 1px solid var(--n-100);
        }

        .pref-toggle:last-child {
            border-bottom: none;
        }

        .pref-toggle__info {
            flex: 1;
        }

        .pref-toggle__label {
            font-weight: 700;
            font-size: .875rem;
        }

        .pref-toggle__desc {
            font-size: .78rem;
            color: var(--tx-muted);
        }
    </style>
@endpush

@section('content')

    <div class="settings-shell">

        {{-- ══ Left Nav ══ --}}
        <div class="settings-nav">
            <div class="settings-nav__divider">Store</div>
            @foreach ([['branding', 'bi-shop', 'Branding'], ['highlights', 'bi-stars', 'Highlights'], ['hours', 'bi-clock', 'Operating Hours']] as [$id, $icon, $label])
                <div class="settings-nav__item {{ $id === 'branding' ? 'active' : '' }}"
                    onclick="switchSection('{{ $id }}')" id="nav-{{ $id }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </div>
            @endforeach

            <div class="settings-nav__divider mt-1">Info</div>
            @foreach ([['contact', 'bi-telephone', 'Contact & Social'], ['location', 'bi-geo-alt', 'Location'], ['policies', 'bi-file-text', 'Policies']] as [$id, $icon, $label])
                <div class="settings-nav__item" onclick="switchSection('{{ $id }}')" id="nav-{{ $id }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </div>
            @endforeach

            <div class="settings-nav__divider mt-1">System</div>
            @foreach ([['preferences', 'bi-toggles', 'Preferences & SEO']] as [$id, $icon, $label])
                <div class="settings-nav__item" onclick="switchSection('{{ $id }}')"
                    id="nav-{{ $id }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </div>
            @endforeach
        </div>

        {{-- ══ Right Content ══ --}}
        <div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: BRANDING
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section active" id="section-branding">

                {{-- Live preview --}}
                <div class="brand-preview" id="brandPreview">
                    <div class="brand-preview__cover" id="prevCoverWrap">
                        @if ($vendor->cover_photo)
                            <img src="{{ asset('storage/' . $vendor->cover_photo) }}" id="prevCoverImg" alt="Cover">
                        @else
                            <div class="brand-preview__cover-placeholder" id="prevCoverPlaceholder">
                                <i class="bi bi-image"></i>
                                <span>Cover photo</span>
                            </div>
                        @endif
                        <div class="brand-preview__logo-wrap" id="prevLogoWrap">
                            @if ($vendor->shop_logo)
                                <img src="{{ asset('storage/' . $vendor->shop_logo) }}" id="prevLogoImg" alt="Logo">
                            @else
                                <span id="prevLogoText">{{ strtoupper(substr($vendor->business_name, 0, 2)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="brand-preview__body">
                        <div class="brand-preview__name" id="prevName">{{ $vendor->business_name }}</div>
                        <div class="brand-preview__tagline" id="prevTagline">
                            {{ $vendor->tagline ?? 'Your tagline appears here' }}</div>
                        @if ($vendor->average_rating > 0)
                            <div style="font-size:.78rem;color:var(--tx-muted);margin-top:.35rem;">
                                ⭐ {{ $vendor->average_rating }} · {{ $vendor->total_reviews }} reviews
                            </div>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('vendor.settings.branding') }}" enctype="multipart/form-data"
                    class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-shop"></i> Branding</span>
                    </div>
                    <div class="vd-card__body">

                        <div class="row g-3 mb-4">
                            {{-- Logo --}}
                            <div class="col-md-6">
                                <label class="vd-label">Shop Logo</label>
                                <div class="upload-zone" id="logoZone"
                                    onclick="document.getElementById('logoFile').click()">
                                    <input type="file" id="logoFile" name="shop_logo" accept="image/*"
                                        onchange="previewLogo(this)" style="display:none;">
                                    @if ($vendor->shop_logo)
                                        <img src="{{ asset('storage/' . $vendor->shop_logo) }}" class="img-thumb"
                                            id="logoPreview" style="margin-bottom:.5rem;">
                                    @else
                                        <i class="bi bi-badge-ad"></i>
                                    @endif
                                    <div style="font-size:.78rem;font-weight:600;color:var(--tx-secondary);">
                                        Click to upload logo
                                    </div>
                                    <div class="text-muted" style="font-size:.7rem;">JPG, PNG, WebP · max 2MB · Recommended:
                                        400×400px</div>
                                </div>
                                @if ($vendor->shop_logo)
                                    <label
                                        style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;cursor:pointer;font-size:.78rem;color:#ef4444;">
                                        <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                                    </label>
                                @endif
                            </div>

                            {{-- Cover --}}
                            <div class="col-md-6">
                                <label class="vd-label">Cover / Banner Photo</label>
                                <div class="upload-zone" id="coverZone"
                                    onclick="document.getElementById('coverFile').click()">
                                    <input type="file" id="coverFile" name="cover_photo" accept="image/*"
                                        onchange="previewCover(this)" style="display:none;">
                                    @if ($vendor->cover_photo)
                                        <img src="{{ asset('storage/' . $vendor->cover_photo) }}"
                                            style="width:100%;height:80px;object-fit:cover;border-radius:var(--r-sm);margin-bottom:.5rem;"
                                            id="coverPreview">
                                    @else
                                        <i class="bi bi-image"></i>
                                    @endif
                                    <div style="font-size:.78rem;font-weight:600;color:var(--tx-secondary);">
                                        Click to upload cover photo
                                    </div>
                                    <div class="text-muted" style="font-size:.7rem;">JPG, PNG, WebP · max 5MB · Recommended:
                                        1200×400px</div>
                                </div>
                                @if ($vendor->cover_photo)
                                    <label
                                        style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;cursor:pointer;font-size:.78rem;color:#ef4444;">
                                        <input type="checkbox" name="remove_cover" value="1"> Remove current cover
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="vd-label">Business Name <span class="req">*</span></label>
                                <input type="text" name="business_name"
                                    value="{{ old('business_name', $vendor->business_name) }}"
                                    class="vd-input @error('business_name') is-invalid @enderror"
                                    oninput="document.getElementById('prevName').textContent = this.value" required
                                    maxlength="120">
                                @error('business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="vd-label">Year Established</label>
                                <input type="number" name="year_established"
                                    value="{{ old('year_established', $vendor->year_established) }}" class="vd-input"
                                    min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Tagline <span class="form-hint d-inline">Short catchy phrase (max
                                        160 chars)</span></label>
                                <input type="text" name="tagline" value="{{ old('tagline', $vendor->tagline) }}"
                                    class="vd-input" maxlength="160"
                                    placeholder="e.g. Your trusted solar energy partner in Cebu"
                                    oninput="document.getElementById('prevTagline').textContent = this.value || 'Your tagline appears here'">
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Short Description <span class="form-hint d-inline">(shown on store
                                        listing, max 500)</span></label>
                                <textarea name="shop_description" rows="2" class="vd-input" maxlength="500"
                                    placeholder="Brief summary shown in search results…">{{ old('shop_description', $vendor->shop_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="vd-label">About / Full Story <span class="form-hint d-inline">(shown on your
                                        store page)</span></label>
                                <textarea name="about" rows="5" class="vd-input" maxlength="4000"
                                    placeholder="Tell customers your story — how you started, your mission, experience, what makes you different…">{{ old('about', $vendor->about) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Branding</button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: HIGHLIGHTS
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-highlights">
                <form method="POST" action="{{ route('vendor.settings.highlights') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-stars"></i> Highlights & Certifications</span>
                    </div>
                    <div class="vd-card__body">

                        <div class="fw-700 mb-2" style="font-size:.875rem;">
                            🌟 Store Highlights
                            <span class="form-hint fw-400">(up to 8 — shown as bullet points on your store)</span>
                        </div>
                        <p class="text-muted mb-3" style="font-size:.78rem;">
                            Examples: "Licensed DTI-registered installer", "10+ years experience", "500+ installations
                            completed"
                        </p>
                        <div id="highlightsList">
                            @php $highlights = $vendor->highlights ?? ['', '', '']; @endphp
                            @foreach ($highlights as $i => $h)
                                <div class="highlight-item" id="hl-{{ $i }}">
                                    <i class="bi bi-check-circle-fill"
                                        style="color:var(--g-400);font-size:.9rem;flex-shrink:0;"></i>
                                    <input type="text" name="highlights[]" value="{{ $h }}"
                                        class="vd-input" placeholder="e.g. 5-year installation guarantee…"
                                        maxlength="100">
                                    <button type="button" onclick="removeHighlight({{ $i }})"
                                        class="vd-btn vd-btn--ghost vd-btn--icon">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addHighlight()" class="vd-btn vd-btn--ghost vd-btn--sm mt-1">
                            <i class="bi bi-plus-lg"></i> Add Highlight
                        </button>

                        <hr style="border-color:var(--n-100);margin:1.5rem 0;">

                        <div class="fw-700 mb-2" style="font-size:.875rem;">
                            🏅 Certifications & Awards
                            <span class="form-hint fw-400">(up to 6)</span>
                        </div>
                        <div id="certsList">
                            @php $certs = $vendor->certifications ?? []; @endphp
                            @foreach ($certs as $i => $cert)
                                <div class="row g-2 mb-2 align-items-center" id="cert-{{ $i }}">
                                    <div class="col-5">
                                        <input type="text" name="certifications[{{ $i }}][name]"
                                            value="{{ $cert['name'] ?? '' }}" class="vd-input"
                                            placeholder="Certificate / Award name">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="certifications[{{ $i }}][issuer]"
                                            value="{{ $cert['issuer'] ?? '' }}" class="vd-input"
                                            placeholder="Issued by">
                                    </div>
                                    <div class="col-2">
                                        <input type="number" name="certifications[{{ $i }}][year]"
                                            value="{{ $cert['year'] ?? '' }}" class="vd-input" placeholder="Year"
                                            min="1990" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-1">
                                        <button type="button" onclick="removeCert({{ $i }})"
                                            class="vd-btn vd-btn--danger vd-btn--icon"><i
                                                class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            @endforeach
                            @if (empty($certs))
                                <div class="row g-2 mb-2 align-items-center" id="cert-0">
                                    <div class="col-5"><input type="text" name="certifications[0][name]"
                                            class="vd-input" placeholder="Certificate / Award name"></div>
                                    <div class="col-4"><input type="text" name="certifications[0][issuer]"
                                            class="vd-input" placeholder="Issued by"></div>
                                    <div class="col-2"><input type="number" name="certifications[0][year]"
                                            class="vd-input" placeholder="Year" min="1990"
                                            max="{{ date('Y') }}"></div>
                                    <div class="col-1"><button type="button" onclick="removeCert(0)"
                                            class="vd-btn vd-btn--danger vd-btn--icon"><i
                                                class="bi bi-trash"></i></button></div>
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addCert()" class="vd-btn vd-btn--ghost vd-btn--sm mt-1">
                            <i class="bi bi-plus-lg"></i> Add Certification
                        </button>
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Highlights</button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: OPERATING HOURS
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-hours">
                @php
                    $savedHours = $vendor->operating_hours ?? [];
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $defaults = ['from' => '08:00', 'to' => '17:00'];
                @endphp
                <form method="POST" action="{{ route('vendor.settings.hours') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-clock"></i> Operating Hours</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="hours-grid mb-4">
                            @foreach ($days as $i => $day)
                                @php
                                    $dayKey = strtolower(substr($day, 0, 3));
                                    $h = $savedHours[$i] ?? [
                                        'open' => in_array($i, [1, 2, 3, 4, 5]),
                                        'from' => '08:00',
                                        'to' => '17:00',
                                    ];
                                @endphp
                                <div class="hours-row">
                                    <span class="hours-row__day">{{ $day }}</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="hours[{{ $dayKey }}][open]" value="1"
                                            id="toggle-{{ $i }}" {{ $h['open'] ? 'checked' : '' }}
                                            onchange="toggleDay({{ $i }}, this.checked)">
                                        <span class="toggle-switch__track"></span>
                                        <span class="toggle-switch__thumb"></span>
                                    </label>
                                    <input type="time" name="hours[{{ $dayKey }}][from]"
                                        value="{{ $h['from'] ?? '08:00' }}" class="vd-input"
                                        id="from-{{ $i }}" style="font-size:.82rem;padding:.4rem .65rem;"
                                        {{ $h['open'] ? '' : 'disabled' }}>
                                    <span style="color:var(--tx-muted);font-size:.78rem;text-align:center;">to</span>
                                    <input type="time" name="hours[{{ $dayKey }}][to]"
                                        value="{{ $h['to'] ?? '17:00' }}" class="vd-input" id="to-{{ $i }}"
                                        style="font-size:.82rem;padding:.4rem .65rem;" {{ $h['open'] ? '' : 'disabled' }}>
                                </div>
                            @endforeach
                        </div>

                        <hr style="border-color:var(--n-100);margin:1rem 0;">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="pref-toggle" style="padding:.5rem 0;border:none;">
                                    <div class="pref-toggle__info">
                                        <div class="pref-toggle__label">Temporarily Closed</div>
                                        <div class="pref-toggle__desc">Override hours — show "Temporarily Closed" on your
                                            store</div>
                                    </div>
                                    <label class="toggle-switch ms-3">
                                        <input type="checkbox" name="is_open_now_override" value="1"
                                            {{ $vendor->is_open_now_override ? 'checked' : '' }}>
                                        <span class="toggle-switch__track"></span>
                                        <span class="toggle-switch__thumb"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Closure Note <span class="form-hint d-inline">(shown to
                                        customers)</span></label>
                                <input type="text" name="temporary_closure_note"
                                    value="{{ old('temporary_closure_note', $vendor->temporary_closure_note) }}"
                                    class="vd-input" maxlength="255"
                                    placeholder="e.g. Closed for holiday until Jan 2. We'll be back!">
                            </div>
                        </div>
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Hours</button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: CONTACT & SOCIAL
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-contact">
                <form method="POST" action="{{ route('vendor.settings.contact') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-telephone"></i> Contact & Social</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="fw-700 mb-3" style="font-size:.875rem;">📞 Contact Information</div>
                        <div class="row g-3 mb-4">
                            @foreach ([['business_phone', 'Primary Phone', 'tel', 'bi-telephone', '+63 9XX XXX XXXX', 'bg-secondary'], ['support_phone', 'Support Phone', 'tel', 'bi-headset', '+63 9XX XXX XXXX', 'bg-secondary'], ['business_email', 'Business Email', 'email', 'bi-envelope', 'info@shop.com', 'bg-secondary'], ['support_email', 'Support Email', 'email', 'bi-envelope-check', 'support@shop.com', 'bg-secondary'], ['whatsapp', 'WhatsApp', 'text', 'bi-whatsapp', '+63 9XX XXX XXXX', 'bg-success'], ['viber', 'Viber', 'text', 'bi-phone-vibrate', '+63 9XX XXX XXXX', 'bg-purple'], ['business_website', 'Website', 'url', 'bi-globe', 'https://yourshop.com', 'bg-secondary']] as [$field, $label, $type, $icon, $ph, $bg])
                                <div class="col-md-6">
                                    <label class="vd-label">{{ $label }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"
                                            style="background:var(--n-50);border-color:var(--card-border);">
                                            <i class="bi {{ $icon }}" style="font-size:.9rem;"></i>
                                        </span>
                                        <input type="{{ $type }}" name="{{ $field }}"
                                            value="{{ old($field, $vendor->$field) }}" class="vd-input"
                                            style="border-radius:0 var(--r-md) var(--r-md) 0!important;"
                                            placeholder="{{ $ph }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr style="border-color:var(--n-100);margin:1rem 0 1.5rem;">

                        <div class="fw-700 mb-3" style="font-size:.875rem;">📱 Social Media</div>
                        @foreach ([['social_facebook', 'Facebook Page URL', 'social-icon--fb', 'bi-facebook', 'https://facebook.com/yourpage'], ['social_instagram', 'Instagram Profile', 'social-icon--ig', 'bi-instagram', 'https://instagram.com/yourshop'], ['social_youtube', 'YouTube Channel', 'social-icon--yt', 'bi-youtube', 'https://youtube.com/@yourchannel'], ['social_tiktok', 'TikTok Profile', 'social-icon--tt', 'bi-tiktok', 'https://tiktok.com/@yourshop']] as [$field, $label, $iconCls, $icon, $ph])
                            <div class="social-row">
                                <div class="social-icon {{ $iconCls }}"><i class="bi {{ $icon }}"></i>
                                </div>
                                <div style="flex:1;">
                                    <label class="vd-label mb-1">{{ $label }}</label>
                                    <input type="url" name="{{ $field }}"
                                        value="{{ old($field, $vendor->$field) }}" class="vd-input"
                                        placeholder="{{ $ph }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save Contact
                            Info</button>
                    </div>
                </form>
            </div>

            {{-- ── Location Section ─────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-location">
                <form method="POST" action="{{ route('vendor.settings.location') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-geo-alt"></i> Location</span>
                    </div>
                    <div class="vd-card__body">

                        {{-- Map --}}
                        <div id="settingsMap" class="mb-2"
                            style="border-radius:var(--r-md);overflow:hidden;border:1.5px solid var(--card-border);"></div>

                        <p class="text-muted mb-3" style="font-size:.78rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Click or drag the pin anywhere within <strong>Cavite Province</strong> to set your exact
                            location.
                            City and address fields will auto-fill.
                            <button type="button" onclick="detectLocation()"
                                class="vd-btn vd-btn--ghost vd-btn--sm ms-2">
                                <i class="bi bi-crosshair"></i> Use My Location
                            </button>
                        </p>

                        <div class="row g-3">

                            {{-- Address Line 1 --}}
                            <div class="col-12">
                                <label class="vd-label">Address Line 1 <span class="req">*</span></label>
                                <input type="text" name="address_line1" id="addressLine1Input"
                                    value="{{ old('address_line1', $vendor->address_line1) }}"
                                    class="vd-input @error('address_line1') is-invalid @enderror"
                                    placeholder="Street, barangay…" required>
                                @error('address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Address Line 2 --}}
                            <div class="col-12">
                                <label class="vd-label">Address Line 2</label>
                                <input type="text" name="address_line2"
                                    value="{{ old('address_line2', $vendor->address_line2) }}" class="vd-input"
                                    placeholder="Unit, building, subdivision…">
                            </div>

                            {{-- City dropdown — Cavite municipalities only --}}
                            <div class="col-md-4">
                                <label class="vd-label">City / Municipality <span class="req">*</span></label>
                                <select name="city" id="citySelect"
                                    class="vd-select @error('city') is-invalid @enderror" required>
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
                                        $selectedCity = old('city', $vendor->city);
                                    @endphp
                                    @foreach ($caviteCities as $city)
                                        <option value="{{ $city }}"
                                            {{ $selectedCity === $city ? 'selected' : '' }}>
                                            {{ $city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Province — locked to Cavite --}}
                            <div class="col-md-4">
                                <label class="vd-label">Province / State</label>
                                <input type="text" name="province_state" value="Cavite" class="vd-input" readonly
                                    style="background:var(--n-50);color:var(--tx-muted);cursor:not-allowed;">
                                <div style="font-size:.7rem;color:var(--tx-muted);margin-top:.25rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Restricted to Cavite Province
                                </div>
                            </div>

                            {{-- Postal Code --}}
                            <div class="col-md-2">
                                <label class="vd-label">Postal Code</label>
                                <input type="text" name="postal_code" id="postalInput"
                                    value="{{ old('postal_code', $vendor->postal_code) }}" class="vd-input"
                                    placeholder="4xxx">
                            </div>

                            {{-- Country — locked --}}
                            <div class="col-md-2">
                                <label class="vd-label">Country</label>
                                <input type="text" name="country" value="Philippines" class="vd-input" readonly
                                    style="background:var(--n-50);color:var(--tx-muted);cursor:not-allowed;">
                            </div>

                            {{-- Coordinates (read-only display) --}}
                            <div class="col-md-6">
                                <label class="vd-label">Latitude</label>
                                <div style="position:relative;">
                                    <input type="number" name="latitude" id="latInput" step="any"
                                        value="{{ old('latitude', $vendor->latitude) }}" class="vd-input"
                                        placeholder="Auto-filled from map" readonly
                                        style="background:var(--n-50);color:var(--tx-muted);padding-right:2.5rem;">
                                    <i class="bi bi-pin-map"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                  color:var(--tx-muted);font-size:.85rem;pointer-events:none;"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Longitude</label>
                                <div style="position:relative;">
                                    <input type="number" name="longitude" id="lngInput" step="any"
                                        value="{{ old('longitude', $vendor->longitude) }}" class="vd-input"
                                        placeholder="Auto-filled from map" readonly
                                        style="background:var(--n-50);color:var(--tx-muted);padding-right:2.5rem;">
                                    <i class="bi bi-pin-map"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                  color:var(--tx-muted);font-size:.85rem;pointer-events:none;"></i>
                                </div>
                            </div>

                            {{-- Service Area --}}
                            <div class="col-12">
                                <label class="vd-label">Service Coverage Area</label>
                                <input type="text" name="service_area"
                                    value="{{ old('service_area', $vendor->service_area ?? '') }}" class="vd-input"
                                    maxlength="255" placeholder="e.g. Bacoor, Imus, General Trias, Dasmariñas">
                            </div>

                        </div>
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-floppy"></i> Save Location
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: POLICIES
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-policies">
                <form method="POST" action="{{ route('vendor.settings.policies') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-file-text"></i> Store Policies</span>
                    </div>
                    <div class="vd-card__body">
                        <p class="text-muted mb-4" style="font-size:.82rem;">
                            These policies are shown to customers on your store page and during checkout. Clear policies
                            build customer trust.
                        </p>
                        @foreach ([['return_policy', '↩️ Return & Refund Policy', "We accept returns within 7 days of purchase for defective items.\n• Items must be in original condition\n• Proof of purchase required\n• Processing takes 3–5 business days"], ['warranty_policy', '🛡️ Warranty Policy', "All solar products come with manufacturer warranty:\n• Solar panels: 25-year performance warranty\n• Inverters: 5-10 year warranty\n• Installation labor: 1-year workmanship warranty"], ['payment_terms', '💳 Payment Terms', "We accept:\n• Cash on Delivery (COD) for Metro Cebu\n• GCash, Maya, BankTransfer\n• 50% downpayment required for installation services"]] as [$field, $label, $placeholder])
                            <div class="mb-4">
                                <label class="vd-label">{{ $label }}</label>
                                <textarea name="{{ $field }}" rows="5" class="vd-input" maxlength="4000"
                                    placeholder="{{ $placeholder }}" style="font-family:monospace;font-size:.82rem;">{{ old($field, $vendor->$field) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Policies</button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────────────────
             SECTION: PREFERENCES & SEO
        ───────────────────────────────────────────────────────────── --}}
            <div class="settings-section" id="section-preferences">
                <form method="POST" action="{{ route('vendor.settings.preferences') }}" class="vd-card">
                    @csrf @method('PUT')
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-toggles"></i> Preferences & SEO</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="fw-700 mb-3" style="font-size:.875rem;">⚙️ Store Preferences</div>

                        @foreach ([['accept_online_orders', 'Accept Online Orders', 'Allow customers to purchase products from your store.'], ['accept_service_bookings', 'Accept Service Bookings', 'Allow customers to book your services online.'], ['show_reviews_publicly', 'Show Reviews Publicly', 'Display customer reviews on your store page.'], ['show_operating_hours', 'Show Operating Hours', 'Display your operating hours on your store page.']] as [$field, $label, $desc])
                            <div class="pref-toggle">
                                <div class="pref-toggle__info">
                                    <div class="pref-toggle__label">{{ $label }}</div>
                                    <div class="pref-toggle__desc">{{ $desc }}</div>
                                </div>
                                <label class="toggle-switch ms-3">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        {{ $vendor->$field ?? true ? 'checked' : '' }}>
                                    <span class="toggle-switch__track"></span>
                                    <span class="toggle-switch__thumb"></span>
                                </label>
                            </div>
                        @endforeach

                        <hr style="border-color:var(--n-100);margin:1.5rem 0;">

                        <div class="fw-700 mb-3" style="font-size:.875rem;">🔍 SEO / Search Optimization</div>
                        <p class="text-muted mb-3" style="font-size:.78rem;">
                            These fields improve how your store appears in search results on the platform.
                        </p>
                        <div class="mb-3">
                            <label class="vd-label">SEO Title <span class="form-hint d-inline">max 160
                                    chars</span></label>
                            <input type="text" name="seo_title" value="{{ old('seo_title', $vendor->seo_title) }}"
                                class="vd-input" maxlength="160" id="seoTitle"
                                placeholder="{{ $vendor->business_name }} — Solar Panels & Installation in {{ $vendor->city }}"
                                oninput="updateSeoPreview()">
                            <div style="font-size:.7rem;color:var(--tx-muted);text-align:right;margin-top:.2rem;"
                                id="seoTitleCount">0/160</div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">SEO Description <span class="form-hint d-inline">max 320 chars — shown
                                    in search snippets</span></label>
                            <textarea name="seo_description" rows="3" class="vd-input" maxlength="320" id="seoDesc"
                                placeholder="Licensed solar energy provider offering residential and commercial installation, maintenance, and repair services in {{ $vendor->city }}."
                                oninput="updateSeoPreview()">{{ old('seo_description', $vendor->seo_description) }}</textarea>
                            <div style="font-size:.7rem;color:var(--tx-muted);text-align:right;margin-top:.2rem;"
                                id="seoDescCount">0/320</div>
                        </div>

                        {{-- SEO preview --}}
                        <div
                            style="border:1.5px solid var(--card-border);border-radius:var(--r-md);padding:1rem;background:var(--n-50);">
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--tx-muted);margin-bottom:.5rem;">
                                Search Preview</div>
                            <div style="font-size:.875rem;color:#1a0dab;font-weight:600;" id="seoPreviewTitle">
                                {{ $vendor->seo_title ?? $vendor->business_name }}
                            </div>
                            <div style="font-size:.72rem;color:#006621;">
                                solarapp.com/vendors/{{ $vendor->id }}
                            </div>
                            <div style="font-size:.78rem;color:var(--tx-secondary);margin-top:.2rem;line-height:1.5;"
                                id="seoPreviewDesc">
                                {{ $vendor->seo_description ?? ($vendor->shop_description ?? 'No description set.') }}
                            </div>
                        </div>
                    </div>
                    <div class="vd-card__footer">
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Preferences</button>
                    </div>
                </form>
            </div>

        </div>{{-- /right --}}
    </div>{{-- /settings-shell --}}
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let mapInited = false;
        let locMap, locMarker;
        let geocodeTimer = null;

        // ── Cavite bounding box ───────────────────────────────────────────────
        const CAVITE_BOUNDS = L.latLngBounds(
            L.latLng(13.9000, 120.6000), // SW corner
            L.latLng(14.6500, 121.2000) // NE corner
        );

        // Default center of Cavite Province
        const CAVITE_CENTER = [14.2456, 120.8789];
        const CAVITE_ZOOM = 11;

        // Municipality → approximate center coords (for dropdown → pin sync)
        const CITY_COORDS = {
            'Alfonso': [14.1167, 120.9333],
            'Amadeo': [14.1699, 120.9212],
            'Bacoor': [14.4624, 120.9645],
            'Carmona': [14.3167, 121.0500],
            'Cavite City': [14.4791, 120.8970],
            'Dasmariñas': [14.3294, 120.9367],
            'General Mariano Alvarez': [14.3000, 121.0000],
            'General Trias': [14.3833, 120.8833],
            'Imus': [14.4297, 120.9367],
            'Indang': [14.1960, 120.8780],
            'Kawit': [14.4395, 120.9009],
            'Magallanes': [14.1833, 120.7500],
            'Maragondon': [14.2667, 120.7333],
            'Mendez': [14.1333, 120.9000],
            'Naic': [14.3167, 120.7667],
            'Noveleta': [14.4333, 120.8833],
            'Rosario': [14.4167, 120.8500],
            'Silang': [14.2298, 120.9745],
            'Tagaytay': [14.0996, 120.9627],
            'Tanza': [14.3500, 120.8500],
            'Ternate': [14.2833, 120.7167],
            'Trece Martires': [14.2833, 120.8667],
        };

        // Postal codes per city
        const CITY_POSTALS = {
            'Alfonso': '4123',
            'Amadeo': '4119',
            'Bacoor': '4102',
            'Carmona': '4116',
            'Cavite City': '4100',
            'Dasmariñas': '4114',
            'General Mariano Alvarez': '4117',
            'General Trias': '4107',
            'Imus': '4103',
            'Indang': '4122',
            'Kawit': '4104',
            'Magallanes': '4126',
            'Maragondon': '4124',
            'Mendez': '4121',
            'Naic': '4110',
            'Noveleta': '4105',
            'Rosario': '4106',
            'Silang': '4118',
            'Tagaytay': '4120',
            'Tanza': '4108',
            'Ternate': '4111',
            'Trece Martires': '4109',
        };
        // ── Section tabs ────────────────────────────────────────────────
        function switchSection(id) {
            document.querySelectorAll('.settings-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.settings-nav__item').forEach(el => el.classList.remove('active'));
            document.getElementById('section-' + id)?.classList.add('active');
            document.getElementById('nav-' + id)?.classList.add('active');
            if (id === 'location' && !mapInited) initMap();
            localStorage.setItem('settings_tab', id);
        }

        const savedTab = localStorage.getItem('settings_tab');
        if (savedTab) switchSection(savedTab);

        // ── Branding preview ────────────────────────────────────────────
        function previewLogo(input) {
            const file = input.files[0];
            if (!file) return;
            const r = new FileReader();
            r.onload = e => {
                const wrap = document.getElementById('prevLogoWrap');
                wrap.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
                document.getElementById('logoZone').classList.add('has-file');
            };
            r.readAsDataURL(file);
        }

        function previewCover(input) {
            const file = input.files[0];
            if (!file) return;
            const r = new FileReader();
            r.onload = e => {
                const wrap = document.getElementById('prevCoverWrap');
                let img = document.getElementById('prevCoverImg');
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'prevCoverImg';
                    img.style = 'width:100%;height:100%;object-fit:cover;';
                    wrap.insertBefore(img, wrap.firstChild);
                }
                img.src = e.target.result;
                document.getElementById('prevCoverPlaceholder')?.remove();
            };
            r.readAsDataURL(file);
        }

        // ── Highlights editor ───────────────────────────────────────────
        let hlIdx = {{ count($vendor->highlights ?? ['', '', '']) }};

        function addHighlight() {
            const list = document.getElementById('highlightsList');
            const div = document.createElement('div');
            div.className = 'highlight-item';
            div.id = 'hl-' + hlIdx;
            div.innerHTML =
                `<i class="bi bi-check-circle-fill" style="color:var(--g-400);font-size:.9rem;flex-shrink:0;"></i>
                <input type="text" name="highlights[]" class="vd-input" placeholder="Add a highlight…" maxlength="100">
                <button type="button" onclick="removeHighlight(${hlIdx})" class="vd-btn vd-btn--ghost vd-btn--icon"><i class="bi bi-x"></i></button>`;
            list.appendChild(div);
            hlIdx++;
        }

        function removeHighlight(i) {
            document.getElementById('hl-' + i)?.remove();
        }

        let certIdx = {{ count($vendor->certifications ?? [['']]) }};

        function addCert() {
            const list = document.getElementById('certsList');
            const div = document.createElement('div');
            div.className = 'row g-2 mb-2 align-items-center';
            div.id = 'cert-' + certIdx;
            div.innerHTML =
                `
            <div class="col-5"><input type="text" name="certifications[${certIdx}][name]" class="vd-input" placeholder="Certificate / Award name"></div>
            <div class="col-4"><input type="text" name="certifications[${certIdx}][issuer]" class="vd-input" placeholder="Issued by"></div>
            <div class="col-2"><input type="number" name="certifications[${certIdx}][year]" class="vd-input" placeholder="Year" min="1990" max="{{ date('Y') }}"></div>
            <div class="col-1"><button type="button" onclick="removeCert(${certIdx})" class="vd-btn vd-btn--danger vd-btn--icon"><i class="bi bi-trash"></i></button></div>`;
            list.appendChild(div);
            certIdx++;
        }

        function removeCert(i) {
            document.getElementById('cert-' + i)?.remove();
        }

        // ── Hours toggles ───────────────────────────────────────────────
        function toggleDay(i, open) {
            document.getElementById('from-' + i).disabled = !open;
            document.getElementById('to-' + i).disabled = !open;
        }



        // ── Initialize map ────────────────────────────────────────────────────
        function initMap() {
            if (mapInited) return;
            mapInited = true;

            const savedLat = parseFloat(document.getElementById('latInput').value);
            const savedLng = parseFloat(document.getElementById('lngInput').value);
            const initLat = (savedLat && CAVITE_BOUNDS.contains([savedLat, savedLng])) ? savedLat : CAVITE_CENTER[0];
            const initLng = (savedLng && CAVITE_BOUNDS.contains([savedLat, savedLng])) ? savedLng : CAVITE_CENTER[1];

            locMap = L.map('settingsMap', {
                center: [initLat, initLng],
                zoom: savedLat ? 15 : CAVITE_ZOOM,
                minZoom: 10,
                maxZoom: 18,
                maxBounds: CAVITE_BOUNDS,
                maxBoundsViscosity: 1.0, // hard lock — cannot pan outside
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(locMap);

            // ── Cavite outline overlay label ──────────────────────────────────
            const caviteLabel = L.control({
                position: 'topright'
            });
            caviteLabel.onAdd = () => {
                const div = L.DomUtil.create('div');
                div.innerHTML = `<div style="background:rgba(255,255,255,.85);border:1.5px solid #16a34a;
                                     border-radius:6px;padding:.3rem .65rem;font-size:.72rem;
                                     font-weight:700;color:#15803d;">
                             📍 Province of Cavite
                         </div>`;
                return div;
            };
            caviteLabel.addTo(locMap);

            // ── Green pin marker ──────────────────────────────────────────────
            const greenIcon = L.divIcon({
                html: `<div style="width:24px;height:24px;background:#16a34a;
                            border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                            border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                className: '',
            });

            locMarker = L.marker([initLat, initLng], {
                icon: greenIcon,
                draggable: true,
            }).addTo(locMap).bindPopup('Your shop location').openPopup();

            // Drag end → reverse geocode
            locMarker.on('dragend', e => {
                const pos = e.target.getLatLng();
                setCoords(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });

            // Map click → move pin + reverse geocode
            locMap.on('click', e => {
                // Clamp to Cavite bounds
                if (!CAVITE_BOUNDS.contains(e.latlng)) return;
                locMarker.setLatLng(e.latlng);
                setCoords(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });
        }

        // ── Set coordinate inputs ─────────────────────────────────────────────
        function setCoords(lat, lng) {
            document.getElementById('latInput').value = lat.toFixed(7);
            document.getElementById('lngInput').value = lng.toFixed(7);
        }

        // ── Reverse geocode using Nominatim → auto-fill fields ────────────────
        function reverseGeocode(lat, lng) {
            clearTimeout(geocodeTimer);
            geocodeTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
                            headers: {
                                'Accept-Language': 'en'
                            }
                        }
                    );
                    const data = await res.json();
                    if (!data?.address) return;

                    const addr = data.address;

                    // ── City / municipality ───────────────────────────────────
                    // Nominatim returns different keys depending on place type
                    const nominatimCity =
                        addr.city ||
                        addr.town ||
                        addr.municipality ||
                        addr.village ||
                        addr.suburb ||
                        '';

                    // Try to match against our known Cavite cities (fuzzy)
                    const matchedCity = matchCaviteCity(nominatimCity, addr);
                    if (matchedCity) {
                        document.getElementById('citySelect').value = matchedCity;
                        // Set postal if not already manually entered
                        if (CITY_POSTALS[matchedCity]) {
                            document.getElementById('postalInput').value = CITY_POSTALS[matchedCity];
                        }
                    }

                    // ── Address line 1 ────────────────────────────────────────
                    const streetParts = [
                        addr.house_number,
                        addr.road || addr.pedestrian || addr.footway,
                        addr.neighbourhood || addr.quarter || addr.suburb,
                    ].filter(Boolean);

                    if (streetParts.length) {
                        document.getElementById('addressLine1Input').value = streetParts.join(', ');
                    }

                } catch (err) {
                    // Silently fail — user can fill in manually
                }
            }, 600); // debounce 600ms
        }

        // ── Match Nominatim result to our dropdown list ───────────────────────
        function matchCaviteCity(nominatimCity, addr) {
            const haystack = [
                nominatimCity,
                addr.city_district ?? '',
                addr.county ?? '',
                addr.state_district ?? '',
            ].join(' ').toLowerCase();

            // Direct match
            for (const city of Object.keys(CITY_COORDS)) {
                if (haystack.includes(city.toLowerCase())) return city;
                // Handle "Dasmariñas" / "Dasmarinas" variant
                if (city === 'Dasmariñas' && haystack.includes('dasmarinas')) return city;
                // Handle "GMA" abbreviation
                if (city === 'General Mariano Alvarez' && (haystack.includes('gma') || haystack.includes(
                        'mariano alvarez'))) return city;
            }

            return null;
        }

        // ── City dropdown → move map + pin ───────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const citySelect = document.getElementById('citySelect');
            if (citySelect) {
                citySelect.addEventListener('change', function() {
                    const city = this.value;
                    const coords = CITY_COORDS[city];
                    if (!coords || !locMap) return;

                    locMap.setView(coords, 14);
                    locMarker.setLatLng(coords);
                    setCoords(coords[0], coords[1]);
                    reverseGeocode(coords[0], coords[1]);

                    // Set postal code
                    if (CITY_POSTALS[city]) {
                        document.getElementById('postalInput').value = CITY_POSTALS[city];
                    }
                });
            }
        });

        // ── Detect GPS location (clamp to Cavite) ────────────────────────────
        function detectLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                pos => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    // Warn if outside Cavite
                    if (!CAVITE_BOUNDS.contains([lat, lng])) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Outside Cavite Province',
                                text: 'Your detected location is outside the Province of Cavite. Only Cavite-based businesses can register.',
                                confirmButtonColor: '#16a34a',
                            });
                        } else {
                            alert('Your detected location appears to be outside Cavite Province.');
                        }
                        return;
                    }

                    setCoords(lat, lng);

                    if (locMap) {
                        locMap.setView([lat, lng], 16);
                        locMarker.setLatLng([lat, lng]);
                        reverseGeocode(lat, lng);
                    }
                },
                err => {
                    console.warn('Geolocation error:', err.message);
                }, {
                    enableHighAccuracy: true,
                    timeout: 8000
                }
            );
        }

        // ── SEO preview ─────────────────────────────────────────────────
        function updateSeoPreview() {
            const title = document.getElementById('seoTitle').value;
            const desc = document.getElementById('seoDesc').value;
            document.getElementById('seoPreviewTitle').textContent = title || '{{ $vendor->business_name }}';
            document.getElementById('seoPreviewDesc').textContent = desc || 'No description set.';
            document.getElementById('seoTitleCount').textContent = title.length + '/160';
            document.getElementById('seoDescCount').textContent = desc.length + '/320';
        }
        updateSeoPreview();
    </script>
@endpush
