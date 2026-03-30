@extends('layouts.vendor')
@section('title', 'New Purchase Request')
@section('page-title', 'Procurement')

@section('breadcrumb')
    <a href="{{ route('vendor.procurement.pr.index') }}" class="text-secondary">Purchase Requests</a>
    <span class="sep">›</span>
    <span class="current">New Request</span>
@endsection

@push('styles')
    <style>
        /* ── Product catalog card ───────────────────── */
        .catalog-card {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .75rem;
            cursor: pointer;
            transition: all .15s;
            user-select: none;
            background: var(--card-bg);
            position: relative;
        }

        .catalog-card:hover {
            border-color: var(--g-400);
            background: var(--n-50);
        }

        .catalog-card.added {
            border-color: var(--g-500);
            background: var(--n-50);
        }

        .catalog-card__badge {
            position: absolute;
            top: .4rem;
            right: .4rem;
            background: var(--g-500);
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            padding: .15rem .45rem;
            border-radius: 999px;
            display: none;
        }

        .catalog-card.added .catalog-card__badge {
            display: inline-block;
        }

        .catalog-card__img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: var(--r-sm);
            background: var(--n-100);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: .5rem;
        }

        .catalog-card__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── PR Items table ─────────────────────────── */
        .pr-item-row td {
            vertical-align: middle;
            font-size: .85rem;
        }

        .pr-item-row .vd-input {
            padding: .35rem .6rem;
            font-size: .82rem;
        }

        /* ── form-hint ──────────────────────────────── */
        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .25rem;
        }

        /* ── Empty state ────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--tx-muted);
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('vendor.procurement.pr.store') }}" id="prForm">
        @csrf

        @if ($errors->any())
            <div class="vd-card mb-3" style="border-color:#fca5a5;background:#fef2f2;">
                <div class="vd-card__body">
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

        {{-- ── Card 1: Request Details ──────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-file-earmark-text"></i> Request Details</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="vd-label">Supplier <span class="req">*</span></label>
                        <select name="supplier_id" id="supplierSelect"
                            class="vd-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">— Select supplier —</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Products will load after supplier selection.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Requested Delivery Date</label>
                        <input type="date" name="requested_delivery_date" value="{{ old('requested_delivery_date') }}"
                            class="vd-input @error('requested_delivery_date') is-invalid @enderror"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                        @error('requested_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="vd-label">Notes</label>
                        <textarea name="notes" rows="2" class="vd-input" placeholder="Special instructions or requirements…"
                            style="resize:vertical;">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Product Catalog ───────────────────────────────────── --}}
        <div class="vd-card mb-3" id="catalogCard" style="display:none;">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-grid"></i> Supplier Catalog</span>
                <div class="ms-auto" style="font-size:.78rem;color:var(--tx-muted);" id="catalogMeta"></div>
            </div>
            <div class="vd-card__body">

                {{-- Search within catalog --}}
                <div class="mb-3">
                    <input type="text" id="catalogSearch" class="vd-input" placeholder="Search products by name or SKU…">
                </div>

                <div id="catalogGrid" class="row g-2">
                    <div class="col-12 empty-state">
                        <i class="bi bi-box-seam" style="font-size:2rem;"></i>
                        <div class="mt-2">Select a supplier to see products.</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Card 3: Selected Items ────────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-cart3"></i> Selected Items</span>
                <span class="ms-auto" style="font-size:.78rem;color:var(--tx-muted);">
                    <span id="itemCount">0</span> item(s) — Total:
                    <strong id="prTotal">₱0.00</strong>
                </span>
            </div>
            <div class="vd-card__body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="prItemsTable">
                        <thead
                            style="background:var(--n-50);font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>SKU</th>
                                <th>Unit Price</th>
                                <th style="width:130px;">Qty</th>
                                <th>Subtotal</th>
                                <th>Notes</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="prItemsBody">
                            <tr id="emptyRow">
                                <td colspan="7" class="empty-state py-4">
                                    <i class="bi bi-cart-x" style="font-size:1.75rem;"></i>
                                    <div class="mt-2 text-muted" style="font-size:.82rem;">
                                        No items added. Select products from the catalog above.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Actions ───────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('vendor.procurement.pr.index') }}" class="vd-btn vd-btn--ghost">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <div class="d-flex gap-2">
                <button type="submit" name="action" value="draft" class="vd-btn vd-btn--ghost">
                    <i class="bi bi-floppy me-1"></i> Save Draft
                </button>
                <button type="submit" name="action" value="submit" class="vd-btn vd-btn--primary" id="submitBtn"
                    style="min-width:170px;">
                    <i class="bi bi-send-check me-1"></i> Submit for Approval
                </button>
            </div>
        </div>

        {{-- Hidden items container --}}
        <div id="hiddenItems"></div>
    </form>

@endsection

@push('scripts')
    <script>
        /* ── State ───────────────────────────────────────────────────────────────── */
        let catalogProducts = [];
        let selectedItems = {}; // key: product id, value: { product, qty, notes }

        /* ── Load catalog on supplier change ────────────────────────────────────── */
        $('#supplierSelect').on('change', function() {
            const supplierId = $(this).val();
            if (!supplierId) {
                $('#catalogCard').hide();
                return;
            }

            $.getJSON(`{{ url('vendor/procurement/pr/supplier') }}/${supplierId}/products`, function(data) {
                catalogProducts = data;
                $('#catalogCard').show();
                $('#catalogMeta').text(data.length + ' products available');
                renderCatalog(data);
            });
        });

        /* ── Render product grid ─────────────────────────────────────────────────── */
        function renderCatalog(products) {
            const grid = $('#catalogGrid');
            grid.empty();

            if (!products.length) {
                grid.html(
                    '<div class="col-12 empty-state"><i class="bi bi-box-seam" style="font-size:2rem;"></i><div class="mt-2">No products found.</div></div>'
                    );
                return;
            }

            products.forEach(p => {
                const isAdded = !!selectedItems[p.id];
                const imgHtml = p.primary_image ?
                    `<img src="/storage/${p.primary_image.file_path}" alt="${p.name}">` :
                    `<i class="bi bi-box-seam" style="font-size:1.75rem;color:var(--tx-muted);"></i>`;

                grid.append(`
            <div class="col-md-3 col-6">
                <div class="catalog-card ${isAdded ? 'added' : ''}" data-id="${p.id}" onclick="toggleProduct(${p.id})">
                    <span class="catalog-card__badge">✓ Added</span>
                    <div class="catalog-card__img">${imgHtml}</div>
                    <div class="fw-700" style="font-size:.8rem;line-height:1.3;">${p.name}</div>
                    <div style="font-size:.7rem;color:var(--tx-muted);margin-top:.1rem;">${p.sku ?? '—'}</div>
                    <div class="fw-700 mt-1" style="font-size:.85rem;color:var(--g-600);">₱${parseFloat(p.unit_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                    <div style="font-size:.68rem;color:var(--tx-muted);">Min. ${p.minimum_order_qty} ${p.unit_of_measure}</div>
                </div>
            </div>
        `);
            });
        }

        /* ── Toggle product in/out of request ───────────────────────────────────── */
        function toggleProduct(productId) {
            const product = catalogProducts.find(p => p.id === productId);
            if (!product) return;

            if (selectedItems[productId]) {
                delete selectedItems[productId];
            } else {
                selectedItems[productId] = {
                    product,
                    qty: product.minimum_order_qty,
                    notes: ''
                };
            }

            $(`.catalog-card[data-id="${productId}"]`).toggleClass('added', !!selectedItems[productId]);
            renderItemsTable();
        }

        /* ── Render selected items table ─────────────────────────────────────────── */
        function renderItemsTable() {
            const body = $('#prItemsBody');
            const ids = Object.keys(selectedItems);
            const emptyRow = $('#emptyRow').detach();

            body.empty();

            if (!ids.length) {
                body.append(emptyRow);
                updateTotals();
                return;
            }

            ids.forEach((id, index) => {
                const {
                    product,
                    qty,
                    notes
                } = selectedItems[id];
                const lineTotal = (product.unit_price * qty).toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                });

                body.append(`
                    <tr class="pr-item-row" data-id="${id}">
                        <td class="ps-3 fw-600">${product.name}</td>
                        <td class="text-muted">${product.sku ?? '—'}</td>
                        <td>₱${parseFloat(product.unit_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                        <td>
                            <input type="number" class="vd-input qty-input" data-id="${id}"
                                value="${qty}" min="${product.minimum_order_qty}" step="1"
                                style="width:90px;" oninput="updateQty(${id}, this.value)">
                        </td>
                        <td class="fw-700 line-total-${id}">₱${lineTotal}</td>
                        <td>
                            <input type="text" class="vd-input notes-input" data-id="${id}"
                                placeholder="Notes…" value="${notes}"
                                style="min-width:140px;" oninput="updateNotes(${id}, this.value)">
                        </td>
                        <td>
                            <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm"
                                onclick="removeItem(${id})" title="Remove">
                                <i class="bi bi-trash3 text-danger"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            updateTotals();
            syncHiddenInputs();
        }

        function updateQty(id, value) {
            selectedItems[id].qty = parseInt(value) || 1;
            const p = selectedItems[id].product;
            const lt = (p.unit_price * selectedItems[id].qty).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
            $(`.line-total-${id}`).text('₱' + lt);
            updateTotals();
            syncHiddenInputs();
        }

        function updateNotes(id, value) {
            selectedItems[id].notes = value;
            syncHiddenInputs();
        }

        function removeItem(id) {
            delete selectedItems[id];
            $(`.catalog-card[data-id="${id}"]`).removeClass('added');
            renderItemsTable();
        }

        function updateTotals() {
            const ids = Object.keys(selectedItems);
            let total = 0;
            ids.forEach(id => {
                total += selectedItems[id].product.unit_price * selectedItems[id].qty;
            });
            $('#itemCount').text(ids.length);
            $('#prTotal').text('₱' + total.toLocaleString('en-PH', {
                minimumFractionDigits: 2
            }));
        }

        /* ── Build hidden inputs before submit ───────────────────────────────────── */
        function syncHiddenInputs() {
            const container = $('#hiddenItems');
            container.empty();
            Object.keys(selectedItems).forEach((id, index) => {
                const {
                    product,
                    qty,
                    notes
                } = selectedItems[id];
                container.append(`
            <input type="hidden" name="items[${index}][supplier_product_id]" value="${id}">
            <input type="hidden" name="items[${index}][quantity_requested]"  value="${qty}">
            <input type="hidden" name="items[${index}][notes]"               value="${notes}">
        `);
            });
        }

        /* ── Catalog search ──────────────────────────────────────────────────────── */
        $('#catalogSearch').on('input', function() {
            const term = $(this).val().toLowerCase();
            const filtered = catalogProducts.filter(p =>
                p.name.toLowerCase().includes(term) || (p.sku ?? '').toLowerCase().includes(term)
            );
            renderCatalog(filtered);
        });

        /* ── Prevent empty submit ────────────────────────────────────────────────── */
        $('#prForm').on('submit', function(e) {
            syncHiddenInputs();
            if (!Object.keys(selectedItems).length) {
                e.preventDefault();
                alert('Please add at least one product before submitting.');
                return false;
            }
            $('#submitBtn').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Submitting…');
        });
    </script>
@endpush
