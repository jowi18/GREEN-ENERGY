@extends('layouts.supplier')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    @php
        $supplier = auth()->user()->supplier;
        $newOrders = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', 'submitted')
            ->count();
        $activeOrders = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['approved', 'processing', 'shipped'])
            ->count();
        $totalProducts = \App\Models\SupplierProduct::where('supplier_id', $supplier->id)
            ->where('status', 'active')
            ->count();
        $totalRevenue = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $recentOrders = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->with('vendor')
            ->latest()
            ->limit(6)
            ->get();
    @endphp

    {{-- ── Stat cards ───────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div
                        style="width:46px;height:46px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-inbox-fill" style="font-size:1.25rem;color:#d97706;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;line-height:1;">{{ $newOrders }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">New Orders</div>
                    </div>
                    @if ($newOrders > 0)
                        <a href="{{ route('supplier.orders.index', ['status' => 'submitted']) }}"
                            class="btn btn-sm btn-warning ms-auto" style="font-size:.72rem;">
                            Review
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div
                        style="width:46px;height:46px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-truck" style="font-size:1.25rem;color:#2563eb;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;line-height:1;">{{ $activeOrders }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">Active Orders</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div
                        style="width:46px;height:46px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-box-seam-fill" style="font-size:1.25rem;color:#16a34a;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;line-height:1;">{{ $totalProducts }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">Active Products</div>
                    </div>
                    <a href="{{ route('supplier.products.create') }}" class="btn btn-sm btn-outline-success ms-auto"
                        style="font-size:.72rem;">
                        + Add
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div
                        style="width:46px;height:46px;border-radius:12px;background:#f3e8ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-cash-stack" style="font-size:1.25rem;color:#9333ea;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.3rem;font-weight:800;line-height:1;">₱{{ number_format($totalRevenue, 0) }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">Confirmed Revenue</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Recent orders ─────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-700" style="font-size:.875rem;">
                <i class="bi bi-clock-history me-1 text-success"></i> Recent Orders
            </span>
            <a href="{{ route('supplier.orders.index') }}" style="font-size:.78rem;">View all →</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:.85rem;">
                    <thead class="table-light" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;">
                        <tr>
                            <th class="ps-3">PO #</th>
                            <th>Vendor</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="ps-3 fw-700">{{ $order->po_number }}</td>
                                <td>{{ $order->vendor->business_name }}</td>
                                <td class="fw-600">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="badge {{ $order->status_badge }} text-primary">{{ $order->status_label }}</span></td>
                                <td class="text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('supplier.orders.show', $order->id) }}"
                                        class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted" style="font-size:.82rem;">
                                    No orders yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
