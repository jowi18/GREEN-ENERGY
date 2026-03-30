@extends('layouts.customer')
@section('title', 'Dashboard')

@section('content')
    <div class="cu-page">

        {{-- ── Greeting ── --}}
        <div style="margin-bottom:1.25rem;">
            <h5 class="fw-800" style="font-family:'Nunito',sans-serif;letter-spacing:-0.02em;margin-bottom:0.15rem;">
                Hi, {{ $customer->first_name }} 👋
            </h5>
            <p class="text-muted" style="font-size:0.875rem;">
                {{ $customer->city ? "Showing vendors near {$customer->city}" : 'Find solar vendors and products near you.' }}
            </p>
        </div>

        {{-- ── Stats ── --}}
        <div class="row g-2 gap-section">
            @php
                $statItems = [
                    ['Total Orders', $stats['total_orders'], 'green', 'bi-bag'],
                    ['Active Orders', $stats['active_orders'], 'yellow', 'bi-truck'],
                    ['Completed', $stats['completed_orders'], 'green', 'bi-bag-check'],
                    ['Total Spent', '₱' . number_format($stats['total_spent'], 0), 'blue', 'bi-cash-coin'],
                ];
            @endphp
            @foreach ($statItems as [$label, $value, $color, $icon])
                <div class="col-6 col-md-3">
                    <div class="cu-stat">
                        <div class="cu-stat__icon cu-stat__icon--{{ $color }}">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div>
                            <div class="cu-stat__value" style="font-size:1.2rem;">{{ $value }}</div>
                            <div class="cu-stat__label">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Nearby vendors ── --}}
        @if ($nearbyVendors->count())
            <div class="gap-section">
                <div class="cu-section-head">
                    <div class="cu-section-title">📍 Vendors Near You</div>
                    <a href="{{ route('customer.vendors.index') }}" class="cu-section-link">See all →</a>
                </div>
                <div class="row g-3">
                    @foreach ($nearbyVendors as $vendor)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('customer.vendors.show', $vendor) }}" class="vendor-card"
                                style="text-decoration:none;display:block;border:1.5px solid var(--card-border);
                                  border-radius:var(--r-xl);overflow:hidden;
                                  transition:border-color .15s,box-shadow .15s;background:var(--card-bg);">

                                {{-- Banner / cover --}}
                                <div style="position:relative;height:80px;overflow:hidden;background:#0a2e14;">
                                    @if ($vendor->cover_url)
                                        <img src="{{ $vendor->cover_url }}" alt=""
                                            style="width:100%;height:100%;object-fit:cover;opacity:.7;">
                                    @endif

                                    {{-- Open/Closed badge --}}
                                    <span
                                        style="position:absolute;top:.5rem;right:.5rem;
                                    font-size:.62rem;font-weight:700;padding:.15rem .5rem;
                                    border-radius:var(--r-full);
                                    background:{{ $vendor->is_open_now ? 'rgba(22,163,74,.9)' : 'rgba(239,68,68,.85)' }};
                                    color:#fff;backdrop-filter:blur(4px);">
                                        {{ $vendor->is_open_now ? '● Open' : '● Closed' }}
                                    </span>

                                    {{-- Logo badge --}}
                                    <div
                                        style="position:absolute;bottom:-16px;left:.85rem;
                                    width:36px;height:36px;border-radius:var(--r-md);
                                    border:2px solid var(--card-bg);overflow:hidden;
                                    background:var(--cg-700);display:flex;align-items:center;
                                    justify-content:center;font-weight:800;color:#fff;font-size:.75rem;">
                                        @if ($vendor->shop_logo)
                                            <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->business_name }}"
                                                style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Body --}}
                                <div style="padding:.75rem .85rem .85rem;padding-top:1.25rem;">

                                    <div
                                        style="font-weight:700;font-size:.875rem;
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                                    margin-bottom:.1rem;">
                                        {{ $vendor->business_name }}
                                    </div>

                                    @if ($vendor->tagline)
                                        <div
                                            style="font-size:.72rem;color:var(--tx-muted);
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                                        margin-bottom:.35rem;font-style:italic;">
                                            {{ $vendor->tagline }}
                                        </div>
                                    @endif

                                    {{-- Location + distance --}}
                                    <div
                                        style="display:flex;align-items:center;gap:.3rem;
                                    font-size:.72rem;color:var(--tx-muted);margin-bottom:.4rem;">
                                        <i class="bi bi-geo-alt-fill" style="color:var(--cg-500);font-size:.68rem;"></i>
                                        {{ $vendor->city }}
                                        @if (isset($vendor->distance_km))
                                            <span
                                                style="background:var(--cg-50);color:var(--cg-700);
                                            padding:.05rem .4rem;border-radius:var(--r-full);
                                            font-size:.65rem;font-weight:700;margin-left:auto;">
                                                {{ number_format($vendor->distance_km, 1) }}km
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Stars --}}
                                    <div
                                        style="display:flex;align-items:center;gap:.2rem;font-size:.68rem;margin-bottom:.4rem;">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi {{ $s <= round($vendor->average_rating) ? 'bi-star-fill' : 'bi-star' }}"
                                                style="color:#f59e0b;"></i>
                                        @endfor
                                        <span style="color:var(--tx-muted);margin-left:.2rem;">
                                            {{ number_format($vendor->average_rating, 1) }}
                                            ({{ $vendor->total_reviews }})
                                        </span>
                                    </div>

                                    {{-- Year established --}}
                                    @if ($vendor->year_established)
                                        <div style="font-size:.68rem;color:var(--tx-muted);">
                                            Est. {{ $vendor->year_established }}
                                        </div>
                                    @endif

                                    {{-- Highlights chips (max 2) --}}
                                    @if ($vendor->highlights && count($vendor->highlights))
                                        <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-top:.45rem;">
                                            @foreach (array_slice($vendor->highlights, 0, 2) as $hl)
                                                <span
                                                    style="font-size:.62rem;padding:.1rem .45rem;
                                                border-radius:var(--r-full);background:var(--cg-50);
                                                color:var(--cg-700);border:1px solid var(--cg-100);
                                                white-space:nowrap;">
                                                    {{ $hl }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Online orders / service bookings badges --}}
                                    <div style="display:flex;gap:.25rem;margin-top:.45rem;flex-wrap:wrap;">
                                        @if ($vendor->accept_online_orders)
                                            <span
                                                style="font-size:.6rem;padding:.1rem .4rem;
                                            border-radius:var(--r-full);background:#eff6ff;
                                            color:#1d4ed8;border:1px solid #bfdbfe;">
                                                🛒 Online Orders
                                            </span>
                                        @endif
                                        @if ($vendor->accept_service_bookings)
                                            <span
                                                style="font-size:.6rem;padding:.1rem .4rem;
                                            border-radius:var(--r-full);background:#f0fdf4;
                                            color:var(--cg-700);border:1px solid var(--cg-200);">
                                                🔧 Service Booking
                                            </span>
                                        @endif
                                    </div>

                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Featured products ── --}}
        @if ($featuredProducts->count())
            <div class="gap-section">
                <div class="cu-section-head">
                    <div class="cu-section-title">⭐ Featured Products</div>
                    <a href="{{ route('customer.products.index') }}" class="cu-section-link">Browse all →</a>
                </div>
                <div class="row g-3">
                    @foreach ($featuredProducts as $product)
                        <div class="col-6 col-md-4 col-lg-3">
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
                                </div>
                                <div class="product-card__body">
                                    <div class="product-card__name">{{ $product->name }}</div>
                                    <div class="product-card__vendor">{{ $product->vendor->business_name }}</div>
                                    <div>
                                        <span class="product-card__price">₱{{ number_format($product->price, 2) }}</span>
                                        @if ($product->compare_price)
                                            <span
                                                class="product-card__compare">₱{{ number_format($product->compare_price, 2) }}</span>
                                        @endif
                                    </div>
                                    <button class="product-card__add" onclick="addToCart({{ $product->id }})">
                                        <i class="bi bi-bag-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Recent orders ── --}}
        @if ($recentOrders->count())
            <div class="gap-section">
                <div class="cu-section-head">
                    <div class="cu-section-title">🛍 Recent Orders</div>
                    <a href="{{ route('customer.orders.index') }}" class="cu-section-link">View all →</a>
                </div>
                @foreach ($recentOrders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}"
                        style="display:flex;align-items:center;gap:0.85rem;padding:0.85rem;
                           background:var(--card-bg);border:1px solid var(--card-border);
                           border-radius:var(--r-md);margin-bottom:0.6rem;text-decoration:none;
                           color:inherit;transition:box-shadow 0.2s;"
                        class="cu-card-link">
                        <div
                            style="width:44px;height:44px;border-radius:var(--r-md);background:var(--cg-50);
                        display:flex;align-items:center;justify-content:center;
                        color:var(--cg-600);font-size:1.1rem;flex-shrink:0;">
                            <i class="bi bi-bag"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:0.875rem;">{{ $order->order_number }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">
                                {{ $order->vendor->business_name }} · {{ $order->items->count() }} item(s)
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div class="mono" style="font-weight:700;font-size:0.875rem;">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </div>
                            <span
                                class="cu-badge
                            @switch($order->status)
                                @case('pending')   cu-badge--pending @break
                                @case('paid')      cu-badge--info    @break
                                @case('shipped')   cu-badge--shipped @break
                                @case('completed') cu-badge--active  @break
                                @case('cancelled') cu-badge--danger  @break
                                @default           cu-badge--neutral
                            @endswitch"
                                style="font-size:0.65rem;">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
@endsection
