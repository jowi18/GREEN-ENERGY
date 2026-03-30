@extends('layouts.admin')

@section('title', 'Customer Management')
@section('page-title', 'Customer Management')

@section('content')

    {{-- ── Stat strip ── --}}
    <div class="row g-3 section-gap">
        @php
            $statItems = [
                ['Total Customers', $stats['total'], 'blue', 'bi-people'],
                ['Verified', $stats['verified'], 'green', 'bi-shield-check'],
                ['Unverified', $stats['unverified'], 'yellow', 'bi-shield-exclamation'],
                ['Suspended', $stats['suspended'], 'red', 'bi-slash-circle'],
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
                <div class="search-bar__wrap" style="min-width:280px;">
                    <i class="bi bi-search search-bar__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="search-bar__input"
                        placeholder="Search by name, email or phone…">
                </div>

                <select name="status" class="form-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified
                    </option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>

                <button type="submit" class="btn btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn--ghost"><i class="bi bi-x"></i> Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Customers table ── --}}
    <div class="panel">
        <div class="panel__header">
            <span class="panel__title"><i class="bi bi-people"></i> All Customers</span>
            <span class="panel__subtitle">{{ $customers->total() }} registered</span>
        </div>

        @if ($customers->count())
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>ID Type</th>
                            <th>Verification</th>
                            <th>Orders</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar--customer">
                                            {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $customer->full_name }}
                                            </div>
                                            <div class="text-muted" style="font-size:0.75rem;">{{ $customer->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">{{ $customer->phone }}</td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $customer->city ? $customer->city . ', ' . $customer->province_state : '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $customer->government_id_type ?: '—' }}</td>
                                <td>
                                    <span
                                        class="badge-status
                            {{ $customer->verification_status === 'verified'
                                ? 'badge--verified'
                                : ($customer->verification_status === 'suspended'
                                    ? 'badge--suspended'
                                    : 'badge--pending') }}">
                                        {{ ucfirst($customer->verification_status) }}
                                    </span>
                                </td>
                                <td class="mono fw-600" style="font-size:0.875rem;">{{ $customer->orders_count }}</td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $customer->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.customers.show', $customer) }}"
                                            class="btn btn--ghost btn--icon" title="View profile">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if ($customer->verification_status === 'unverified')
                                            <form method="POST" action="#"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn--primary btn--icon" title="Verify">
                                                    <i class="bi bi-shield-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($customer->verification_status !== 'suspended')
                                            <button class="btn btn--danger btn--icon"
                                                onclick="confirmSuspendCustomer({{ $customer->id }}, '{{ addslashes($customer->full_name) }}')"
                                                title="Suspend">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @else
                                            <form method="POST" action="admin/customers/{{ $customer }}}/restore"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn--primary btn--icon" title="Restore">
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

            @if ($customers->hasPages())
                <div class="panel__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}
                    </span>
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding:4rem 0;">
                <i class="bi bi-people"></i>
                <h6 style="font-weight:700;margin-bottom:0.3rem;">No customers found</h6>
                <p>Try adjusting your filters.</p>
            </div>
        @endif
    </div>

    {{-- Suspend customer modal --}}
    <div class="modal fade" id="suspendCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="suspendCustomerForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title text-red"><i class="bi bi-slash-circle me-2"></i>Suspend Customer</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1rem;">
                            Suspending <strong id="suspendCustomerName"></strong> will prevent them from logging in.
                        </p>
                        <label class="form-label">Reason <span class="text-red">*</span></label>
                        <textarea name="reason" rows="3" class="form-control" required placeholder="Reason for suspension…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--danger"><i class="bi bi-slash-circle"></i>
                            Suspend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmSuspendCustomer(customerId, name) {
            document.getElementById('suspendCustomerName').textContent = name;
            document.getElementById('suspendCustomerForm').action = `/admin/customers/${customerId}/suspend`;
            new bootstrap.Modal(document.getElementById('suspendCustomerModal')).show();
        }
    </script>
@endpush
