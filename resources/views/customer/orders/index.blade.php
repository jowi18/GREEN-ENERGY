{{-- ═══════════════════════════════════════════════
     resources/views/customer/orders/index.blade.php
═══════════════════════════════════════════════ --}}
@extends('layouts.customer')
@section('title', 'My Orders')
@section('content')
    <div class="cu-page">
        <div class="cu-section-head" style="margin-bottom:1.1rem;">
            <div class="cu-section-title">My Orders</div>
            <a href="{{ route('customer.products.index') }}" class="cu-section-link">Browse products →</a>
        </div>

        {{-- Status filter --}}
        <div
            style="display:flex;gap:0.4rem;overflow-x:auto;padding-bottom:0.35rem;margin-bottom:1.1rem;scrollbar-width:none;">
            @foreach (['', 'pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'] as $status)
                <a href="{{ route('customer.orders.index', $status ? ['status' => $status] : []) }}"
                    class="cu-btn cu-btn--sm {{ request('status', $status === '' ? '' : null) === $status ? 'cu-btn--primary' : 'cu-btn--ghost' }}"
                    style="flex-shrink:0;white-space:nowrap;">
                    {{ $status ? ucfirst($status) : 'All' }}
                </a>
            @endforeach
        </div>

        @if ($orders->count())
            @foreach ($orders as $order)
                <a href="{{ route('customer.orders.show', $order) }}"
                    style="display:block;text-decoration:none;color:inherit;margin-bottom:0.75rem;">
                    <div class="cu-card" style="transition:box-shadow 0.2s;">
                        <div class="cu-card__body">
                            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                                <div
                                    style="width:42px;height:42px;border-radius:var(--r-md);background:var(--cg-50);
                                display:flex;align-items:center;justify-content:center;color:var(--cg-600);flex-shrink:0;">
                                    <i class="bi bi-bag" style="font-size:1.1rem;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:800;font-size:0.9rem;">{{ $order->order_number }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">
                                        {{ $order->vendor->business_name }} &middot; {{ $order->items->count() }} item(s)
                                        &middot; {{ $order->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div class="mono fw-700" style="font-size:0.9rem;">
                                        ₱{{ number_format($order->total_amount, 2) }}</div>
                                    <span
                                        class="cu-badge cu-badge--{{ match ($order->status) {'completed' => 'active','cancelled' => 'danger','shipped' => 'shipped','paid', 'processing' => 'info',default => 'pending'} }}"
                                        style="font-size:0.65rem;">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
            <div class="d-flex justify-content-center mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="cu-empty"><i class="bi bi-bag"></i>
                <h6>No orders yet</h6>
                <p>Start shopping to see your orders here.</p>
                <a href="{{ route('customer.products.index') }}" class="cu-btn cu-btn--primary mt-3 d-inline-flex"><i
                        class="bi bi-bag-plus"></i> Browse Products</a>
            </div>
        @endif
    </div>
@endsection
