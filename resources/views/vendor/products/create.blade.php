@extends('layouts.vendor')

@section('title', 'Add Product')
@section('page-title', 'Add Product')
@section('breadcrumb')
    <a href="{{ route('vendor.products.index') }}" class="text-secondary">Products</a>
    <span class="sep">›</span><span class="current">New Product</span>
@endsection

@section('content')

    <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="row g-3">

            {{-- ── Left: main details ── --}}
            <div class="col-12 col-xl-8">

                {{-- Basic info --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-info-circle"></i> Product Information</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="vd-label">Product name <span class="req">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="vd-input @error('name') is-invalid @enderror"
                                    placeholder="e.g. 400W Monocrystalline Solar Panel" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="vd-label">Category <span class="req">*</span></label>
                                <select name="category_id" class="vd-select @error('category_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select category…</option>
                                    @foreach ($categories as $parent)
                                        <option value="{{ $parent->id }}"
                                            {{ old('category_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                        @foreach ($parent->children as $child)
                                            <option value="{{ $child->id }}"
                                                {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;— {{ $child->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="vd-label">Product type <span class="req">*</span></label>
                                <select name="product_type" id="productType"
                                    class="vd-select @error('product_type') is-invalid @enderror"
                                    onchange="onTypeChange(this.value)" required>
                                    <option value="physical"
                                        {{ old('product_type', 'physical') === 'physical' ? 'selected' : '' }}>Physical
                                        (panels, batteries…)</option>
                                    <option value="service" {{ old('product_type') === 'service' ? 'selected' : '' }}>
                                        Service (installation, maintenance…)</option>
                                    <option value="digital" {{ old('product_type') === 'digital' ? 'selected' : '' }}>
                                        Digital (manuals, warranties…)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="vd-label">Short description</label>
                                <textarea name="short_description" rows="2" class="vd-textarea @error('short_description') is-invalid @enderror"
                                    placeholder="One-line summary shown in product cards (max 500 chars)" maxlength="500">{{ old('short_description') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="vd-label">Full description</label>
                                <textarea name="description" rows="5" class="vd-textarea @error('description') is-invalid @enderror"
                                    placeholder="Detailed product description, specs, features…">{{ old('description') }}</textarea>
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
                                <input type="number" name="price" value="{{ old('price') }}"
                                    class="vd-input @error('price') is-invalid @enderror" step="0.01" min="0"
                                    placeholder="0.00" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="vd-label">Compare-at price (₱)</label>
                                <input type="number" name="compare_price" value="{{ old('compare_price') }}"
                                    class="vd-input" step="0.01" min="0" placeholder="0.00">
                                <div class="form-hint">Shown as strike-through original price.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="vd-label">Cost price (₱)</label>
                                <input type="number" name="cost_price" value="{{ old('cost_price') }}" class="vd-input"
                                    step="0.01" min="0" placeholder="0.00">
                                <div class="form-hint">Internal only — for margin tracking.</div>
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
                                <input type="text" name="sku" value="{{ old('sku') }}"
                                    class="vd-input @error('sku') is-invalid @enderror" placeholder="SP-MONO-400W">
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Barcode / EAN</label>
                                <input readonly type="text" name="barcode" value="{{ old('barcode') }}"
                                    class="vd-input" placeholder="6901234567890">
                                <div class="form-hint">Used for POS barcode scanning.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Physical details (hidden for services) --}}
                <div class="vd-card gap-section" id="physicalSection">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-box"></i> Physical Details</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">Weight (kg)</label>
                                <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" class="vd-input"
                                    step="0.001" min="0" placeholder="0.000">
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Warranty (months)</label>
                                <input type="number" name="warranty_months" value="{{ old('warranty_months') }}"
                                    class="vd-input" min="0" placeholder="24">
                            </div>
                            <div class="col-12">
                                <label class="vd-label">Warranty terms</label>
                                <textarea name="warranty_terms" rows="2" class="vd-textarea"
                                    placeholder="What the warranty covers, exclusions, claim process…">{{ old('warranty_terms') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-images"></i> Product Images</span>
                        <span class="vd-card__subtitle">Up to 6 images. First image is the primary.</span>
                    </div>
                    <div class="vd-card__body">
                        <div style="border:2px dashed var(--card-border);border-radius:var(--r-md);
                                padding:2rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                            onclick="document.getElementById('imageInput').click()" id="dropZone">
                            <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--tx-muted);"></i>
                            <p style="margin-top:0.5rem;font-size:0.875rem;color:var(--tx-secondary);">
                                Click to upload or drag & drop
                            </p>
                            <p style="font-size:0.75rem;color:var(--tx-muted);">JPG, PNG, WebP — max 3MB each</p>
                        </div>
                        <input type="file" name="images[]" id="imageInput"
                            accept="image/jpg,image/jpeg,image/png,image/webp" multiple style="display:none;"
                            onchange="previewImages(this)">
                        <div id="imagePreview" class="row g-2 mt-2"></div>
                    </div>
                </div>

            </div>

            {{-- ── Right: inventory + publish ── --}}
            <div class="col-12 col-xl-4">

                {{-- Publish --}}
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-send"></i> Publish</span>
                    </div>
                    <div class="vd-card__body">
                        <label class="vd-label">Status</label>
                        <select name="status" class="vd-select mb-3">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft
                                (hidden from customers)</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (visible in
                                store)</option>
                        </select>

                        <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.875rem;">
                            <input type="checkbox" name="is_featured" value="1"
                                {{ old('is_featured') ? 'checked' : '' }}
                                style="accent-color:var(--g-500);width:15px;height:15px;">
                            <span>Feature this product on storefront</span>
                        </label>

                        <hr class="divider">

                        <button type="submit" class="vd-btn vd-btn--primary vd-btn--full" id="saveBtn">
                            <i class="bi bi-check-lg"></i> Save Product
                        </button>
                        <a href="{{ route('vendor.products.index') }}" class="vd-btn vd-btn--ghost vd-btn--full mt-2">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Inventory ── hidden for services --}}
                <div class="vd-card" id="inventorySection">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-archive"></i> Inventory</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="mb-3">
                            <label class="vd-label">Opening stock</label>
                            <input type="number" name="opening_stock" value="{{ old('opening_stock', 0) }}"
                                class="vd-input" min="0" placeholder="0">
                            <div class="form-hint">Starting quantity when product is created.</div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Reorder point</label>
                            <input type="number" name="reorder_point" value="{{ old('reorder_point', 5) }}"
                                class="vd-input" min="0" placeholder="5">
                            <div class="form-hint">Low-stock alert is triggered below this.</div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Reorder quantity</label>
                            <input type="number" name="reorder_quantity" value="{{ old('reorder_quantity', 10) }}"
                                class="vd-input" min="1" placeholder="10">
                            <div class="form-hint">Suggested quantity to reorder.</div>
                        </div>
                        <div>
                            <label class="vd-label">Storage location</label>
                            <input type="text" name="storage_location" value="{{ old('storage_location') }}"
                                class="vd-input" placeholder="e.g. Shelf A3, Warehouse 2">
                        </div>
                    </div>
                </div>

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

        // Init on load
        onTypeChange(document.getElementById('productType').value);

        function previewImages(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            [...input.files].forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.insertAdjacentHTML('beforeend', `
                <div class="col-4 col-md-3">
                    <div style="position:relative;">
                        <img src="${e.target.result}"
                             style="width:100%;aspect-ratio:1;object-fit:cover;
                                    border-radius:var(--r-sm);border:1px solid var(--card-border);">
                        ${i === 0 ? '<span style="position:absolute;top:4px;left:4px;background:var(--g-500);color:#fff;font-size:0.62rem;font-weight:700;padding:0.1rem 0.4rem;border-radius:var(--r-full);">Primary</span>' : ''}
                    </div>
                </div>`);
                };
                reader.readAsDataURL(file);
            });
        }

        // Drag & drop
        const dz = document.getElementById('dropZone');
        dz.addEventListener('dragover', e => {
            e.preventDefault();
            dz.style.borderColor = 'var(--g-400)';
        });
        dz.addEventListener('dragleave', () => {
            dz.style.borderColor = 'var(--card-border)';
        });
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.style.borderColor = 'var(--card-border)';
            const input = document.getElementById('imageInput');
            input.files = e.dataTransfer.files;
            previewImages(input);
        });

        document.getElementById('productForm').addEventListener('submit', () => {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
        });
        $(document).ready(function() {

            function generateBarcode() {
                let barcode = '';
                for (let i = 0; i < 13; i++) {
                    barcode += Math.floor(Math.random() * 10);
                }
                return barcode;
            }

            let barcodeInput = $('input[name="barcode"]');

            if (!barcodeInput.val()) {
                barcodeInput.val(generateBarcode());
            }

        });
    </script>
@endpush
