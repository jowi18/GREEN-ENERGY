@extends('layouts.supplier')
@section('title', 'Edit · ' . $product->name)
@section('page-title', 'Products')

@section('breadcrumb')
    <a href="{{ route('supplier.products.index') }}" class="text-secondary">My Products</a>
    <span class="sep">›</span>
    <span class="current">Edit</span>
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

        .img-upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 1.25rem 1rem;
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

        /* Existing image grid */
        .existing-img-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .existing-img-item {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            overflow: hidden;
            border: 1.5px solid #e5e7eb;
            position: relative;
            flex-shrink: 0;
        }

        .existing-img-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .existing-img-item__rm {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239, 68, 68, .85);
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

        .primary-badge {
            position: absolute;
            bottom: 2px;
            left: 2px;
            background: rgba(22, 163, 74, .9);
            color: #fff;
            font-size: .55rem;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('supplier.products.update', $product) }}" enctype="multipart/form-data"
        id="productForm">
        @csrf
        @method('PATCH')

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
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                            class="form-control form-control-sm @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">— Select category —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat['id'] }}"
                                    {{ old('category_id', $product->category_id) == $cat['id'] ? 'selected' : '' }}>
                                    {{ $cat['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Product Type</label>
                        <select name="product_type" class="form-select form-select-sm">
                            <option value="physical"
                                {{ old('product_type', $product->product_type) === 'physical' ? 'selected' : '' }}>Physical
                            </option>
                            <option value="service"
                                {{ old('product_type', $product->product_type) === 'service' ? 'selected' : '' }}>Service
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">Short Description</label>
                        <input type="text" name="short_description"
                            value="{{ old('short_description', $product->short_description) }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">Full Description</label>
                        <textarea name="description" rows="4" class="form-control form-control-sm" style="resize:vertical;">{{ old('description', $product->description) }}</textarea>
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
                        <input type="number" name="unit_price" value="{{ old('unit_price', $product->unit_price) }}"
                            class="form-control form-control-sm @error('unit_price') is-invalid @enderror" min="0"
                            step="0.01" required>
                        @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Unit of Measure</label>
                        <input type="text" name="unit_of_measure"
                            value="{{ old('unit_of_measure', $product->unit_of_measure) }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Minimum Order Qty</label>
                        <input type="number" name="minimum_order_qty"
                            value="{{ old('minimum_order_qty', $product->minimum_order_qty) }}"
                            class="form-control form-control-sm" min="1" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Stock Available</label>
                        <input type="number" name="stock_available"
                            value="{{ old('stock_available', $product->stock_available) }}"
                            class="form-control form-control-sm" min="0" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Lead Time (days)</label>
                        <input type="number" name="lead_time_days"
                            value="{{ old('lead_time_days', $product->lead_time_days) }}"
                            class="form-control form-control-sm" min="0" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:.82rem;">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>
                                Active</option>
                            <option value="inactive"
                                {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="isFeatured" style="font-size:.82rem;">
                                Feature this product in catalog
                            </label>
                        </div>
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
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Weight (kg)</label>
                        <input type="number" name="weight_kg" value="{{ old('weight_kg', $product->weight_kg) }}"
                            class="form-control form-control-sm" min="0" step="0.001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">Warranty (months)</label>
                        <input type="number" name="warranty_months"
                            value="{{ old('warranty_months', $product->warranty_months) }}"
                            class="form-control form-control-sm" min="0" step="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 4: Images & Documents ──────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom fw-700" style="font-size:.85rem;">
                <i class="bi bi-images me-1 text-success"></i> Images & Documents
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Existing images --}}
                    @if ($product->images->isNotEmpty())
                        <div class="col-12">
                            <label class="form-label fw-600" style="font-size:.82rem;">Current Images</label>
                            <div class="existing-img-grid" id="existingImgGrid">
                                @foreach ($product->images as $img)
                                    <div class="existing-img-item" id="imgItem_{{ $img->id }}">
                                        <img src="{{ Storage::url($img->file_path) }}" alt="">
                                        @if ($img->is_primary)
                                            <span class="primary-badge">Primary</span>
                                        @endif
                                        <button type="button" class="existing-img-item__rm"
                                            onclick="deleteImage({{ $img->id }}, this)" title="Remove">✕</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Add new images --}}
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Add More Images
                            <span class="fw-400 text-muted">(JPG, PNG, WebP · max 5 MB each)</span>
                        </label>
                        <div class="img-upload-zone" onclick="document.getElementById('imagesInput').click()">
                            <i class="bi bi-cloud-upload" style="font-size:1.5rem;color:#9ca3af;"></i>
                            <div class="fw-600 mt-2" style="font-size:.82rem;">Click to upload more images</div>
                        </div>
                        <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple
                            style="display:none;" onchange="previewNewImages(this)">
                        <div class="img-previews" id="imgPreviews"></div>
                    </div>

                    {{-- Spec sheet --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.82rem;">
                            Spec Sheet / Datasheet
                            @if ($product->spec_sheet)
                                <a href="{{ Storage::url($product->spec_sheet) }}" target="_blank"
                                    class="ms-2 text-success" style="font-size:.72rem;font-weight:400;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View current
                                </a>
                            @endif
                        </label>
                        <input type="file" name="spec_sheet" class="form-control form-control-sm"
                            accept=".pdf,.doc,.docx">
                        @if ($product->spec_sheet)
                            <div class="form-hint">Upload a new file to replace the current one.</div>
                        @endif
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
                <i class="bi bi-floppy-fill me-1"></i> Save Changes
            </button>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        function previewNewImages(input) {
            const previews = document.getElementById('imgPreviews');
            previews.innerHTML = '';
            Array.from(input.files).slice(0, 8).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    previews.insertAdjacentHTML('beforeend', `
                    <div class="img-preview-item">
                        <img src="${e.target.result}" alt="">
                    </div>
                `);
                };
                reader.readAsDataURL(file);
            });
        }

        function deleteImage(imageId, btn) {
            if (!confirm('Remove this image?')) return;
            btn.disabled = true;

            $.ajax({
                url: `{{ url('supplier/products/images') }}/${imageId}`,
                method: 'DELETE',
                success: () => document.getElementById(`imgItem_${imageId}`)?.remove(),
                error: () => {
                    alert('Failed to remove image.');
                    btn.disabled = false;
                }
            });
        }

        document.getElementById('productForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        });
    </script>
@endpush
