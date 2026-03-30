@extends('layouts.admin')

@section('title', 'Vendor Management')
@section('page-title', 'Vendor Management')

@section('content')

    {{-- ── Stat strip ── --}}
    <div class="row g-3 section-gap">
        @php
            $statItems = [
                ['Active', $stats['active'], 'green', 'bi-shop'],
                ['Subscription Required', $stats['subscription_required'], 'yellow', 'bi-credit-card'],
                ['Suspended', $stats['suspended'], 'red', 'bi-slash-circle'],
                ['Rejected', $stats['rejected'], 'red', 'bi-x-circle'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card--{{ $color }}">
                    <div class="stat-card__icon stat-card__icon--{{ $color }}"><i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="stat-card__value">{{ number_format($count) }}</div>
                    <div class="stat-card__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Filter bar ── --}}
    <div class="panel section-gap">
        <div class="panel__body" style="padding:0.85rem 1.2rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="search-bar__wrap">
                    <i class="bi bi-search search-bar__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="search-bar__input"
                        placeholder="Search vendors…">
                </div>

                <select name="status" class="form-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                    </option>
                    <option value="subscription_required"
                        {{ request('status') === 'subscription_required' ? 'selected' : '' }}>Subscription Required</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>
                        Suspended</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected
                    </option>
                </select>

                <button type="submit" class="btn btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn--ghost"><i class="bi bi-x"></i> Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Vendors table ── --}}
    <div class="panel">
        <div class="panel__header">
            <span class="panel__title"><i class="bi bi-shop"></i> All Vendors</span>
            <span class="panel__subtitle">{{ $vendors->total() }} vendor{{ $vendors->total() !== 1 ? 's' : '' }}</span>
        </div>

        @if ($vendors->count())
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Owner</th>
                            <th>Location</th>
                            <th>Subscription</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar--vendor">
                                            {{ strtoupper(substr($vendor->business_name, 0, 2)) }}</div>
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $vendor->business_name }}
                                            </div>
                                            <div class="mono text-muted" style="font-size:0.72rem;">
                                                {{ $vendor->business_registration_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.875rem;">{{ $vendor->owner_full_name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $vendor->user->email }}</div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $vendor->city }}, {{ $vendor->province_state }}
                                </td>
                                <td>
                                    @if ($vendor->activeSubscription)
                                        <div class="text-green fw-600" style="font-size:0.82rem;">
                                            {{ $vendor->activeSubscription->plan->name }}</div>
                                        <div class="text-muted" style="font-size:0.72rem;">Expires
                                            {{ $vendor->activeSubscription->expires_at->format('M d, Y') }}</div>
                                    @else
                                        <span class="text-muted" style="font-size:0.82rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($vendor->total_reviews > 0)
                                        <div class="d-flex align-items-center gap-1" style="font-size:0.85rem;">
                                            <i class="bi bi-star-fill text-yellow"></i>
                                            <span class="fw-600">{{ number_format($vendor->average_rating, 1) }}</span>
                                            <span class="text-muted"
                                                style="font-size:0.75rem;">({{ $vendor->total_reviews }})</span>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">No reviews</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge-status
                            @switch($vendor->status)
                                @case('active')                badge--active    @break
                                @case('subscription_required') badge--sub-req   @break
                                @case('suspended')             badge--suspended @break
                                @case('rejected')              badge--rejected  @break
                                @default                       badge--pending
                            @endswitch">
                                        {{ ucwords(str_replace('_', ' ', $vendor->status)) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $vendor->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.vendors.show', $vendor) }}"
                                            class="btn btn--ghost btn--icon" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if ($vendor->isActive())
                                            <button class="btn btn--danger btn--icon"
                                                onclick="confirmSuspend({{ $vendor->id }}, '{{ addslashes($vendor->business_name) }}')"
                                                title="Suspend">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @elseif($vendor->isSuspended())
                                            <form method="POST" action="{{ route('admin.vendors.reactivate', $vendor) }}"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn--primary btn--icon"
                                                    title="Reactivate">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($vendors->hasPages())
                <div class="panel__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}
                    </span>
                    {{ $vendors->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding:4rem 0;">
                <i class="bi bi-shop"></i>
                <h6 style="font-weight:700;margin-bottom:0.3rem;">No vendors found</h6>
                <p>Try adjusting your search filters.</p>
            </div>
        @endif
    </div>

    {{-- Suspend modal --}}
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="suspendForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title text-red"><i class="bi bi-slash-circle me-2"></i>Suspend Vendor</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1rem;">
                            Suspending <strong id="suspendVendorName"></strong> will prevent access to the vendor portal.
                        </p>
                        <label class="form-label">Reason <span class="text-red">*</span></label>
                        <textarea name="reason" rows="3" class="form-control" required placeholder="Reason for suspension…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--danger"><i class="bi bi-slash-circle"></i> Suspend
                            Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmSuspend(vendorId, vendorName) {
            document.getElementById('suspendVendorName').textContent = vendorName;
            document.getElementById('suspendForm').action = `/admin/vendors/${vendorId}/suspend`;
            new bootstrap.Modal(document.getElementById('suspendModal')).show();
        }
    </script>
@endpush
