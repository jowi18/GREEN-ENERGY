@extends('layouts.admin')
@section('title', $order->order_number)
@section('page-title', 'Order Detail')
@section('breadcrumb')
    <a href="{{ route('admin.orders.index') }}" class="text-secondary">Orders</a>
    <span class="sep">›</span><span class="current">{{ $order->order_number }}</span>
@endsection

@section('content')
    <div class="row g-3">

        {{-- Left: order items + timeline --}}
        <div class="col-12 col-lg-8">

            {{-- Items --}}
            <div class="panel mb-3">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.875rem;">
                    <i class="bi bi-box-seam me-2" style="color:#157a30;"></i>
                    Order Items ({{ $order->items->count() }})
                </div>
                @foreach ($order->items as $item)
                    <div
                        style="display:flex;gap:1rem;padding:1rem 1.25rem;border-bottom:1px solid #f8faf8;align-items:center;">
                        @if ($item->productImage)
                            <img src="{{ asset('storage/' . $item->productImage->file_path) }}"
                                style="width:52px;height:52px;object-fit:cover;border-radius:6px;
                            border:1.5px solid #e5ede5;flex-shrink:0;"
                                alt="">
                        @else
                            <div
                                style="width:52px;height:52px;background:#f4f7f4;border-radius:6px;
                            display:flex;align-items:center;justify-content:center;
                            color:#b4beb6;flex-shrink:0;font-size:1.2rem;">
                                <i class="bi bi-box"></i>
                            </div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div class="fw-600" style="font-size:.875rem;">{{ $item->product_name }}</div>
                            <div class="text-muted" style="font-size:.75rem;">
                                ₱{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}
                                @if ($item->warranty_months)
                                    &nbsp;·&nbsp; <i class="bi bi-shield-check" style="color:#157a30;"></i>
                                    {{ $item->warranty_months }}-month warranty
                                @endif
                            </div>
                        </div>
                        <div class="font-monospace fw-800" style="color:#157a30;">
                            ₱{{ number_format($item->subtotal, 2) }}
                        </div>
                    </div>
                @endforeach

                {{-- Totals --}}
                <div style="padding:1rem 1.25rem;">
                    @foreach ([['Subtotal', $order->subtotal], ['Shipping', $order->shipping_fee], ['Tax', $order->tax_amount], ['Discount', -($order->discount_amount ?? 0)]] as [$l, $v])
                        @if ($v != 0)
                            <div
                                style="display:flex;justify-content:space-between;font-size:.82rem;
                            padding:.2rem 0;color:#4b5563;">
                                <span>{{ $l }}</span>
                                <span class="font-monospace">₱{{ number_format($v, 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div
                        style="display:flex;justify-content:space-between;font-size:1rem;
                            font-weight:800;padding:.5rem 0;border-top:1.5px solid #e5ede5;
                            margin-top:.35rem;color:#157a30;">
                        <span>Total</span>
                        <span class="font-monospace">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Shipping address --}}
            <div class="panel">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.875rem;">
                    <i class="bi bi-geo-alt me-2" style="color:#ef4444;"></i>Shipping Address
                </div>
                <div style="padding:1rem 1.25rem;font-size:.875rem;color:#4b5563;line-height:1.7;">
                    {{ $order->shipping_address_line1 }}<br>
                    @if ($order->shipping_address_line2)
                        {{ $order->shipping_address_line2 }}<br>
                    @endif
                    {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country ?? 'Philippines' }}
                </div>
            </div>
        </div>

        {{-- Right: order meta + status --}}
        <div class="col-12 col-lg-4">

            {{-- Status card --}}
            <div class="panel mb-3">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.875rem;">
                    Order Info
                </div>
                <div style="padding:1rem 1.25rem;">
                    @php
                        $sc = [
                            'pending' => 'warning text-dark',
                            'confirmed' => 'info',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        ];
                        $ps = [
                            'paid' => 'success',
                            'unpaid' => 'secondary',
                            'failed' => 'danger',
                            'refunded' => 'warning text-dark',
                        ];
                    @endphp
                    @foreach ([['Order #', $order->order_number, null], ['Status', null, 'badge bg-' . ($sc[$order->status] ?? 'secondary') . ' ' . ucfirst($order->status)], ['Payment', null, 'badge bg-' . ($ps[$order->payment_status ?? 'unpaid'] ?? 'secondary') . ' ' . ucfirst($order->payment_status ?? 'unpaid')], ['Method', ucfirst(str_replace('_', ' ', $order->payment_method ?? '—')), null], ['Placed', $order->created_at->format('M d, Y g:i A'), null], ['PayPal ID', Str::limit($order->paypal_order_id ?? '—', 20), null]] as [$l, $v, $badge])
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;
                            padding:.4rem 0;font-size:.82rem;border-bottom:1px solid #f8faf8;">
                            <span style="color:#6b7a70;">{{ $l }}</span>
                            @if ($badge)
                                <span class="{{ implode(' ', array_slice(explode(' ', $badge), 0, 3)) }}"
                                    style="font-size:.68rem;">{{ last(explode(' ', $badge)) }}</span>
                            @else
                                <span class="fw-600 font-monospace"
                                    style="font-size:.78rem;color:#1a2e1c;">{{ $v }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Customer --}}
            <div class="panel mb-3">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.875rem;">
                    Customer
                </div>
                <div style="padding:1rem 1.25rem;">
                    <div class="fw-700" style="font-size:.9rem;">{{ $order->customer->full_name }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $order->customer->user->email }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $order->customer->phone ?? '—' }}</div>
                    <a href="{{ route('admin.customers.show', $order->customer) }}"
                        class="btn btn-sm btn-outline-success mt-2 w-100">
                        <i class="bi bi-person me-1"></i> View Profile
                    </a>
                </div>
            </div>

            {{-- Vendor --}}
            <div class="panel">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.875rem;">
                    Vendor
                </div>
                <div style="padding:1rem 1.25rem;">
                    <div class="fw-700" style="font-size:.9rem;">{{ $order->vendor->business_name }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $order->vendor->city }},
                        {{ $order->vendor->province_state }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $order->vendor->business_phone }}</div>
                    <a href="{{ route('admin.vendors.show', $order->vendor) }}"
                        class="btn btn-sm btn-outline-success mt-2 w-100">
                        <i class="bi bi-shop me-1"></i> View Vendor
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
