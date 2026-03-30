

{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/supplier/orders/show.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.supplier')
@section('title', 'PO · ' . $order->po_number)
@section('page-title', 'Purchase Orders')

@section('breadcrumb')
    <a href="{{ route('supplier.orders.index') }}" class="text-secondary">Orders</a>
    <span class="sep">›</span>
    <span class="current">{{ $order->po_number }}</span>
@endsection

@push('styles')
    <style>
        .locked-field__label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
            margin-bottom: .15rem;
        }

        .locked-field__value {
            font-size: .875rem;
            font-weight: 700;
        }

        .form-hint {
            font-size: .72rem;
            color: #6b7280;
            margin-top: .25rem;
        }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- ── Main ──────────────────────────────────────────────────────── --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                    <i class="bi bi-file-earmark-text me-1 text-success"></i> {{ $order->po_number }}
                    <span class="badge {{ $order->status_badge }} ms-2">{{ $order->status_label }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <div class="locked-field__label">Vendor</div>
                            <div class="locked-field__value">{{ $order->vendor->business_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Expected Delivery</div>
                            <div class="locked-field__value">
                                {{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Submitted</div>
                            <div class="locked-field__value">
                                {{ $order->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                            </div>
                        </div>
                        @if ($order->vendor_notes)
                            <div class="col-12">
                                <div class="locked-field__label">Vendor Notes</div>
                                <div style="font-size:.85rem;">{{ $order->vendor_notes }}</div>
                            </div>
                        @endif
                        @if ($order->delivery_address)
                            <div class="col-12">
                                <div class="locked-field__label">Delivery Address</div>
                                <div style="font-size:.85rem;">{{ $order->delivery_address }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light"
                                style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;">
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:.85rem;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="fw-600">{{ $item->product_name }}</td>
                                        <td class="text-muted">{{ $item->sku ?? '—' }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity_ordered }} {{ $item->unit_of_measure }}</td>
                                        <td class="fw-700">₱{{ number_format($item->line_total, 2) }}</td>
                                        <td class="text-muted">{{ $item->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light" style="font-size:.82rem;">
                                <tr>
                                    <td colspan="4" class="text-end fw-600">Subtotal</td>
                                    <td class="fw-700">₱{{ number_format($order->subtotal, 2) }}</td>
                                    <td></td>
                                </tr>
                                @if ($order->tax_amount > 0)
                                    <tr>
                                        <td colspan="4" class="text-end">Tax</td>
                                        <td>₱{{ number_format($order->tax_amount, 2) }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                                @if ($order->shipping_cost > 0)
                                    <tr>
                                        <td colspan="4" class="text-end">Shipping</td>
                                        <td>₱{{ number_format($order->shipping_cost, 2) }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end fw-800">Total</td>
                                    <td class="fw-800 text-success">₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Right: Actions ────────────────────────────────────────────── --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                    <i class="bi bi-lightning-charge me-1 text-success"></i> Actions
                </div>
                <div class="card-body d-grid gap-2">

                    @if ($order->status === 'submitted')
                        {{-- Approve --}}
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check2 me-1"></i> Approve Order
                        </button>
                        {{-- Reject --}}
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Order
                        </button>
                    @endif

                    @if ($order->status === 'approved')
                        <form method="POST" action="{{ route('supplier.orders.markProcessing', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-gear me-1"></i> Mark as Processing
                            </button>
                        </form>
                    @endif

                    @if ($order->status === 'processing')
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#shipModal">
                            <i class="bi bi-truck me-1"></i> Mark as Shipped
                        </button>
                    @endif

                    @if ($order->status === 'shipped')
                        <form method="POST" action="{{ route('supplier.orders.markDelivered', $order->id) }}"
                            id="markDeliveredForm">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm w-100" onclick="confirmMarkDelivered()">
                                <i class="bi bi-box-arrow-in-down me-1"></i> Mark as Delivered
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('supplier.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Orders
                    </a>

                </div>
            </div>

        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('supplier.orders.approve', $order->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Approve PO #{{ $order->po_number }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;">
                            Notes to Vendor (optional)
                        </label>
                        <textarea name="supplier_notes" class="form-control" rows="3"
                            placeholder="Preparation notes, estimated dates…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-check2 me-1"></i> Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('supplier.orders.reject', $order->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700 text-danger">Reject PO #{{ $order->po_number }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="3"
                            placeholder="Explain why this PO is being rejected…" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Reject Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Ship Modal --}}
    <div class="modal fade" id="shipModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('supplier.orders.markShipped', $order->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Mark as Shipped</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:.82rem;font-weight:600;">Courier</label>
                                <input type="text" name="courier" class="form-control form-control-sm"
                                    placeholder="e.g. LBC, J&T, Grab">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:.82rem;font-weight:600;">Tracking #</label>
                                <input type="text" name="tracking_number" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-size:.82rem;font-weight:600;">Expected Delivery
                                    Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control form-control-sm"
                                    min="{{ now()->addDay()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-truck me-1"></i> Confirm Shipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmMarkDelivered() {
            Swal.fire({
                title: 'Mark as Delivered?',
                html: 'Confirm that <strong>{{ $order->po_number }}</strong> has been delivered to the vendor. The vendor will then verify and confirm receipt.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-box-arrow-in-down me-1"></i> Yes, Mark Delivered',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('markDeliveredForm').submit();
                }
            });
        }
    </script>
@endpush
