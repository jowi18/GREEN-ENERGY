{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/vendor/procurement/pr/index.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}
@extends('layouts.vendor')
@section('title', 'Purchase Requests')
@section('page-title', 'Procurement')

@section('breadcrumb')
    <span class="current">Purchase Requests</span>
@endsection

@push('styles')
    <style>
        .req-check.met i {
            color: var(--g-500);
        }

        .req-check.unmet i {
            color: #f59e0b;
        }
    </style>
@endpush

@section('content')

    {{-- ── Toolbar ───────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
        {{-- Status tabs --}}
        <div class="d-flex gap-1 flex-wrap">
            @foreach (['' => 'All', 'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'converted' => 'Converted'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                    class="btn btn-sm {{ request('status', '') === $val ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="font-size:.78rem;border-radius:999px;">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('vendor.procurement.pr.create') }}" class="vd-btn vd-btn--primary">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </a>
    </div>

    {{-- ── Table ─────────────────────────────────────────────────────────── --}}
    <div class="vd-card">
        <div class="vd-card__body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                    <thead style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;background:var(--n-50);">
                        <tr>
                            <th class="ps-3">PR #</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Est. Total</th>
                            <th>Delivery By</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prs as $pr)
                            <tr>
                                <td class="ps-3 fw-700">{{ $pr->pr_number }}</td>
                                <td>{{ $pr->supplier->company_name }}</td>
                                <td>{{ $pr->items->count() }} item(s)</td>
                                <td class="fw-600">
                                    ₱{{ number_format($pr->subtotal, 2) }}
                                </td>
                                <td class="text-muted">
                                    {{ $pr->requested_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $pr->status_badge }}">
                                        {{ $pr->status_label }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $pr->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('vendor.procurement.pr.show', $pr) }}"
                                        class="vd-btn vd-btn--ghost vd-btn--sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5" style="color:var(--tx-muted);font-size:.85rem;">
                                    <i class="bi bi-file-earmark-text d-block mb-2" style="font-size:2rem;"></i>
                                    No purchase requests yet.
                                    <a href="{{ route('vendor.procurement.pr.create') }}" class="d-block mt-1"
                                        style="font-size:.82rem;">Create your first →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $prs->links() }}</div>

@endsection


