@extends('layouts.vendor')
@section('title', 'Deliveries')
@section('page-title', 'Deliveries')

@section('content')

    {{-- Stats strip --}}
@extends('layouts.vendor')

@section('title', 'Delivery')
@section('page-title', 'Delivery')

@section('content')

    {{-- ── Stat strip ── --}}
    <div class="row g-2 gap-section">
        @php
            $statItems = [
                ['Pending',          $stats['pending'],          'yellow', 'bi-hourglass-split'],
                ['Preparing',        $stats['preparing'],        'orange', 'bi-box-seam'],
                ['Out for Delivery', $stats['out_for_delivery'], 'blue',   'bi-truck'],
                ['Delivered',        $stats['delivered'],        'green',  'bi-check-circle'],
                ['Failed',           $stats['failed'],           'red',    'bi-x-circle'],
                ['Returned',         $stats['returned'],         'red',    'bi-arrow-return-left'],
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
                        placeholder="Tracking # or order #...">
                </div>

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'preparing', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', ucfirst($s)) }}
                        </option>
                    @endforeach
                </select>

                <button class="vd-btn vd-btn--primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('vendor.delivery.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Delivery table ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body p-0">
            @if ($deliveries->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Tracking #</th>
                                <th>Order</th>
                                <th>Destination</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Scheduled</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr>
                                    <td>
                                        <a href="{{ route('vendor.delivery.show', $delivery) }}"
                                            class="mono fw-600 text-green" style="font-size:0.82rem;">
                                            {{ $delivery->tracking_number }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($delivery->order)
                                            <a href="{{ route('vendor.orders.show', $delivery->order) }}"
                                                class="mono fw-600" style="font-size:0.82rem;">
                                                {{ $delivery->order->order_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td style="max-width:180px;">
                                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                            title="{{ $delivery->delivery_city }}">
                                            {{ $delivery->delivery_city ?? '--' }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $delivery->assignedEmployee?->full_name ?? '--' }}
                                    </td>
                                    <td>
                                        <span class="{{ $delivery->status_badge_class }}">
                                            {{ $delivery->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $delivery->scheduled_at?->format('M d, Y') ?? '--' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('vendor.delivery.show', $delivery) }}"
                                                class="vd-btn vd-btn--ghost vd-btn--icon" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center p-3">
                    {{ $deliveries->links() }}
                </div>
            @else
                <div class="text-center py-5" style="color:var(--tx-muted);">
                    <i class="bi bi-truck" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                    <h6>No deliveries found</h6>
                    <p>Deliveries will appear here once orders are assigned for shipping.</p>
                </div>
            @endif
        </div>
    </div>

@endsection
