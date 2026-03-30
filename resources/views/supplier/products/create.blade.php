@extends('layouts.supplier')
@section('title', 'Add Product')
@section('page-title', 'Products')

@section('breadcrumb')
    <a href="{{ route('supplier.products.index') }}" class="text-secondary">My Products</a>
    <span class="sep">›</span>
    <span class="current">Add Product</span>
@endsection

@push('styles')
    <style>
        .form-hint {
            font-size: .72rem;
            color: #6b7280;
            margin-top: .25rem;
        }

        .req {
            color: #ef4444;
        }

        /* ── Image upload zone ── */
        .img-upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .img-upload-zone:hover {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .img-previews {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .75rem;
        }

        .img-preview-item {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            overflow: hidden;
            border: 1.5px solid #e5e7eb;
            position: relative;
            flex-shrink: 0;
        }

        .img-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .img-preview-item__rm {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239, 68, 68, .9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: .6rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('supplier.products.store') }}" enctype="multipart/form-data" id="productForm">
        @csrf

        @if ($errors->any())
            <div class="card border-0 mb-3" style="border:1.5px solid #fca5a5 !important;background:#fef2f2;">
                <div class="card-body py-2 px-3">
                    <div class="fw-700 mb-1" style="font-size:.82rem;color:#dc2626;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size:.78rem;color:#dc2626;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ── Card 1: Basic Info ─────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                <i class="bi bi-info-circle me-1 text-success"></i> Basic Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Product Name <span class="req">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control form-control-sm @error('name') is-invalid @enderror"
                            placeholder="e.g. Monocrystalline Solar Panel 450W" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku') }}"
                            class="form-control form-control-sm @error('sku') is-invalid @enderror"
                            placeholder="e.g. SP-450W-MONO">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Category</label>
                        <select name="category_id"
                            class="form-select form-select-sm @error('category_id') is-invalid @enderror">
                            <option value="">— Select category —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat['id'] }}"
                                    {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>
                                    {{ $cat['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Product Type</label>
                        <select name="product_type" class="form-select form-select-sm">
                            <option value="physical"
                                {{ old('product_type', 'physical') === 'physical' ? 'selected' : '' }}>Physical</option>
                            <option value="service" {{ old('product_type') === 'service' ? 'selected' : '' }}>Service
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">Short Description</label>
                        <input type="text" name="short_description" value="{{ old('short_description') }}"
                            class="form-control form-control-sm" placeholder="One-line summary shown in catalog listings">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">Full Description</label>
                        <textarea name="description" rows="4" class="form-control form-control-sm"
                            placeholder="Specifications, compatibility, use cases…" style="resize:vertical;">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Pricing & Stock ─────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                <i class="bi bi-tag me-1 text-success"></i> Pricing & Stock
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Unit Price (₱) <span class="req">*</span>
                        </label>
                        <input type="number" name="unit_price" value="{{ old('unit_price') }}"
                            class="form-control form-control-sm @error('unit_price') is-invalid @enderror"
                            placeholder="0.00" min="0" step="0.01" inputmode="decimal" required>
                        @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Unit of Measure</label>
                        <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', 'piece') }}"
                            class="form-control form-control-sm" placeholder="piece, set, unit, box…">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Minimum Order Qty</label>
                        <input type="number" name="minimum_order_qty" value="{{ old('minimum_order_qty', 1) }}"
                            class="form-control form-control-sm" min="1" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Stock Available</label>
                        <input type="number" name="stock_available" value="{{ old('stock_available', 0) }}"
                            class="form-control form-control-sm" min="0" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Lead Time (days)</label>
                        <input type="number" name="lead_time_days" value="{{ old('lead_time_days', 7) }}"
                            class="form-control form-control-sm" min="0" step="1">
                        <div class="form-hint">Estimated days from order to shipment.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Currency</label>
                        <select name="currency" class="form-select form-select-sm">
                            <option value="PHP" selected>PHP — Philippine Peso</option>
                            <option value="USD">USD — US Dollar</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 3: Physical Details ────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                <i class="bi bi-box-seam me-1 text-success"></i> Physical Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Weight (kg)</label>
                        <input type="number" name="weight_kg" value="{{ old('weight_kg') }}"
                            class="form-control form-control-sm" placeholder="0.000" min="0" step="0.001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Warranty (months)</label>
                        <input type="number" name="warranty_months" value="{{ old('warranty_months') }}"
                            class="form-control form-control-sm" placeholder="e.g. 12" min="0" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="isFeatured" style="font-size:.82rem;">
                                Feature this product in catalog
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 4: Images & Spec Sheet ─────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                <i class="bi bi-images me-1 text-success"></i> Images & Documents
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Product Images
                            <span class="fw-400 text-muted">(up to 8 · JPG, PNG, WebP · max 5 MB each)</span>
                        </label>
                        <div class="img-upload-zone" onclick="document.getElementById('imagesInput').click()">
                            <i class="bi bi-cloud-upload" style="font-size:1.75rem;color:#9ca3af;"></i>
                            <div class="fw-600 mt-2" style="font-size:.85rem;">Click to upload images</div>
                            <div class="text-muted" style="font-size:.72rem;margin-top:.2rem;">
                                First image will be set as the primary thumbnail
                            </div>
                        </div>
                        <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple
                            style="display:none;" onchange="previewImages(this)">
                        <div class="img-previews" id="imgPreviews"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Spec Sheet / Datasheet
                            <span class="fw-400 text-muted">(PDF, DOC · max 10 MB)</span>
                        </label>
                        <input type="file" name="spec_sheet"
                            class="form-control form-control-sm @error('spec_sheet') is-invalid @enderror"
                            accept=".pdf,.doc,.docx">
                        @error('spec_sheet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('supplier.products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success btn-sm" id="saveBtn" style="min-width:160px;">
                <i class="bi bi-floppy-fill me-1"></i> Save Product
            </button>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        function previewImages(input) {
            const previews = document.getElementById('imgPreviews');
            previews.innerHTML = '';
            Array.from(input.files).slice(0, 8).forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    previews.insertAdjacentHTML('beforeend', `
                    <div class="img-preview-item">
                        <img src="${e.target.result}" alt="">
                        ${i === 0 ? '<span style="position:absolute;bottom:2px;left:2px;background:rgba(22,163,74,.9);color:#fff;font-size:.55rem;font-weight:700;padding:1px 4px;border-radius:4px;">Primary</span>' : ''}
                    </div>
                `);
                };
                reader.readAsDataURL(file);
            });
        }

        document.getElementById('productForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        });
    </script>
@endpush
