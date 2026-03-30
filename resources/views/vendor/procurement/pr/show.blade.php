{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/vendor/procurement/pr/show.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}
@extends('layouts.vendor')
@section('title', 'PR · ' . $pr->pr_number)
@section('page-title', 'Procurement')

@section('breadcrumb')
    <a href="{{ route('vendor.procurement.pr.index') }}" class="text-secondary">Purchase Requests</a>
    <span class="sep">›</span>
    <span class="current">{{ $pr->pr_number }}</span>
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
    </style>
@endpush

@section('content')

    <div class="row g-3">

        {{-- ── Left: Details + Items ────────────────────────────────────── --}}
        <div class="col-lg-8">

            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-file-earmark-text"></i> {{ $pr->pr_number }}
                    </span>
                    <span class="badge {{ $pr->status_badge }} ms-2">{{ $pr->status_label }}</span>
                </div>
                <div class="vd-card__body">
                    <div class="row g-3 mb-3 pb-3" style="border-bottom:1px solid var(--n-100);">
                        <div class="col-md-4">
                            <div class="locked-field__label">Supplier</div>
                            <div class="locked-field__value">{{ $pr->supplier->company_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Requested By</div>
                            <div class="locked-field__value">{{ $pr->requester->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Requested Delivery</div>
                            <div class="locked-field__value">
                                {{ $pr->requested_delivery_date?->format('M d, Y') ?? '—' }}
                            </div>
                        </div>
                        @if ($pr->approver)
                            <div class="col-md-4">
                                <div class="locked-field__label">Approved By</div>
                                <div class="locked-field__value">{{ $pr->approver->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="locked-field__label">Approved At</div>
                                <div class="locked-field__value">
                                    {{ $pr->approved_at?->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        @endif
                        @if ($pr->rejection_reason)
                            <div class="col-12">
                                <div class="locked-field__label">Rejection Reason</div>
                                <div style="font-size:.85rem;color:#dc2626;">{{ $pr->rejection_reason }}</div>
                            </div>
                        @endif
                        @if ($pr->notes)
                            <div class="col-12">
                                <div class="locked-field__label">Notes</div>
                                <div style="font-size:.85rem;color:var(--tx-secondary);">{{ $pr->notes }}</div>
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
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pr->items as $item)
                                    <tr>
                                        <td class="ps-2 fw-600" style="font-size:.85rem;">{{ $item->product_name }}</td>
                                        <td class="text-muted" style="font-size:.82rem;">{{ $item->sku ?? '—' }}</td>
                                        <td style="font-size:.85rem;">₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td style="font-size:.85rem;">{{ $item->quantity_requested }}
                                            {{ $item->unit_of_measure }}</td>
                                        <td class="fw-700" style="font-size:.85rem;">
                                            ₱{{ number_format($item->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--n-50);font-size:.82rem;">
                                <tr>
                                    <td colspan="4" class="text-end fw-800 pe-2">Estimated Total</td>
                                    <td class="fw-800" style="color:var(--g-600);">
                                        ₱{{ number_format($pr->subtotal, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

            {{-- Linked PO --}}
            @if ($pr->purchaseOrder)
                <div class="vd-card mb-3" style="border-color:var(--g-300);">
                    <div class="vd-card__body d-flex align-items-center gap-3">
                        <i class="bi bi-receipt-cutoff text-success" style="font-size:1.4rem;flex-shrink:0;"></i>
                        <div>
                            <div class="fw-700" style="font-size:.85rem;">
                                Purchase Order Generated
                            </div>
                            <div class="text-muted" style="font-size:.78rem;">
                                This PR was converted to
                                <a href="{{ route('vendor.procurement.po.show', $pr->purchaseOrder) }}"
                                    class="fw-700 text-success">
                                    {{ $pr->purchaseOrder->po_number }}
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('vendor.procurement.po.show', $pr->purchaseOrder) }}"
                            class="vd-btn vd-btn--ghost vd-btn--sm ms-auto">
                            View PO →
                        </a>
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Right: Actions + Timeline ────────────────────────────────── --}}
        <div class="col-lg-4">

            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-lightning-charge"></i> Actions</span>
                </div>
                <div class="vd-card__body d-grid gap-2">

                    {{-- Submit draft --}}
                    @if ($pr->isDraft())
                        <form method="POST" action="{{ route('vendor.procurement.pr.submit', $pr) }}">
                            @csrf
                            <button type="submit" class="vd-btn vd-btn--primary w-100">
                                <i class="bi bi-send me-1"></i> Submit for Approval
                            </button>
                        </form>
                    @endif

                    {{-- Approve (internal) --}}
                    @if ($pr->isSubmitted())
                        <form method="POST" action="{{ route('vendor.procurement.pr.approve', $pr) }}">
                            @csrf
                            <button type="submit" class="vd-btn vd-btn--primary w-100">
                                <i class="bi bi-check2 me-1"></i> Approve Request
                            </button>
                        </form>

                        {{-- Reject --}}
                        <button class="vd-btn vd-btn--ghost w-100 text-danger" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Request
                        </button>
                    @endif

                    {{-- Convert to PO --}}
                    @if ($pr->isApproved() && !$pr->isConverted())
                        <a href="{{ route('vendor.procurement.po.createFromPR', $pr) }}"
                            class="vd-btn vd-btn--primary w-100">
                            <i class="bi bi-receipt-cutoff me-1"></i> Generate Purchase Order
                        </a>
                    @endif

                    {{-- Cancel --}}
                    @if (in_array($pr->status, ['draft', 'submitted']))
                        <form method="POST" action="{{ route('vendor.procurement.pr.cancel', $pr) }}">
                            @csrf
                            <button type="submit" class="vd-btn vd-btn--ghost w-100 text-danger"
                                onclick="return confirm('Cancel this purchase request?')">
                                <i class="bi bi-trash3 me-1"></i> Cancel Request
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('vendor.procurement.pr.index') }}" class="vd-btn vd-btn--ghost w-100">
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
                        @if ($pr->approved_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $pr->approved_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Approved</div>
                                <div class="act-note">By {{ $pr->approver?->name }}</div>
                            </div>
                        @endif
                        @if ($pr->isRejected())
                            <div class="act-item">
                                <div class="act-dot" style="background:#ef4444;"></div>
                                <div class="act-time">{{ $pr->updated_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label" style="color:#dc2626;">Rejected</div>
                            </div>
                        @endif
                        @if ($pr->submitted_at)
                            <div class="act-item">
                                <div class="act-dot"></div>
                                <div class="act-time">{{ $pr->submitted_at->format('M d, Y h:i A') }}</div>
                                <div class="act-label">Submitted for Approval</div>
                            </div>
                        @endif
                        <div class="act-item">
                            <div class="act-dot"></div>
                            <div class="act-time">{{ $pr->created_at->format('M d, Y h:i A') }}</div>
                            <div class="act-label">PR Created</div>
                            <div class="act-note">By {{ $pr->requester->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.procurement.pr.reject', $pr) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700 text-danger">Reject PR #{{ $pr->pr_number }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Rejection Reason <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="3"
                            placeholder="Explain why this request is being rejected…" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
