@extends('layouts.vendor')

@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')

    {{-- ── Stats ── --}}
    <div class="row g-3 gap-section">
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-archive"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['total_products']) }}</div>
                <div class="vd-stat__label">Total Products</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--blue">
                <div class="vd-stat__icon vd-stat__icon--blue"><i class="bi bi-currency-dollar"></i></div>
                <div class="vd-stat__value" style="font-size:1.3rem;">₱{{ number_format($stats['total_value'], 0) }}</div>
                <div class="vd-stat__label">Inventory Value (cost)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--yellow">
                <div class="vd-stat__icon vd-stat__icon--yellow"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['low_stock']) }}</div>
                <div class="vd-stat__label">Low Stock Items</div>
                @if ($stats['low_stock'] > 0)
                    <div class="vd-stat__change vd-stat__change--warn">
                        <i class="bi bi-exclamation-circle"></i> Needs restocking
                    </div>
                @endif
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="vd-stat vd-stat--red">
                <div class="vd-stat__icon vd-stat__icon--red"><i class="bi bi-x-circle"></i></div>
                <div class="vd-stat__value">{{ number_format($stats['out_of_stock']) }}</div>
                <div class="vd-stat__label">Out of Stock</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Product name, SKU or barcode…">
                </div>

                <select name="filter" class="vd-select" style="width:auto;">
                    <option value="">All Stock</option>
                    <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Low Stock
                    </option>
                    <option value="out_of_stock" {{ request('filter') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                    </option>
                </select>

                <select name="category" class="vd-select" style="width:auto;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['search', 'filter', 'category']))
                    <a href="{{ route('vendor.inventory.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif

                <a href="{{ route('vendor.inventory.movements') }}" class="vd-btn vd-btn--ghost ms-auto">
                    <i class="bi bi-clock-history"></i> Stock Movements
                </a>
            </form>
        </div>
    </div>

    {{-- ── Inventory table ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-archive"></i> Stock Levels</span>
            <span class="vd-card__subtitle">{{ $inventory->total() }} items</span>
        </div>

        @if ($inventory->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>On Hand</th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Reorder Point</th>
                            <th>Location</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventory as $inv)
                            @php
                                $qty = $inv->quantity_on_hand;
                                $avail = $inv->quantity_available;
                                $state = $qty === 0 ? 'danger' : ($inv->isLowStock() ? 'warning' : 'ok');
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($inv->product?->primaryImage)
                                            <img src="{{ asset('storage/' . $inv->product->primaryImage->file_path) }}"
                                                style="width:34px;height:34px;object-fit:cover;border-radius:var(--r-sm);
                                        border:1px solid var(--card-border);flex-shrink:0;">
                                        @else
                                            <div
                                                style="width:34px;height:34px;background:var(--n-50);border-radius:var(--r-sm);
                                        border:1px solid var(--card-border);display:flex;align-items:center;
                                        justify-content:center;color:var(--tx-muted);flex-shrink:0;">
                                                <i class="bi bi-image" style="font-size:0.82rem;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $inv->product->name }}</div>
                                            <div class="mono text-muted" style="font-size:0.72rem;">
                                                {{ $inv->product->sku ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="mono fw-600 {{ $state === 'danger' ? 'text-red' : ($state === 'warning' ? 'text-yellow' : '') }}"
                                        style="font-size:0.875rem;">
                                        {{ $qty }}
                                    </span>
                                    @if ($state !== 'ok')
                                        <span
                                            class="vd-badge vd-badge--{{ $state === 'danger' ? 'danger' : 'warning' }} ms-1"
                                            style="font-size:0.65rem;">
                                            {{ $state === 'danger' ? 'Out' : 'Low' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="mono text-secondary">{{ $inv->quantity_reserved }}</td>
                                <td class="mono fw-600">{{ $avail }}</td>
                                <td class="mono text-secondary">{{ $inv->reorder_point }}</td>
                                <td class="text-muted" style="font-size:0.82rem;">
                                    {{ $inv->storage_location ?? '—' }}
                                </td>
                                <td class="text-muted" style="font-size:0.78rem;">
                                    {{ $inv->last_stock_update?->diffForHumans() ?? '—' }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="vd-btn vd-btn--primary vd-btn--sm"
                                            onclick="openAdjust({{ $inv->id }}, '{{ addslashes($inv->product->name) }}', {{ $qty }})">
                                            <i class="bi bi-arrows-move"></i> Adjust
                                        </button>
                                        <button class="vd-btn vd-btn--ghost vd-btn--sm"
                                            onclick="openAddStock({{ $inv->id }}, '{{ addslashes($inv->product->name) }}')">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($inventory->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $inventory->firstItem() }}–{{ $inventory->lastItem() }} of {{ $inventory->total() }}
                    </span>
                    {{ $inventory->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-archive"></i>
                <h6>No inventory records</h6>
                <p>Add products to start tracking stock.</p>
                <a href="{{ route('vendor.products.create') }}" class="vd-btn vd-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </div>
        @endif
    </div>

    {{-- ── Adjust stock modal ── --}}
    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="adjustForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700"><i class="bi bi-arrows-move me-2 text-green"></i>Adjust Stock</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1.25rem;">
                            Set the exact stock count for <strong id="adjustProductName"></strong>.
                            A movement record will be logged automatically.
                        </p>

                        <div class="mb-3">
                            <label class="vd-label">Current stock</label>
                            <input type="text" id="currentQty" class="vd-input" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="vd-label">New quantity <span class="req">*</span></label>
                            <input type="number" name="new_quantity" id="newQty" class="vd-input" min="0"
                                required>
                            <div id="qtyDiff" style="font-size:0.8rem;margin-top:0.35rem;font-weight:600;"></div>
                        </div>

                        <div>
                            <label class="vd-label">Reason <span class="req">*</span></label>
                            <textarea name="notes" rows="2" class="vd-textarea" required
                                placeholder="Why is this adjustment being made?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary" id="adjustBtn">
                            <i class="bi bi-check-lg"></i> Save Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Add stock modal ── --}}
    <div class="modal fade" id="addStockModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addStockForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700"><i class="bi bi-plus-circle me-2 text-green"></i>Add Stock</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1.25rem;">
                            Add incoming stock for <strong id="addProductName"></strong>.
                        </p>

                        <div class="mb-3">
                            <label class="vd-label">Quantity to add <span class="req">*</span></label>
                            <input type="number" name="quantity" class="vd-input" min="1" required
                                placeholder="0">
                        </div>

                        <div class="mb-3">
                            <label class="vd-label">Reason</label>
                            <select name="movement_type" class="vd-select">
                                <option value="purchase">Purchase / New Stock Received</option>
                                <option value="return">Customer Return</option>
                                <option value="transfer_in">Transfer In</option>
                            </select>
                        </div>

                        <div>
                            <label class="vd-label">Notes</label>
                            <textarea name="notes" rows="2" class="vd-textarea"
                                placeholder="Supplier, PO number, or any relevant info…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary" id="addStockBtn">
                            <i class="bi bi-plus-lg"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let adjustInventoryId = null;
        let addStockInventoryId = null;

        /* ── Adjust modal ────────────────────────────── */
        function openAdjust(inventoryId, productName, currentQty) {
            adjustInventoryId = inventoryId;
            document.getElementById('adjustProductName').textContent = productName;
            document.getElementById('currentQty').value = currentQty;
            document.getElementById('newQty').value = currentQty;
            document.getElementById('qtyDiff').textContent = '';
            new bootstrap.Modal(document.getElementById('adjustModal')).show();
        }

        document.getElementById('newQty').addEventListener('input', function() {
            const curr = parseInt(document.getElementById('currentQty').value) || 0;
            const next = parseInt(this.value) || 0;
            const diff = next - curr;
            const el = document.getElementById('qtyDiff');
            if (diff === 0) {
                el.textContent = 'No change';
                el.style.color = 'var(--tx-muted)';
            } else if (diff > 0) {
                el.textContent = `+${diff} units will be added`;
                el.style.color = 'var(--g-600)';
            } else {
                el.textContent = `${diff} units will be removed`;
                el.style.color = 'var(--red-500)';
            }
        });

        document.getElementById('adjustForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('adjustBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';

            $.post(`/vendor/inventory/${adjustInventoryId}/adjust`,
                $(this).serialize(),
                function(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('adjustModal')).hide();
                        Swal.fire({
                                icon: 'success',
                                title: 'Adjusted',
                                text: res.message,
                                confirmButtonColor: '#157a30',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Adjustment';
                    }
                }
            ).fail(xhr => {
                const msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Adjustment';
            });
        });

        /* ── Add stock modal ─────────────────────────── */
        function openAddStock(inventoryId, productName) {
            addStockInventoryId = inventoryId;
            document.getElementById('addProductName').textContent = productName;
            document.getElementById('addStockForm').reset();
            new bootstrap.Modal(document.getElementById('addStockModal')).show();
        }

        document.getElementById('addStockForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('addStockBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding…';

            $.post(`/vendor/inventory/${addStockInventoryId}/add`,
                $(this).serialize(),
                function(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
                        Swal.fire({
                                icon: 'success',
                                title: 'Stock Added',
                                text: res.message,
                                confirmButtonColor: '#157a30',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-plus-lg"></i> Add Stock';
                    }
                }
            ).fail(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus-lg"></i> Add Stock';
            });
        });
    </script>
@endpush
