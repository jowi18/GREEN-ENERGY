@extends('layouts.vendor')
@section('title', 'My Suppliers')
@section('page-title', 'Suppliers')

@section('breadcrumb')
    <span class="current">Supplier Directory</span>
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

        .doc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: .5rem;
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

        .req-check {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            padding: .3rem 0;
        }

        .req-check i {
            font-size: .8rem;
            flex-shrink: 0;
        }

        .req-check.met i {
            color: var(--g-500);
        }

        .req-check.unmet i {
            color: #f59e0b;
        }

        .req-check.met span {
            color: var(--tx-secondary);
        }

        .req-check.unmet span {
            color: #92400e;
            font-weight: 600;
        }

        .vd-btn--blocked {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Supplier avatar ── */
        .sup-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--n-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            font-weight: 800;
            color: var(--g-700);
            flex-shrink: 0;
            border: 1.5px solid var(--card-border);
            text-transform: uppercase;
        }

        /* ── Stat mini card ── */
        .stat-mini {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .75rem 1rem;
        }

        .stat-mini__val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-mini__label {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .2rem;
        }
    </style>
@endpush

@section('content')

    {{-- ── Stat row ─────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini__val">{{ $stats['total'] }}</div>
                <div class="stat-mini__label">Total Suppliers</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini__val" style="color:var(--g-600);">{{ $stats['active'] }}</div>
                <div class="stat-mini__label">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini__val" style="color:#f59e0b;">{{ $stats['preferred'] }}</div>
                <div class="stat-mini__label">Preferred</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini__val" style="color:#6366f1;">{{ $stats['portal'] }}</div>
                <div class="stat-mini__label">Portal Linked</div>
            </div>
        </div>
    </div>

    {{-- ── Toolbar ───────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">

        {{-- Left: filters + search --}}
        <div class="d-flex gap-2 flex-wrap align-items-center">
            {{-- Status tabs --}}
            @foreach (['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => null]) }}"
                    class="btn btn-sm {{ request('status', '') === $val ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="font-size:.78rem;border-radius:999px;">
                    {{ $label }}
                </a>
            @endforeach

            {{-- Preferred toggle --}}
            <a href="{{ request()->has('preferred')
                ? request()->fullUrlWithQuery(['preferred' => null])
                : request()->fullUrlWithQuery(['preferred' => '1']) }}"
                class="btn btn-sm {{ request()->has('preferred') ? 'btn-warning' : 'btn-outline-secondary' }}"
                style="font-size:.78rem;border-radius:999px;">
                <i class="bi bi-star{{ request()->has('preferred') ? '-fill' : '' }} me-1"></i>Preferred
            </a>
        </div>

        {{-- Right: search + add --}}
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex">
                <div class="input-group input-group-sm" style="width:220px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted" style="font-size:.75rem;"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Search suppliers…" value="{{ request('search') }}" style="font-size:.82rem;">
                    @foreach (request()->except('search', 'page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                </div>
            </form>
            <a href="{{ route('vendor.suppliers.create') }}" class="vd-btn vd-btn--primary">
                <i class="bi bi-plus-lg me-1"></i> Add Supplier
            </a>
        </div>

    </div>

    {{-- ── Flash errors (delete blocked) ─────────────────────────────────── --}}
    @if ($errors->has('delete'))
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.82rem;">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ $errors->first('delete') }}</span>
        </div>
    @endif

    {{-- ── Supplier list ─────────────────────────────────────────────────── --}}
    @if ($suppliers->isEmpty())
        <div class="vd-card">
            <div class="vd-card__body text-center py-5">
                <i class="bi bi-building d-block mb-2" style="font-size:2.5rem;color:var(--tx-muted);"></i>
                <div class="fw-700 mb-1">No suppliers yet</div>
                <div class="text-muted mb-3" style="font-size:.82rem;">
                    Add your first supplier to start managing procurement.
                </div>
                <a href="{{ route('vendor.suppliers.create') }}" class="vd-btn vd-btn--primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Supplier
                </a>
            </div>
        </div>
    @else
        <div class="vd-card">
            <div class="vd-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                        <thead
                            style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;background:var(--n-50);">
                            <tr>
                                <th class="ps-3" style="width:52px;"></th>
                                <th>Supplier</th>
                                <th>Contact</th>
                                <th>Payment Terms</th>
                                <th>Credit Limit</th>
                                <th>Portal</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $sup)
                                <tr>
                                    <td class="ps-3">
                                        <div class="sup-avatar">
                                            {{ strtoupper(substr($sup->company_name, 0, 2)) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="{{ route('vendor.suppliers.show', $sup) }}"
                                                class="fw-700 text-decoration-none" style="color:var(--tx-primary);">
                                                {{ $sup->company_name }}
                                            </a>
                                            @if ($sup->is_preferred)
                                                <i class="bi bi-star-fill" style="font-size:.7rem;color:#f59e0b;"
                                                    title="Preferred"></i>
                                            @endif
                                        </div>
                                        @if ($sup->city)
                                            <div class="text-muted" style="font-size:.72rem;">
                                                <i
                                                    class="bi bi-geo-alt me-1"></i>{{ $sup->city }}{{ $sup->province_state ? ', ' . $sup->province_state : '' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size:.82rem;">{{ $sup->contact_person ?? '—' }}</div>
                                        @if ($sup->email)
                                            <div class="text-muted" style="font-size:.72rem;">{{ $sup->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size:.82rem;">{{ $sup->payment_terms_label }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size:.82rem;">{{ $sup->formatted_credit_limit }}</span>
                                    </td>
                                    <td>
                                        @if ($sup->hasPortalAccount())
                                            <span class="badge bg-primary" style="font-size:.68rem;">
                                                <i class="bi bi-link-45deg me-1"></i>Linked
                                            </span>
                                        @else
                                            <span class="badge bg-secondary" style="font-size:.68rem;">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $sup->status_badge }}">
                                            {{ ucfirst($sup->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end pe-2">
                                            {{-- Preferred toggle --}}
                                            <form method="POST"
                                                action="{{ route('vendor.suppliers.togglePreferred', $sup) }}">
                                                @csrf
                                                <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm"
                                                    title="{{ $sup->is_preferred ? 'Remove preferred' : 'Mark preferred' }}">
                                                    <i class="bi bi-star{{ $sup->is_preferred ? '-fill' : '' }}"
                                                        style="{{ $sup->is_preferred ? 'color:#f59e0b;' : '' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('vendor.suppliers.edit', $sup) }}"
                                                class="vd-btn vd-btn--ghost vd-btn--sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('vendor.suppliers.destroy', $sup) }}"
                                                onsubmit="return confirm('Remove {{ addslashes($sup->company_name) }} from your directory?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm"
                                                    title="Remove">
                                                    <i class="bi bi-trash3 text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">{{ $suppliers->links() }}</div>
    @endif

@endsection
