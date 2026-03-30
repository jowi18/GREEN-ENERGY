@extends('layouts.vendor')

@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Detail')
@section('breadcrumb')
    <a href="{{ route('vendor.orders.index') }}" class="text-secondary">Orders</a>
    <span class="sep">›</span>
    <span class="current">{{ $order->order_number }}</span>
@endsection

@section('content')

    {{-- ── Header row ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="mono fw-700" style="font-size:1rem;">{{ $order->order_number }}</span>
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
                <span class="vd-badge {{ $statusMap[$order->status] ?? 'vd-badge--neutral' }}" id="orderBadge"
                    style="font-size:0.72rem;">
                    {{ ucfirst($order->status) }}
                </span>
                <span class="vd-badge {{ $order->payment_status === 'paid' ? 'vd-badge--active' : 'vd-badge--pending' }}"
                    style="font-size:0.72rem;">
                    {{ $order->payment_method === 'paypal' ? '💳 PayPal' : '💵 COD' }}
                    — {{ ucfirst($order->payment_status) }}
                </span>
            </div>
            <div class="text-muted mt-1" style="font-size:0.8rem;">
                Placed {{ $order->created_at->format('F d, Y · g:i A') }}
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            {{-- Status update buttons --}}
            @foreach ($allowedTransitions as $transition)
                @php
                    $btnColors = [
                        'paid' => 'vd-btn--primary',
                        'processing' => 'vd-btn--primary',
                        'shipped' => 'vd-btn--secondary',
                        'delivered' => 'vd-btn--primary',
                        'completed' => 'vd-btn--primary',
                        'cancelled' => 'vd-btn--danger',
                    ];
                    $btnIcons = [
                        'paid' => 'bi-check-circle',
                        'processing' => 'bi-gear',
                        'shipped' => 'bi-truck',
                        'delivered' => 'bi-house-check',
                        'completed' => 'bi-bag-check',
                        'cancelled' => 'bi-x-circle',
                    ];
                @endphp
                <button class="vd-btn {{ $btnColors[$transition] ?? 'vd-btn--ghost' }}"
                    onclick="updateStatus('{{ $transition }}')">
                    <i class="bi {{ $btnIcons[$transition] ?? 'bi-arrow-right' }}"></i>
                    Mark {{ ucfirst($transition) }}
                </button>
            @endforeach

            <a href="{{ route('vendor.orders.invoice', $order) }}" target="_blank" class="vd-btn vd-btn--ghost">
                <i class="bi bi-printer"></i> Invoice
            </a>
        </div>
    </div>

    {{-- ── Status pipeline ── --}}
    @php
        $pipeline = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'completed'];
        $currentIdx = array_search($order->status, $pipeline);
    @endphp

    @if ($order->status !== 'cancelled')
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:1.1rem 1.25rem;">
                <div style="display:flex;align-items:flex-start;gap:0;">
                    @foreach ($pipeline as $i => $step)
                        @php
                            $done = $currentIdx !== false && $i < $currentIdx;
                            $current = $currentIdx !== false && $i === $currentIdx;
                        @endphp
                        <div style="flex:1;text-align:center;position:relative;">
                            {{-- Connector line --}}
                            @if ($i < count($pipeline) - 1)
                                <div
                                    style="position:absolute;top:14px;left:50%;right:-50%;
                            height:2px;background:{{ $done ? 'var(--g-400)' : 'var(--n-200)' }};
                            z-index:0;">
                                </div>
                            @endif
                            {{-- Circle --}}
                            <div
                                style="width:28px;height:28px;border-radius:50%;margin:0 auto 0.4rem;
                            position:relative;z-index:1;
                            background:{{ $done ? 'var(--g-400)' : ($current ? 'var(--g-600)' : '#fff') }};
                            border:2px solid {{ $done ? 'var(--g-400)' : ($current ? 'var(--g-600)' : 'var(--n-300)') }};
                            {{ $current ? 'box-shadow:0 0 0 4px rgba(21,122,48,0.15);' : '' }}
                            display:flex;align-items:center;justify-content:center;
                            font-size:0.72rem;color:{{ $done || $current ? '#fff' : 'var(--tx-muted)' }};">
                                @if ($done)
                                    <i class="bi bi-check2"></i>@else{{ $i + 1 }}
                                @endif
                            </div>
                            {{-- Label --}}
                            <div
                                style="font-size:0.68rem;font-weight:{{ $current ? '700' : '500' }};
                            color:{{ $current ? 'var(--g-700)' : ($done ? 'var(--tx-secondary)' : 'var(--tx-muted)') }};">
                                {{ ucfirst($step) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="vd-flash vd-flash--error gap-section">
            <i class="bi bi-x-circle-fill"></i>
            <span>This order was cancelled.</span>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Left: items + customer ── --}}
        <div class="col-12 col-xl-8">

            {{-- Order items --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-bag"></i> Order Items</span>
                    <span class="vd-card__subtitle">{{ $order->items->count() }} item(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="vd-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Warranty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-600" style="font-size:0.875rem;">
                                            {{ $item->product_name }}
                                        </div>
                                        @if ($item->product?->sku)
                                            <div class="mono text-muted" style="font-size:0.72rem;">
                                                {{ $item->product->sku }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="mono fw-600">{{ $item->quantity }}</td>
                                    <td class="mono">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="mono fw-600">₱{{ number_format($item->total_price, 2) }}</td>
                                    <td class="text-secondary" style="font-size:0.82rem;">
                                        {{ $item->warranty_months ? $item->warranty_months . ' months' : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="vd-card__footer">
                    <div class="d-flex flex-column align-items-end gap-1" style="font-size:0.875rem;width:100%;">
                        <div class="d-flex justify-content-between w-100" style="max-width:280px;">
                            <span class="text-muted">Subtotal</span>
                            <span class="mono">₱{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if ($order->discount_amount > 0)
                            <div class="d-flex justify-content-between w-100" style="max-width:280px;">
                                <span class="text-muted">Discount</span>
                                <span class="mono text-red">— ₱{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between w-100 fw-700" style="max-width:280px;font-size:0.95rem;">
                            <span>Total</span>
                            <span class="mono text-green">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery section --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-truck"></i> Delivery</span>
                    @if (!$order->delivery && !in_array($order->status, ['completed', 'cancelled']))
                        <button class="vd-btn vd-btn--primary vd-btn--sm" data-bs-toggle="modal"
                            data-bs-target="#deliveryModal">
                            <i class="bi bi-plus-lg"></i> Assign Delivery
                        </button>
                    @endif
                </div>

                @if ($order->delivery)
                    <div class="vd-card__body">
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item__label">Courier</div>
                                <div class="detail-item__value">{{ $order->delivery->courier_name }}</div>
                            </div>
                            <div>
                                <div class="detail-item__label">Tracking #</div>
                                <div class="detail-item__value mono">
                                    {{ $order->delivery->tracking_number ?? '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Est. Delivery</div>
                                <div class="detail-item__value" style="font-size:0.82rem;">
                                    {{ $order->delivery->estimated_date
                                        ? \Carbon\Carbon::parse($order->delivery->estimated_date)->format('M d, Y')
                                        : '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Status</div>
                                <div class="detail-item__value">
                                    <span
                                        class="vd-badge vd-badge--{{ $order->delivery->status === 'delivered' ? 'active' : 'info' }}">
                                        {{ ucfirst($order->delivery->status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if ($order->delivery->notes)
                            <p class="text-muted mt-2" style="font-size:0.82rem;">
                                <i class="bi bi-info-circle me-1"></i>{{ $order->delivery->notes }}
                            </p>
                        @endif

                        @if ($order->status === 'shipped')
                            <div class="mt-3">
                                <button class="vd-btn vd-btn--primary vd-btn--sm" onclick="markDelivered()">
                                    <i class="bi bi-house-check"></i> Mark as Delivered
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="empty-state" style="padding:2rem 0;">
                        <i class="bi bi-truck"></i>
                        <p>No delivery assigned yet.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- ── Right: customer + shipping + notes ── --}}
        <div class="col-12 col-xl-4">

            {{-- Customer info --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person"></i> Customer</span>
                </div>
                <div class="vd-card__body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="avatar avatar--customer">
                            {{ strtoupper(substr($order->customer->first_name, 0, 1) . substr($order->customer->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-600" style="font-size:0.875rem;">
                                {{ $order->customer->full_name }}
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                {{ $order->customer->user->email }}
                            </div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row__label"><i class="bi bi-phone me-1"></i>Phone</span>
                        <span class="detail-row__value">{{ $order->customer->phone ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row__label"><i class="bi bi-bag me-1"></i>Total Orders</span>
                        <span
                            class="detail-row__value">{{ \App\Models\Order::forCustomer($order->customer_id)->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Shipping address --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-geo-alt"></i> Ship To</span>
                </div>
                <div class="vd-card__body">
                    <address style="font-style:normal;font-size:0.875rem;line-height:1.8;color:var(--tx-secondary);">
                        {{ $order->shipping_address_line1 }}
                        @if ($order->shipping_address_line2)
                            <br>{{ $order->shipping_address_line2 }}
                        @endif
                        <br>{{ $order->shipping_city }}, {{ $order->shipping_province }}
                        <br>{{ $order->shipping_postal_code }}, {{ $order->shipping_country }}
                    </address>
                </div>
            </div>

            {{-- Order notes --}}
            @if ($order->notes)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-chat-left-text"></i> Customer Note</span>
                    </div>
                    <div class="vd-card__body">
                        <p style="font-size:0.875rem;color:var(--tx-secondary);line-height:1.7;">
                            {{ $order->notes }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Vendor notes --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-pencil-square"></i> Vendor Notes</span>
                </div>
                <div class="vd-card__body">
                    <p class="text-muted" style="font-size:0.82rem;margin-bottom:0.75rem;">
                        Internal notes — not visible to the customer.
                    </p>
                    <textarea id="vendorNotesInput" rows="3" class="vd-input" style="min-height:70px;"
                        placeholder="Add internal notes…">{{ $order->vendor_notes }}</textarea>
                    <button class="vd-btn vd-btn--ghost vd-btn--sm mt-2" onclick="saveNotes()">
                        <i class="bi bi-floppy"></i> Save Notes
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ════ Assign Delivery Modal ════ --}}
    <div class="modal fade" id="deliveryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deliveryForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">
                            <i class="bi bi-truck me-2 text-green"></i>Assign Delivery
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="vd-label">Courier / Logistics <span class="req">*</span></label>
                            <select name="courier_name" class="vd-input" id="courierSelect"
                                onchange="toggleCustomCourier(this.value)" required>
                                <option value="">Select courier…</option>
                                <option value="J&T Express">J&T Express</option>
                                <option value="LBC Express">LBC Express</option>
                                <option value="JRS Express">JRS Express</option>
                                <option value="Grab Express">Grab Express</option>
                                <option value="Lalamove">Lalamove</option>
                                <option value="Ninja Van">Ninja Van</option>
                                <option value="Flash Express">Flash Express</option>
                                <option value="In-house Delivery">In-house Delivery</option>
                                <option value="custom">Other (type below)…</option>
                            </select>
                            <input type="text" id="customCourier" name="courier_name_custom" class="vd-input mt-2"
                                placeholder="Enter courier name" style="display:none;">
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Tracking number</label>
                            <input type="text" name="tracking_number" class="vd-input"
                                placeholder="e.g. JT12345678PH">
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Estimated delivery date</label>
                            <input type="date" name="estimated_date" class="vd-input"
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="vd-label">Notes</label>
                            <textarea name="notes" rows="2" class="vd-input" placeholder="Any delivery instructions…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="vd-btn vd-btn--primary" id="assignDeliveryBtn">
                            <i class="bi bi-truck"></i> Assign & Ship
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ Status Update Modal ════ --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-700" id="statusModalTitle">Update Status</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1rem;">
                        Add an optional note for this status change.
                    </p>
                    <label class="vd-label">Notes (optional)</label>
                    <textarea id="statusNotes" rows="3" class="vd-input"
                        placeholder="e.g. Payment confirmed, Packed and ready to ship…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--primary" id="confirmStatusBtn">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let pendingStatus = null;

        /* ── Status update ────────────────────────────────────── */
        function updateStatus(newStatus) {
            pendingStatus = newStatus;

            const labels = {
                paid: 'Mark as Paid',
                processing: 'Start Processing',
                shipped: 'Mark as Shipped',
                delivered: 'Mark as Delivered',
                completed: 'Complete Order',
                cancelled: 'Cancel Order',
            };

            document.getElementById('statusModalTitle').textContent = labels[newStatus] ?? 'Update Status';
            document.getElementById('statusNotes').value = '';
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        document.getElementById('confirmStatusBtn').addEventListener('click', function() {
            if (!pendingStatus) return;

            const btn = this;
            const notes = document.getElementById('statusNotes').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating…';

            $.ajax({
                url: '{{ route('vendor.orders.status', $order) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    status: pendingStatus,
                    notes
                }),
                success(res) {
                    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();

                    if (res.success) {
                        // Update badge in header
                        const badge = document.getElementById('orderBadge');
                        const statusClasses = {
                            pending: 'vd-badge--pending',
                            paid: 'vd-badge--info',
                            processing: 'vd-badge--warning',
                            shipped: 'vd-badge--shipped',
                            delivered: 'vd-badge--info',
                            completed: 'vd-badge--active',
                            cancelled: 'vd-badge--danger',
                        };
                        badge.className = 'vd-badge ' + (statusClasses[res.new_status] ??
                            'vd-badge--neutral');
                        badge.textContent = res.new_status.charAt(0).toUpperCase() + res.new_status.slice(
                        1);

                        Swal.fire({
                            icon: 'success',
                            text: res.message,
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    }
                },
                error(xhr) {
                    const msg = xhr.responseJSON?.message ?? 'An error occurred.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                    btn.disabled = false;
                    btn.innerHTML = 'Confirm';
                }
            });
        });

        /* ── Delivery assignment ──────────────────────────────── */
        function toggleCustomCourier(val) {
            const custom = document.getElementById('customCourier');
            custom.style.display = val === 'custom' ? 'block' : 'none';
            custom.required = val === 'custom';
            document.querySelector('[name="courier_name"]').required = val !== 'custom';
        }

        document.getElementById('deliveryForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Use custom courier name if "Other" was selected
            const courierSelect = document.getElementById('courierSelect').value;
            if (courierSelect === 'custom') {
                const custom = document.getElementById('customCourier').value.trim();
                if (!custom) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Please enter the courier name.',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                formData.set('courier_name', custom);
            } else {
                formData.set('courier_name', courierSelect);
            }

            const btn = document.getElementById('assignDeliveryBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning…';

            $.ajax({
                url: '{{ route('vendor.orders.delivery', $order) }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deliveryModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            text: res.message,
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-truck"></i> Assign & Ship';
                    }
                },
                error() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-truck"></i> Assign & Ship';
                }
            });
        });

        /* ── Mark delivered ───────────────────────────────────── */
        function markDelivered() {
            Swal.fire({
                title: 'Mark as Delivered?',
                text: 'Confirm the order has been received by the customer.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, delivered',
                confirmButtonColor: '#157a30',
            }).then(r => {
                if (!r.isConfirmed) return;

                $.post('{{ route('vendor.orders.delivered', $order) }}', {}, function(res) {
                    if (res.success) {
                        Swal.fire({
                                icon: 'success',
                                text: res.message,
                                toast: true,
                                position: 'top-end',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                });
            });
        }

        /* ── Save vendor notes ────────────────────────────────── */
        function saveNotes() {
            const notes = document.getElementById('vendorNotesInput').value;

            $.ajax({
                url: '{{ route('vendor.orders.status', $order) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    status: '{{ $order->status }}',
                    notes
                }),
                success(res) {
                    Swal.fire({
                        icon: 'success',
                        text: 'Notes saved.',
                        toast: true,
                        position: 'top-end',
                        timer: 1800,
                        showConfirmButton: false,
                    });
                }
            });
        }
    </script>
@endpush
