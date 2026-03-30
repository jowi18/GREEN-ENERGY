{{-- ================================================================
     resources/views/vendor/warranty/index.blade.php
================================================================ --}}
@extends('layouts.vendor')
@section('title', 'Warranty Claims')
@section('page-title', 'Warranty Claims')

@section('content')

    {{-- Stats --}}
    <div class="row g-2 gap-section">
        @foreach ([['New', $stats['submitted'], 'yellow', 'bi-inbox'], ['Reviewing', $stats['under_review'], 'blue', 'bi-eye'], ['Active', $stats['in_progress'], 'orange', 'bi-gear'], ['Completed', $stats['completed'], 'green', 'bi-patch-check'], ['Rejected', $stats['rejected'], 'danger', 'bi-x-circle']] as [$l, $v, $c, $i])
            <div class="col-6 col-md">
                <div class="vd-stat vd-stat--{{ $c }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $c }}"><i class="bi {{ $i }}"></i></div>
                    <div class="vd-stat__value">{{ $v }}</div>
                    <div class="vd-stat__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:.8rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Claim # or customer name…">
                </div>
                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\WarrantyRequest::STATUS_LABELS as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>
                            {{ $l }}</option>
                    @endforeach
                </select>
                <select name="type" class="vd-select" style="width:auto;">
                    <option value="">All Types</option>
                    <option value="warranty_repair" {{ request('type') === 'warranty_repair' ? 'selected' : '' }}>🔧
                        Repair</option>
                    <option value="warranty_replacement" {{ request('type') === 'warranty_replacement' ? 'selected' : '' }}>
                        🔁 Replacement</option>
                    <option value="warranty_inspection" {{ request('type') === 'warranty_inspection' ? 'selected' : '' }}>🔍
                        Inspection</option>
                </select>
                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>
                @if (request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('vendor.warranty.index') }}" class="vd-btn vd-btn--ghost"><i class="bi bi-x"></i>
                        Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Claims table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-shield-check"></i> Warranty Claims</span>
            <span class="vd-card__subtitle">{{ $claims->total() }} total</span>
        </div>
        @if ($claims->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Claim #</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Warranty</th>
                            <th>Status</th>
                            <th>Filed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($claims as $claim)
                            <tr>
                                <td><a href="{{ route('vendor.warranty.show', $claim) }}"
                                        class="mono fw-600 text-green">{{ $claim->request_number }}</a></td>
                                <td>
                                    <div class="fw-600" style="font-size:.875rem;">{{ $claim->customer->full_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $claim->customer->phone ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:.82rem;">
                                        {{ \Illuminate\Support\Str::limit($claim->orderItem->product_name, 28) }}</div>
                                    @if ($claim->serial_number)
                                        <div class="mono text-muted" style="font-size:.72rem;">S/N:
                                            {{ $claim->serial_number }}</div>
                                    @endif
                                </td>
                                <td style="font-size:.82rem;">{{ $claim->request_type_label }}</td>
                                <td>
                                    <span
                                        style="font-size:.68rem;font-weight:700;padding:.1rem .45rem;border-radius:var(--r-full);
                                     background:{{ $claim->is_within_warranty ? 'var(--g-50)' : '#fef2f2' }};
                                     color:{{ $claim->is_within_warranty ? 'var(--g-700)' : '#dc2626' }};
                                     border:1px solid {{ $claim->is_within_warranty ? 'var(--g-200)' : '#fecaca' }};">
                                        {{ $claim->is_within_warranty ? '✅ Valid' : '❌ Expired' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="vd-badge {{ $claim->status_badge_class }}"
                                        style="font-size:.65rem;">{{ $claim->status_label }}</span>
                                </td>
                                <td class="text-muted" style="font-size:.78rem;">{{ $claim->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <a href="{{ route('vendor.warranty.show', $claim) }}"
                                        class="vd-btn vd-btn--ghost vd-btn--icon" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">{{ $claims->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="empty-state"><i class="bi bi-shield-check"></i>
                <h6>No warranty claims</h6>
                <p>Warranty claims from customers will appear here.</p>
            </div>
        @endif
    </div>
@endsection
