@extends('layouts.vendor')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')

    {{-- ── Stats ── --}}
    <div class="row g-3 gap-section">
        @php
            $statItems = [
                ['Total', $stats['total'], 'green', 'bi-box-seam'],
                ['Active', $stats['active'], 'green', 'bi-check-circle'],
                ['Draft', $stats['draft'], 'yellow', 'bi-pencil-square'],
                ['Out of Stock', $stats['out_of_stock'], 'red', 'bi-exclamation-circle'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $color }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                    <div class="vd-stat__value">{{ number_format($count) }}</div>
                    <div class="vd-stat__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Toolbar ── --}}
    <div class="vd-card gap-section">
        <div class="panel__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Search name, SKU or barcode…">
                </div>

                <select name="category" class="vd-select" style="width:auto;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                    </option>
                </select>

                <select name="type" class="vd-select" style="width:auto;">
                    <option value="">All Types</option>
                    <option value="physical" {{ request('type') === 'physical' ? 'selected' : '' }}>Physical</option>
                    <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Service</option>
                </select>

                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['search', 'category', 'status', 'type']))
                    <a href="{{ route('vendor.products.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif

                <a href="{{ route('vendor.products.create') }}" class="vd-btn vd-btn--primary ms-auto">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </form>
        </div>
    </div>

    {{-- ── Products table ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-box-seam"></i> All Products</span>
            <span class="vd-card__subtitle">{{ $products->total() }}
                product{{ $products->total() !== 1 ? 's' : '' }}</span>
        </div>

        @if ($products->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Thumbnail --}}
                                        @if ($product->primaryImage)
                                            <img src="{{ asset('storage/' . $product->primaryImage->file_path) }}"
                                                alt="{{ $product->name }}"
                                                style="width:38px;height:38px;object-fit:cover;border-radius:var(--r-sm);
                                        border:1px solid var(--card-border);flex-shrink:0;">
                                        @else
                                            <div
                                                style="width:38px;height:38px;background:var(--n-50);border-radius:var(--r-sm);
                                        border:1px solid var(--card-border);display:flex;align-items:center;
                                        justify-content:center;color:var(--tx-muted);flex-shrink:0;">
                                                <i class="bi bi-image" style="font-size:0.9rem;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $product->name }}</div>
                                            <div class="text-muted mono" style="font-size:0.72rem;">
                                                {{ $product->sku ?? 'No SKU' }}
                                                @if ($product->barcode)
                                                    &middot; {{ $product->barcode }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $product->category->name ?? '—' }}
                                </td>
                                <td>
                                    <div class="mono fw-600" style="font-size:0.875rem;">
                                        ₱{{ number_format($product->price, 2) }}</div>
                                    @if ($product->isOnSale())
                                        <div class="text-muted mono"
                                            style="font-size:0.72rem;text-decoration:line-through;">
                                            ₱{{ number_format($product->compare_price, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($product->isService())
                                        <span class="text-muted" style="font-size:0.82rem;">Service</span>
                                    @else
                                        @php $qty = $product->inventory?->quantity_on_hand ?? 0; @endphp
                                        <span
                                            class="mono fw-600 {{ $qty === 0 ? 'text-red' : ($qty <= ($product->inventory?->reorder_point ?? 5) ? 'text-yellow' : '') }}"
                                            style="font-size:0.875rem;">
                                            {{ $qty }}
                                        </span>
                                        @if ($qty <= ($product->inventory?->reorder_point ?? 5) && $qty > 0)
                                            <span class="vd-badge vd-badge--warning ms-1"
                                                style="font-size:0.65rem;">Low</span>
                                        @elseif($qty === 0)
                                            <span class="vd-badge vd-badge--danger ms-1"
                                                style="font-size:0.65rem;">Out</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="vd-badge vd-badge--{{ $product->isService() ? 'info' : 'neutral' }}">
                                        {{ ucfirst($product->product_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="vd-badge
                            @switch($product->status)
                                @case('active')       vd-badge--active  @break
                                @case('draft')        vd-badge--pending @break
                                @case('inactive')     vd-badge--neutral @break
                                @case('out_of_stock') vd-badge--danger  @break
                            @endswitch">
                                        {{ ucwords(str_replace('_', ' ', $product->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.products.edit', $product) }}"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="vd-btn vd-btn--ghost vd-btn--icon"
                                            title="{{ $product->status === 'active' ? 'Unpublish' : 'Publish' }}"
                                            onclick="toggleStatus({{ $product->id }}, '{{ $product->status }}', this)">
                                            <i class="bi bi-{{ $product->status === 'active' ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button class="vd-btn vd-btn--danger vd-btn--icon" title="Delete"
                                            onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
                    </span>
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                <h6>No products yet</h6>
                <p>Start by adding your first product.</p>
                <a href="{{ route('vendor.products.create') }}" class="vd-btn vd-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </div>
        @endif
    </div>

    {{-- Delete confirmation form (hidden) --}}
    <form method="POST" id="deleteForm" style="display:none;">
        @csrf @method('DELETE')
    </form>

@endsection

@push('scripts')
    <script>
        function toggleStatus(id, currentStatus, btn) {
            $.post(`/vendor/products/${id}/toggle-status`, {}, function(res) {
                if (res.success) {
                    const icon = btn.querySelector('i');
                    icon.className = res.status === 'active' ? 'bi bi-eye-slash' : 'bi bi-eye';
                    btn.title = res.status === 'active' ? 'Unpublish' : 'Publish';
                }
            });
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Delete product?',
                text: `"${name}" will be removed from your store.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'Cancel',
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('deleteForm');
                    form.action = `/vendor/products/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endpush
