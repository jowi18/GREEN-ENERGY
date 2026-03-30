@extends('layouts.customer')
@section('title', $product->name)
@section('content')
    <div class="cu-page">

        <div class="row g-4">
            {{-- Images --}}
            <div class="col-12 col-md-5">
                @if ($product->images->count())
                    <div id="mainImg"
                        style="border-radius:var(--r-lg);overflow:hidden;aspect-ratio:1;background:var(--cn-50);border:1px solid var(--card-border);">
                        <img src="{{ asset('storage/' . $product->images->first()->file_path) }}" id="mainImgEl"
                            style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    @if ($product->images->count() > 1)
                        <div style="display:flex;gap:0.5rem;margin-top:0.65rem;overflow-x:auto;">
                            @foreach ($product->images as $img)
                                <img src="{{ asset('storage/' . $img->file_path) }}"
                                    onclick="document.getElementById('mainImgEl').src='{{ asset('storage/' . $img->file_path) }}'"
                                    style="width:60px;height:60px;object-fit:cover;border-radius:var(--r-sm);
                            border:2px solid {{ $img->is_primary ? 'var(--cg-400)' : 'var(--card-border)' }};
                            cursor:pointer;flex-shrink:0;">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div
                        style="aspect-ratio:1;border-radius:var(--r-lg);border:1px solid var(--card-border);
                        display:flex;align-items:center;justify-content:center;
                        background:var(--cn-50);font-size:4rem;color:var(--tx-muted);">
                        <i class="bi bi-box-seam"></i>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="col-12 col-md-7">
                <div style="font-size:0.75rem;color:var(--tx-muted);margin-bottom:0.35rem;">{{ $product->category?->name }}
                </div>
                <h4 style="font-family:'Nunito',sans-serif;font-weight:800;letter-spacing:-0.02em;margin-bottom:0.5rem;">
                    {{ $product->name }}
                </h4>
                <a href="{{ route('customer.vendors.show', $product->vendor) }}"
                    style="font-size:0.82rem;color:var(--cg-600);font-weight:700;margin-bottom:1rem;display:block;">
                    <i class="bi bi-shop me-1"></i>{{ $product->vendor->business_name }}
                </a>

                {{-- Price --}}
                <div style="margin-bottom:1.25rem;">
                    <span
                        style="font-family:'JetBrains Mono',monospace;font-size:1.8rem;font-weight:700;color:var(--cg-700);">
                        ₱{{ number_format($product->price, 2) }}
                    </span>
                    @if ($product->compare_price)
                        <span
                            style="font-family:'JetBrains Mono',monospace;font-size:1rem;color:var(--tx-muted);text-decoration:line-through;margin-left:0.5rem;">
                            ₱{{ number_format($product->compare_price, 2) }}
                        </span>
                    @endif
                </div>

                {{-- Stock --}}
                @if (!$product->isService())
                    @php $stock = $product->inventory?->quantity_on_hand ?? 0; @endphp
                    <div style="margin-bottom:1rem;">
                        @if ($stock > 0)
                            <span class="cu-badge cu-badge--active"><i class="bi bi-check-circle me-1"></i>In Stock
                                ({{ $stock }} units)</span>
                        @else
                            <span class="cu-badge cu-badge--danger">Out of Stock</span>
                        @endif
                    </div>
                @endif

                {{-- Qty + Add --}}
                @if ($product->isService() || ($product->inventory?->quantity_on_hand ?? 0) > 0)
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                        <div
                            style="display:flex;align-items:center;gap:0;border:1.5px solid var(--card-border);border-radius:var(--r-md);overflow:hidden;">
                            <button onclick="changeQty(-1)"
                                style="width:38px;height:38px;background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--tx-secondary);">−</button>
                            <span id="qtyDisplay"
                                style="min-width:36px;text-align:center;font-family:'JetBrains Mono',monospace;font-weight:700;">1</span>
                            <button onclick="changeQty(1)"
                                style="width:38px;height:38px;background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--tx-secondary);">+</button>
                        </div>
                        <button class="cu-btn cu-btn--primary" style="flex:1;" onclick="addQtyToCart()">
                            <i class="bi bi-bag-plus"></i> Add to Cart
                        </button>
                    </div>
                @endif

                {{-- Specs --}}
                <div
                    style="font-size:0.82rem;border:1px solid var(--card-border);border-radius:var(--r-md);overflow:hidden;">
                    @if ($product->sku)
                        <div
                            style="display:flex;justify-content:space-between;padding:0.55rem 0.85rem;border-bottom:1px solid var(--cn-100);">
                            <span class="text-muted">SKU</span><span class="mono fw-700">{{ $product->sku }}</span>
                        </div>
                    @endif
                    @if ($product->warranty_months)
                        <div
                            style="display:flex;justify-content:space-between;padding:0.55rem 0.85rem;border-bottom:1px solid var(--cn-100);">
                            <span class="text-muted">Warranty</span><span class="fw-700">{{ $product->warranty_months }}
                                months</span>
                        </div>
                    @endif
                    @if ($product->weight_kg)
                        <div style="display:flex;justify-content:space-between;padding:0.55rem 0.85rem;">
                            <span class="text-muted">Weight</span><span>{{ $product->weight_kg }} kg</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if ($product->description)
            <div class="cu-card mt-4">
                <div class="cu-card__body">
                    <div style="font-weight:800;font-family:'Nunito',sans-serif;margin-bottom:0.65rem;">Product Description
                    </div>
                    <div style="font-size:0.9rem;color:var(--tx-secondary);line-height:1.8;white-space:pre-line;">
                        {{ $product->description }}</div>
                </div>
            </div>
        @endif

        {{-- Related --}}
        @if ($related->count())
            <div class="mt-4">
                <div class="cu-section-head">
                    <div class="cu-section-title">Related Products</div>
                </div>
                <div class="row g-3">
                    @foreach ($related as $rel)
                        <div class="col-6 col-md-3">
                            <a href="{{ route('customer.products.show', $rel) }}" class="product-card">
                                <div class="product-card__img">
                                    @if ($rel->primaryImage)
                                        <img src="{{ asset('storage/' . $rel->primaryImage->file_path) }}"
                                            alt="{{ $rel->name }}">
                                    @else<i class="bi bi-box-seam"></i>
                                    @endif
                                </div>
                                <div class="product-card__body">
                                    <div class="product-card__name">{{ $rel->name }}</div>
                                    <div class="product-card__price">₱{{ number_format($rel->price, 2) }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        let qty = 1;
        const max = {{ $product->isService() ? 99 : $product->inventory?->quantity_on_hand ?? 0 }};

        function changeQty(delta) {
            qty = Math.max(1, Math.min(max, qty + delta));
            document.getElementById('qtyDisplay').textContent = qty;
        }

        function addQtyToCart() {
            addToCart({{ $product->id }}, qty);
        }
    </script>
@endpush
