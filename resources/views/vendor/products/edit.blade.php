@extends('layouts.vendor')

@section('title', 'Edit: ' . $product->name)
@section('page-title', 'Edit Product')
@section('breadcrumb')
    <a href="{{ route('vendor.products.index') }}" class="text-secondary">Products</a>
    <span class="sep">›</span><span class="current">{{ Str::limit($product->name, 30) }}</span>
@endsection

@section('content')

    <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data"
        id="productForm">
        @csrf @method('POST')
        <div class="row g-3">

            {{-- ── Left ── --}}
            <div class="col-12 col-xl-8">

                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-info-circle"></i> Product Information</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="vd-label">Product name <span class="req">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                    class="vd-input @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Category <span class="req">*</span></label>
                                <select name="category_id" class="vd-select" required>
                                    @foreach ($categories as $parent)
                                        <option value="{{ $parent->id }}"
                                            {{ old('category_id', $product->category_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                        @foreach ($parent->children as $child)
                                            <option value="{{ $child->id }}"
                                                {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;— {{ $child->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Product type <span class="req">*</span></label>
                                <select name="product_type" id="productType" onchange="onTypeChange(this.value)"
                                    class="vd-select">
                                    <option value="physical"
                                        {{ old('product_type', $product->product_type) === 'physical' ? 'selected' : '' }}>
                                        Physical</option>
                                    <option value="service"
                                        {{ old('product_type', $product->product_type) === 'service' ? 'selected' : '' }}>
                                        Service</option>
                                    <option value="digital"
                                        {{ old('product_type', $product->product_type) === 'digital' ? 'selected' : '' }}>
                                        Digital</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Short description</label>
                                <textarea name="short_description" rows="2" class="vd-textarea" maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Full description</label>
                                <textarea name="description" rows="5" class="vd-textarea">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-tag"></i> Pricing</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="vd-label">Selling price (₱) <span class="req">*</span></label>
                                <input type="number" name="price" step="0.01" min="0" required
                                    value="{{ old('price', $product->price) }}" class="vd-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vd-label">Compare-at price (₱)</label>
                                <input type="number" name="compare_price" step="0.01" min="0"
                                    value="{{ old('compare_price', $product->compare_price) }}" class="vd-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vd-label">Cost price (₱)</label>
                                <input type="number" name="cost_price" step="0.01" min="0"
                                    value="{{ old('cost_price', $product->cost_price) }}" class="vd-input">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Identifiers --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-upc-scan"></i> Identifiers</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">SKU</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                    class="vd-input">
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Barcode / EAN</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                                    class="vd-input">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Physical details --}}
                <div class="vd-card gap-section" id="physicalSection">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-box"></i> Physical Details</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">Weight (kg)</label>
                                <input type="number" name="weight_kg" step="0.001" min="0"
                                    value="{{ old('weight_kg', $product->weight_kg) }}" class="vd-input">
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Warranty (months)</label>
                                <input type="number" name="warranty_months" min="0"
                                    value="{{ old('warranty_months', $product->warranty_months) }}" class="vd-input">
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Warranty terms</label>
                                <textarea name="warranty_terms" rows="2" class="vd-textarea">{{ old('warranty_terms', $product->warranty_terms) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Existing images + upload more --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-images"></i> Product Images</span>
                    </div>
                    <div class="vd-card__body">
                        {{-- Existing --}}
                        @if ($product->images->count())
                            <div class="row g-2 mb-3">
                                @foreach ($product->images->sortBy('sort_order') as $img)
                                    <div class="col-4 col-md-3" id="img-{{ $img->id }}">
                                        <div style="position:relative;">
                                            <img src="{{ asset('storage/' . $img->file_path) }}"
                                                style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--r-sm);
                                            border:2px solid {{ $img->is_primary ? 'var(--g-400)' : 'var(--card-border)' }};">
                                            @if ($img->is_primary)
                                                <span
                                                    style="position:absolute;top:4px;left:4px;background:var(--g-500);
                                             color:#fff;font-size:0.62rem;font-weight:700;
                                             padding:0.1rem 0.4rem;border-radius:var(--r-full);">Primary</span>
                                            @else
                                                <button type="button"
                                                    onclick="setPrimary({{ $product->id }}, {{ $img->id }})"
                                                    style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,0.5);
                                               color:#fff;border:none;border-radius:var(--r-full);
                                               font-size:0.62rem;padding:0.1rem 0.4rem;cursor:pointer;">
                                                    Set primary
                                                </button>
                                            @endif
                                            <button type="button"
                                                onclick="deleteImage({{ $product->id }}, {{ $img->id }})"
                                                style="position:absolute;top:4px;right:4px;background:rgba(239,68,68,0.85);
                                               color:#fff;border:none;border-radius:50%;width:22px;height:22px;
                                               display:flex;align-items:center;justify-content:center;
                                               cursor:pointer;font-size:0.75rem;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Upload more --}}
                        <div style="border:2px dashed var(--card-border);border-radius:var(--r-md);
                                padding:1.5rem;text-align:center;cursor:pointer;"
                            onclick="document.getElementById('imageInput').click()">
                            <i class="bi bi-plus-circle" style="font-size:1.5rem;color:var(--tx-muted);"></i>
                            <p style="font-size:0.82rem;color:var(--tx-secondary);margin-top:0.4rem;">
                                Add more images
                            </p>
                        </div>
                        <input type="file" name="images[]" id="imageInput" accept="image/*" multiple
                            style="display:none;" onchange="previewImages(this)">
                        <div id="imagePreview" class="row g-2 mt-2"></div>
                    </div>
                </div>

            </div>

            {{-- ── Right ── --}}
            <div class="col-12 col-xl-4">

                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-send"></i> Publish</span>
                    </div>
                    <div class="vd-card__body">
                        <label class="vd-label">Status</label>
                        <select name="status" class="vd-select mb-3">
                            <option value="draft"
                                {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active"
                                {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive"
                                {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>

                        <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.875rem;">
                            <input type="checkbox" name="is_featured" value="1"
                                {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                style="accent-color:var(--g-500);width:15px;height:15px;">
                            <span>Feature on storefront</span>
                        </label>

                        <hr class="divider">

                        <button type="submit" class="vd-btn vd-btn--primary vd-btn--full" id="saveBtn">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                        <a href="{{ route('vendor.products.index') }}"
                            class="vd-btn vd-btn--ghost vd-btn--full mt-2">Cancel</a>
                    </div>
                </div>

                {{-- Inventory settings --}}
                <div class="vd-card gap-section" id="inventorySection">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-archive"></i> Inventory</span>
                    </div>
                    <div class="vd-card__body">
                        @php $inv = $product->inventory; @endphp
                        <div class="detail-grid mb-3">
                            <div>
                                <div class="detail-item__label">On Hand</div>
                                <div
                                    class="detail-item__value mono text-{{ ($inv?->quantity_on_hand ?? 0) === 0 ? 'red' : 'green' }}">
                                    {{ $inv?->quantity_on_hand ?? 0 }}
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Reserved</div>
                                <div class="detail-item__value mono">{{ $inv?->quantity_reserved ?? 0 }}</div>
                            </div>
                        </div>
                        <a href="{{ route('vendor.inventory.index') }}"
                            class="vd-btn vd-btn--ghost vd-btn--full vd-btn--sm mb-3">
                            <i class="bi bi-arrows-move"></i> Adjust Stock
                        </a>
                        <hr class="divider">
                        <div class="mb-3">
                            <label class="vd-label">Reorder point</label>
                            <input type="number" name="reorder_point" min="0"
                                value="{{ old('reorder_point', $inv?->reorder_point ?? 5) }}" class="vd-input">
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Reorder quantity</label>
                            <input type="number" name="reorder_quantity" min="1"
                                value="{{ old('reorder_quantity', $inv?->reorder_quantity ?? 10) }}" class="vd-input">
                        </div>
                        <div>
                            <label class="vd-label">Storage location</label>
                            <input type="text" name="storage_location"
                                value="{{ old('storage_location', $inv?->storage_location) }}" class="vd-input"
                                placeholder="e.g. Shelf A3">
                        </div>
                    </div>
                </div>

                {{-- Danger zone --}}
                {{-- <div class="vd-card" style="border-color:var(--red-100);">
                    <div class="vd-card__header" style="background:var(--red-50);">
                        <span class="vd-card__title" style="color:var(--red-600);">
                            <i class="bi bi-exclamation-triangle"></i> Danger Zone
                        </span>
                    </div>
                    <div class="vd-card__body">
                        <p class="text-muted" style="font-size:0.82rem;margin-bottom:1rem;">
                            Deleting a product removes it from your store and cannot be undone.
                        </p>
                        <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" id="deleteForm">
                            @csrf @method('DELETE')
                            <button type="button" class="vd-btn vd-btn--danger vd-btn--full" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Delete Product
                            </button>
                        </form>
                    </div>
                </div> --}}

            </div>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        function onTypeChange(type) {
            const isService = type === 'service';
            document.getElementById('physicalSection').style.display = isService ? 'none' : '';
            document.getElementById('inventorySection').style.display = isService ? 'none' : '';
        }
        onTypeChange(document.getElementById('productType').value);

        function previewImages(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            [...input.files].forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.insertAdjacentHTML('beforeend', `
                <div class="col-4 col-md-3">
                    <img src="${e.target.result}"
                         style="width:100%;aspect-ratio:1;object-fit:cover;
                                border-radius:var(--r-sm);border:1px solid var(--card-border);">
                </div>`);
                };
                reader.readAsDataURL(file);
            });
        }

        function deleteImage(productId, imageId) {
            Swal.fire({
                title: 'Delete image?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post(`/vendor/products/${productId}/images/delete`, {
                    image_id: imageId
                }, res => {
                    if (res.success) document.getElementById(`img-${imageId}`)?.remove();
                });
            });
        }

        function setPrimary(productId, imageId) {
            $.post(`/vendor/products/${productId}/images/primary`, {
                image_id: imageId
            }, () => location.reload());
        }

        function confirmDelete() {
            Swal.fire({
                title: 'Delete this product?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (r.isConfirmed) document.getElementById('deleteForm').submit();
            });
        }

        document.getElementById('productForm').addEventListener('submit', () => {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
        });
    </script>
@endpush
