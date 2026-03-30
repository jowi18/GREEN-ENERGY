@extends('layouts.vendor')

@section('title', 'POS History')
@section('page-title', 'POS History')
@section('breadcrumb')
    <a href="{{ route('vendor.pos.index') }}" class="text-secondary">POS</a>
    <span class="sep">›</span><span class="current">Transaction History</span>
@endsection

@section('content')

    {{-- ── Stats ── --}}
    <div class="row g-3 gap-section">
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-cash-coin"></i></div>
                <div class="vd-stat__value" style="font-size:1.4rem;">₱{{ number_format($stats['sales_today'], 2) }}</div>
                <div class="vd-stat__label">Sales Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-receipt"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['count_today']) }}</div>
                <div class="vd-stat__label">Transactions Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--blue">
                <div class="vd-stat__icon vd-stat__icon--blue"><i class="bi bi-calendar-month"></i></div>
                <div class="vd-stat__value" style="font-size:1.4rem;">₱{{ number_format($stats['sales_month'], 2) }}</div>
                <div class="vd-stat__label">Sales This Month</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--red">
                <div class="vd-stat__icon vd-stat__icon--red"><i class="bi bi-x-circle"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['voided_today']) }}</div>
                <div class="vd-stat__label">Voided Today</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="vd-input"
                    style="width:auto;" title="From">
                <span class="text-muted" style="font-size:0.82rem;">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="vd-input" style="width:auto;"
                    title="To">

                @if ($cashiers->count() > 1)
                    <select name="cashier" class="vd-select" style="width:auto;">
                        <option value="">All Cashiers</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" {{ request('cashier') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                </select>

                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['date_from', 'date_to', 'cashier', 'status']))
                    <a href="{{ route('vendor.pos.history') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif

                <a href="{{ route('vendor.pos.index') }}" class="vd-btn vd-btn--secondary ms-auto">
                    <i class="bi bi-pos2"></i> Open POS
                </a>
            </form>
        </div>
    </div>

    {{-- ── Transactions table ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-receipt"></i> Transactions</span>
            <span class="vd-card__subtitle">{{ $transactions->total() }} records</span>
        </div>

        @if ($transactions->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Cash</th>
                            <th>Change</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $tx)
                            <tr id="tx-row-{{ $tx->id }}">
                                <td class="mono fw-600" style="font-size:0.8rem;">
                                    {{ $tx->transaction_number }}
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;white-space:nowrap;">
                                    {{ $tx->completed_at?->format('M d, Y') }}<br>
                                    <span style="font-size:0.72rem;">{{ $tx->completed_at?->format('h:i A') }}</span>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $tx->customer_name ?? '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $tx->cashier?->name ?? 'POS' }}
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $tx->items->count() }} item{{ $tx->items->count() !== 1 ? 's' : '' }}
                                </td>
                                <td class="mono fw-600">₱{{ number_format($tx->total_amount, 2) }}</td>
                                <td class="mono text-secondary">₱{{ number_format($tx->cash_tendered, 2) }}</td>
                                <td class="mono text-secondary">₱{{ number_format($tx->change_due, 2) }}</td>
                                <td>
                                    <span
                                        class="vd-badge {{ $tx->status === 'completed' ? 'vd-badge--active' : 'vd-badge--danger' }}"
                                        id="tx-badge-{{ $tx->id }}">
                                        {{ ucfirst($tx->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.pos.receipt', $tx) }}" target="_blank"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="View Receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                        @if ($tx->status === 'completed')
                                            <button class="vd-btn vd-btn--danger vd-btn--icon" title="Void"
                                                onclick="confirmVoid({{ $tx->id }}, '{{ $tx->transaction_number }}')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of
                        {{ $transactions->total() }}
                    </span>
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <h6>No transactions yet</h6>
                <p>Completed POS sales will appear here.</p>
                <a href="{{ route('vendor.pos.index') }}" class="vd-btn vd-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-pos2"></i> Open POS
                </a>
            </div>
        @endif
    </div>

    {{-- Void modal --}}
    <div class="modal fade" id="voidModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-700 text-red">
                        <i class="bi bi-x-circle me-2"></i>Void Transaction
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1.1rem;">
                        Voiding <strong id="voidTxNum"></strong> will restore all stock.
                        This cannot be undone.
                    </p>
                    <label class="vd-label">Reason <span class="req">*</span></label>
                    <textarea id="voidReason" rows="3" class="vd-textarea" placeholder="Why is this transaction being voided?"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--danger" id="voidConfirmBtn" onclick="submitVoid()">
                        <i class="bi bi-x-circle"></i> Void Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let voidTxId = null;

        function confirmVoid(id, num) {
            voidTxId = id;
            document.getElementById('voidTxNum').textContent = '#' + num;
            document.getElementById('voidReason').value = '';
            new bootstrap.Modal(document.getElementById('voidModal')).show();
        }

        function submitVoid() {
            const reason = document.getElementById('voidReason').value.trim();
            if (!reason) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please enter a reason.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            const btn = document.getElementById('voidConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Voiding…';

            $.post(`/vendor/pos/${voidTxId}/void`, {
                reason
            }, function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('voidModal')).hide();
                    const badge = document.getElementById(`tx-badge-${voidTxId}`);
                    if (badge) {
                        badge.className = 'vd-badge vd-badge--danger';
                        badge.textContent = 'Voided';
                    }
                    Swal.fire({
                        icon: 'success',
                        text: res.message,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message
                    });
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Void Transaction';
            }).fail(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Void Transaction';
            });
        }
    </script>
@endpush
