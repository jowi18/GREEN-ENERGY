{{-- resources/views/vendor/solar/quotation.blade.php --}}
@extends('layouts.vendor')
@section('title', 'Create Quotation · ' . $solarProject->project_number)
@section('page-title', 'Create Quotation')

@section('breadcrumb')
    <a href="{{ route('vendor.solar.index') }}" class="text-secondary">Solar Projects</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.solar.show', $solarProject) }}" class="text-secondary">
        {{ $solarProject->project_number }}
    </a>
    <span class="sep">›</span>
    <span class="current">Quotation</span>
@endsection

@push('styles')
    <style>
        .qt-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
        }

        .qt-section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tx-muted);
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .qt-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 80px 80px 90px 32px;
            gap: .4rem;
            align-items: start;
            margin-bottom: .4rem;
        }

        @media (max-width:768px) {
            .qt-row {
                grid-template-columns: 1fr;
            }
        }

        .qt-row-head {
            font-size: .68rem;
            font-weight: 700;
            color: var(--tx-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 0 .1rem .35rem;
            border-bottom: 1px solid var(--card-border);
            margin-bottom: .5rem;
        }

        .qt-total-row {
            display: flex;
            justify-content: space-between;
            font-size: .875rem;
            padding: .3rem 0;
        }

        .qt-total-row.grand {
            font-size: 1.1rem;
            font-weight: 800;
            border-top: 2px solid var(--card-border);
            padding-top: .65rem;
            margin-top: .35rem;
        }

        .remove-row-btn {
            width: 28px;
            height: 28px;
            border: 1.5px solid #fca5a5;
            background: #fef2f2;
            color: #dc2626;
            border-radius: var(--r-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            flex-shrink: 0;
            transition: all .15s;
        }

        .remove-row-btn:hover {
            background: #fee2e2;
            border-color: #ef4444;
        }

        .type-badge {
            display: inline-block;
            padding: .15rem .5rem;
            border-radius: var(--r-full);
            font-size: .7rem;
            font-weight: 700;
        }

        .type-badge--equipment {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .type-badge--labor {
            background: #f0fdf4;
            color: #15803d;
        }

        .type-badge--permit {
            background: #fffbeb;
            color: #b45309;
        }

        .type-badge--other {
            background: var(--n-100);
            color: var(--tx-muted);
        }
    </style>
@endpush

@section('content')
    <div style="max-width:100%;">

        {{-- Customer summary --}}
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:.85rem 1.25rem;">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <div class="text-muted"
                            style="font-size:.72rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;">
                            Project</div>
                        <div class="mono fw-700" style="color:var(--g-700);">
                            {{ $solarProject->project_number }}
                        </div>
                    </div>
                    <div style="width:1px;height:32px;background:var(--card-border);"></div>
                    <div>
                        <div class="text-muted"
                            style="font-size:.72rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;">
                            Customer</div>
                        <div class="fw-600" style="font-size:.875rem;">
                            {{ $solarProject->customer->full_name }}
                        </div>
                    </div>
                    <div style="width:1px;height:32px;background:var(--card-border);"></div>
                    <div>
                        <div class="text-muted"
                            style="font-size:.72rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;">
                            Property</div>
                        <div class="fw-600" style="font-size:.875rem;">
                            {{ ucfirst($solarProject->property_type) }}
                            @if ($solarProject->desired_system_size_kw)
                                · {{ $solarProject->desired_system_size_kw }} kW
                            @endif
                        </div>
                    </div>
                    @if ($solarProject->estimated_monthly_bill)
                        <div style="width:1px;height:32px;background:var(--card-border);"></div>
                        <div>
                            <div class="text-muted"
                                style="font-size:.72rem;font-weight:700;
                            text-transform:uppercase;letter-spacing:.06em;">
                                Monthly Bill</div>
                            <div class="fw-600" style="font-size:.875rem;">
                                ₱{{ number_format($solarProject->estimated_monthly_bill, 2) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor.solar.quotation.store', $solarProject) }}" id="quotationForm">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-lg-8">

                    {{-- Equipment items --}}
                    <div class="vd-card mb-3">
                        <div class="vd-card__body">
                            <div class="qt-section-head">
                                <div class="qt-section-title">
                                    <span class="type-badge type-badge--equipment">⚙️ Equipment</span>
                                    Bill of Materials
                                </div>
                                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm"
                                    onclick="addRow('equipment')">
                                    <i class="bi bi-plus-lg"></i> Add Item
                                </button>
                            </div>

                            <div class="qt-row qt-row-head"
                                style="display:grid;
                                    grid-template-columns:2.5fr 1fr 1fr 80px 90px 90px 32px;gap:.4rem;">
                                <span>Product</span>
                                <span>SKU / Notes</span>
                                <span>Unit</span>
                                <span>Qty</span>
                                <span>Unit Price</span>
                                <span>Total</span>
                                <span></span>
                            </div>

                            <div id="equipmentRows"></div>

                            <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm mt-2"
                                onclick="addRow('equipment')" style="width:100%;border-style:dashed;">
                                <i class="bi bi-plus-lg me-1"></i> Add equipment row
                            </button>
                        </div>
                    </div>

                    {{-- Labor items --}}
                    <div class="vd-card mb-3">
                        <div class="vd-card__body">
                            <div class="qt-section-head">
                                <div class="qt-section-title">
                                    <span class="type-badge type-badge--labor">🔧 Labor</span>
                                    Installation & Services
                                </div>
                                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm" onclick="addRow('labor')">
                                    <i class="bi bi-plus-lg"></i> Add Item
                                </button>
                            </div>

                            <div class="qt-row qt-row-head"
                                style="display:grid;
                            grid-template-columns:2fr 1fr 1fr 80px 80px 90px 32px;gap:.4rem;">
                                <span>Description</span>
                                <span>Notes</span>
                                <span>Unit</span>
                                <span>Qty</span>
                                <span>Unit Price</span>
                                <span>Total</span>
                                <span></span>
                            </div>

                            <div id="laborRows"></div>

                            <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm mt-2" onclick="addRow('labor')"
                                style="width:100%;border-style:dashed;">
                                <i class="bi bi-plus-lg me-1"></i> Add labor row
                            </button>
                        </div>
                    </div>

                    {{-- Permit / other items --}}
                    <div class="vd-card mb-3">
                        <div class="vd-card__body">
                            <div class="qt-section-head">
                                <div class="qt-section-title">
                                    <span class="type-badge type-badge--permit">📄 Permit</span>
                                    Permits & Other Fees
                                </div>
                                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm" onclick="addRow('permit')">
                                    <i class="bi bi-plus-lg"></i> Add Item
                                </button>
                            </div>
                            <div id="permitRows"></div>
                            <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm mt-2" onclick="addRow('permit')"
                                style="width:100%;border-style:dashed;">
                                <i class="bi bi-plus-lg me-1"></i> Add permit/fee row
                            </button>
                        </div>
                    </div>

                    {{-- Notes & T&C --}}
                    <div class="vd-card">
                        <div class="vd-card__body">
                            <div class="qt-section-title mb-3">📝 Notes & Terms</div>
                            <div class="mb-3">
                                <label class="vd-label">Quotation notes</label>
                                <textarea name="notes" rows="3" class="vd-input"
                                    placeholder="Additional notes for the customer about this quotation…" style="font-size:.82rem;resize:vertical;"></textarea>
                            </div>
                            <div>
                                <label class="vd-label">Terms & Conditions</label>
                                <textarea name="terms_and_conditions" rows="4" class="vd-input"
                                    placeholder="Payment terms, warranty coverage, installation conditions…" style="font-size:.82rem;resize:vertical;">50% downpayment required upon signing. Balance due upon system activation. Warranty: 1 year workmanship, manufacturer warranty applies to all equipment.</textarea>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right: totals + settings --}}
                <div class="col-12 col-lg-4">
                    <div class="vd-card" style="position:sticky;top:calc(var(--tb-height,60px) + 1rem);">
                        <div class="vd-card__header">
                            <span class="vd-card__title"><i class="bi bi-calculator"></i> Summary</span>
                        </div>
                        <div class="vd-card__body">

                            {{-- Totals display --}}
                            <div style="margin-bottom:1rem;">
                                <div class="qt-total-row">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="mono fw-600" id="subtotalDisplay">₱0.00</span>
                                </div>
                                <div class="qt-total-row">
                                    <span class="text-muted">Discount</span>
                                    <span class="mono text-red" id="discountDisplay">— ₱0.00</span>
                                </div>
                                <div class="qt-total-row">
                                    <span class="text-muted">Tax / VAT</span>
                                    <span class="mono" id="taxDisplay">+ ₱0.00</span>
                                </div>
                                <div class="qt-total-row grand">
                                    <span>Total</span>
                                    <span class="mono" style="color:var(--g-700);" id="grandTotalDisplay">₱0.00</span>
                                </div>
                            </div>

                            {{-- Discount & Tax inputs --}}
                            <div class="mb-2">
                                <label class="vd-label" style="font-size:.72rem;">Discount (₱)</label>
                                <input type="number" name="discount_amount" id="discountInput" class="vd-input"
                                    placeholder="0.00" min="0" step="0.01" oninput="recalculate()"
                                    value="0">
                            </div>
                            <div class="mb-3">
                                <label class="vd-label" style="font-size:.72rem;">Tax / VAT (₱)</label>
                                <input type="number" name="tax_amount" id="taxInput" class="vd-input"
                                    placeholder="0.00" min="0" step="0.01" oninput="recalculate()"
                                    value="0">
                            </div>

                            <hr style="border-color:var(--card-border);margin:.75rem 0;">

                            {{-- Valid until --}}
                            <div class="mb-3">
                                <label class="vd-label" style="font-size:.72rem;">Valid until</label>
                                <input type="date" name="valid_until" class="vd-input"
                                    min="{{ today()->addDay()->format('Y-m-d') }}"
                                    value="{{ today()->addDays(14)->format('Y-m-d') }}">
                            </div>

                            {{-- Actions --}}
                            <button type="submit" name="action" value="send"
                                class="vd-btn vd-btn--primary vd-btn--full mb-2" id="sendBtn">
                                <i class="bi bi-send"></i> Send to Customer
                            </button>
                            <button type="submit" name="action" value="draft"
                                class="vd-btn vd-btn--ghost vd-btn--full">
                                <i class="bi bi-save"></i> Save as Draft
                            </button>

                            <p class="text-muted text-center mt-2" style="font-size:.72rem;">
                                Sending will notify the customer to review and respond.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // ── Product catalogue injected from PHP ────────────────────────────
        const PRODUCTS = @json($products);
        let rowIndex = 0;

        // ── Build product <select> options ─────────────────────────────────
        function buildProductOptions(selectedId = '') {
            let opts = `<option value="">— Select product or type custom —</option>`;
            PRODUCTS.forEach(p => {
                const stockLabel = p.out_of_stock ?
                    ' ⚠ Out of stock' :
                    p.low_stock ? ' ⚠ Low stock' : '';
                const selected = p.id == selectedId ? 'selected' : '';
                opts += `<option value="${p.id}"
                        data-name="${p.name}"
                        data-sku="${p.sku ?? ''}"
                        data-price="${p.unit_price}"
                        data-unit="${p.unit}"
                        data-stock="${p.stock}"
                        ${selected}>
                        ${p.name}${stockLabel}
                     </option>`;
            });
            opts += `<option value="custom">+ Custom / unlisted item</option>`;
            return opts;
        }

        // ── Add row ────────────────────────────────────────────────────────
        function addRow(type, defaults = {}) {
            const containerId = type === 'equipment' ? 'equipmentRows' :
                type === 'labor' ? 'laborRows' :
                'permitRows';

            const i = rowIndex++;
            const div = document.createElement('div');
            div.className = 'qt-row';
            div.id = `row-${i}`;

            if (type === 'equipment') {
                // ── Equipment: product dropdown ────────────────────────────
                div.style.cssText =
                    'display:grid;grid-template-columns:2.5fr 1fr 1fr 80px 90px 90px 32px;gap:.4rem;margin-bottom:.4rem;';
                div.innerHTML = `
                <input type="hidden" name="items[${i}][item_type]" value="equipment">
                <input type="hidden" name="items[${i}][product_id]" id="productId-${i}" value="${defaults.product_id ?? ''}">
                <div>
                    <select id="productSelect-${i}" class="vd-select" style="font-size:.8rem;"
                        onchange="onProductSelect(${i})">
                        ${buildProductOptions(defaults.product_id ?? '')}
                    </select>
                    <input type="text"
                        name="items[${i}][description]"
                        id="desc-${i}"
                        class="vd-input mt-1"
                        style="font-size:.8rem;"
                        placeholder="Product description…"
                        value="${defaults.description ?? ''}"
                        required>
                    <div id="stockBadge-${i}" style="font-size:.68rem;margin-top:.2rem;"></div>
                </div>

                <input type="text" name="items[${i}][brand]" id="brand-${i}"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="SKU / Notes"
                    value="${defaults.brand ?? ''}">

                <select name="items[${i}][unit]" id="unit-${i}" class="vd-select" style="font-size:.78rem;"
                    onchange="recalculate()">
                    ${['pc','set','lot','m','hr','day','kg'].map(u =>
                        `<option value="${u}" ${(defaults.unit ?? 'pc') === u ? 'selected' : ''}>${u}</option>`
                    ).join('')}
                </select>

                <input type="number" name="items[${i}][quantity]" id="qty-${i}"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="1" min="0.01" step="0.01"
                    value="${defaults.quantity ?? 1}" required
                    oninput="updateRowTotal(${i})">

                <input type="number" name="items[${i}][unit_price]" id="price-${i}"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="0.00" min="0" step="0.01"
                    value="${defaults.unit_price ?? ''}" required
                    oninput="updateRowTotal(${i})">

                <input type="text" id="rowTotal-${i}"
                    class="vd-input mono" style="font-size:.78rem;background:var(--n-50);color:var(--g-700);font-weight:600;"
                    placeholder="0.00" readonly tabindex="-1">

                <button type="button" class="remove-row-btn" onclick="removeRow(${i})">
                    <i class="bi bi-trash" style="font-size:.65rem;"></i>
                </button>
            `;
            } else {
                // ── Labor / Permit: plain text (unchanged) ─────────────────
                div.style.cssText =
                    'display:grid;grid-template-columns:2fr 1fr 1fr 80px 80px 90px 32px;gap:.4rem;margin-bottom:.4rem;';
                div.innerHTML = `
                <input type="hidden" name="items[${i}][item_type]" value="${type}">
                <input type="text" name="items[${i}][description]"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="${type === 'labor' ? 'e.g. Panel Installation & Wiring' : 'e.g. Building Permit'}"
                    value="${defaults.description ?? ''}" required>
                <input type="text" name="items[${i}][brand]"
                    class="vd-input" style="font-size:.8rem;" placeholder="Notes"
                    value="${defaults.brand ?? ''}">
                <select name="items[${i}][unit]" class="vd-select" style="font-size:.78rem;"
                    onchange="recalculate()">
                    ${['pc','set','lot','m','hr','day','kg'].map(u =>
                        `<option value="${u}" ${(defaults.unit ?? 'lot') === u ? 'selected' : ''}>${u}</option>`
                    ).join('')}
                </select>
                <input type="number" name="items[${i}][quantity]"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="1" min="0.01" step="0.01"
                    value="${defaults.quantity ?? 1}" required
                    oninput="updateRowTotal(${i})">
                <input type="number" name="items[${i}][unit_price]"
                    class="vd-input" style="font-size:.8rem;"
                    placeholder="0.00" min="0" step="0.01"
                    value="${defaults.unit_price ?? ''}" required
                    oninput="updateRowTotal(${i})">
                <input type="text" id="rowTotal-${i}"
                    class="vd-input mono" style="font-size:.78rem;background:var(--n-50);color:var(--g-700);font-weight:600;"
                    placeholder="0.00" readonly tabindex="-1">
                <button type="button" class="remove-row-btn" onclick="removeRow(${i})">
                    <i class="bi bi-trash" style="font-size:.65rem;"></i>
                </button>
            `;
            }

            document.getElementById(containerId).appendChild(div);
            if (defaults.unit_price) updateRowTotal(i);

            // Auto-trigger populate if product_id already set (edit mode)
            if (type === 'equipment' && defaults.product_id) {
                onProductSelect(i);
            }
        }

        // ── Product selected from dropdown ─────────────────────────────────
        function onProductSelect(i) {
            const select     = document.getElementById(`productSelect-${i}`);
            const val        = select.value;
            const stockBadge = document.getElementById(`stockBadge-${i}`);
            const hiddenId   = document.getElementById(`productId-${i}`);
            const descInput  = document.getElementById(`desc-${i}`);

            if (val === '' || val === 'custom') {
                stockBadge.innerHTML = '';
                hiddenId.value = '';
                descInput.value = '';
                descInput.readOnly = false;
                descInput.placeholder = 'Describe the item…';
                return;
            }

            const opt   = select.options[select.selectedIndex];
            const name  = opt.dataset.name;
            const sku   = opt.dataset.sku;
            const price = opt.dataset.price;
            const unit  = opt.dataset.unit;
            const stock = parseInt(opt.dataset.stock);

            // Populate fields
            hiddenId.value  = val;
            descInput.value = name;
            descInput.readOnly = true; // lock when product selected, user can clear select to edit

            document.getElementById(`brand-${i}`).value = sku;
            document.getElementById(`price-${i}`).value = price;

            // Set unit dropdown
            const unitSel = document.getElementById(`unit-${i}`);
            [...unitSel.options].forEach(o => o.selected = o.value === unit);

            // Stock badge
            const badgeColor = stock <= 0 ? '#dc2626' : stock <= 5 ? '#d97706' : '#15803d';
            stockBadge.innerHTML = `
                <span style="color:${badgeColor};font-weight:600;">
                    <i class="bi bi-box"></i> ${stock} ${unit} in stock
                </span>`;

            updateRowTotal(i);
        }

        // ── Remove row ─────────────────────────────────────────────────────
        function removeRow(i) {
            document.getElementById(`row-${i}`)?.remove();
            recalculate();
        }

        // ── Row total ──────────────────────────────────────────────────────
        function updateRowTotal(i) {
            const qty = parseFloat(document.getElementById(`qty-${i}`)?.value ??
                document.querySelector(`[name="items[${i}][quantity]"]`)?.value) || 0;
            const price = parseFloat(document.getElementById(`price-${i}`)?.value ??
                document.querySelector(`[name="items[${i}][unit_price]"]`)?.value) || 0;
            const el = document.getElementById(`rowTotal-${i}`);
            if (el) el.value = (qty * price).toFixed(2);
            recalculate();
        }

        // ── Grand total ────────────────────────────────────────────────────
        function recalculate() {
            let subtotal = 0;
            document.querySelectorAll('[name^="items["]').forEach(el => {
                if (!el.name.includes('[quantity]')) return;
                const idx = el.name.match(/\[(\d+)\]/)[1];
                const qty = parseFloat(el.value) || 0;
                const price = parseFloat(
                    document.getElementById(`price-${idx}`)?.value ??
                    document.querySelector(`[name="items[${idx}][unit_price]"]`)?.value
                ) || 0;
                subtotal += qty * price;
            });

            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            const tax = parseFloat(document.getElementById('taxInput').value) || 0;
            const grand = subtotal - discount + tax;

            document.getElementById('subtotalDisplay').textContent = '₱' + fmt(subtotal);
            document.getElementById('discountDisplay').textContent = '— ₱' + fmt(discount);
            document.getElementById('taxDisplay').textContent = '+ ₱' + fmt(tax);
            document.getElementById('grandTotalDisplay').textContent = '₱' + fmt(grand);
        }

        function fmt(n) {
            return parseFloat(n).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // ── Seed default rows ──────────────────────────────────────────────
        addRow('equipment');
        addRow('equipment');
        addRow('labor', {
            description: 'Panel Installation & Wiring',
            unit: 'lot',
            quantity: 1
        });
        addRow('labor', {
            description: 'Electrical Works & Conduit',
            unit: 'lot',
            quantity: 1
        });
        addRow('permit', {
            description: 'Building / Electrical Permit Fee',
            unit: 'lot',
            quantity: 1
        });

        // ── Submit state ───────────────────────────────────────────────────
        // ── Submit: capture action BEFORE disabling button ─────────────────
        // ── Submit: capture action BEFORE disabling button ─────────────────
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('button[value="send"], button[value="draft"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Add hidden input with action value
                    let hidden = document.getElementById('_action_input');
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'action';
                        hidden.id = '_action_input';
                        document.getElementById('quotationForm').appendChild(hidden);
                    }
                    hidden.value = this.value;

                    // Disable button after short delay so form submits first
                    const self = this;
                    setTimeout(() => {
                        self.disabled = true;
                        self.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
                    }, 100);
                });
            });
        });
    </script>
@endpush
