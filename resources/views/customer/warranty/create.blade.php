@extends('layouts.customer')
@section('title', 'File a Warranty Claim')

@push('styles')
    <style>
        .item-selector {
            display: flex;
            flex-direction: column;
            gap: .65rem;
        }

        .item-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem;
            border: 2px solid var(--card-border);
            border-radius: var(--r-md);
            cursor: pointer;
            transition: border-color .15s, background .15s;
            position: relative;
        }

        .item-card:hover {
            border-color: var(--cg-300);
            background: var(--cg-50);
        }

        .item-card.selected {
            border-color: var(--cg-500);
            background: var(--cg-50);
        }

        .item-card__radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .item-card__img {
            width: 52px;
            height: 52px;
            border-radius: var(--r-sm);
            object-fit: cover;
            border: 1.5px solid var(--card-border);
            flex-shrink: 0;
        }

        .item-card__img-ph {
            width: 52px;
            height: 52px;
            border-radius: var(--r-sm);
            background: var(--n-50);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--tx-muted);
        }

        .item-card__info {
            flex: 1;
            min-width: 0;
        }

        .item-card__name {
            font-weight: 700;
            font-size: .875rem;
            line-height: 1.3;
        }

        .item-card__meta {
            font-size: .75rem;
            color: var(--tx-muted);
            margin-top: .15rem;
        }

        .item-card__warranty {
            font-size: .72rem;
            font-weight: 700;
            border-radius: var(--r-full);
            padding: .15rem .55rem;
        }

        .item-card__warranty--active {
            background: var(--cg-50);
            color: var(--cg-700);
            border: 1px solid var(--cg-200);
        }

        .item-card__warranty--expired {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .item-card__check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--cn-300);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .15s;
        }

        .item-card.selected .item-card__check {
            background: var(--cg-500);
            border-color: var(--cg-500);
        }

        .item-card.selected .item-card__check::after {
            content: '';
            width: 10px;
            height: 10px;
            background: #fff;
            border-radius: 50%;
        }

        .upload-zone {
            border: 2px dashed var(--card-border);
            border-radius: var(--r-md);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .upload-zone:hover {
            border-color: var(--cg-300);
            background: var(--cg-50);
        }

        .upload-zone i {
            font-size: 1.75rem;
            color: var(--tx-muted);
            display: block;
            margin-bottom: .4rem;
        }

        .preview-strip {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-top: .5rem;
        }

        .preview-thumb {
            width: 60px;
            height: 60px;
            border-radius: var(--r-sm);
            object-fit: cover;
            border: 1.5px solid var(--card-border);
            position: relative;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:740px;">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('customer.warranty.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">🛡️ File a Warranty Claim</h5>
        </div>

        @if ($eligibleItems->isEmpty())
            <div class="cu-card">
                <div class="cu-card__body text-center" style="padding:3rem 1rem;">
                    <i class="bi bi-shield-x"
                        style="font-size:3rem;color:var(--cn-300);display:block;margin-bottom:1rem;"></i>
                    <h6 class="fw-700 mb-2">No warrantied items found</h6>
                    <p class="text-muted" style="font-size:.875rem;">
                        Warranty claims can only be filed for products from completed orders that include warranty coverage.
                    </p>
                    <a href="{{ route('customer.orders.index') }}" class="cu-btn cu-btn--ghost mt-3 d-inline-flex">
                        View My Orders
                    </a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('customer.warranty.store') }}" enctype="multipart/form-data"
                id="warrantyForm">
                @csrf

                {{-- ── Step 1: Select Item ── --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-box-seam text-green me-1"></i>
                            Step 1 — Select Product
                        </div>

                        <div class="item-selector" id="itemSelector">
                            @foreach ($eligibleItems as $item)
                                @php
                                    $expired = $item->warranty_expires_at && $item->warranty_expires_at->isPast();
                                    $daysLeft = $item->warranty_expires_at
                                        ? max(0, now()->diffInDays($item->warranty_expires_at, false))
                                        : null;
                                    $existingOpen = \App\Models\WarrantyRequest::where('order_item_id', $item->id)
                                        ->whereNotIn('status', ['rejected', 'closed'])
                                        ->first();
                                @endphp
                                <label
                                    class="item-card {{ $selectedItem && $selectedItem->id === $item->id ? 'selected' : '' }}"
                                    for="item-{{ $item->id }}" onclick="selectItem(this, {{ $item->id }})">
                                    <input type="radio" name="order_item_id" value="{{ $item->id }}"
                                        id="item-{{ $item->id }}" class="item-card__radio"
                                        {{ $selectedItem && $selectedItem->id === $item->id ? 'checked' : '' }}
                                        {{ $expired || $existingOpen ? 'disabled' : '' }}>
                                    @if ($item->product?->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->file_path) }}"
                                            class="item-card__img" alt="{{ $item->product_name }}">
                                    @else
                                        <div class="item-card__img-ph"><i class="bi bi-box-seam"></i></div>
                                    @endif
                                    <div class="item-card__info">
                                        <div class="item-card__name">{{ $item->product_name }}</div>
                                        <div class="item-card__meta">
                                            Order #{{ $item->order->order_number }}
                                            · Qty {{ $item->quantity }}
                                            · Purchased {{ $item->order->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="mt-1 d-flex align-items-center gap-2 flex-wrap">
                                            @if ($existingOpen)
                                                <span class="item-card__warranty"
                                                    style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                                                    ⏳ Open claim: #{{ $existingOpen->request_number }}
                                                </span>
                                            @elseif($expired)
                                                <span class="item-card__warranty item-card__warranty--expired">
                                                    ❌ Warranty expired {{ $item->warranty_expires_at->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="item-card__warranty item-card__warranty--active">
                                                    ✅ Warranty valid
                                                    @if ($item->warranty_expires_at)
                                                        · expires {{ $item->warranty_expires_at->format('M d, Y') }}
                                                        @if ($daysLeft !== null)
                                                            ({{ $daysLeft }}d left)
                                                        @endif
                                                    @endif
                                                </span>
                                            @endif
                                            @if ($item->warranty_months)
                                                <span class="text-muted"
                                                    style="font-size:.72rem;">{{ $item->warranty_months }}-month
                                                    warranty</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="item-card__check"></div>
                                </label>
                            @endforeach
                        </div>

                        @error('order_item_id')
                            <div style="color:#ef4444;font-size:.78rem;margin-top:.5rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Step 2: Claim Details ── --}}
                <div class="cu-card mb-3" id="claimDetails" style="{{ $selectedItem ? '' : 'display:none;' }}">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-clipboard-check text-green me-1"></i>
                            Step 2 — Claim Details
                        </div>

                        {{-- Claim type --}}
                        <div class="mb-3">
                            <label class="cu-label">What do you need? <span class="req">*</span></label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach ([['warranty_repair', '🔧', 'Repair', 'Fix the defect/malfunction'], ['warranty_replacement', '🔁', 'Replacement', 'Replace with new unit'], ['warranty_inspection', '🔍', 'Inspection', 'Diagnose the problem first']] as [$val, $icon, $label, $desc])
                                    <label
                                        style="flex:1;min-width:140px;display:flex;align-items:center;gap:.75rem;
                                      padding:.75rem;border:2px solid var(--card-border);
                                      border-radius:var(--r-md);cursor:pointer;
                                      transition:border-color .15s,background .15s;"
                                        id="type-{{ $val }}" onmouseover="this.style.borderColor='var(--cg-300)'"
                                        onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--card-border)'">
                                        <input type="radio" name="request_type" value="{{ $val }}"
                                            {{ old('request_type', 'warranty_repair') === $val ? 'checked' : '' }}
                                            onchange="highlightType(this)"
                                            style="accent-color:var(--cg-500);flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:700;font-size:.875rem;">{{ $icon }}
                                                {{ $label }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $desc }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Issue description --}}
                        <div class="mb-3">
                            <label class="cu-label">Describe the Issue <span class="req">*</span></label>
                            <textarea name="issue_description" rows="4" class="cu-textarea @error('issue_description') is-invalid @enderror"
                                placeholder="Describe the problem in detail: when it started, what happens when you use it, any error messages, etc."
                                required>{{ old('issue_description') }}</textarea>
                            @error('issue_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Serial number --}}
                        <div class="mb-3">
                            <label class="cu-label">Serial Number / Model Number</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="cu-input"
                                placeholder="Found on the product label or packaging" maxlength="100">
                        </div>

                        {{-- Customer notes --}}
                        <div>
                            <label class="cu-label">Additional Notes</label>
                            <textarea name="customer_notes" rows="2" class="cu-textarea"
                                placeholder="Anything else the vendor should know (storage conditions, usage history, etc.)">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Step 3: Upload Evidence ── --}}
                <div class="cu-card mb-3" id="uploadStep" style="{{ $selectedItem ? '' : 'display:none;' }}">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-images text-green me-1"></i>
                            Step 3 — Upload Evidence
                        </div>
                        <p class="text-muted mb-4" style="font-size:.78rem;">
                            Clear photos help the vendor assess your claim faster.
                            Receipts strengthen your case. All uploads are optional but recommended.
                        </p>

                        {{-- Issue / defect photos --}}
                        <div class="mb-4">
                            <label class="cu-label">
                                📸 Photos of the Defect / Issue
                                <span class="text-muted fw-400">(up to 5 images)</span>
                            </label>
                            <div class="upload-zone" onclick="document.getElementById('issueInput').click()">
                                <i class="bi bi-camera-fill"></i>
                                <div style="font-size:.82rem;font-weight:600;color:var(--tx-secondary);">Click to add
                                    defect photos</div>
                                <div class="text-muted" style="font-size:.72rem;">Show the damage, malfunction, or issue
                                    clearly</div>
                            </div>
                            <input type="file" id="issueInput" name="issue_images[]" multiple accept="image/*"
                                style="display:none;" onchange="previewFiles(this, 'issuePreviews')">
                            <div class="preview-strip" id="issuePreviews"></div>
                        </div>

                        {{-- Receipt / proof of purchase --}}
                        <div class="mb-4">
                            <label class="cu-label">
                                🧾 Proof of Purchase
                                <span class="text-muted fw-400">(receipt, invoice, or warranty card — up to 3)</span>
                            </label>
                            <div class="upload-zone" onclick="document.getElementById('receiptInput').click()">
                                <i class="bi bi-receipt"></i>
                                <div style="font-size:.82rem;font-weight:600;color:var(--tx-secondary);">Upload receipt or
                                    warranty card</div>
                                <div class="text-muted" style="font-size:.72rem;">JPG, PNG, or PDF accepted</div>
                            </div>
                            <input type="file" id="receiptInput" name="receipt_images[]" multiple
                                accept="image/*,.pdf" style="display:none;"
                                onchange="previewFiles(this, 'receiptPreviews')">
                            <div class="preview-strip" id="receiptPreviews"></div>
                        </div>

                        {{-- Product condition photos --}}
                        <div>
                            <label class="cu-label">
                                📦 Product Condition Photos
                                <span class="text-muted fw-400">(overall condition, not just the defect — up to 5)</span>
                            </label>
                            <div class="upload-zone" onclick="document.getElementById('productInput').click()">
                                <i class="bi bi-box-seam"></i>
                                <div style="font-size:.82rem;font-weight:600;color:var(--tx-secondary);">Show overall
                                    product condition</div>
                                <div class="text-muted" style="font-size:.72rem;">Helps rule out physical damage claims
                                </div>
                            </div>
                            <input type="file" id="productInput" name="product_images[]" multiple accept="image/*"
                                style="display:none;" onchange="previewFiles(this, 'productPreviews')">
                            <div class="preview-strip" id="productPreviews"></div>
                        </div>
                    </div>
                </div>

                {{-- What happens next --}}
                <div class="cu-card mb-4" id="processCard" style="{{ $selectedItem ? '' : 'display:none;' }}">
                    <div class="cu-card__body">
                        <div class="fw-700 mb-3" style="font-size:.875rem;">
                            <i class="bi bi-info-circle-fill text-green me-1"></i> What happens after you submit?
                        </div>
                        @php $steps = [['bi-send', 'Submitted', 'Your claim is sent to the vendor for review.'], ['bi-eye', 'Under Review', 'Vendor checks your claim, photos, and warranty validity.'], ['bi-check-circle', 'Approved', 'Vendor confirms your claim is covered under warranty.'], ['bi-calendar-check', 'Scheduled', 'A technician is assigned and a service date is confirmed.'], ['bi-gear-wide-connected', 'In Progress', 'The technician repairs, replaces, or inspects your product.'], ['bi-patch-check', 'Completed', 'Work is done. Vendor uploads completion photos.']]; @endphp
                        <div class="d-flex flex-column gap-2">
                            @foreach ($steps as $i => [$icon, $title, $desc])
                                <div style="display:flex;gap:.65rem;align-items:flex-start;">
                                    <div
                                        style="width:26px;height:26px;border-radius:50%;
                                    background:{{ $i === 0 ? 'var(--cg-600)' : 'var(--cn-100)' }};
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:.7rem;flex-shrink:0;
                                    color:{{ $i === 0 ? '#fff' : 'var(--tx-muted)' }};">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:.82rem;font-weight:700;">{{ $title }}</div>
                                        <div style="font-size:.75rem;color:var(--tx-muted);">{{ $desc }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="submitSection" style="{{ $selectedItem ? '' : 'display:none;' }}">
                    <button type="submit" class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg" id="submitBtn">
                        <i class="bi bi-shield-check"></i> Submit Warranty Claim
                    </button>
                    <p class="text-muted text-center mt-2" style="font-size:.75rem;">
                        By submitting, you confirm that the issue is not due to physical damage, misuse, or unauthorized
                        repair.
                    </p>
                </div>

            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function selectItem(card, id) {
            document.querySelectorAll('.item-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            ['claimDetails', 'uploadStep', 'processCard', 'submitSection'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = '';
            });
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        function highlightType(input) {
            document.querySelectorAll('[id^="type-"]').forEach(el => {
                el.style.borderColor = 'var(--card-border)';
                el.style.background = '';
            });
            const label = input.closest('label');
            label.style.borderColor = 'var(--cg-500)';
            label.style.background = 'var(--cg-50)';
        }

        function previewFiles(input, containerId) {
            const container = document.getElementById(containerId);
            [...input.files].forEach(file => {
                if (!file.type.startsWith('image/')) {
                    const div = document.createElement('div');
                    div.className = 'preview-thumb';
                    div.style.cssText =
                        'display:flex;align-items:center;justify-content:center;background:var(--n-50);';
                    div.innerHTML =
                        '<i class="bi bi-file-earmark-pdf" style="font-size:1.3rem;color:#ef4444;"></i>';
                    container.appendChild(div);
                    return;
                }
                const r = new FileReader();
                r.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-thumb';
                    container.appendChild(img);
                };
                r.readAsDataURL(file);
            });
        }

        // Init: highlight pre-checked type
        document.querySelector('input[name="request_type"]:checked')?.dispatchEvent(new Event('change'));

        // Submit spinner
        document.getElementById('warrantyForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        });

        // Pre-select if ?item_id passed
        @if ($selectedItem)
            document.getElementById('item-{{ $selectedItem->id }}')?.closest('.item-card')
                ?.classList.add('selected');
        @endif
    </script>
@endpush
