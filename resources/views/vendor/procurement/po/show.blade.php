{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/vendor/procurement/po/show.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.vendor')
@section('title', 'PO · ' . $order->po_number)
@section('page-title', 'Procurement')

@section('breadcrumb')
    <a href="{{ route('vendor.procurement.po.index') }}" class="text-secondary">Purchase Orders</a>
    <span class="sep">›</span>
    <span class="current">{{ $order->po_number }}</span>
@endsection

@push('styles')
    <style>
        .act-log {
            position: relative;
            padding-left: 1.5rem;
        }

        .act-log::before {
            content: '';
            position: absolute;
            left: .45rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--card-border);
        }

        .act-item {
            position: relative;
            margin-bottom: .85rem;
        }

        .act-item:last-child {
            margin-bottom: 0;
        }

        .act-dot {
            position: absolute;
            left: -1.27rem;
            top: .2rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--g-500);
            border: 2px solid var(--card-bg);
        }

        .act-time {
            font-size: .7rem;
            color: var(--tx-muted);
        }

        .act-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        .act-note {
            font-size: .78rem;
            color: var(--tx-secondary);
            margin-top: .1rem;
        }

        .locked-field__label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--tx-muted);
            margin-bottom: .15rem;
        }

        .locked-field__value {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
        }

        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .25rem;
        }

        .status-stepper {
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            padding-bottom: .5rem;
        }

        .step-bubble {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
        }

        .step-bubble__dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2.5px solid var(--card-border);
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            color: var(--tx-muted);
        }

        .step-bubble.done .step-bubble__dot {
            background: var(--g-500);
            border-color: var(--g-500);
            color: #fff;
        }

        .step-bubble.active .step-bubble__dot {
            border-color: var(--g-500);
            color: var(--g-600);
            font-weight: 700;
        }

        .step-bubble__label {
            font-size: .65rem;
            color: var(--tx-muted);
            white-space: nowrap;
        }

        .step-bubble.done .step-bubble__label,
        .step-bubble.active .step-bubble__label {
            color: var(--g-600);
            font-weight: 600;
        }

        .step-line {
            flex: 1;
            min-width: 20px;
            height: 2px;
            background: var(--card-border);
            margin: 0 .25rem;
            margin-bottom: 1.2rem;
            flex-shrink: 0;
        }

        .step-line.done {
            background: var(--g-400);
        }
    </style>
@endpush

