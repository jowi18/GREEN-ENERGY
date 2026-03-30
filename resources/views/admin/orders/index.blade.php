@extends('layouts.admin')
@section('title', 'Platform Orders')
@section('page-title', 'Orders')

@section('content')
    <div class="row g-3 section-gap">
        @foreach ([['Total', $stats['total'], 'blue', 'bi-bag-check'], ['Today', $stats['today'], 'green', 'bi-calendar-check'], ['Pending', $stats['pending'], 'yellow', 'bi-hourglass'], ['Completed', $stats['completed'], 'green', 'bi-check-circle'], ['Cancelled', $stats['cancelled'], 'red', 'bi-x-circle'], ['Revenue', '₱' . number_format($stats['revenue'], 0), 'blue', 'bi-currency-exchange']] as [$l, $v, $c, $i])
            <div class="col-6 col-xl-2">
                <div class="stat-card stat-card--{{ $c }}">
                    <div class="stat-card__icon stat-card__icon--{{ $c }}"><i class="bi {{ $i }}"></i>
                    </div>
                    <div class="stat-card__value">{{ $v }}</div>
                    <div class="stat-card__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="panel section-gap" style="padding:.85rem 1.25rem;">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                style="width:180px;" placeholder="Order # or customer…">
            <select name="status" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Statuses</option>
                @foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <select name="vendor" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Vendors</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor') == $v->id ? 'selected' : '' }}>
                        {{ $v->business_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-funnel"></i></button>
            @if (request()->hasAny(['search', 'status', 'vendor']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light"><i class="bi bi-x"></i></a>
            @endif
        </form>
    </div>

    <div class="panel">
        <table class="table table-hover mb-0" style="font-size:.82rem;">
            <thead style="background:#f8faf8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Vendor</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $o) }}"
                                class="font-monospace fw-600 text-success">{{ $o->order_number }}</a></td>
                        <td>
                            <div class="fw-600">{{ $o->customer->full_name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $o->customer->user->email }}</div>
                        </td>
                        <td style="font-size:.78rem;">{{ $o->vendor->business_name }}</td>
                        <td class="font-monospace fw-600">₱{{ number_format($o->total_amount, 2) }}</td>
                        <td>
                            <span
                                class="badge bg-{{ $o->payment_status === 'paid' ? 'success' : ($o->payment_status === 'failed' ? 'danger' : 'warning text-dark') }}"
                                style="font-size:.65rem;">
                                {{ ucfirst($o->payment_status ?? 'pending') }}
                            </span>
                        </td>
                        <td>
                            @php $sc=['pending'=>'warning text-dark','completed'=>'success','cancelled'=>'danger','shipped'=>'info','delivered'=>'primary']; @endphp
                            <span class="badge bg-{{ $sc[$o->status] ?? 'secondary' }}"
                                style="font-size:.65rem;">{{ ucfirst($o->status) }}</span>
                        </td>
                        <td style="font-size:.78rem;white-space:nowrap;">{{ $o->created_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-light"><i
                                    class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-top">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
