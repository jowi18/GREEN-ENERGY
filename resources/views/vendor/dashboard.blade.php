@extends('layouts.vendor')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- ── Subscription expiry warning banner ── --}}
    @if ($subscription && $subscription->daysRemaining() <= 7)
        <div class="sub-banner sub-banner--expiring gap-section">
            <div class="sub-banner__icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="sub-banner__text">
                <div class="sub-banner__title">Subscription expiring in {{ $subscription->daysRemaining() }}
                    day{{ $subscription->daysRemaining() !== 1 ? 's' : '' }}</div>
                <div class="sub-banner__desc">Renew now to avoid interruption to your vendor portal access.</div>
            </div>
            <a href="{{ route('vendor.subscription.index') }}" class="vd-btn vd-btn--secondary">
                <i class="bi bi-arrow-repeat"></i> Renew Now
            </a>
        </div>
    @elseif(!$subscription)
        <div class="sub-banner sub-banner--expired gap-section">
            <div class="sub-banner__icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="sub-banner__text">
                <div class="sub-banner__title">No active subscription</div>
                <div class="sub-banner__desc">Subscribe to unlock full access to your vendor portal.</div>
            </div>
            <a href="{{ route('vendor.subscription.index') }}" class="vd-btn vd-btn--primary">
                <i class="bi bi-lightning-charge"></i> Subscribe Now
            </a>
        </div>
    @endif

    {{-- ── Stat cards ── --}}
    <div class="row g-3 gap-section">

        <div class="col-6 col-xl-2-4">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-bag-check"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['orders_today']) }}</div>
                <div class="vd-stat__label">Orders Today</div>
                <div class="vd-stat__change vd-stat__change--neutral">
                    <i class="bi bi-calendar3"></i> {{ number_format($stats['total_orders']) }} total
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-2-4">
            <div class="vd-stat vd-stat--yellow">
                <div class="vd-stat__icon vd-stat__icon--yellow"><i class="bi bi-shop"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['pos_sales_today']) }}</div>
                <div class="vd-stat__label">POS Sales Today</div>
                <div class="vd-stat__change vd-stat__change--up">
                    <i class="bi bi-cash-stack"></i> ₱{{ number_format($stats['pos_revenue_today'], 2) }}
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-2-4">
            <div class="vd-stat vd-stat--orange">
                <div class="vd-stat__icon vd-stat__icon--orange"><i class="bi bi-hourglass-split"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['orders_pending']) }}</div>
                <div class="vd-stat__label">Pending Orders</div>
                <div
                    class="vd-stat__change {{ $stats['orders_pending'] > 0 ? 'vd-stat__change--warn' : 'vd-stat__change--up' }}">
                    <i class="bi bi-{{ $stats['orders_pending'] > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
                    {{ $stats['orders_pending'] > 0 ? 'Need attention' : 'All clear' }}
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-2-4">
            <div class="vd-stat vd-stat--red">
                <div class="vd-stat__icon vd-stat__icon--red"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['low_stock_count']) }}</div>
                <div class="vd-stat__label">Low Stock Items</div>
                <div
                    class="vd-stat__change {{ $stats['low_stock_count'] > 0 ? 'vd-stat__change--down' : 'vd-stat__change--up' }}">
                    <i class="bi bi-archive"></i>
                    {{ $stats['out_of_stock_count'] }} out of stock
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-2-4">
            <div class="vd-stat vd-stat--blue">
                <div class="vd-stat__icon vd-stat__icon--blue"><i class="bi bi-tools"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['open_service_requests']) }}</div>
                <div class="vd-stat__label">Open Services</div>
                <div class="vd-stat__change vd-stat__change--neutral">
                    <i class="bi bi-shield-check"></i>
                    {{ $stats['open_warranty_claims'] }} warranty claims
                </div>
            </div>
        </div>

    </div>

    <style>
        /* 5-col stat grid fix */
        @media (min-width:1280px) {
            .col-xl-2-4 {
                width: 20%;
                flex: 0 0 20%;
                max-width: 20%;
            }
        }
    </style>

    {{-- ── Sales chart + subscription info ── --}}
    <div class="row g-3 gap-section">

        <div class="col-12 col-xl-8">
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-bar-chart-line"></i>Revenue — Last 30 Days</span>
                    <div class="d-flex gap-2 align-items-center" style="font-size:0.75rem;">
                        <span class="d-flex align-items-center gap-1 text-muted">
                            <span
                                style="width:10px;height:3px;background:var(--g-400);display:inline-block;border-radius:2px;"></span>
                            Online orders
                        </span>
                        <span class="d-flex align-items-center gap-1 text-muted">
                            <span
                                style="width:10px;height:3px;background:var(--y-400);display:inline-block;border-radius:2px;"></span>
                            POS sales
                        </span>
                    </div>
                </div>
                <div class="vd-card__body chart-container">
                    <canvas id="salesChart" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="vd-card h-100">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-credit-card"></i>Subscription</span>
                    <a href="{{ route('vendor.subscription.index') }}" class="vd-btn vd-btn--ghost vd-btn--sm">Manage</a>
                </div>
                <div class="vd-card__body">

                    @if ($subscription)
                        <div class="text-center" style="padding:0.5rem 0 1.25rem;">
                            <div
                                style="font-family:'Outfit',sans-serif;font-size:2rem;font-weight:800;
                                letter-spacing:-0.04em;color:var(--tx-primary);line-height:1;">
                                {{ $subscription->daysRemaining() }}
                            </div>
                            <div class="text-muted" style="font-size:0.8rem;margin-top:0.2rem;">days remaining</div>

                            {{-- Radial progress bar --}}
                            @php
                                $totalDays = $subscription->plan->duration_days;
                                $elapsed = $totalDays - $subscription->daysRemaining();
                                $pct = $totalDays > 0 ? round(($subscription->daysRemaining() / $totalDays) * 100) : 0;
                                $color = $pct > 30 ? 'var(--g-400)' : ($pct > 10 ? 'var(--y-400)' : 'var(--red-500)');
                            @endphp

                            <div style="margin:1rem auto;width:80px;height:80px;position:relative;">
                                <svg viewBox="0 0 36 36" style="transform:rotate(-90deg);">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--n-100)"
                                        stroke-width="2.5" />
                                    <circle cx="18" cy="18" r="15.9" fill="none"
                                        stroke="{{ $color }}" stroke-width="2.5"
                                        stroke-dasharray="{{ $pct }} {{ 100 - $pct }}"
                                        stroke-linecap="round" />
                                </svg>
                                <div
                                    style="position:absolute;inset:0;display:flex;align-items:center;
                                    justify-content:center;font-family:'Outfit',sans-serif;
                                    font-size:0.9rem;font-weight:700;color:var(--tx-primary);">
                                    {{ $pct }}%
                                </div>
                            </div>
                        </div>

                        <hr class="divider">

                        <div class="detail-grid">
                            <div>
                                <div class="detail-item__label">Plan</div>
                                <div class="detail-item__value">{{ $subscription->plan->name }}</div>
                            </div>
                            <div>
                                <div class="detail-item__label">Status</div>
                                <div class="detail-item__value">
                                    <span class="vd-badge vd-badge--active">Active</span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Renews</div>
                                <div class="detail-item__value" style="font-size:0.82rem;">
                                    {{ $subscription->expires_at->format('M d, Y') }}
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Amount</div>
                                <div class="detail-item__value">
                                    ${{ number_format($subscription->plan->price, 2) }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-credit-card"></i>
                            <h6>No active plan</h6>
                            <p>Subscribe to unlock your full vendor portal.</p>
                            <a href="{{ route('vendor.subscription.index') }}"
                                class="vd-btn vd-btn--primary vd-btn--sm mt-3 d-inline-flex">
                                <i class="bi bi-lightning-charge"></i> Subscribe Now
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

    {{-- ── Recent orders + low stock ── --}}
    <div class="row g-3 gap-section">

        {{-- Recent orders --}}
        <div class="col-12 col-xl-7">
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-bag-check"></i>Recent Orders</span>
                    <a href="#" class="vd-btn vd-btn--ghost vd-btn--sm">View all <i
                            class="bi bi-arrow-right"></i></a>
                </div>

                @if ($recentOrders->count())
                    <div class="table-responsive">
                        <table class="vd-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="mono text-secondary" style="font-size:0.78rem;">
                                            {{ $order->order_number }}
                                        </td>
                                        <td class="fw-600" style="font-size:0.875rem;">
                                            {{ $order->customer->full_name }}
                                        </td>
                                        <td class="mono fw-600">{{ $order->formatted_total }}</td>
                                        <td>
                                            <span
                                                class="vd-badge
                                    @switch($order->status)
                                        @case('pending')   vd-badge--pending @break
                                        @case('paid')      vd-badge--paid    @break
                                        @case('processing')vd-badge--info    @break
                                        @case('shipped')   vd-badge--shipped @break
                                        @case('delivered')
                                        @case('completed') vd-badge--active  @break
                                        @case('cancelled') vd-badge--danger  @break
                                        @default           vd-badge--neutral
                                    @endswitch">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted" style="font-size:0.78rem;">
                                            {{ $order->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state"><i class="bi bi-bag"></i>
                        <h6>No orders yet</h6>
                        <p>Orders will appear here once customers start purchasing.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Low stock items --}}
        <div class="col-12 col-xl-5">
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-exclamation-triangle"></i>Low Stock Alerts</span>
                    <a href="#" class="vd-btn vd-btn--ghost vd-btn--sm">View inventory</a>
                </div>

                @if ($lowStockItems->count())
                    <div class="vd-card__body" style="padding-bottom:0.5rem;">
                        @foreach ($lowStockItems as $item)
                            @php
                                $qty = $item->quantity_on_hand;
                                $max = max($item->reorder_point * 3, 1);
                                $pct = min(100, round(($qty / $max) * 100));
                                $state = $qty === 0 ? 'critical' : ($qty <= $item->reorder_point ? 'low' : 'ok');
                            @endphp
                            <div
                                style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0;
                            border-bottom:1px solid var(--n-100);">
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-600"
                                        style="font-size:0.84rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $item->product->name }}
                                    </div>
                                    <div class="stock-bar mt-1">
                                        <div class="stock-bar__track">
                                            <div class="stock-bar__fill stock-bar__fill--{{ $state }}"
                                                style="width:{{ $pct }}%;"></div>
                                        </div>
                                        <span class="stock-bar__qty">{{ $qty }}</span>
                                    </div>
                                </div>
                                <span class="vd-badge {{ $qty === 0 ? 'vd-badge--danger' : 'vd-badge--warning' }}">
                                    {{ $qty === 0 ? 'Out' : 'Low' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state"><i class="bi bi-box-seam"></i>
                        <h6>All stock levels are healthy</h6>
                        <p>You'll be alerted when items drop below reorder point.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Recent service requests ── --}}
    @if ($recentServices->count())
        <div class="vd-card gap-section">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-tools"></i>Recent Service Requests</span>
                <a href="#" class="vd-btn vd-btn--ghost vd-btn--sm">View all</a>
            </div>
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentServices as $sr)
                            <tr>
                                <td class="mono text-secondary" style="font-size:0.78rem;">{{ $sr->request_number }}</td>
                                <td class="fw-600" style="font-size:0.875rem;">{{ $sr->customer->full_name }}</td>
                                <td class="text-secondary">{{ $sr->service_type_label }}</td>
                                <td class="text-secondary" style="font-size:0.82rem;">{{ $sr->service_city }}</td>
                                <td>
                                    <span
                                        class="vd-badge
                            @switch($sr->status)
                                @case('submitted')    vd-badge--pending @break
                                @case('scheduled')    vd-badge--info    @break
                                @case('in_progress')  vd-badge--warning @break
                                @case('completed')    vd-badge--active  @break
                                @case('cancelled')    vd-badge--danger  @break
                                @default              vd-badge--neutral
                            @endswitch">
                                        {{ ucwords(str_replace('_', ' ', $sr->status)) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.78rem;">{{ $sr->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        const chartData = @json($chartData);

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.label),
                datasets: [{
                        label: 'Online Orders',
                        data: chartData.map(d => d.online_revenue),
                        backgroundColor: 'rgba(46,204,113,0.22)',
                        borderColor: '#2ecc71',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'POS Sales',
                        data: chartData.map(d => d.pos_revenue),
                        backgroundColor: 'rgba(245,197,24,0.22)',
                        borderColor: '#f5c518',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', {
                                minimumFractionDigits: 2
                            })
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxTicksLimit: 10
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.04)'
                        },
                        ticks: {
                            callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                        }
                    }
                }
            }
        });
    </script>
@endpush
