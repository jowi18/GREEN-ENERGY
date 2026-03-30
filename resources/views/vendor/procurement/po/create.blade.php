{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/vendor/procurement/po/create.blade.php
     Generates a Purchase Order from an approved Purchase Request
     ═══════════════════════════════════════════════════════════════════════ --}}
@extends('layouts.vendor')
@section('title', 'Generate PO from ' . $pr->pr_number)
@section('page-title', 'Procurement')

@section('breadcrumb')
    <a href="{{ route('vendor.procurement.pr.index') }}" class="text-secondary">Purchase Requests</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.procurement.pr.show', $pr) }}" class="text-secondary">{{ $pr->pr_number }}</a>
    <span class="sep">›</span>
    <span class="current">Generate PO</span>
@endsection

@push('styles')
    <style>
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

        .req {
            color: #ef4444;
        }

        /* ── Editable qty cell ── */
        .qty-cell .vd-input {
            width: 100px;
            padding: .3rem .6rem;
            font-size: .82rem;
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('vendor.procurement.po.store') }}" id="poForm">
        @csrf
        <input type="hidden" name="purchase_request_id" value="{{ $pr->id }}">

        @if ($errors->any())
            <div class="vd-card mb-3" style="border-color:#fca5a5;background:#fef2f2;">
                <div class="vd-card__body">
                    <div class="fw-700 mb-1" style="font-size:.82rem;color:#dc2626;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size:.78rem;color:#dc2626;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ── Card 1: Source PR info (read-only) ──────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-shield-lock"></i> From Purchase Request
                </span>
                <span class="badge bg-success ms-2">{{ $pr->pr_number }}</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="locked-field__label">Supplier</div>
                        <div class="locked-field__value">{{ $pr->supplier->company_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="locked-field__label">Contact</div>
                        <div class="locked-field__value">{{ $pr->supplier->contact_person }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="locked-field__label">Supplier Email</div>
                        <div class="locked-field__value">{{ $pr->supplier->email }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Order Items (from PR, qty editable) ─────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-cart3"></i> Order Items</span>
                <span class="ms-auto" style="font-size:.78rem;color:var(--tx-muted);">
                    Adjust quantities if needed — prices are locked from the PR.
                </span>
            </div>
            <div class="vd-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead
                            style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>SKU</th>
                                <th>Unit Price</th>
                                <th>Qty (PR)</th>
                                <th style="width:130px;">Qty (PO) <span class="req">*</span></th>
                                <th>Subtotal</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="poItemsBody">
                            @foreach ($pr->items as $index => $item)
                                <input type="hidden" name="items[{{ $index }}][supplier_product_id]"
                                    value="{{ $item->supplier_product_id }}">
                                <input type="hidden" name="items[{{ $index }}][unit_price]"
                                    value="{{ $item->unit_price }}" class="unit-price-{{ $index }}">
                                <tr>
                                    <td class="ps-3 fw-600" style="font-size:.85rem;">{{ $item->product_name }}</td>
                                    <td class="text-muted" style="font-size:.82rem;">{{ $item->sku ?? '—' }}</td>
                                    <td style="font-size:.85rem;">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-muted" style="font-size:.82rem;">{{ $item->quantity_requested }}</td>
                                    <td class="qty-cell">
                                        <input type="number" name="items[{{ $index }}][quantity_ordered]"
                                            class="vd-input qty-input" data-index="{{ $index }}"
                                            data-unit-price="{{ $item->unit_price }}"
                                            value="{{ old("items.{$index}.quantity_ordered", $item->quantity_requested) }}"
                                            min="1" step="1" required
                                            oninput="updateLineTotal({{ $index }})">
                                    </td>
                                    <td class="fw-700 line-total-{{ $index }}" style="font-size:.85rem;">
                                        ₱{{ number_format($item->unit_price * $item->quantity_requested, 2) }}
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][notes]" class="vd-input"
                                            value="{{ old("items.{$index}.notes") }}" placeholder="Optional…"
                                            style="min-width:130px;padding:.3rem .6rem;font-size:.78rem;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Card 3: Order Details ────────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-file-earmark-text"></i> Order Details</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="vd-label">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date"
                            value="{{ old('expected_delivery_date', $pr->requested_delivery_date?->format('Y-m-d')) }}"
                            class="vd-input @error('expected_delivery_date') is-invalid @enderror"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                        @error('expected_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Delivery Address</label>
                        <textarea name="delivery_address" rows="2" class="vd-input" style="resize:vertical;">{{ old('delivery_address') }}</textarea>
                        <div class="form-hint">Leave blank to use your registered address.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Tax Amount (₱)</label>
                        <input type="number" name="tax_amount" value="{{ old('tax_amount', 0) }}" class="vd-input"
                            min="0" step="0.01" oninput="updateGrandTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Shipping Cost (₱)</label>
                        <input type="number" name="shipping_cost" value="{{ old('shipping_cost', 0) }}"
                            class="vd-input" min="0" step="0.01" oninput="updateGrandTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Grand Total (₱)</label>
                        <div class="vd-input" id="grandTotalDisplay"
                            style="background:var(--n-50);font-weight:800;color:var(--g-600);cursor:default;">
                            ₱0.00
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="vd-label">Notes to Supplier</label>
                        <textarea name="vendor_notes" rows="2" class="vd-input"
                            placeholder="Special instructions, packaging requirements…" style="resize:vertical;">{{ old('vendor_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('vendor.procurement.pr.show', $pr) }}" class="vd-btn vd-btn--ghost">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex gap-2">
                <button type="submit" name="submit_action" value="draft" class="vd-btn vd-btn--ghost">
                    <i class="bi bi-floppy me-1"></i> Save as Draft
                </button>
                <button type="submit" name="submit_action" value="submit" class="vd-btn vd-btn--primary"
                    id="sendBtn" style="min-width:180px;">
                    <i class="bi bi-send-check me-1"></i> Save & Send to Supplier
                </button>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        // Line total per row
        function updateLineTotal(index) {
            const qty = parseFloat(document.querySelector(`.qty-input[data-index="${index}"]`).value) || 0;
            const price = parseFloat(document.querySelector(`.qty-input[data-index="${index}"]`).dataset.unitPrice) || 0;
            const lt = qty * price;
            document.querySelector(`.line-total-${index}`).textContent = '₱' + lt.toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let subtotal = 0;
            document.querySelectorAll('.qty-input').forEach(input => {
                const qty = parseFloat(input.value) || 0;
                const price = parseFloat(input.dataset.unitPrice) || 0;
                subtotal += qty * price;
            });
            const tax = parseFloat(document.querySelector('[name="tax_amount"]')?.value) || 0;
            const shipping = parseFloat(document.querySelector('[name="shipping_cost"]')?.value) || 0;
            const total = subtotal + tax + shipping;

            document.getElementById('grandTotalDisplay').textContent =
                '₱' + total.toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                });
        }

        // Init on load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.qty-input').forEach((_, i) => updateLineTotal(i));
        });

        // Disable send button on submit
        document.getElementById('poForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('sendBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending…';
        });
    </script>
@endpush
