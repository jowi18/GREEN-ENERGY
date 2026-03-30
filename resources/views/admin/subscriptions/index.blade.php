@extends('layouts.admin')
@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')

    {{-- Stats --}}
    <div class="row g-3 section-gap">
        @foreach ([['Active', $stats['active'], 'green', 'bi-check-circle'], ['Expiring 7d', $stats['expiring'], 'yellow', 'bi-hourglass'], ['Expired', $stats['expired'], 'red', 'bi-x-circle'], ['Cancelled', $stats['cancelled'], 'gray', 'bi-slash-circle'], ['Total Revenue', '₱' . number_format($stats['revenue'], 0), 'blue', 'bi-currency-exchange']] as [$l, $v, $c, $i])
            <div class="col-6 col-xl">
                <div class="stat-card stat-card--{{ $c }}">
                    <div class="stat-card__icon stat-card__icon--{{ $c }}"><i class="bi {{ $i }}"></i>
                    </div>
                    <div class="stat-card__value">{{ $v }}</div>
                    <div class="stat-card__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="panel section-gap" style="padding:.85rem 1.25rem;">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                style="width:200px;" placeholder="Vendor name…">
            <select name="status" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Statuses</option>
                @foreach (['active', 'cancelled', 'expired', 'past_due'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <select name="plan" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Plans</option>
                @foreach ($plans as $p)
                    <option value="{{ $p->id }}" {{ request('plan') == $p->id ? 'selected' : '' }}>{{ $p->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-funnel"></i> Filter</button>
            @if (request()->hasAny(['search', 'status', 'plan']))
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-light"><i class="bi bi-x"></i></a>
            @endif
            <a href="{{ route('admin.subscriptions.plans') }}" class="btn btn-sm btn-outline-success ms-auto">
                <i class="bi bi-layers me-1"></i> Manage Plans
            </a>
        </form>
    </div>

    <div class="panel">
        <table class="table table-hover mb-0" style="font-size:.82rem;">
            <thead style="background:#f8faf8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
                <tr>
                    <th>Vendor</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>PayPal ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    @php $expired = $sub->expires_at && $sub->expires_at->isPast(); @endphp
                    <tr>
                        <td>
                            <div class="fw-600">{{ $sub->vendor->business_name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $sub->vendor->city }}</div>
                        </td>
                        <td class="fw-600">{{ $sub->plan?->name ?? '—' }}</td>
                        <td class="font-monospace fw-600">₱{{ number_format($sub->amount_paid, 2) }}</td>
                        <td>
                            <span
                                class="badge bg-{{ $sub->status === 'active' && !$expired ? 'success' : ($sub->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                {{ $sub->status === 'active' && $expired ? 'Expired' : ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td style="font-size:.78rem;">
                            @if ($sub->expires_at)
                                <span
                                    style="color:{{ $expired ? '#ef4444' : ($sub->expires_at->diffInDays(now()) <= 7 ? '#f59e0b' : 'inherit') }};">
                                    {{ $sub->expires_at->format('M d, Y') }}
                                    @if (!$expired)
                                        <span class="text-muted">({{ $sub->expires_at->diffForHumans() }})</span>
                                    @endif
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="font-monospace" style="font-size:.68rem;">
                            {{ Str::limit($sub->paypal_subscription_id ?? '—', 20) }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#extendModal" data-id="{{ $sub->id }}">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                                @if ($sub->status !== 'cancelled')
                                    <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Cancel this subscription?')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-top">{{ $subscriptions->links('pagination::bootstrap-5') }}</div>
    </div>

    {{-- Extend modal --}}
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="extendForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Extend Subscription</h6><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-600">Days to add</label>
                        <input type="number" name="days" class="form-control" min="1" max="365"
                            value="30">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm">Extend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('extendModal').addEventListener('show.bs.modal', e => {
                const id = e.relatedTarget.dataset.id;
                document.getElementById('extendForm').action = `/admin/subscriptions/${id}/extend`;
            });
        </script>
    @endpush
@endsection
