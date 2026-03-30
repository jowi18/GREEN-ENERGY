@extends('layouts.vendor')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')

    {{-- ── Stat strip ── --}}
    <div class="row g-2 gap-section">
        @php
            $statItems = [
                ['Pending', $stats['pending'], 'yellow', 'bi-hourglass-split'],
                ['Paid', $stats['paid'], 'blue', 'bi-credit-card'],
                ['Processing', $stats['processing'], 'orange', 'bi-gear'],
                ['Shipped', $stats['shipped'], 'blue', 'bi-truck'],
                ['Completed', $stats['completed'], 'green', 'bi-bag-check'],
                ['Cancelled', $stats['cancelled'], 'red', 'bi-x-circle'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-4 col-md-2">
                <div class="vd-stat vd-stat--{{ $color }}" style="padding:0.85rem;">
                    <div>
                        <div class="vd-stat__value" style="font-size:1.5rem;">{{ number_format($count) }}</div>
                        <div class="vd-stat__label" style="font-size:0.72rem;">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Filter bar ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">

                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Order # or customer name…">
                </div>

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'paid', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="vd-input"
                    style="width:auto;" title="From date">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="vd-input" style="width:auto;"
                    title="To date">

                <button type="submit" class="vd-btn vd-btn--primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('vendor.orders.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Orders table ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-bag-check"></i> Orders</span>
            <span class="vd-card__subtitle">{{ $orders->total() }} total</span>
        </div>

        @if ($orders->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Delivery</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.orders.show', $order) }}" class="mono fw-600 text-green"
                                        style="font-size:0.82rem;">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.875rem;">
                                        {{ $order->customer->full_name }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        {{ $order->shipping_city }}
                                    </div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
                                </td>
                                <td class="mono fw-600">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="vd-badge {{ $order->payment_status === 'paid' ? 'vd-badge--active' : 'vd-badge--pending' }}"
                                        style="font-size:0.68rem;">
                                        {{ $order->payment_method === 'paypal' ? '💳 PayPal' : '💵 COD' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'pending' => 'vd-badge--pending',
                                            'paid' => 'vd-badge--info',
                                            'processing' => 'vd-badge--warning',
                                            'shipped' => 'vd-badge--shipped',
                                            'delivered' => 'vd-badge--info',
                                            'completed' => 'vd-badge--active',
                                            'cancelled' => 'vd-badge--danger',
                                        ];
                                    @endphp
                                    <span class="vd-badge {{ $statusMap[$order->status] ?? 'vd-badge--neutral' }}"
                                        id="badge-{{ $order->id }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($order->delivery)
                                        <div style="font-size:0.78rem;">
                                            <div class="fw-600">{{ $order->delivery->courier_name }}</div>
                                            @if ($order->delivery->tracking_number)
                                                <div class="mono text-muted" style="font-size:0.72rem;">
                                                    {{ $order->delivery->tracking_number }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $order->created_at->format('M d') }}<br>
                                    <span style="font-size:0.72rem;">{{ $order->created_at->format('g:i A') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.orders.show', $order) }}"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('vendor.orders.invoice', $order) }}" target="_blank"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="Invoice">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}
                    </span>
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-bag-check"></i>
                <h6>No orders yet</h6>
                <p>Orders from customers will appear here.</p>
            </div>
        @endif
    </div>

@endsection
