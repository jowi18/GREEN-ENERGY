@extends('layouts.customer')

@section('title', 'Find Solar Vendors')

@push('styles')
    <style>
        /* ── Location bar ── */
        .location-bar {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .65rem 1rem;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--r-md);
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .location-bar__label {
            font-size: .78rem;
            color: var(--tx-muted);
            flex-shrink: 0;
        }

        .location-bar__value {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .location-bar__value i {
            color: var(--cg-500);
            margin-right: .3rem;
        }

        /* ── Vendor card ── */
        .vendor-card {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-lg);
            overflow: hidden;
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
            height: 100%;
        }

        .vendor-card:hover {
            border-color: var(--cg-300);
            box-shadow: 0 6px 24px rgba(21, 122, 48, .1);
            transform: translateY(-2px);
        }

        /* ── Card banner / cover ── */
        .vendor-card__banner {
            position: relative;
            height: 88px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .vendor-card__banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .7;
        }

        .vendor-card__banner-pattern {
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(45deg,
                    rgba(255, 255, 255, .03) 0,
                    rgba(255, 255, 255, .03) 1px,
                    transparent 1px,
                    transparent 12px);
        }

        .vendor-card__banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .55) 0%, transparent 60%);
        }

        /* Logo avatar on the banner */
        .vendor-card__logo {
            position: absolute;
            bottom: -16px;
            left: .85rem;
            width: 40px;
            height: 40px;
            border-radius: 9px;
            border: 2.5px solid var(--card-bg);
            background: #0a2e14;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 900;
            color: #4ade80;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            z-index: 1;
        }

        .vendor-card__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Open/closed pill on banner */
        .vendor-card__status {
            position: absolute;
            top: .5rem;
            right: .6rem;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .63rem;
            font-weight: 800;
            padding: .18rem .5rem;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        .vendor-card__status--open {
            background: rgba(21, 128, 61, .85);
            color: #fff;
        }

        .vendor-card__status--closed {
            background: rgba(0, 0, 0, .55);
            color: rgba(255, 255, 255, .75);
        }

        .vendor-card__status--override {
            background: rgba(202, 138, 4, .85);
            color: #fff;
        }

        /* ── Card body ── */
        .vendor-card__body {
            padding: 1.1rem .9rem .85rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-top: 1.3rem;
            /* space for logo overlap */
        }

        .vendor-card__name {
            font-family: 'Nunito', sans-serif;
            font-size: .9rem;
            font-weight: 800;
            color: var(--tx-primary);
            line-height: 1.2;
            margin-bottom: .2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vendor-card__tagline {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-bottom: .35rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vendor-card__loc {
            font-size: .75rem;
            color: var(--tx-secondary);
            display: flex;
            align-items: center;
            gap: .25rem;
            margin-bottom: .35rem;
        }

        .vendor-card__dist {
            display: inline-block;
            background: var(--cg-50);
            color: var(--cg-700);
            font-size: .65rem;
            font-weight: 700;
            padding: .08rem .4rem;
            border-radius: var(--r-full);
            border: 1px solid var(--cg-100);
            margin-left: .2rem;
        }

        .vendor-card__stars {
            display: flex;
            align-items: center;
            gap: .2rem;
            font-size: .72rem;
            color: #f59e0b;
            margin-bottom: .45rem;
        }

        .vendor-card__stars span {
            color: var(--tx-muted);
            margin-left: .15rem;
        }

        .vendor-card__tags {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            margin-top: auto;
        }

        .vendor-tag {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            font-size: .67rem;
            font-weight: 700;
            background: var(--cg-50);
            color: var(--cg-700);
            border: 1px solid var(--cg-100);
            padding: .15rem .5rem;
            border-radius: var(--r-full);
        }

        .vendor-tag--est {
            background: var(--n-50);
            color: var(--tx-muted);
            border-color: var(--card-border);
        }

        /* ── Location modal ── */
        .loc-method-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: 2px solid var(--card-border);
            border-radius: var(--r-md);
            padding: 1.25rem 1rem;
            cursor: pointer;
            flex: 1;
            background: none;
            font-family: 'Nunito', sans-serif;
            transition: all .15s;
        }

        .loc-method-btn:hover {
            border-color: var(--cg-300);
            background: var(--cg-50);
        }

        .loc-method-btn.active {
            border-color: var(--cg-500);
            background: var(--cg-50);
        }

        .loc-method-btn i {
            font-size: 1.75rem;
            color: var(--cg-600);
        }

        .loc-method-btn span {
            font-size: .82rem;
            font-weight: 700;
            color: var(--tx-primary);
        }

        .loc-method-btn small {
            font-size: .72rem;
            color: var(--tx-muted);
            text-align: center;
        }

        .gps-status {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .7rem 1rem;
            border-radius: var(--r-sm);
            font-size: .845rem;
            font-weight: 600;
            margin-top: .75rem;
        }

        .gps-status--detecting {
            background: var(--cg-50);
            color: var(--cg-700);
            border: 1px solid var(--cg-200);
        }

        .gps-status--success {
            background: var(--cg-50);
            color: var(--cg-700);
            border: 1px solid var(--cg-200);
        }

        .gps-status--error {
            background: var(--red-50);
            color: var(--red-500);
            border: 1px solid #fecaca;
        }

        @media (max-width: 480px) {
            .vendor-card__banner {
                height: 72px;
            }

            .vendor-card__logo {
                width: 34px;
                height: 34px;
                bottom: -14px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="cu-page">

        {{-- ── Search bar ── --}}
        <form method="GET" class="d-flex gap-2 mb-3">
            <div style="position:relative;flex:1;">
                <i class="bi bi-search"
                    style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);
                          color:var(--tx-muted);font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="cu-input"
                    style="padding-left:2.3rem;border-radius:var(--r-full);"
                    placeholder="Search by business name, city, or service…">
            </div>
            <button type="submit" class="cu-btn cu-btn--primary">
                <i class="bi bi-search"></i>
            </button>
            @if (request('search'))
                <a href="{{ route('customer.vendors.index') }}" class="cu-btn cu-btn--ghost">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </form>

        {{-- ── Location bar ── --}}
        <div class="location-bar" id="locationBar">
            <span class="location-bar__label">📍 Your location:</span>
            <span class="location-bar__value" id="locationDisplay">
                @if ($customer->city)
                    <i class="bi bi-geo-alt-fill"></i>
                    {{ $customer->city }}@if ($customer->province_state)
                        , {{ $customer->province_state }}
                    @endif
                @else
                    <span style="color:var(--tx-muted);">Not set — vendors sorted by rating</span>
                @endif
            </span>
            <button class="cu-btn cu-btn--ghost cu-btn--sm" data-bs-toggle="modal" data-bs-target="#locationModal"
                style="flex-shrink:0;">
                <i class="bi bi-pencil-square"></i> Update Location
            </button>
        </div>

        {{-- ── Leaflet map ── --}}
        <div class="gap-section">
            <div id="vendorMap" class="cu-map"></div>
        </div>

        {{-- ── Vendor grid ── --}}
        <div class="cu-section-head">
            <div class="cu-section-title">
                {{ $vendors->total() }} Vendor{{ $vendors->total() !== 1 ? 's' : '' }}
                @if ($customer->city)
                    near {{ $customer->city }}
                @endif
            </div>
        </div>

        @if ($vendors->count())
            <div class="row g-3">
                @foreach ($vendors as $vendor)
                    @php
                        $bgColors = ['#0a2e14', '#1a3d08', '#0a1f2e', '#2e1a08', '#0d1a2e', '#241a00'];
                        $bg = $bgColors[$loop->index % count($bgColors)];
                    @endphp
                    <div class="col-6 col-md-4">
                        <a href="{{ route('customer.vendors.show', $vendor) }}" class="vendor-card">

                            {{-- ── Banner / cover ── --}}
                            <div class="vendor-card__banner" style="background:{{ $bg }};">
                                @if ($vendor->cover_photo)
                                    <img src="{{ asset('storage/' . $vendor->cover_photo) }}"
                                        class="vendor-card__banner-img" alt="">
                                @endif
                                <div class="vendor-card__banner-pattern"></div>
                                <div class="vendor-card__banner-overlay"></div>

                                {{-- Logo avatar --}}
                                <div class="vendor-card__logo">
                                    @if ($vendor->shop_logo)
                                        <img src="{{ asset('storage/' . $vendor->shop_logo) }}"
                                            alt="{{ $vendor->business_name }}">
                                    @else
                                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                                    @endif
                                </div>

                                {{-- Open/closed status --}}
                                @if ($vendor->show_operating_hours && $vendor->operating_hours)
                                    @if ($vendor->is_open_now_override)
                                        <div class="vendor-card__status vendor-card__status--override">
                                            <i class="bi bi-clock" style="font-size:.55rem;"></i> Temp. Closed
                                        </div>
                                    @elseif($vendor->is_open_now)
                                        <div class="vendor-card__status vendor-card__status--open">
                                            <i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Open
                                        </div>
                                    @else
                                        <div class="vendor-card__status vendor-card__status--closed">
                                            <i class="bi bi-circle" style="font-size:.4rem;"></i> Closed
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- ── Body ── --}}
                            <div class="vendor-card__body">
                                <div class="vendor-card__name">{{ $vendor->business_name }}</div>

                                @if ($vendor->tagline)
                                    <div class="vendor-card__tagline">{{ $vendor->tagline }}</div>
                                @endif

                                <div class="vendor-card__loc">
                                    <i class="bi bi-geo-alt-fill" style="color:var(--cg-500);font-size:.65rem;"></i>
                                    {{ $vendor->city }}, {{ $vendor->province_state }}
                                    @if (isset($vendor->distance_km))
                                        <span class="vendor-card__dist">
                                            {{ number_format($vendor->distance_km, 1) }}km
                                        </span>
                                    @endif
                                </div>

                                <div class="vendor-card__stars">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <i class="bi {{ $s <= round($vendor->average_rating) ? 'bi-star-fill' : 'bi-star' }}"
                                            style="font-size:.65rem;"></i>
                                    @endfor
                                    <span>{{ number_format($vendor->average_rating, 1) }}
                                        ({{ $vendor->total_reviews }})
                                    </span>
                                </div>

                                {{-- Tags ── --}}
                                <div class="vendor-card__tags">
                                    @if ($vendor->products->count())
                                        <span class="vendor-tag">
                                            <i class="bi bi-box-seam"></i>
                                            {{ $vendor->products->count() }} products
                                        </span>
                                    @endif

                                    @if ($vendor->accept_service_bookings)
                                        <span class="vendor-tag">
                                            <i class="bi bi-calendar-check"></i> Bookings
                                        </span>
                                    @endif

                                    @if ($vendor->year_established)
                                        <span class="vendor-tag vendor-tag--est">
                                            Est. {{ $vendor->year_established }}
                                        </span>
                                    @endif

                                    @if ($vendor->accept_online_orders)
                                        <span class="vendor-tag">
                                            <i class="bi bi-bag-check"></i> Online Orders
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </a>
                    </div>
                @endforeach
            </div>

            @if ($vendors->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $vendors->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="cu-empty">
                <i class="bi bi-shop"></i>
                <h6>No vendors found</h6>
                <p>Try a different search term or update your location.</p>
            </div>
        @endif

    </div>

    {{-- ════════════════════════════════════════
         LOCATION UPDATE MODAL
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="locationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--r-xl)!important;">
                <div class="modal-header" style="border-bottom:1px solid var(--card-border);">
                    <h6 class="modal-title fw-800" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-geo-alt-fill text-green me-2"></i>Update Your Location
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.25rem;">

                    <p style="font-size:.845rem;color:var(--tx-secondary);margin-bottom:1.1rem;">
                        Your location helps us sort vendors by distance and show you who's nearby.
                    </p>

                    <div class="d-flex gap-2 mb-3">
                        <button class="loc-method-btn active" id="btnGps" onclick="switchMethod('gps')">
                            <i class="bi bi-crosshair2"></i>
                            <span>Use GPS</span>
                            <small>Auto-detect my location</small>
                        </button>
                        <button class="loc-method-btn" id="btnManual" onclick="switchMethod('manual')">
                            <i class="bi bi-keyboard"></i>
                            <span>Enter Manually</span>
                            <small>Type your city or address</small>
                        </button>
                    </div>

                    <div id="gpsPanel">
                        <button class="cu-btn cu-btn--primary cu-btn--full" id="detectBtn" onclick="detectLocation()">
                            <i class="bi bi-crosshair2"></i> Detect My Location
                        </button>
                        <div id="gpsStatus" style="display:none;"></div>
                        <p style="font-size:.75rem;color:var(--tx-muted);margin-top:.65rem;text-align:center;">
                            <i class="bi bi-shield-check me-1"></i>
                            Your coordinates are only used to calculate distance to vendors.
                        </p>
                    </div>

                    <div id="manualPanel" style="display:none;">
                        <div class="mb-3">
                            <label class="cu-label">City / Municipality <span class="req">*</span></label>
                            <input type="text" id="manualCity" class="cu-input" value="{{ $customer->city }}"
                                placeholder="e.g. Bacoor, Imus, Dasmariñas">
                        </div>
                        <div class="mb-1">
                            <label class="cu-label">Province / Region</label>
                            <input type="text" id="manualProvince" class="cu-input"
                                value="{{ $customer->province_state }}" placeholder="e.g. Cavite">
                        </div>
                        <div id="manualError"
                            style="display:none;color:var(--red-500);font-size:.78rem;margin-top:.4rem;"></div>
                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid var(--card-border);">
                    <button type="button" class="cu-btn cu-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="cu-btn cu-btn--primary" id="saveLocationBtn" onclick="saveLocation()"
                        style="display:none;">
                        <i class="bi bi-check-lg"></i> Save Location
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ── Map ──────────────────────────────────────────────────────────────
        const mapVendors = @json($mapVendors);
        const customerLat = {{ $customer->latitude ?? 14.2456 }};
        const customerLng = {{ $customer->longitude ?? 120.8789 }};

        const map = L.map('vendorMap').setView([customerLat, customerLng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
        }).addTo(map);

        // Customer dot
        const customerMarker = L.circleMarker([customerLat, customerLng], {
            radius: 9,
            fillColor: '#f5c518',
            color: '#fff',
            weight: 2.5,
            fillOpacity: 1,
        }).addTo(map).bindPopup('<strong>Your location</strong>');

        // Vendor markers — show logo initials + rating
        mapVendors.forEach(v => {
            const icon = L.divIcon({
                html: `<div style="background:#157a30;color:#fff;font-size:.6rem;font-weight:800;
                               padding:.18rem .45rem;border-radius:999px;
                               box-shadow:0 2px 6px rgba(0,0,0,.3);white-space:nowrap;
                               display:flex;align-items:center;gap:.2rem;">
                       ⭐ ${parseFloat(v.average_rating).toFixed(1)}
                   </div>`,
                className: '',
                iconAnchor: [22, 12],
            });

            const logoHtml = v.shop_logo ?
                `<img src="{{ asset('storage/') }}/${v.shop_logo}"
                    style="width:28px;height:28px;border-radius:6px;object-fit:cover;
                           border:2px solid #fff;vertical-align:middle;margin-right:.4rem;">` :
                `<span style="display:inline-flex;align-items:center;justify-content:center;
                            width:28px;height:28px;border-radius:6px;background:#0a2e14;
                            color:#4ade80;font-size:.65rem;font-weight:900;
                            border:2px solid #fff;vertical-align:middle;margin-right:.4rem;">
                   ${v.business_name.substring(0,2).toUpperCase()}
               </span>`;

            const openHtml = v.is_open_now_override ?
                `<span style="font-size:.65rem;color:#ca8a04;font-weight:700;">⏸ Temp. Closed</span>` :
                (v.is_open_now ?
                    `<span style="font-size:.65rem;color:#16a34a;font-weight:700;">● Open Now</span>` :
                    `<span style="font-size:.65rem;color:#9ca3af;">○ Closed</span>`);

            const tagsHtml = [
                v.accept_online_orders ?
                `<span style="background:#f0fdf4;color:#15803d;font-size:.6rem;font-weight:700;padding:.1rem .4rem;border-radius:20px;border:1px solid #bbf7d0;">Online Orders</span>` :
                '',
                v.accept_service_bookings ?
                `<span style="background:#eff6ff;color:#1d4ed8;font-size:.6rem;font-weight:700;padding:.1rem .4rem;border-radius:20px;border:1px solid #bfdbfe;">Bookings</span>` :
                '',
            ].filter(Boolean).join(' ');

            L.marker([v.latitude, v.longitude], {
                    icon
                })
                .addTo(map)
                .bindPopup(`
                <div style="padding:.7rem .85rem;min-width:190px;">
                    <div style="display:flex;align-items:center;margin-bottom:.45rem;">
                        ${logoHtml}
                        <div>
                            <div style="font-weight:800;font-size:.82rem;color:#111827;line-height:1.2;">
                                ${v.business_name}
                            </div>
                            <div style="font-size:.7rem;color:#6b7280;margin-top:.1rem;">
                                📍 ${v.city}, Cavite
                            </div>
                        </div>
                    </div>
                    ${v.tagline ? `<div style="font-size:.72rem;color:#6b7280;margin-bottom:.4rem;font-style:italic;">${v.tagline}</div>` : ''}
                    <div style="font-size:.7rem;color:#f59e0b;margin-bottom:.35rem;">
                        ${'★'.repeat(Math.round(v.average_rating))}${'☆'.repeat(5 - Math.round(v.average_rating))}
                        <span style="color:#9ca3af;">${parseFloat(v.average_rating).toFixed(1)} (${v.total_reviews})</span>
                    </div>
                    <div style="margin-bottom:.55rem;display:flex;gap:.25rem;flex-wrap:wrap;">${tagsHtml}</div>
                    <div style="margin-bottom:.5rem;">${openHtml}</div>
                    <a href="/customer/vendors/${v.id}"
                       style="display:block;text-align:center;background:#15803d;color:#fff;
                              border-radius:6px;padding:.4rem .75rem;font-size:.75rem;
                              font-weight:700;text-decoration:none;">
                        View Vendor →
                    </a>
                </div>`, {
                    maxWidth: 220
                });
        });

        // ── Location modal logic ─────────────────────────────────────────────
        let currentMethod = 'gps';
        let detectedLat = null;
        let detectedLng = null;
        let detectedCity = null;
        let detectedProvince = null;
        let locationReady = false;

        function switchMethod(method) {
            currentMethod = method;
            document.getElementById('gpsPanel').style.display = method === 'gps' ? '' : 'none';
            document.getElementById('manualPanel').style.display = method === 'manual' ? '' : 'none';
            document.getElementById('btnGps').classList.toggle('active', method === 'gps');
            document.getElementById('btnManual').classList.toggle('active', method === 'manual');
            document.getElementById('saveLocationBtn').style.display =
                (method === 'manual' || locationReady) ? '' : 'none';
            if (method === 'manual') document.getElementById('gpsStatus').style.display = 'none';
        }

        function setGpsStatus(type, msg) {
            const el = document.getElementById('gpsStatus');
            el.style.display = '';
            el.className = `gps-status gps-status--${type}`;
            const icons = {
                detecting: 'bi-arrow-repeat',
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill'
            };
            el.innerHTML = `<i class="bi ${icons[type]} ${type === 'detecting' ? 'spin' : ''}"></i> ${msg}`;
        }

        function detectLocation() {
            if (!navigator.geolocation) {
                setGpsStatus('error', 'Geolocation not supported. Use manual entry.');
                switchMethod('manual');
                return;
            }

            const btn = document.getElementById('detectBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Detecting…';
            setGpsStatus('detecting', 'Getting your location…');

            navigator.geolocation.getCurrentPosition(
                pos => {
                    detectedLat = pos.coords.latitude;
                    detectedLng = pos.coords.longitude;
                    setGpsStatus('detecting', 'Resolving city name…');

                    $.get('{{ route('customer.location.reverse-geocode') }}', {
                        latitude: detectedLat,
                        longitude: detectedLng
                    }, res => {
                        detectedCity = res.city || '';
                        detectedProvince = res.province || '';
                        const loc = detectedCity ?
                            `${detectedCity}${detectedProvince ? ', ' + detectedProvince : ''}` :
                            `${detectedLat.toFixed(4)}, ${detectedLng.toFixed(4)}`;
                        setGpsStatus('success',
                            `📍 <strong>${loc}</strong><br>
                         <span style="font-size:.75rem;opacity:.8;">Accuracy: ~${Math.round(pos.coords.accuracy)}m</span>`
                        );
                        locationReady = true;
                        document.getElementById('saveLocationBtn').style.display = '';
                    }).fail(() => {
                        detectedCity = '';
                        locationReady = true;
                        setGpsStatus('success',
                            `📍 Location detected (${detectedLat.toFixed(4)}, ${detectedLng.toFixed(4)})`);
                        document.getElementById('saveLocationBtn').style.display = '';
                    });

                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-crosshair2"></i> Re-detect Location';
                },
                err => {
                    const messages = {
                        1: 'Permission denied. Allow location access in browser settings.',
                        2: 'Location unavailable. Check device GPS or use manual entry.',
                        3: 'Request timed out. Try again or use manual entry.',
                    };
                    setGpsStatus('error', messages[err.code] || 'Could not detect location.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-crosshair2"></i> Try Again';
                }, {
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        }

        function saveLocation() {
            const btn = document.getElementById('saveLocationBtn');

            if (currentMethod === 'gps') {
                if (!locationReady || detectedLat === null) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Please detect your location first.',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
                $.ajax({
                    url: '{{ route('customer.location.update') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        latitude: detectedLat,
                        longitude: detectedLng,
                        city: detectedCity,
                        province: detectedProvince
                    }),
                    success: onSaveSuccess,
                    error: onSaveError,
                });
            } else {
                const city = document.getElementById('manualCity').value.trim();
                const province = document.getElementById('manualProvince').value.trim();
                if (!city) {
                    document.getElementById('manualError').style.display = '';
                    document.getElementById('manualError').textContent = 'Please enter your city.';
                    return;
                }
                document.getElementById('manualError').style.display = 'none';
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
                $.ajax({
                    url: '{{ route('customer.location.update') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        city,
                        province
                    }),
                    success: onSaveSuccess,
                    error: onSaveError,
                });
            }
        }

        function onSaveSuccess(res) {
            if (!res.success) {
                onSaveError();
                return;
            }
            bootstrap.Modal.getInstance(document.getElementById('locationModal')).hide();
            const city = res.city || '',
                province = res.province || '';
            document.getElementById('locationDisplay').innerHTML = city ?
                `<i class="bi bi-geo-alt-fill"></i> ${city}${province ? ', ' + province : ''}` :
                '<span style="color:var(--tx-muted);">Location updated</span>';
            if (res.latitude && res.longitude) {
                customerMarker.setLatLng([res.latitude, res.longitude]);
                map.setView([res.latitude, res.longitude], 12);
            }
            Swal.fire({
                icon: 'success',
                title: 'Location updated!',
                text: city ? `Showing vendors near ${city}.` : 'Vendors sorted by distance.',
                confirmButtonColor: '#157a30',
                confirmButtonText: 'Refresh results',
            }).then(() => window.location.reload());
        }

        function onSaveError() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not save location. Try again.'
            });
            const btn = document.getElementById('saveLocationBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Location';
        }

        @if (!$customer->latitude && !$customer->city)
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    const bar = document.getElementById('locationBar');
                    if (bar) bar.style.borderColor = 'var(--cg-300)';
                }, 1500);
            });
        @endif

        // Spin animation for GPS detecting icon
        const style = document.createElement('style');
        style.textContent =
            `.spin{animation:spin 1s linear infinite;}@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}`;
        document.head.appendChild(style);
    </script>
@endpush
