{{-- ═══════════════════════════════════════════════════════════════════════
     resources/views/supplier/orders/index.blade.php
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.supplier') {{-- create a layouts/supplier.blade.php mirroring admin layout --}}
@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')

    {{-- Filter tabs --}}
    @php
        $tabs = [
            '' => 'All',
            'submitted' => 'New',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'confirmed' => 'Confirmed',
        ];
        $current = request('status', '');
    @endphp

    <div class="d-flex gap-1 mb-3 flex-wrap">
        @foreach ($tabs as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                class="btn btn-sm {{ $current === $val ? 'btn-success' : 'btn-outline-secondary' }}"
                style="font-size:.78rem;border-radius:999px;">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="ordersTable">
                    <thead class="table-light" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">
                        <tr>
                            <th class="ps-3">PO #</th>
                            <th>Vendor</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Expected Delivery</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody style="font-size:.85rem;">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="ps-3 fw-700">{{ $order->po_number }}</td>
                                <td>{{ $order->vendor->business_name }}</td>
                                <td>{{ $order->items->count() }} item(s)</td>
                                <td class="fw-600">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="badge {{ $order->status_badge }} text-primary">{{ $order->status_label }}</span></td>
                                <td class="text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('supplier.orders.show', $order->id) }}"
                                        class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted" style="font-size:.85rem;">
                                    No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>

@endsection



