@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- ── Stat cards ── --}}
    <div class="row g-3 section-gap">

        <div class="col-6 col-xl-3">
            <div class="stat-card stat-card--green">
                <div class="stat-card__icon stat-card__icon--green"><i class="bi bi-shop"></i></div>
                <div class="stat-card__value">{{ number_format($stats['active_vendors']) }}</div>
                <div class="stat-card__label">Active Vendors</div>
                <div class="stat-card__change stat-card__change--up">
                    <i class="bi bi-arrow-up-short"></i> {{ $stats['total_vendors'] }} total registered
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card stat-card--yellow">
                <div class="stat-card__icon stat-card__icon--yellow"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__value">{{ number_format($stats['pending_approvals']) }}</div>
                <div class="stat-card__label">Pending Approvals</div>
                <div
                    class="stat-card__change {{ $stats['pending_approvals'] > 0 ? 'stat-card__change--warn' : 'stat-card__change--up' }}">
                    <i class="bi bi-{{ $stats['pending_approvals'] > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
                    {{ $stats['pending_approvals'] > 0 ? 'Requires review' : 'All clear' }}
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card stat-card--blue">
                <div class="stat-card__icon stat-card__icon--blue"><i class="bi bi-people"></i></div>
                <div class="stat-card__value">{{ number_format($stats['total_customers']) }}</div>
                <div class="stat-card__label">Total Customers</div>
                <div class="stat-card__change stat-card__change--up">
                    <i class="bi bi-arrow-up-short"></i> Platform registrations
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card stat-card--green">
                <div class="stat-card__icon stat-card__icon--green"><i class="bi bi-credit-card"></i></div>
                <div class="stat-card__value">{{ number_format($stats['active_subscriptions']) }}</div>
                <div class="stat-card__label">Active Subscriptions</div>
                <div class="stat-card__change stat-card__change--up">
                    <i class="bi bi-arrow-up-short"></i> ${{ number_format($stats['revenue_this_month'], 2) }} this month
                </div>
            </div>
        </div>

    </div>

    {{-- ── Charts ── --}}
    <div class="row g-3 section-gap">

        <div class="col-12 col-xl-8">
            <div class="panel">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-bar-chart-line"></i>Subscription Revenue</span>
                    <span class="panel__subtitle">Last 6 months</span>
                </div>
                <div class="panel__body chart-wrap">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel h-100">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-graph-up-arrow"></i>Vendor Growth</span>
                    <span class="panel__subtitle">Last 6 months</span>
                </div>
                <div class="panel__body chart-wrap">
                    <canvas id="growthChart" height="175"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Pending approvals + expiring subs ── --}}
    <div class="row g-3 section-gap">

        <div class="col-12 col-xl-7">
            <div class="panel">
                <div class="panel__header">
                    <span class="panel__title">
                        <i class="bi bi-hourglass-split"></i> Pending Approvals
                        @if (count($pendingVendors))
                            <span class="nav-badge ms-1">{{ count($pendingVendors) }}</span>
                        @endif
                    </span>
                    <a href="{{ route('admin.vendors.pending') }}" class="btn btn--ghost">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                @if (count($pendingVendors))
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Business</th>
                                    <th>Type</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingVendors as $vendor)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar avatar--vendor">
                                                    {{ strtoupper(substr($vendor->business_name, 0, 2)) }}</div>
                                                <div>
                                                    <div class="fw-600" style="font-size:0.875rem;">
                                                        {{ $vendor->business_name }}</div>
                                                    <div class="text-muted" style="font-size:0.75rem;">{{ $vendor->city }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-secondary">
                                            {{ ucwords(str_replace('_', ' ', $vendor->business_type)) }}</td>
                                        <td class="text-secondary" style="font-size:0.8rem;">
                                            {{ $vendor->created_at->diffForHumans() }}</td>
                                        <td>
                                            <span
                                                class="badge-status {{ $vendor->status === 'pending' ? 'badge--pending' : 'badge--review' }}">
                                                {{ $vendor->status === 'under_review' ? 'Under Review' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.vendors.show', $vendor) }}"
                                                class="btn btn--primary btn--icon">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state"><i class="bi bi-check-circle"></i>
                        <p>No pending approvals — all clear!</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="panel">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-clock-history"></i> Expiring Soon</span>
                    <span class="panel__subtitle">Within 7 days</span>
                </div>

                @if (count($expiringSoon))
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Plan</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiringSoon as $sub)
                                    <tr>
                                        <td class="fw-600" style="font-size:0.875rem;">{{ $sub->vendor->business_name }}
                                        </td>
                                        <td class="text-secondary" style="font-size:0.82rem;">{{ $sub->plan->name }}</td>
                                        <td><span class="mono text-red"
                                                style="font-size:0.78rem;font-weight:600;">{{ $sub->expires_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state"><i class="bi bi-calendar-check"></i>
                        <p>No subscriptions expiring soon.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Recent orders ── --}}
    <div class="panel section-gap">
        <div class="panel__header">
            <span class="panel__title"><i class="bi bi-bag-check"></i> Recent Platform Orders</span>
            <span class="panel__subtitle">{{ $stats['orders_today'] }} today</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="mono text-secondary" style="font-size:0.8rem;">{{ $order->order_number }}</td>
                            <td class="fw-600">{{ $order->customer->full_name }}</td>
                            <td class="text-secondary">{{ $order->vendor->business_name }}</td>
                            <td class="mono fw-600">{{ $order->formatted_total }}</td>
                            <td>
                                <span
                                    class="badge-status @switch($order->status) @case('completed') badge--active @break @case('cancelled') badge--rejected @break @case('paid') badge--verified @break @default badge--review @endswitch">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $order->created_at->format('M d, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="padding:1.5rem 0;"><i class="bi bi-bag"></i>
                                    <p>No orders yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const revenueData = @json($revenueChart);
        const growthData = @json($vendorGrowth);

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: revenueData.map(d => d.label),
                datasets: [{
                    label: 'Revenue (USD)',
                    data: revenueData.map(d => d.total),
                    backgroundColor: 'rgba(46,204,113,0.18)',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.04)'
                        },
                        ticks: {
                            callback: v => '$' + v.toLocaleString()
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('growthChart'), {
            type: 'line',
            data: {
                labels: growthData.map(d => d.label),
                datasets: [{
                    label: 'New Vendors',
                    data: growthData.map(d => d.total),
                    borderColor: '#f5c518',
                    backgroundColor: 'rgba(245,197,24,0.1)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#f5c518'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.04)'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endpush
