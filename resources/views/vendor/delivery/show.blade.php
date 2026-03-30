@extends('layouts.vendor')
@section('title', 'Delivery ' . $delivery->tracking_number)
@section('page-title', 'Delivery Details')

@section('content')

    {{-- Back link --}}
    <div class="gap-section">
        <a href="{{ route('vendor.delivery.index') }}" class="vd-btn vd-btn--ghost vd-btn--sm">
            <i class="bi bi-arrow-left"></i> Back to Deliveries
        </a>
    </div>

    <div class="row g-3">

        {{-- Left column: delivery info --}}
        <div class="col-lg-8">

            {{-- Delivery overview --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-truck"></i> {{ $delivery->tracking_number }}</span>
                    <span class="{{ $delivery->status_badge_class }}" style="font-size:0.85rem;">
                        {{ $delivery->status_label }}
                    </span>
                </div>
                <div class="vd-card__body">
                    <div class="detail-grid">
                        <div>
                            <div class="detail-item__label">Tracking Number</div>
                            <div class="detail-item__value mono">{{ $delivery->tracking_number ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Status</div>
                            <div class="detail-item__value">
                                <span class="{{ $delivery->status_badge_class }}">{{ $delivery->status_label }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Assigned To</div>
                            <div class="detail-item__value">
                                @if ($delivery->assignedEmployee)
                                    {{ $delivery->assignedEmployee->user->name ?? 'Employee #' . $delivery->assigned_to }}
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Recipient</div>
                            <div class="detail-item__value">{{ $delivery->recipient_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Scheduled At</div>
                            <div class="detail-item__value">
                                {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('M d, Y h:i A') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Dispatched At</div>
                            <div class="detail-item__value">
                                {{ $delivery->dispatched_at ? $delivery->dispatched_at->format('M d, Y h:i A') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Delivered At</div>
                            <div class="detail-item__value">
                                {{ $delivery->delivered_at ? $delivery->delivered_at->format('M d, Y h:i A') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Created</div>
                            <div class="detail-item__value">{{ $delivery->created_at->format('M d, Y h:i A') }}</div>
                        </div>
@extends('layouts.vendor')
@section('title', 'Delivery · ' . $delivery->tracking_number)

@section('breadcrumb')
    <a href="{{ route('vendor.delivery.index') }}" class="text-secondary">Deliveries</a>
    <span class="sep">></span>
    <span class="current">{{ $delivery->tracking_number }}</span>
@endsection

@section('content')

    <div class="row g-3">
        {{-- ── Main info ── --}}
        <div class="col-lg-8">
            <div class="vd-card">
                <div class="vd-card__header d-flex align-items-center justify-content-between">
                    <span class="vd-card__title"><i class="bi bi-truck"></i> Delivery Details</span>
                    <span class="{{ $delivery->status_badge_class }}">{{ $delivery->status_label }}</span>
                </div>
                <div class="vd-card__body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;">Tracking Number</div>
                            <div class="mono fw-700">{{ $delivery->tracking_number }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;">Order</div>
                            @if ($delivery->order)
                                <a href="{{ route('vendor.orders.show', $delivery->order) }}" class="fw-700 text-green">
                                    {{ $delivery->order->order_number }}
                                </a>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;">Recipient</div>
                            <div>{{ $delivery->recipient_name ?? '--' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;">Assigned To</div>
                            <div>{{ $delivery->assignedEmployee?->full_name ?? 'Unassigned' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;">Delivery Address</div>
                            <div>
                                {{ $delivery->delivery_address_line1 ?? '' }}
                                @if ($delivery->delivery_address_line2)
                                    <br>{{ $delivery->delivery_address_line2 }}
                                @endif
                                @if ($delivery->delivery_city || $delivery->delivery_province)
                                    <br>{{ $delivery->delivery_city }}, {{ $delivery->delivery_province }}
                                    {{ $delivery->delivery_postal_code }}
                                @endif
                            </div>
                        </div>
                        @if ($delivery->delivery_notes)
                            <div class="col-12">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;">Delivery Notes</div>
                                <div style="font-size:.875rem;">{{ $delivery->delivery_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Timeline ── --}}
            <div class="vd-card mt-3">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-clock-history"></i> Timeline</span>
                </div>
                <div class="vd-card__body">
                    <div class="d-flex flex-column gap-2" style="font-size:.85rem;">
                        @if ($delivery->assigned_at)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-check text-primary"></i>
                                <span>Assigned {{ $delivery->assigned_at->format('M d, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if ($delivery->scheduled_at)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-info"></i>
                                <span>Scheduled for {{ $delivery->scheduled_at->format('M d, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if ($delivery->dispatched_at)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-primary"></i>
                                <span>Dispatched {{ $delivery->dispatched_at->format('M d, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if ($delivery->delivered_at)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Delivered {{ $delivery->delivered_at->format('M d, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if (!$delivery->assigned_at && !$delivery->dispatched_at && !$delivery->delivered_at)
                            <div class="text-muted text-center py-3">No timeline events yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Sidebar actions ── --}}
        <div class="col-lg-4">
            @if (!in_array($delivery->status, ['delivered', 'returned']))
                <div class="vd-card mb-3">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-gear"></i> Update Status</span>
                    </div>
                    <div class="vd-card__body">
                        <form method="POST" action="{{ route('vendor.delivery.status', $delivery) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="vd-select mb-2">
                                @foreach (['pending', 'preparing', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $s)
                                    <option value="{{ $s }}" {{ $delivery->status === $s ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($s)) }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="vd-btn vd-btn--primary w-100">
                                <i class="bi bi-check-lg"></i> Update
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <a href="{{ route('vendor.delivery.index') }}" class="vd-btn vd-btn--ghost w-100">
                <i class="bi bi-arrow-left me-1"></i> Back to Deliveries
            </a>
        </div>
    </div>

@endsection