@section('content')

    @php
        $statusFlow = ['draft', 'submitted', 'approved', 'processing', 'shipped', 'delivered', 'confirmed'];
        $currentIdx = array_search($order->status, $statusFlow);
        $isCancelled = $order->status === 'cancelled' || $order->status === 'rejected';
    @endphp

    {{-- ── Status Stepper ──────────────────────────────────────────────── --}}
    <div class="vd-card mb-3">
        <div class="vd-card__body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-700" style="font-size:.82rem;">{{ $order->po_number }}</span>
                <span class="badge {{ $order->status_badge }}">{{ $order->status_label }}</span>
            </div>
            @if (!$isCancelled)
                <div class="status-stepper mt-2">
                    @foreach ($statusFlow as $i => $step)
                        @php
                            $done = $i < $currentIdx;
                            $active = $i === $currentIdx;
                            $labels = ['Draft', 'Sent', 'Approved', 'Processing', 'Shipped', 'Delivered', 'Confirmed'];
                            $icons = [
                                'pencil',
                                'send',
                                'check2',
                                'gear',
                                'truck',
                                'box-arrow-in-down',
                                'check2-circle',
                            ];
                        @endphp
                        <div class="step-bubble {{ $done ? 'done' : ($active ? 'active' : '') }}">
                            <div class="step-bubble__dot">
                                <i class="bi bi-{{ $icons[$i] }}"></i>
                            </div>
                            <div class="step-bubble__label">{{ $labels[$i] }}</div>
                        </div>
                        @if (!$loop->last)
                            <div class="step-line {{ $done ? 'done' : '' }}"></div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">
        {{-- ── Left Column ──────────────────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- PO Details --}}
            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-file-earmark-text"></i> Order Details</span>
                </div>
                <div class="vd-card__body">
                    <div class="row g-3 mb-3 pb-3" style="border-bottom:1px solid var(--n-100);">
                        <div class="col-md-4">
                            <div class="locked-field__label">PO Number</div>
                            <div class="locked-field__value">{{ $order->po_number }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Supplier</div>
                            <div class="locked-field__value">{{ $order->supplier->company_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Created By</div>
                            <div class="locked-field__value">{{ $order->creator->name }}</div>
                        </div>
                        @if ($order->purchaseRequest)
                            <div class="col-md-4">
                                <div class="locked-field__label">From PR</div>
                                <div class="locked-field__value">
                                    <a href="{{ route('vendor.procurement.pr.show', $order->purchaseRequest) }}">
                                        {{ $order->purchaseRequest->pr_number }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <div class="locked-field__label">Expected Delivery</div>
                            <div class="locked-field__value">
                                {{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}
                            </div>
                        </div>
                        @if ($order->tracking_number)
                            <div class="col-md-4">
                                <div class="locked-field__label">Tracking / Courier</div>
                                <div class="locked-field__value">
                                    {{ $order->tracking_number }}
                                    @if ($order->courier)
                                        <span class="text-muted fw-400"> via {{ $order->courier }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if ($order->vendor_notes)
                            <div class="col-12">
                                <div class="locked-field__label">Your Notes</div>
                                <div style="font-size:.85rem;color:var(--tx-secondary);">{{ $order->vendor_notes }}</div>
                            </div>
                        @endif
                        @if ($order->supplier_notes)
                            <div class="col-12">
                                <div class="locked-field__label">Supplier Notes</div>
                                <div style="font-size:.85rem;color:var(--tx-secondary);">{{ $order->supplier_notes }}</div>
                            </div>
                        @endif
                        @if ($order->rejection_reason)
                            <div class="col-12">
                                <div class="locked-field__label">Rejection Reason</div>
                                <div style="font-size:.85rem;color:#dc2626;">{{ $order->rejection_reason }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Items table --}}
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead
                                style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                                <tr>
                                    <th class="ps-2">Product</th>
                                    <th>SKU</th>
                                    <th>Unit Price</th>
                                    <th>Qty Ordered</th>
                                    @if ($order->isConfirmed())
                                        <th>Qty Received</th>
                                    @endif
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="ps-2 fw-600" style="font-size:.85rem;">{{ $item->product_name }}</td>
                                        <td class="text-muted" style="font-size:.82rem;">{{ $item->sku ?? '—' }}</td>
                                        <td style="font-size:.85rem;">₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td style="font-size:.85rem;">{{ $item->quantity_ordered }}
                                            {{ $item->unit_of_measure }}</td>
                                        @if ($order->isConfirmed())
                                            <td style="font-size:.85rem;color:var(--g-600);font-weight:600;">
                                                {{ $item->quantity_received }}
                                            </td>
                                        @endif
                                        <td class="fw-700" style="font-size:.85rem;">
                                            ₱{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="font-size:.82rem;background:var(--n-50);">
                                <tr>
                                    <td colspan="{{ $order->isConfirmed() ? 5 : 4 }}" class="text-end fw-600 pe-2">Subtotal
                                    </td>
                                    <td class="fw-700">₱{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                @if ($order->tax_amount > 0)
                                    <tr>
                                        <td colspan="{{ $order->isConfirmed() ? 5 : 4 }}" class="text-end pe-2">Tax</td>
                                        <td>₱{{ number_format($order->tax_amount, 2) }}</td>
                                    </tr>
                                @endif
                                @if ($order->shipping_cost > 0)
                                    <tr>
                                        <td colspan="{{ $order->isConfirmed() ? 5 : 4 }}" class="text-end pe-2">Shipping
                                        </td>
                                        <td>₱{{ number_format($order->shipping_cost, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="{{ $order->isConfirmed() ? 5 : 4 }}" class="text-end fw-800 pe-2">Total
                                    </td>
                                    <td class="fw-800" style="color:var(--g-600);">
                                        ₱{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Confirm Delivery Form (only when supplier marked delivered) ── --}}
            @if ($order->status === 'delivered')
                <div class="vd-card mb-3" style="border-color:var(--g-300);">
                    <div class="vd-card__header" style="background:var(--n-50);">
                        <span class="vd-card__title" style="color:var(--g-700);">
                            <i class="bi bi-box-arrow-in-down"></i> Confirm Delivery & Update Inventory
                        </span>
                    </div>
                    <div class="vd-card__body">
                        <div class="mb-3" style="font-size:.82rem;color:var(--tx-secondary);">
                            Verify the items you actually received. Confirming will automatically add
                            stock to your inventory.
                        </div>
                        <form method="POST" action="{{ route('vendor.procurement.po.confirmDelivery', $order) }}"
                            id="confirmForm">
                            @csrf
                            <div class="table-responsive mb-3">
                                <table class="table table-sm">
                                    <thead
                                        style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                                        <tr>
                                            <th class="ps-2">Product</th>
                                            <th>Qty Ordered</th>
                                            <th style="width:160px;">Qty Received <span class="req">*</span></th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <input type="hidden"
                                                    name="items[{{ $loop->index }}][purchase_order_item_id]"
                                                    value="{{ $item->id }}">
                                                <td class="ps-2 fw-600" style="font-size:.85rem;">
                                                    {{ $item->product_name }}</td>
                                                <td style="font-size:.85rem;">{{ $item->quantity_ordered }}
                                                    {{ $item->unit_of_measure }}</td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $loop->index }}][quantity_received]"
                                                        class="vd-input" value="{{ $item->quantity_ordered }}"
                                                        min="0" max="{{ $item->quantity_ordered }}"
                                                        step="1" style="width:120px;padding:.35rem .6rem;">
                                                </td>
                                                <td style="font-size:.78rem;color:var(--tx-muted);">
                                                    Adjust if short-shipped
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="vd-btn vd-btn--primary" id="confirmBtn">
                                    <i class="bi bi-check2-circle me-1"></i> Confirm Receipt & Update Inventory
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Right Column: Actions + Timeline ────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Actions --}}
            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-lightning-charge"></i> Actions</span>
                </div>
                <div class="vd-card__body d-grid gap-2">

                    @if ($order->isDraft())
                        <form method="POST" action="{{ route('vendor.procurement.po.submit', $order) }}">
                            @csrf
                            <button type="submit" class="vd-btn vd-btn--primary w-100">
                                <i class="bi bi-send me-1"></i> Send to Supplier
                            </button>
                        </form>
                    @endif

                    @if (in_array($order->status, ['draft', 'submitted']))
                        <form method="POST" action="{{ route('vendor.procurement.po.cancel', $order) }}">
                            @csrf
                            <button type="submit" class="vd-btn vd-btn--ghost w-100 text-danger"
                                onclick="return confirm('Cancel this PO?')">
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('vendor.procurement.po.index') }}" class="vd-btn vd-btn--ghost w-100">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-clock-history"></i> Timeline</span>
                </div>
                <div class="vd-card__body">
                    <div class="act-log">
                        @if ($order->confirmed_at)
                            <div class="act-item">
                                <div class="act-dot" style="background:var(--g-500);"></div>
                                <div class="act-time">{{ $order->confirmed_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Delivery Confirmed</div>
                                <div class="act-note">Inventory updated automatically.</div>
                            </div>
                        @endif
                        @if ($order->delivered_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $order->delivered_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Marked as Delivered</div>
                                <div class="act-note">Awaiting your confirmation.</div>
                            </div>
                        @endif
                        @if ($order->shipped_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $order->shipped_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Shipped</div>
                                @if ($order->tracking_number)
                                    <div class="act-note">Tracking: {{ $order->tracking_number }}</div>
                                @endif
                            </div>
                        @endif
                        @if ($order->supplier_responded_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $order->supplier_responded_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">
                                    Supplier {{ $order->status === 'rejected' ? 'Rejected' : 'Approved' }}
                                </div>
                            </div>
                        @endif
                        @if ($order->submitted_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $order->submitted_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Sent to Supplier</div>
                            </div>
                        @endif
                        <div class="act-item">
                            <div class="act-dot"></div>
                            <div class="act-time">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                            <div class="act-label">PO Created</div>
                            <div class="act-note">By {{ $order->creator->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('confirmBtn')?.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Confirming…';
            document.getElementById('confirmForm').submit();
        });
    </script>
@endpush
