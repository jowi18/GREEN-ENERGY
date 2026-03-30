{{-- ================================================================
     resources/views/vendor/delivery/index.blade.php
================================================================ --}}
@extends('layouts.vendor')
@section('title', 'Deliveries')
@section('page-title', 'Deliveries')

@section('content')

    {{-- Stats strip --}}
    <div class="row g-2 gap-section">
        @php
            $statItems = [
                ['Pending',          $stats['pending'],          'yellow', 'bi-hourglass-split'],
                ['Preparing',        $stats['preparing'],        'orange', 'bi-box-seam'],
                ['Out for Delivery', $stats['out_for_delivery'], 'blue',   'bi-truck'],
                ['Delivered',        $stats['delivered'],        'green',  'bi-check-circle'],
                ['Failed',           $stats['failed'],           'red',    'bi-exclamation-triangle'],
                ['Returned',         $stats['returned'],         'red',    'bi-arrow-return-left'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-4 col-md-2">
                <div class="vd-stat vd-stat--{{ $color }}" style="padding:0.85rem;">
                    <div>
                        <div class="vd-stat__value" style="font-size:1.5rem;">{{ number_format($count) }}</div>
                        <div class="vd-stat__label" style="font-size:0.72rem;">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">

                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Tracking # / order # / customer...">
                </div>

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'preparing', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', ucfirst($s)) }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="vd-input"
                    style="width:auto;" title="From date">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="vd-input"
                    style="width:auto;" title="To date">

                <button type="submit" class="vd-btn vd-btn--primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('vendor.delivery.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Deliveries table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-truck"></i> Deliveries</span>
            <span class="vd-card__subtitle">{{ $deliveries->total() }} total</span>
        </div>

        @if ($deliveries->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Scheduled</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.delivery.show', $delivery) }}"
                                       class="mono fw-600 text-green" style="font-size:0.82rem;">
                                        {{ $delivery->tracking_number ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    @if ($delivery->order)
                                        <a href="{{ route('vendor.orders.show', $delivery->order) }}"
                                           class="mono fw-600" style="font-size:0.82rem;">
                                            {{ $delivery->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($delivery->order?->customer)
                                        {{ $delivery->order->customer->first_name }}
                                        {{ $delivery->order->customer->last_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $delivery->status_badge_class }}">
                                        {{ $delivery->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if ($delivery->assignedEmployee)
                                        {{ $delivery->assignedEmployee->user->name ?? 'Employee #' . $delivery->assigned_to }}
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('M d, Y') : '-' }}
                                </td>
                                <td>{{ $delivery->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.delivery.show', $delivery) }}"
                                           class="vd-btn vd-btn--ghost vd-btn--sm" title="View details">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if (!in_array($delivery->status, ['delivered', 'returned']))
                                            <button type="button"
                                                    class="vd-btn vd-btn--ghost vd-btn--sm btn-update-status"
                                                    data-delivery-id="{{ $delivery->id }}"
                                                    data-current-status="{{ $delivery->status }}"
                                                    data-tracking="{{ $delivery->tracking_number }}"
                                                    title="Update status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="vd-card__body">
                {{ $deliveries->links() }}
            </div>
        @else
            <div class="vd-card__body text-center" style="padding:3rem;">
                <i class="bi bi-truck" style="font-size:2.5rem;color:var(--tx-muted);"></i>
                <p class="mt-2 mb-0" style="color:var(--tx-muted);">No deliveries found.</p>
                <p style="font-size:0.85rem;color:var(--tx-muted);">
                    Deliveries are created when you assign a courier from an order's detail page.
                </p>
            </div>
        @endif
    </div>

    {{-- Update Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Delivery Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Tracking: <strong id="modalTracking" class="mono"></strong>
                    </p>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status</label>
                        <select id="newStatus" class="vd-select">
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Failed</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notes (optional)</label>
                        <textarea id="statusNotes" class="vd-input" rows="3" maxlength="500"
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--primary" id="confirmStatusUpdate">
                        <i class="bi bi-check-lg"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    let currentDeliveryId = null;

    // Open modal on button click
    document.querySelectorAll('.btn-update-status').forEach(btn => {
        btn.addEventListener('click', function () {
            currentDeliveryId = this.dataset.deliveryId;
            document.getElementById('modalTracking').textContent = this.dataset.tracking;
            document.getElementById('newStatus').value = this.dataset.currentStatus;
            document.getElementById('statusNotes').value = '';
            modal.show();
        });
    });

    // Confirm status update
    document.getElementById('confirmStatusUpdate').addEventListener('click', function () {
        if (!currentDeliveryId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

        fetch(`/vendor/delivery/${currentDeliveryId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                status: document.getElementById('newStatus').value,
                notes: document.getElementById('statusNotes').value || null,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                // Reload the page to reflect changes
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(() => alert('Network error. Please try again.'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Update Status';
        });
    });
});
</script>
@endpush
