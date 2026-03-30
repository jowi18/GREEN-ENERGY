@extends('layouts.customer')
@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="cu-page" style="max-width:720px;">
        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <a href="{{ route('customer.orders.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;">
                    Order {{ $order->order_number }}
                </div>
                <div class="text-muted" style="font-size:0.78rem;">
                    Placed {{ $order->created_at->format('F d, Y') }} &middot; {{ $order->vendor->business_name }}
                </div>
            </div>
            <span
                class="cu-badge cu-badge--{{ match ($order->status) {
                    'completed' => 'active',
                    'cancelled' => 'danger',
                    'shipped' => 'shipped',
                    'paid', 'processing' => 'info',
                    default => 'pending',
                } }}"
                style="margin-left:auto;">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        {{-- Order timeline --}}
        @php
            $steps = ['pending', 'paid', 'processing', 'shipped', 'completed'];
            $currentIdx = array_search($order->status, $steps);
        @endphp
        @if ($order->status !== 'cancelled')
            <div class="cu-card gap-section">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">Order Status</div>
                    <ul class="order-timeline">
                        @foreach ($steps as $i => $step)
                            @php
                                $done = $currentIdx !== false && $i < $currentIdx;
                                $current = $currentIdx !== false && $i === $currentIdx;
                            @endphp
                            <li class="order-timeline__item {{ $done ? 'done' : ($current ? 'current' : '') }}">
                                <div class="order-timeline__dot">
                                    @if ($done)
                                        <i class="bi bi-check2"></i>
                                    @endif
                                </div>
                                <div class="order-timeline__label">{{ ucfirst($step) }}</div>
                                <div class="order-timeline__time">
                                    @if ($done || $current)
                                        @switch($step)
                                            @case('pending')
                                                {{ $order->created_at->format('M d, g:i A') }}
                                            @break

                                            @case('paid')
                                                {{ $order->paid_at?->format('M d, g:i A') ?? '—' }}
                                            @break

                                            @case('processing')
                                                Processing started
                                            @break

                                            @case('shipped')
                                                {{ $order->delivery?->shipped_at?->format('M d, g:i A') ?? 'Preparing shipment' }}
                                            @break

                                            @case('completed')
                                                {{ $order->completed_at?->format('M d, g:i A') ?? 'Order complete' }}
                                            @break
                                        @endswitch
                                    @else
                                        Pending
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Order items --}}
        <div class="cu-card gap-section">
            <div class="cu-card__body">
                <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">Items Ordered</div>
                @foreach ($order->items as $item)
                    <div style="display:flex;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid var(--cn-100);">
                        @if ($item->product?->primaryImage)
                            <img src="{{ asset('storage/' . $item->product->primaryImage->file_path) }}"
                                style="width:52px;height:52px;border-radius:var(--r-sm);object-fit:cover;border:1px solid var(--card-border);flex-shrink:0;">
                        @else
                            <div
                                style="width:52px;height:52px;border-radius:var(--r-sm);background:var(--cn-50);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-box-seam text-muted"></i>
                            </div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:0.875rem;">{{ $item->product_name }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">
                                ₱{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}
                            </div>
                            @if ($item->warranty_months)
                                <div style="font-size:0.72rem;color:var(--cg-600);margin-top:2px;">
                                    <i class="bi bi-shield-check me-1"></i>{{ $item->warranty_months }}-month warranty
                                </div>
                            @endif
                        </div>
                        <div class="mono fw-700" style="font-size:0.875rem;flex-shrink:0;">
                            ₱{{ number_format($item->total_price, 2) }}
                        </div>
                    </div>
                @endforeach
                <div style="margin-top:0.85rem;padding-top:0.85rem;border-top:1.5px solid var(--card-border);">
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.35rem;">
                        <span class="text-muted">Subtotal</span>
                        <span class="mono">₱{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.35rem;">
                        <span class="text-muted">Shipping</span>
                        <span
                            class="mono">{{ $order->shipping_fee > 0 ? '₱' . number_format($order->shipping_fee, 2) : 'Free' }}</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;border-top:1px solid var(--card-border);padding-top:0.5rem;margin-top:0.35rem;">
                        <span>Total</span>
                        <span class="mono"
                            style="color:var(--cg-700);">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery address --}}
        <div class="cu-card gap-section">
            <div class="cu-card__body">
                <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;">Delivery Address</div>
                <p style="font-size:0.875rem;color:var(--tx-secondary);line-height:1.7;">
                    {{ $order->shipping_address_line1 }}@if ($order->shipping_address_line2)
                        , {{ $order->shipping_address_line2 }}
                    @endif
                    <br>
                    {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country }}
                </p>
                @if ($order->notes)
                    <p style="font-size:0.82rem;color:var(--tx-muted);margin-top:0.65rem;font-style:italic;">
                        Note: {{ $order->notes }}
                    </p>
                @endif
            </div>
        </div>

        @if ($order->status === 'completed')
            @php
                $alreadyReviewed = \App\Models\Review::where('customer_id', auth()->user()->customer->id)
                    ->where('reviewable_type', \App\Models\Order::class)
                    ->where('reviewable_id', $order->id)
                    ->exists();
            @endphp
            @if (!$alreadyReviewed)
                <a href="{{ route('customer.reviews.order.create', $order) }}"
                    class="cu-btn cu-btn--primary cu-btn--full mb-3">
                    <i class="bi bi-star me-1"></i> Write a Review for this Order
                </a>
            @else
                <div class="cu-card mb-3">
                    <div class="cu-card__body text-center" style="padding:1rem;">
                        <i class="bi bi-patch-check-fill text-green me-1"></i>
                        <span style="font-size:.875rem;font-weight:700;">You have reviewed this order. Thank you!</span>
                    </div>
                </div>
            @endif
        @endif
        {{-- Cancel button --}}
        @if (in_array($order->status, ['pending']))
            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                @csrf @method('PATCH')
                <button type="button" class="cu-btn cu-btn--danger cu-btn--full"
                    onclick="Swal.fire({title:'Cancel order?',icon:'warning',showCancelButton:true,confirmButtonText:'Cancel Order',confirmButtonColor:'#ef4444'}).then(r=>{ if(r.isConfirmed) this.closest('form').submit(); })">
                    <i class="bi bi-x-circle"></i> Cancel Order
                </button>
            </form>
        @endif

    </div>
@endsection
