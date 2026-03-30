@extends('layouts.supplier')
@section('title', 'My Products')
@section('page-title', 'Products')

@section('breadcrumb')
    <span class="current">My Products</span>
@endsection

@push('styles')
    <style>
        .product-thumb {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            border: 1.5px solid #e5e7eb;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-hint {
            font-size: .72rem;
            color: #6b7280;
            margin-top: .2rem;
        }
    </style>
@endpush

@section('content')

    {{-- ── Toolbar ───────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
        <div class="d-flex gap-2 flex-wrap">
            {{-- Status filter --}}
            @foreach (['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                    class="btn btn-sm {{ request('status', '') === $val ? 'btn-success' : 'btn-outline-secondary' }}"
                    style="font-size:.78rem;border-radius:999px;">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('supplier.products.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>

    {{-- ── Search ────────────────────────────────────────────────────────── --}}
    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm" style="max-width:360px;">
            <span class="input-group-text bg-white">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control" placeholder="Search by name or SKU…"
                value="{{ request('search') }}">
            @if (request('search'))
                <a href="{{ route('supplier.products.index') }}" class="btn btn-outline-secondary">✕</a>
            @endif
        </div>
    </form>

    {{-- ── Table ─────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                    <thead class="table-light" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;">
                        <tr>
                            <th class="ps-3" style="width:52px;"></th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Stock</th>
                            <th>MOQ</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="ps-3">
                                    <div class="product-thumb">
                                        @if ($product->primaryImage)
                                            <img src="{{ Storage::url($product->primaryImage->file_path) }}"
                                                alt="{{ $product->name }}">
                                        @else
                                            <i class="bi bi-box-seam text-muted" style="font-size:.9rem;"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-700">{{ $product->name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $product->sku ? 'SKU: ' . $product->sku : '—' }}
                                    </div>
                                </td>
                                <td class="text-muted">{{ $product->category?->name ?? '—' }}</td>
                                <td class="fw-700">₱{{ number_format($product->unit_price, 2) }}</td>
                                <td>
                                    <span
                                        class="{{ $product->stock_available > 0 ? 'text-success fw-600' : 'text-danger fw-600' }}">
                                        {{ $product->stock_available }}
                                    </span>
                                    <span class="text-muted"
                                        style="font-size:.72rem;">{{ $product->unit_of_measure }}</span>
                                </td>
                                <td class="text-muted">{{ $product->minimum_order_qty }}</td>
                                <td>
                                    <span
                                        class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('supplier.products.edit', $product) }}"
                                            class="btn btn-sm btn-outline-secondary" title="Edit"
                                            style="font-size:.75rem;">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('supplier.products.destroy', $product) }}"
                                            onsubmit="return confirm('Remove this product?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"
                                                style="font-size:.75rem;">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam d-block mb-2" style="font-size:2rem;"></i>
                                    No products yet.
                                    <a href="{{ route('supplier.products.create') }}" class="d-block mt-1"
                                        style="font-size:.82rem;">Add your first product →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($products->hasPages())
        <div class="vd-card__footer">
            <span class="text-muted" style="font-size:0.8rem;">
                Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
            </span>

            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif

@endsection
