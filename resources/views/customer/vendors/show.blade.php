@extends('layouts.customer')

@section('title', $vendor->seo_title ?? $vendor->business_name)

@if ($vendor->seo_description)
    @push('meta')
        <meta name="description" content="{{ $vendor->seo_description }}">
    @endpush
@endif

@push('styles')
    <style>
        /* ── Hero ── */
        .vendor-hero {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #0a2e14;
        }

        .vendor-hero__cover {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .65;
        }

        .vendor-hero__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .7) 0%, rgba(0, 0, 0, .2) 60%, transparent 100%);
        }

        .vendor-hero__pattern {
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(45deg,
                    rgba(255, 255, 255, .025) 0,
                    rgba(255, 255, 255, .025) 1px,
                    transparent 1px,
                    transparent 14px);
        }

        .vendor-hero__content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        /* ── Logo avatar ── */
        .vendor-logo-avatar {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            border: 3px solid rgba(255, 255, 255, .9);
            object-fit: cover;
            background: #0a2e14;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 900;
            color: #4ade80;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .3);
        }

        .vendor-logo-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── Open/closed badge ── */
        .open-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .68rem;
            font-weight: 700;
            padding: .2rem .55rem;
            border-radius: 20px;
        }

        .open-badge--open {
            background: rgba(34, 197, 94, .15);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, .3);
        }

        .open-badge--closed {
            background: rgba(239, 68, 68, .12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, .25);
        }

        .open-badge--override {
            background: rgba(234, 179, 8, .12);
            color: #ca8a04;
            border: 1px solid rgba(234, 179, 8, .25);
        }

        /* ── Highlight dot ── */
        .highlight-dot {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            font-size: .82rem;
            color: var(--tx-secondary);
            line-height: 1.5;
        }

        .highlight-dot i {
            color: var(--cg-500);
            font-size: .7rem;
            margin-top: .25rem;
            flex-shrink: 0;
        }

        /* ── Cert card ── */
        .cert-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--cy-50);
            border: 1px solid var(--cy-100);
            border-radius: var(--r-full);
            padding: .25rem .75rem;
            font-size: .75rem;
            font-weight: 700;
            color: var(--cy-700);
        }

        /* ── Hours table ── */
        .hours-table {
            width: 100%;
            font-size: .82rem;
        }

        .hours-table tr td {
            padding: .3rem 0;
        }

        .hours-table tr td:first-child {
            font-weight: 700;
            color: var(--tx-primary);
            width: 110px;
        }

        .hours-table tr td:last-child {
            color: var(--tx-secondary);
        }

        .hours-table tr.today-row td {
            color: var(--cg-700);
        }

        .hours-table tr.today-row td:first-child::after {
            content: ' ←';
            font-size: .65rem;
            color: var(--cg-500);
            font-weight: 400;
        }

        /* ── Social links ── */
        .social-links {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            font-size: 1rem;
            text-decoration: none;
            transition: transform .12s, opacity .12s;
        }

        .social-link:hover {
            transform: translateY(-2px);
            opacity: .85;
        }

        .social-link--fb {
            background: #1877f2;
            color: #fff;
        }

        .social-link--ig {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            color: #fff;
        }

        .social-link--yt {
            background: #ff0000;
            color: #fff;
        }

        .social-link--tt {
            background: #010101;
            color: #fff;
        }

        .social-link--wa {
            background: #25d366;
            color: #fff;
        }

        .social-link--vb {
            background: #7360f2;
            color: #fff;
        }

        /* ── Service catalog ── */
        .svc-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-lg);
            padding: 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: border-color var(--t-base), box-shadow var(--t-base), transform var(--t-base);
        }

        .svc-card:hover {
            border-color: var(--cg-300);
            box-shadow: 0 6px 20px rgba(21, 122, 48, .1);
            transform: translateY(-2px);
        }

        .svc-card__type {
            font-size: .67rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--cg-600);
            margin-bottom: .45rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .svc-card__name {
            font-family: 'Nunito', sans-serif;
            font-size: .95rem;
            font-weight: 800;
            color: var(--tx-primary);
            line-height: 1.3;
            margin-bottom: .4rem;
        }

        .svc-card__desc {
            font-size: .82rem;
            color: var(--tx-secondary);
            line-height: 1.6;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: .75rem;
        }

        .svc-card__price {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            color: var(--cg-700);
        }

        /* ── Tab switcher ── */
        .vendor-tabs {
            display: flex;
            gap: .4rem;
            border-bottom: 2px solid var(--card-border);
            margin-bottom: 1.25rem;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .vendor-tabs::-webkit-scrollbar {
            display: none;
        }

        .vendor-tab {
            padding: .55rem 1.1rem;
            background: none;
            border: none;
            font-family: 'Nunito', sans-serif;
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-secondary);
            cursor: pointer;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: color var(--t-fast), border-color var(--t-fast);
            flex-shrink: 0;
        }

        .vendor-tab:hover {
            color: var(--cg-600);
        }

        .vendor-tab.active {
            color: var(--cg-600);
            border-bottom-color: var(--cg-500);
        }

        .svc-featured-badge {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            font-size: .63rem;
            font-weight: 800;
            background: var(--cy-50);
            color: var(--cy-600);
            border: 1px solid var(--cy-100);
            padding: .1rem .4rem;
            border-radius: var(--r-full);
        }

        /* ── Info card label/value rows ── */
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: .45rem 0;
            font-size: .875rem;
            border-bottom: 1px solid var(--card-border);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row__icon {
            color: var(--cg-500);
            width: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-row__label {
            font-weight: 700;
            color: var(--tx-muted);
            font-size: .72rem;
            min-width: 90px;
        }

        .info-row__value {
            color: var(--tx-primary);
            flex: 1;
        }

        @media (max-width: 480px) {
            .vendor-hero {
                height: 180px;
            }

            .vendor-logo-avatar {
                width: 52px;
                height: 52px;
            }
        }
    </style>
@endpush

@section('content')

    {{-- ═══════════════════════════════════════
     HERO BANNER
═══════════════════════════════════════ --}}
    @php
        $bgColors = ['#0a2e14', '#1a3d08', '#0a1f2e', '#2e1a08', '#0d1a2e', '#1a0829'];
        $heroBg = $bgColors[abs(crc32($vendor->business_name)) % count($bgColors)];
        $todayDow = now()->dayOfWeek;
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    @endphp

    <div class="vendor-hero" style="background:{{ $heroBg }};">
        {{-- Cover photo --}}
        @if ($vendor->cover_photo)
            <img src="{{ asset('storage/' . $vendor->cover_photo) }}" class="vendor-hero__cover" alt="Cover">
        @endif
        <div class="vendor-hero__overlay"></div>
        <div class="vendor-hero__pattern"></div>

        <div class="vendor-hero__content">
            <div style="display:flex;align-items:flex-end;gap:.85rem;flex:1;min-width:0;">
                {{-- Logo --}}
                <div class="vendor-logo-avatar">
                    @if ($vendor->shop_logo)
                        <img src="{{ asset('storage/' . $vendor->shop_logo) }}" alt="{{ $vendor->business_name }}">
                    @else
                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                    @endif
                </div>

                <div style="min-width:0;">
                    <div
                        style="font-family:'Nunito',sans-serif;font-size:1.35rem;font-weight:900;
                            color:#fff;letter-spacing:-.02em;line-height:1.1;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $vendor->business_name }}
                    </div>

                    {{-- Tagline --}}
                    @if ($vendor->tagline)
                        <div
                            style="font-size:.78rem;color:rgba(255,255,255,.7);margin-top:.15rem;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $vendor->tagline }}
                        </div>
                    @endif

                    <div style="display:flex;align-items:center;gap:.6rem;margin-top:.35rem;flex-wrap:wrap;">
                        <span style="font-size:.78rem;color:rgba(255,255,255,.75);">
                            <i class="bi bi-geo-alt-fill"></i>
                            {{ $vendor->city }}, {{ $vendor->province_state }}
                        </span>

                        @if ($vendor->total_reviews > 0)
                            <span style="font-size:.78rem;color:rgba(255,255,255,.75);">
                                ⭐ {{ number_format($vendor->average_rating, 1) }}
                                ({{ $vendor->total_reviews }} review{{ $vendor->total_reviews !== 1 ? 's' : '' }})
                            </span>
                        @endif

                        @if ($vendor->year_established)
                            <span style="font-size:.72rem;color:rgba(255,255,255,.6);">
                                Est. {{ $vendor->year_established }}
                            </span>
                        @endif

                        {{-- Open / closed badge --}}
                        @if ($vendor->is_open_now_override)
                            <span class="open-badge open-badge--override">
                                <i class="bi bi-clock"></i> Temporarily Closed
                            </span>
                        @elseif($vendor->show_operating_hours && $vendor->operating_hours)
                            <span
                                class="open-badge {{ $vendor->is_open_now ? 'open-badge--open' : 'open-badge--closed' }}">
                                <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                                {{ $vendor->is_open_now ? 'Open Now' : 'Closed' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;flex-shrink:0;">
                @if ($vendor->accept_service_bookings)
                    <button class="cu-btn cu-btn--secondary cu-btn--sm btn-book-service"
                        data-url="{{ route('customer.services.create', ['vendor' => $vendor->id]) }}">
                        <i class="bi bi-calendar-plus"></i> Book Service
                    </button>
                @endif
                <button class="cu-btn cu-btn--success cu-btn--sm btn-chat"
                    data-url="{{ route('customer.chat.show', ['vendor' => $vendor->id]) }}">
                    <i class="bi bi-chat-dots"></i> Chat
                </button>
            </div>
        </div>
    </div>

    {{-- Temporary closure note --}}
    @if ($vendor->is_open_now_override && $vendor->temporary_closure_note)
        <div
            style="background:#fffbeb;border-bottom:1px solid #fde68a;padding:.6rem 1.25rem;
                font-size:.82rem;color:#92400e;display:flex;align-items:center;gap:.6rem;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;"></i>
            {{ $vendor->temporary_closure_note }}
        </div>
    @endif

    <div class="cu-page">

        {{-- ── Tab switcher ── --}}
        <div class="vendor-tabs" id="vendorTabs">
            <button class="vendor-tab active" data-tab="products" onclick="switchTab('products', this)">
                <i class="bi bi-box-seam me-1"></i>Products
                <span style="font-size:.72rem;color:var(--tx-muted);margin-left:.3rem;">
                    ({{ $vendor->products->count() }})
                </span>
            </button>
            <button class="vendor-tab" data-tab="services" onclick="switchTab('services', this)">
                <i class="bi bi-tools me-1"></i>Services
                <span style="font-size:.72rem;color:var(--tx-muted);margin-left:.3rem;">
                    ({{ $vendorServices->count() }})
                </span>
            </button>
            <button class="vendor-tab" data-tab="about" onclick="switchTab('about', this)">
                <i class="bi bi-info-circle me-1"></i>About
            </button>
            @if ($vendor->show_operating_hours && $vendor->operating_hours)
                <button class="vendor-tab" data-tab="hours" onclick="switchTab('hours', this)">
                    <i class="bi bi-clock me-1"></i>Hours
                </button>
            @endif
        </div>

        {{-- ════════════ TAB: PRODUCTS ════════════ --}}
        <div id="tab-products">
            @if ($categories->count())
                <div
                    style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.35rem;
                        margin-bottom:1.25rem;scrollbar-width:none;">
                    <button class="cu-btn cu-btn--primary cu-btn--sm cat-filter active" data-cat="">All</button>
                    @foreach ($categories as $cat)
                        <button class="cu-btn cu-btn--ghost cu-btn--sm cat-filter" data-cat="{{ $cat->id }}"
                            style="white-space:nowrap;flex-shrink:0;">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if (!$vendor->accept_online_orders)
                <div
                    style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--r-md);
                        padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;
                        display:flex;align-items:center;gap:.6rem;">
                    <i class="bi bi-info-circle-fill" style="color:#d97706;"></i>
                    This vendor currently does not accept online orders. Please contact them directly.
                </div>
            @endif

            <div class="row g-3" id="vendorProducts">
                @forelse($vendor->products as $product)
                    @php $stock = $product->inventory?->quantity_on_hand ?? 0; @endphp
                    <div class="col-6 col-md-4 col-lg-3 product-item" data-cat="{{ $product->category_id }}">
                        <div class="product-card">
                            <div class="product-card__img">
                                @if ($product->primaryImage)
                                    <img src="{{ asset('storage/' . $product->primaryImage->file_path) }}"
                                        alt="{{ $product->name }}">
                                @else
                                    <i class="bi bi-box-seam"></i>
                                @endif
                                @if ($product->isOnSale())
                                    <span class="product-card__badge product-card__badge--sale">Sale</span>
                                @endif
                                @if ($stock === 0)
                                    <div
                                        style="position:absolute;inset:0;background:rgba(255,255,255,.75);
                                            display:flex;align-items:center;justify-content:center;">
                                        <span style="font-size:.75rem;font-weight:800;color:var(--cn-500);">
                                            Out of Stock
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="product-card__body">
                                <div class="product-card__name">{{ $product->name }}</div>
                                <div>
                                    <span class="product-card__price">₱{{ number_format($product->price, 2) }}</span>
                                    @if ($product->compare_price)
                                        <span
                                            class="product-card__compare">₱{{ number_format($product->compare_price, 2) }}</span>
                                    @endif
                                </div>
                                @if ($stock > 0)
                                    <div class="text-muted" style="font-size:.72rem;margin-top:.25rem;">
                                        {{ $stock }} in stock
                                    </div>
                                    @if ($vendor->accept_online_orders)
                                        <button class="product-card__add" onclick="addToCart({{ $product->id }})">
                                            <i class="bi bi-bag-plus"></i> Add to Cart
                                        </button>
                                    @endif
                                @else
                                    <button class="product-card__add" disabled
                                        style="background:var(--cn-200);cursor:not-allowed;">
                                        Out of Stock
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="cu-empty">
                            <i class="bi bi-box-seam"></i>
                            <h6>No products listed</h6>
                            <p>This vendor hasn't listed any products yet.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ════════════ TAB: SERVICES ════════════ --}}
        <div id="tab-services" style="display:none;">
            @if ($vendorServices->count())
                <div
                    style="background:var(--cg-50);border:1px solid var(--cg-100);
                        border-radius:var(--r-md);padding:.85rem 1.1rem;
                        display:flex;align-items:center;gap:.85rem;
                        margin-bottom:1.25rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <div style="font-weight:800;font-size:.9rem;color:var(--cg-800);">
                            Need a service from {{ $vendor->business_name }}?
                        </div>
                        <div style="font-size:.78rem;color:var(--cg-600);margin-top:.15rem;">
                            Choose below or request a custom service.
                        </div>
                    </div>
                    @if ($vendor->accept_service_bookings)
                        <a href="{{ route('customer.services.create', ['vendor' => $vendor->id]) }}"
                            class="cu-btn cu-btn--primary cu-btn--sm">
                            <i class="bi bi-calendar-plus"></i> Book Custom Service
                        </a>
                    @endif
                </div>

                @php
                    $featured = $vendorServices->where('is_featured', true);
                    $regular = $vendorServices->where('is_featured', false);
                @endphp

                @if ($featured->count())
                    <div
                        style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                            letter-spacing:.08em;color:var(--cy-600);
                            margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
                        <i class="bi bi-star-fill"></i> Featured Services
                    </div>
                    <div class="row g-3 mb-4">
                        @foreach ($featured as $svc)
                            <div class="col-12 col-md-6">
                                @include('customer.vendors._service-card', ['svc' => $svc])
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($regular->count())
                    @if ($featured->count())
                        <div
                            style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                letter-spacing:.08em;color:var(--tx-muted);margin-bottom:.75rem;">
                            All Services
                        </div>
                    @endif
                    <div class="row g-3">
                        @foreach ($regular as $svc)
                            <div class="col-12 col-md-6">
                                @include('customer.vendors._service-card', ['svc' => $svc])
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="cu-empty">
                    <i class="bi bi-tools"></i>
                    <h6>No services listed yet</h6>
                    <p>This vendor hasn't added services yet.</p>
                    @if ($vendor->accept_service_bookings)
                        <a href="{{ route('customer.services.create', ['vendor' => $vendor->id]) }}"
                            class="cu-btn cu-btn--primary mt-3 d-inline-flex">
                            <i class="bi bi-calendar-plus"></i> Request a Custom Service
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- ════════════ TAB: ABOUT ════════════ --}}
        <div id="tab-about" style="display:none;">
            <div class="row g-3">

                {{-- Left: story + highlights + certs ── --}}
                <div class="col-12 col-md-7">

                    {{-- About / story --}}
                    @if ($vendor->about || $vendor->shop_description)
                        <div class="cu-card mb-3">
                            <div class="cu-card__body">
                                <div
                                    style="font-weight:800;font-family:'Nunito',sans-serif;
                                        margin-bottom:.65rem;font-size:.95rem;">
                                    About {{ $vendor->business_name }}
                                </div>
                                <p
                                    style="font-size:.875rem;color:var(--tx-secondary);line-height:1.85;white-space:pre-line;">
                                    {{ $vendor->about ?? $vendor->shop_description }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Highlights --}}
                    @if (!empty($vendor->highlights) && array_filter($vendor->highlights))
                        <div class="cu-card mb-3">
                            <div class="cu-card__body">
                                <div
                                    style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                        letter-spacing:.08em;color:var(--cg-600);margin-bottom:.85rem;">
                                    🌟 Why Choose Us
                                </div>
                                <div style="display:flex;flex-direction:column;gap:.55rem;">
                                    @foreach (array_filter($vendor->highlights) as $highlight)
                                        <div class="highlight-dot">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>{{ $highlight }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Certifications --}}
                    @if (!empty($vendor->certifications))
                        <div class="cu-card mb-3">
                            <div class="cu-card__body">
                                <div
                                    style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                        letter-spacing:.08em;color:var(--cy-600);margin-bottom:.85rem;">
                                    🏅 Certifications & Awards
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                                    @foreach ($vendor->certifications as $cert)
                                        @if (!empty($cert['name']))
                                            <div class="cert-chip">
                                                <i class="bi bi-patch-check-fill" style="font-size:.75rem;"></i>
                                                <span>
                                                    {{ $cert['name'] }}
                                                    @if (!empty($cert['issuer']))
                                                        <span style="font-weight:400;opacity:.7;">
                                                            · {{ $cert['issuer'] }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($cert['year']))
                                                        <span style="font-weight:400;opacity:.6;">
                                                            {{ $cert['year'] }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Right: contact info ── --}}
                <div class="col-12 col-md-5">

                    {{-- Contact info --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div
                                style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                    letter-spacing:.08em;color:var(--tx-muted);margin-bottom:.85rem;">
                                📞 Contact Information
                            </div>

                            @if ($vendor->business_phone)
                                <div class="info-row">
                                    <i class="bi bi-telephone-fill info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Business</div>
                                        <div class="info-row__value">{{ $vendor->business_phone }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->support_phone)
                                <div class="info-row">
                                    <i class="bi bi-headset info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Support</div>
                                        <div class="info-row__value">{{ $vendor->support_phone }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->business_email)
                                <div class="info-row">
                                    <i class="bi bi-envelope-fill info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Email</div>
                                        <a href="mailto:{{ $vendor->business_email }}" class="info-row__value"
                                            style="color:var(--cg-600);text-decoration:none;">
                                            {{ $vendor->business_email }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->support_email)
                                <div class="info-row">
                                    <i class="bi bi-envelope-check info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Support Email</div>
                                        <a href="mailto:{{ $vendor->support_email }}" class="info-row__value"
                                            style="color:var(--cg-600);text-decoration:none;">
                                            {{ $vendor->support_email }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->full_address)
                                <div class="info-row">
                                    <i class="bi bi-geo-alt-fill info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Address</div>
                                        <div class="info-row__value">{{ $vendor->full_address }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->service_area)
                                <div class="info-row">
                                    <i class="bi bi-map info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Service Area</div>
                                        <div class="info-row__value">{{ $vendor->service_area }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->business_website)
                                <div class="info-row">
                                    <i class="bi bi-globe info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Website</div>
                                        <a href="{{ $vendor->business_website }}" target="_blank"
                                            class="info-row__value" style="color:var(--cg-600);text-decoration:none;">
                                            {{ str_replace(['https://', 'http://'], '', $vendor->business_website) }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if ($vendor->year_established)
                                <div class="info-row">
                                    <i class="bi bi-calendar-check info-row__icon"></i>
                                    <div>
                                        <div class="info-row__label">Est.</div>
                                        <div class="info-row__value">{{ $vendor->year_established }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Messaging apps --}}
                    @if ($vendor->whatsapp || $vendor->viber)
                        <div class="cu-card mb-3">
                            <div class="cu-card__body">
                                <div
                                    style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                        letter-spacing:.08em;color:var(--tx-muted);margin-bottom:.85rem;">
                                    💬 Message Us
                                </div>
                                <div style="display:flex;flex-direction:column;gap:.5rem;">
                                    @if ($vendor->whatsapp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $vendor->whatsapp) }}"
                                            target="_blank" class="cu-btn cu-btn--ghost cu-btn--sm"
                                            style="justify-content:flex-start;gap:.5rem;text-decoration:none;color:var(--tx-primary);">
                                            <i class="bi bi-whatsapp" style="color:#25d366;font-size:1rem;"></i>
                                            WhatsApp: {{ $vendor->whatsapp }}
                                        </a>
                                    @endif
                                    @if ($vendor->viber)
                                        <a href="viber://chat?number={{ preg_replace('/[^0-9]/', '', $vendor->viber) }}"
                                            class="cu-btn cu-btn--ghost cu-btn--sm"
                                            style="justify-content:flex-start;gap:.5rem;text-decoration:none;color:var(--tx-primary);">
                                            <i class="bi bi-phone-vibrate" style="color:#7360f2;font-size:1rem;"></i>
                                            Viber: {{ $vendor->viber }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Social media --}}
                    @php
                        $socials = array_filter([
                            'fb' => $vendor->social_facebook,
                            'ig' => $vendor->social_instagram,
                            'yt' => $vendor->social_youtube,
                            'tt' => $vendor->social_tiktok,
                        ]);
                    @endphp

                    @if (!empty($socials))
                        <div class="cu-card">
                            <div class="cu-card__body">
                                <div
                                    style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                        letter-spacing:.08em;color:var(--tx-muted);margin-bottom:.85rem;">
                                    📱 Follow Us
                                </div>
                                <div class="social-links">
                                    @if ($vendor->social_facebook)
                                        <a href="{{ $vendor->social_facebook }}" target="_blank"
                                            class="social-link social-link--fb" title="Facebook">
                                            <i class="bi bi-facebook"></i>
                                        </a>
                                    @endif
                                    @if ($vendor->social_instagram)
                                        <a href="{{ $vendor->social_instagram }}" target="_blank"
                                            class="social-link social-link--ig" title="Instagram">
                                            <i class="bi bi-instagram"></i>
                                        </a>
                                    @endif
                                    @if ($vendor->social_youtube)
                                        <a href="{{ $vendor->social_youtube }}" target="_blank"
                                            class="social-link social-link--yt" title="YouTube">
                                            <i class="bi bi-youtube"></i>
                                        </a>
                                    @endif
                                    @if ($vendor->social_tiktok)
                                        <a href="{{ $vendor->social_tiktok }}" target="_blank"
                                            class="social-link social-link--tt" title="TikTok">
                                            <i class="bi bi-tiktok"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: HOURS ════════════ --}}
        @if ($vendor->show_operating_hours && $vendor->operating_hours)
            <div id="tab-hours" style="display:none;">
                <div class="cu-card" style="max-width:480px;">
                    <div class="cu-card__body">
                        <div
                            style="font-size:.72rem;font-weight:800;text-transform:uppercase;
                                letter-spacing:.08em;color:var(--tx-muted);margin-bottom:1rem;">
                            🕐 Operating Hours
                        </div>

                        @if ($vendor->is_open_now_override)
                            <div
                                style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--r-md);
                                    padding:.65rem .85rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;">
                                <i class="bi bi-exclamation-triangle-fill me-1" style="color:#d97706;"></i>
                                {{ $vendor->temporary_closure_note ?? 'Temporarily closed.' }}
                            </div>
                        @endif

                        <table class="hours-table">
                            @foreach ($days as $i => $day)
                                @php
                                    $key = strtolower(substr($day, 0, 3));
                                    $h = collect($vendor->operating_hours)->first(
                                        fn($v, $k) => is_int($k) ? $k === $i : $k === $key,
                                    );
                                    $isToday = $i === $todayDow;
                                @endphp
                                <tr class="{{ $isToday ? 'today-row' : '' }}">
                                    <td style="{{ $isToday ? 'color:var(--cg-700);font-weight:800;' : '' }}">
                                        {{ $day }}
                                    </td>
                                    <td>
                                        @if (!empty($h['open']))
                                            <span style="{{ $isToday ? 'color:var(--cg-700);font-weight:700;' : '' }}">
                                                {{ $h['from'] ?? '08:00' }} – {{ $h['to'] ?? '17:00' }}
                                            </span>
                                        @else
                                            <span style="color:var(--tx-muted);">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        @if (!$vendor->is_open_now_override)
                            <div
                                style="margin-top:1rem;padding-top:.75rem;border-top:1px solid var(--card-border);
                                    display:flex;align-items:center;gap:.5rem;font-size:.78rem;">
                                <span
                                    class="open-badge {{ $vendor->is_open_now ? 'open-badge--open' : 'open-badge--closed' }}">
                                    <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                                    {{ $vendor->is_open_now ? 'Open Now' : 'Closed Now' }}
                                </span>
                                <span style="color:var(--tx-muted);">as of {{ now()->format('g:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- /cu-page --}}

@endsection

@push('scripts')
    <script>
        /* ── Tab switching ── */
        const TAB_IDS = ['products', 'services', 'about', 'hours'];

        function switchTab(name, btn) {
            TAB_IDS.forEach(t => {
                const el = document.getElementById(`tab-${t}`);
                if (el) el.style.display = t === name ? '' : 'none';
            });
            document.querySelectorAll('.vendor-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            if (name !== 'products') window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        /* ── Category filter ── */
        document.querySelectorAll('.cat-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cat-filter').forEach(b =>
                    b.className = 'cu-btn cu-btn--ghost cu-btn--sm cat-filter'
                );
                this.className = 'cu-btn cu-btn--primary cu-btn--sm cat-filter active';
                const cat = this.dataset.cat;
                document.querySelectorAll('.product-item').forEach(item => {
                    item.style.display = (!cat || item.dataset.cat === cat) ? '' : 'none';
                });
            });
        });

        /* ── URL ?tab param ── */
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab) {
            const btn = document.querySelector(`[data-tab="${urlTab}"]`);
            if (btn) switchTab(urlTab, btn);
        }

        /* ── Button handlers ── */
        $(document).ready(function() {
            $(document).on('click', '.btn-book-service', function() {
                window.location.href = $(this).data('url');
            });
            $(document).on('click', '.btn-chat', function() {
                window.location.href = $(this).data('url');
            });
        });
    </script>
@endpush
