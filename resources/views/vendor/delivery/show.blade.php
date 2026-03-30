{{-- ================================================================
     resources/views/vendor/delivery/show.blade.php
================================================================ --}}
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
                    </div>
                </div>
            </div>

            {{-- Delivery address --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-geo-alt"></i> Delivery Address</span>
                </div>
                <div class="vd-card__body">
                    <div class="detail-grid">
                        <div>
                            <div class="detail-item__label">Address Line 1</div>
                            <div class="detail-item__value">{{ $delivery->delivery_address_line1 ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Address Line 2</div>
                            <div class="detail-item__value">{{ $delivery->delivery_address_line2 ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">City</div>
                            <div class="detail-item__value">{{ $delivery->delivery_city ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Province</div>
                            <div class="detail-item__value">{{ $delivery->delivery_province ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Postal Code</div>
                            <div class="detail-item__value">{{ $delivery->delivery_postal_code ?? '-' }}</div>
                        </div>
                    </div>

                    @if ($delivery->delivery_notes)
                        <div class="mt-3">
                            <div class="detail-item__label">Delivery Notes</div>
                            <div class="detail-item__value">{{ $delivery->delivery_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order items --}}
            @if ($delivery->order && $delivery->order->items->count())
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-box-seam"></i> Order Items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="vd-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($delivery->order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>R {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="fw-600">R {{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column: actions & linked order --}}
        <div class="col-lg-4">

            {{-- Quick actions --}}
            @if (!in_array($delivery->status, ['delivered', 'returned']))
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-lightning"></i> Quick Actions</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label fw-600">Update Status</label>
                            <select id="newStatus" class="vd-select">
                                @foreach (['pending', 'preparing', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $s)
                                    <option value="{{ $s }}" {{ $delivery->status === $s ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusNotes" class="form-label fw-600">Notes</label>
                            <textarea id="statusNotes" class="vd-input" rows="3" maxlength="500"
                                      placeholder="Optional notes..."></textarea>
                        </div>
                        <button type="button" class="vd-btn vd-btn--primary w-100" id="btnUpdateStatus">
                            <i class="bi bi-check-lg"></i> Update Status
                        </button>
                    </div>
                </div>
            @endif

            {{-- Linked order --}}
            @if ($delivery->order)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-bag-check"></i> Linked Order</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item__label">Order Number</div>
                                <div class="detail-item__value mono">
                                    <a href="{{ route('vendor.orders.show', $delivery->order) }}" class="text-green fw-600">
                                        {{ $delivery->order->order_number }}
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Order Status</div>
                                <div class="detail-item__value">
                                    <span class="badge bg-secondary">{{ ucfirst($delivery->order->status) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Customer</div>
                                <div class="detail-item__value">
                                    @if ($delivery->order->customer)
                                        {{ $delivery->order->customer->first_name }}
                                        {{ $delivery->order->customer->last_name }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Total Amount</div>
                                <div class="detail-item__value fw-600">
                                    R {{ number_format($delivery->order->total_amount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Proof of delivery --}}
            @if ($delivery->proof_of_delivery)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-camera"></i> Proof of Delivery</span>
                    </div>
                    <div class="vd-card__body text-center">
                        <img src="{{ asset('storage/' . $delivery->proof_of_delivery) }}"
                             alt="Proof of delivery" class="img-fluid rounded"
                             style="max-height:300px;">
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnUpdateStatus');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

        fetch('{{ route("vendor.delivery.status", $delivery) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                status: document.getElementById('newStatus').value,
                notes: document.getElementById('statusNotes').value || null,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(() => alert('Network error. Please try again.'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Update Status';
        });
    });
});
</script>
@endpush
