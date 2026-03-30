@extends('layouts.vendor')
@section('title', $supplier->company_name)
@section('page-title', 'Suppliers')

@section('breadcrumb')
    <a href="{{ route('vendor.suppliers.index') }}" class="text-secondary">Supplier Directory</a>
    <span class="sep">›</span>
    <span class="current">{{ $supplier->company_name }}</span>
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

        .doc-tile {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s;
            display: block;
        }

        .doc-tile:hover {
            border-color: var(--g-400);
        }

        .vd-btn--blocked {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Supplier hero ── */
        .supplier-hero {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .supplier-hero__avatar {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: var(--n-100);
            border: 2px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--g-700);
            text-transform: uppercase;
            flex-shrink: 0;
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
            line-height: 1.4;
        }

        /* ── Stat strip ── */
        .stat-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
        }

        .stat-strip__item {
            padding: .75rem 1rem;
            text-align: center;
            border-right: 1px solid var(--card-border);
        }

        .stat-strip__item:last-child {
            border-right: none;
        }

        .stat-strip__val {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-strip__label {
            font-size: .7rem;
            color: var(--tx-muted);
            margin-top: .2rem;
        }

        /* ── Product thumb ── */
        .product-thumb {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: var(--n-100);
            border: 1.5px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')

    <div class="row g-3">

        {{-- ══════════════════════════════════════════════════════════════════
             LEFT COLUMN
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- ── Hero card ──────────────────────────────────────────────── --}}
            <div class="vd-card mb-3">
                <div class="vd-card__body">
                    <div class="supplier-hero mb-3">
                        <div class="supplier-hero__avatar">
                            {{ strtoupper(substr($supplier->company_name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;font-size:1.05rem;">
                                    {{ $supplier->company_name }}
                                </h5>
                                @if ($supplier->is_preferred)
                                    <span
                                        style="background:#fef3c7;color:#92400e;border:1.5px solid #fcd34d;border-radius:999px;font-size:.68rem;font-weight:700;padding:.15rem .55rem;">
                                        <i class="bi bi-star-fill me-1" style="color:#f59e0b;"></i>Preferred
                                    </span>
                                @endif
                                <span class="badge {{ $supplier->status_badge }}">{{ ucfirst($supplier->status) }}</span>
                                @if ($supplier->hasPortalAccount())
                                    <span class="badge bg-primary" style="font-size:.68rem;">
                                        <i class="bi bi-link-45deg me-1"></i>Portal Linked
                                    </span>
                                @endif
                            </div>
                            @if ($supplier->contact_person)
                                <div class="text-muted mt-1" style="font-size:.82rem;">
                                    <i class="bi bi-person me-1"></i>{{ $supplier->contact_person }}
                                </div>
                            @endif
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('vendor.suppliers.edit', $supplier) }}"
                                class="vd-btn vd-btn--ghost vd-btn--sm">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                        </div>
                    </div>

                    {{-- Order stats --}}
                    <div class="stat-strip mb-3">
                        <div class="stat-strip__item">
                            <div class="stat-strip__val" style="color:var(--g-600);">
                                {{ $orderStats['total_pos'] }}
                            </div>
                            <div class="stat-strip__label">Total Orders</div>
                        </div>
                        <div class="stat-strip__item">
                            <div class="stat-strip__val" style="color:#6366f1;">
                                {{ $orderStats['pending_pos'] }}
                            </div>
                            <div class="stat-strip__label">Active Orders</div>
                        </div>
                        <div class="stat-strip__item">
                            <div class="stat-strip__val" style="color:#0ea5e9;">
                                ₱{{ number_format($orderStats['total_spend'], 0) }}
                            </div>
                            <div class="stat-strip__label">Total Spend</div>
                        </div>
                    </div>

                    {{-- Contact info grid --}}
                    <div class="row g-3">
                        @if ($supplier->email)
                            <div class="col-md-4">
                                <div class="locked-field__label">Email</div>
                                <div class="locked-field__value" style="font-size:.82rem;font-weight:500;">
                                    <a href="mailto:{{ $supplier->email }}" style="color:var(--g-600);">
                                        {{ $supplier->email }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if ($supplier->phone)
                            <div class="col-md-4">
                                <div class="locked-field__label">Phone</div>
                                <div class="locked-field__value" style="font-size:.82rem;font-weight:500;">
                                    {{ $supplier->phone }}
                                </div>
                            </div>
                        @endif
                        @if ($supplier->city)
                            <div class="col-md-4">
                                <div class="locked-field__label">Location</div>
                                <div class="locked-field__value" style="font-size:.82rem;font-weight:500;">
                                    {{ collect([$supplier->city, $supplier->province_state])->filter()->implode(', ') }}
                                </div>
                            </div>
                        @endif
                        @if ($supplier->address_line1)
                            <div class="col-12">
                                <div class="locked-field__label">Address</div>
                                <div class="locked-field__value" style="font-size:.82rem;font-weight:500;">
                                    {{ $supplier->address_line1 }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Business Relationship ───────────────────────────────────── --}}
            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-briefcase-fill"></i> Business Terms
                    </span>
                    <a href="{{ route('vendor.suppliers.edit', $supplier) }}"
                        class="vd-btn vd-btn--ghost vd-btn--sm ms-auto" style="font-size:.75rem;">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
                <div class="vd-card__body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="locked-field__label">Payment Terms</div>
                            <div class="locked-field__value">{{ $supplier->payment_terms_label }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="locked-field__label">Credit Limit</div>
                            <div class="locked-field__value">{{ $supplier->formatted_credit_limit }}</div>
                        </div>
                        @if ($supplier->account_number)
                            <div class="col-md-4">
                                <div class="locked-field__label">Account Number</div>
                                <div class="locked-field__value">{{ $supplier->account_number }}</div>
                            </div>
                        @endif
                        @if ($supplier->notes)
                            <div class="col-12">
                                <div class="locked-field__label">Internal Notes</div>
                                <div style="font-size:.85rem;color:var(--tx-secondary);line-height:1.6;">
                                    {{ $supplier->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Portal product catalog ──────────────────────────────────── --}}
            @if ($supplier->hasPortalAccount() && $supplier->portalSupplier?->products->isNotEmpty())
                <div class="vd-card mb-3">
                    <div class="vd-card__header">
                        <span class="vd-card__title">
                            <i class="bi bi-grid"></i> Available Products
                        </span>
                        <span class="ms-auto text-muted" style="font-size:.75rem;">
                            {{ $supplier->portalSupplier->products->count() }} product(s)
                        </span>
                    </div>
                    <div class="vd-card__body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.83rem;">
                                <thead
                                    style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                                    <tr>
                                        <th class="ps-3" style="width:52px;"></th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Unit Price</th>
                                        <th>Stock</th>
                                        <th>MOQ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($supplier->portalSupplier->products->where('status', 'active') as $product)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="product-thumb">
                                                    @if ($product->primaryImage)
                                                        <img src="{{ Storage::url($product->primaryImage->file_path) }}"
                                                            alt="{{ $product->name }}">
                                                    @else
                                                        <i class="bi bi-box-seam text-muted" style="font-size:.85rem;"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="fw-600">{{ $product->name }}</td>
                                            <td class="text-muted">{{ $product->sku ?? '—' }}</td>
                                            <td class="fw-700" style="color:var(--g-600);">
                                                ₱{{ number_format($product->unit_price, 2) }}
                                            </td>
                                            <td>
                                                <span
                                                    class="{{ $product->stock_available > 0 ? 'text-success' : 'text-danger' }} fw-600">
                                                    {{ $product->stock_available }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $product->minimum_order_qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Recent PRs ──────────────────────────────────────────────── --}}
            @if ($recentPRs->isNotEmpty())
                <div class="vd-card mb-3">
                    <div class="vd-card__header">
                        <span class="vd-card__title">
                            <i class="bi bi-file-earmark-text"></i> Recent Purchase Requests
                        </span>
                        <a href="{{ route('vendor.procurement.pr.index') }}" class="ms-auto"
                            style="font-size:.75rem;">View all →</a>
                    </div>
                    <div class="vd-card__body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.83rem;">
                                <thead
                                    style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                                    <tr>
                                        <th class="ps-3">PR #</th>
                                        <th>Items</th>
                                        <th>Est. Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPRs as $pr)
                                        <tr>
                                            <td class="ps-3 fw-700">{{ $pr->pr_number }}</td>
                                            <td>{{ $pr->items->count() }}</td>
                                            <td class="fw-600">₱{{ number_format($pr->subtotal, 2) }}</td>
                                            <td><span
                                                    class="badge {{ $pr->status_badge }}">{{ $pr->status_label }}</span>
                                            </td>
                                            <td class="text-muted">{{ $pr->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('vendor.procurement.pr.show', $pr) }}"
                                                    class="vd-btn vd-btn--ghost vd-btn--sm">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Recent POs ──────────────────────────────────────────────── --}}
            @if ($recentPOs->isNotEmpty())
                <div class="vd-card mb-3">
                    <div class="vd-card__header">
                        <span class="vd-card__title">
                            <i class="bi bi-receipt-cutoff"></i> Recent Purchase Orders
                        </span>
                        <a href="{{ route('vendor.procurement.po.index') }}" class="ms-auto"
                            style="font-size:.75rem;">View all →</a>
                    </div>
                    <div class="vd-card__body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.83rem;">
                                <thead
                                    style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                                    <tr>
                                        <th class="ps-3">PO #</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPOs as $po)
                                        <tr>
                                            <td class="ps-3 fw-700">{{ $po->po_number }}</td>
                                            <td class="fw-700" style="color:var(--g-600);">
                                                ₱{{ number_format($po->total_amount, 2) }}</td>
                                            <td><span
                                                    class="badge {{ $po->status_badge }}">{{ $po->status_label }}</span>
                                            </td>
                                            <td class="text-muted">{{ $po->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('vendor.procurement.po.show', $po) }}"
                                                    class="vd-btn vd-btn--ghost vd-btn--sm">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             RIGHT COLUMN — Quick Actions + Status
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Quick actions --}}
            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-lightning-charge"></i> Quick Actions
                    </span>
                </div>
                <div class="vd-card__body d-grid gap-2">

                    @if ($supplier->hasPortalAccount() && $supplier->isActive())
                        <a href="{{ route('vendor.procurement.pr.create') }}" class="vd-btn vd-btn--primary w-100">
                            <i class="bi bi-file-earmark-plus me-1"></i> New Purchase Request
                        </a>
                    @elseif (!$supplier->hasPortalAccount())
                        <div class="vd-btn vd-btn--primary w-100 vd-btn--blocked"
                            title="Link to a portal supplier to create orders">
                            <i class="bi bi-file-earmark-plus me-1"></i> New Purchase Request
                        </div>
                        <div style="font-size:.72rem;color:var(--tx-muted);text-align:center;margin-top:-.25rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Link a portal account to enable ordering.
                        </div>
                    @endif

                    {{-- Preferred toggle --}}
                    <form method="POST" action="{{ route('vendor.suppliers.togglePreferred', $supplier) }}">
                        @csrf
                        <button type="submit" class="vd-btn vd-btn--ghost w-100">
                            <i class="bi bi-star{{ $supplier->is_preferred ? '-fill' : '' }} me-1"
                                style="{{ $supplier->is_preferred ? 'color:#f59e0b;' : '' }}"></i>
                            {{ $supplier->is_preferred ? 'Remove Preferred' : 'Mark as Preferred' }}
                        </button>
                    </form>

                    <a href="{{ route('vendor.suppliers.edit', $supplier) }}" class="vd-btn vd-btn--ghost w-100">
                        <i class="bi bi-pencil me-1"></i> Edit Details
                    </a>

                    <form method="POST" action="{{ route('vendor.suppliers.destroy', $supplier) }}"
                        onsubmit="return confirm('Remove {{ addslashes($supplier->company_name) }} from your directory?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="vd-btn vd-btn--ghost w-100 text-danger">
                            <i class="bi bi-trash3 me-1"></i> Remove from Directory
                        </button>
                    </form>

                </div>
            </div>

            {{-- Status control --}}
            <div class="vd-card mb-3">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-toggle-on"></i> Status</span>
                </div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.suppliers.updateStatus', $supplier) }}">
                        @csrf
                        <div class="d-grid gap-2">
                            @foreach ([
            'active' => ['success', 'check-circle-fill', 'Set Active'],
            'inactive' => ['secondary', 'pause-circle-fill', 'Set Inactive'],
            'blocked' => ['danger', 'x-circle-fill', 'Block Supplier'],
        ] as $status => [$color, $icon, $label])
                                @php
                                    $isCurrent = $supplier->status === $status;
                                    $cssVar = $color === 'success' ? 'g' : ($color === 'danger' ? 'r' : 'n');
                                    $activeStyle = $isCurrent ? "border-color:var(--{$cssVar}-500);" : '';
                                @endphp
                                <button type="submit" name="status" value="{{ $status }}"
                                    class="vd-btn vd-btn--ghost w-100 {{ $isCurrent ? 'fw-700' : '' }}"
                                    style="{{ $activeStyle }}">
                                    <i class="bi bi-{{ $icon }} me-1 text-{{ $color }}"></i>
                                    {{ $label }}
                                    @if ($isCurrent)
                                        <span class="ms-1" style="font-size:.7rem;">(current)</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            {{-- Portal link info --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-link-45deg"></i> Portal Account
                    </span>
                </div>
                <div class="vd-card__body">
                    @if ($supplier->hasPortalAccount())
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-patch-check-fill"
                                style="color:var(--g-500);font-size:1.1rem;flex-shrink:0;"></i>
                            <div>
                                <div class="fw-700" style="font-size:.82rem;">
                                    {{ $supplier->portalSupplier->company_name }}
                                </div>
                                <div style="font-size:.72rem;color:var(--tx-muted);">Active portal account</div>
                            </div>
                        </div>
                        <div style="font-size:.75rem;color:var(--tx-muted);">
                            This supplier manages their own product catalog and can process your orders directly through the
                            platform.
                        </div>
                    @else
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle"
                                style="color:#f59e0b;font-size:1rem;flex-shrink:0;margin-top:.1rem;"></i>
                            <div style="font-size:.78rem;color:var(--tx-secondary);line-height:1.55;">
                                This supplier doesn't have a portal account. You can still track them as a contact, but
                                Purchase Requests and Orders require a linked portal account.
                            </div>
                        </div>
                        <a href="{{ route('vendor.suppliers.edit', $supplier) }}" class="vd-btn vd-btn--ghost w-100 mt-2"
                            style="font-size:.8rem;">
                            <i class="bi bi-link-45deg me-1"></i> Link Portal Account
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection
