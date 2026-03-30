{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/vendor/procurement/po/index.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}
@extends('layouts.vendor')
@section('title', 'Purchase Orders')
@section('page-title', 'Procurement')

@section('breadcrumb')
    <span class="current">Purchase Orders</span>
@endsection

@section('content')

    {{-- ── Toolbar ───────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
        <div class="d-flex gap-1 flex-wrap">
            @foreach ([
            '' => 'All',
            'draft' => 'Draft',
            'submitted' => 'Sent',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'confirmed' => 'Confirmed',
        ] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                    class="btn btn-sm {{ request('status', '') === $val ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="font-size:.78rem;border-radius:999px;">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        {{-- POs are created from approved PRs --}}
        <a href="{{ route('vendor.procurement.pr.index', ['status' => 'approved']) }}" class="vd-btn vd-btn--ghost"
            style="font-size:.8rem;">
            <i class="bi bi-receipt-cutoff me-1"></i> Create from PR
        </a>
    </div>

    {{-- Deliver pending alert --}}
    @php
        $pendingConfirm = $orders->getCollection()->where('status', 'delivered')->count();
    @endphp
    @if ($pendingConfirm > 0)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 py-2" style="font-size:.82rem;">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>
                <strong>{{ $pendingConfirm }}</strong> order(s) delivered and awaiting your confirmation.
                <a href="{{ request()->fullUrlWithQuery(['status' => 'delivered']) }}" class="fw-700">Review →</a>
            </span>
        </div>
    @endif

    {{-- ── Table ─────────────────────────────────────────────────────────── --}}
    <div class="vd-card">
        <div class="vd-card__body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                    <thead style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                        <tr>
                            <th class="ps-3">PO #</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Tracking</th>
                            <th>Exp. Delivery</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr {{ $order->status === 'delivered' ? 'style=background:var(--n-50);' : '' }}>
                                <td class="ps-3 fw-700">{{ $order->po_number }}</td>
                                <td>{{ $order->supplier->company_name }}</td>
                                <td>{{ $order->items->count() }}</td>
                                <td class="fw-600">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ $order->tracking_number ? $order->tracking_number . ($order->courier ? ' / ' . $order->courier : '') : '—' }}
                                </td>
                                <td class="text-muted">
                                    {{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $order->status_badge }} text-primary">{{ $order->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.procurement.po.show', $order) }}"
                                        class="vd-btn vd-btn--ghost vd-btn--sm">
                                        @if ($order->status === 'delivered')
                                            <i class="bi bi-check2-circle me-1 text-success"></i>Confirm
                                        @else
                                            View
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5" style="color:var(--tx-muted);font-size:.85rem;">
                                    <i class="bi bi-receipt d-block mb-2" style="font-size:2rem;"></i>
                                    No purchase orders yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>

@endsection


