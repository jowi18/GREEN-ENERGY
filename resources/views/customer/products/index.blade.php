{{-- resources/views/customer/products/index.blade.php --}}
@extends('layouts.customer')
@section('title', 'Browse Products')
@section('content')
    <div class="cu-page">

        {{-- Filters --}}
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
            <div style="position:relative;flex:1;min-width:180px;">
                <i class="bi bi-search"
                    style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--tx-muted);pointer-events:none;"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="cu-input"
                    style="padding-left:2.3rem;" placeholder="Search products…">
            </div>
            <select name="category" class="cu-select" style="width:auto;">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="cu-select" style="width:auto;">
                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low–High
                </option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High–Low
                </option>
                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular
                </option>
            </select>
            <button type="submit" class="cu-btn cu-btn--primary"><i class="bi bi-funnel"></i> Filter</button>
            @if (request()->hasAny(['search', 'category', 'sort', 'max_price']))
                <a href="{{ route('customer.products.index') }}" class="cu-btn cu-btn--ghost"><i class="bi bi-x"></i></a>
            @endif
        </form>

        <div class="cu-section-head">
            <div class="cu-section-title">{{ $products->total() }} products</div>
        </div>

        @if ($products->count())
            <div class="row g-3">
                @foreach ($products as $product)
                    @php $stock = $product->inventory?->quantity_on_hand ?? 0; @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card">
                            <a href="{{ route('customer.products.show', $product) }}">
                                <div class="product-card__img">
                                    @if ($product->primaryImage)
                                        <img src="{{ asset('storage/' . $product->primaryImage->file_path) }}"
                                            alt="{{ $product->name }}">
                                    @else<i class="bi bi-box-seam"></i>
                                    @endif
                                    @if ($product->isOnSale())
                                        <span class="product-card__badge product-card__badge--sale">Sale</span>
                                    @endif
                                </div>
                            </a>
                            <div class="product-card__body">
                                <a href="{{ route('customer.products.show', $product) }}"
                                    style="text-decoration:none;color:inherit;">
                                    <div class="product-card__name">{{ $product->name }}</div>
                                </a>
                                <div class="product-card__vendor">{{ $product->vendor->business_name }}</div>
                                <div>
                                    <span class="product-card__price">₱{{ number_format($product->price, 2) }}</span>
                                    @if ($product->compare_price)
                                        <span
                                            class="product-card__compare">₱{{ number_format($product->compare_price, 2) }}</span>
                                    @endif
                                </div>
                                @if ($stock > 0 || $product->isService())
                                    <button class="product-card__add" onclick="addToCart({{ $product->id }})">
                                        <i class="bi bi-bag-plus"></i> Add to Cart
                                    </button>
                                @else
                                    <button class="product-card__add" disabled
                                        style="background:var(--cn-200);cursor:not-allowed;">Out of Stock</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-4">{{ $products->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="cu-empty"><i class="bi bi-box-seam"></i>
                <h6>No products found</h6>
                <p>Try adjusting your filters.</p>
            </div>
        @endif

    </div>
@endsection
