@extends('layouts.vendor')

@section('title', 'POS Terminal')
@section('page-title', 'POS Terminal')

@push('styles')
    <style>
        /* ── POS-specific overrides ─────────────────── */
        .vd-content {
            padding: 0 !important;
        }

        .pos-shell {
            display: grid;
            grid-template-columns: 1fr 380px;
            height: calc(100vh - var(--tb-height, 60px));
            overflow: hidden;
        }

        /* ── LEFT: Product panel ── */
        .pos-left {
            display: flex;
            flex-direction: column;
            background: var(--body-bg);
            border-right: 1px solid var(--card-border);
            overflow: hidden;
        }

        .pos-toolbar {
            padding: 0.75rem 1rem;
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            gap: 0.65rem;
            align-items: center;
            flex-shrink: 0;
        }

        .pos-scan-input {
            flex: 1;
            padding: 0.6rem 0.9rem 0.6rem 2.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            border: 2px solid var(--g-400);
            border-radius: var(--r-sm);
            outline: none;
            background: var(--card-bg);
            color: var(--tx-primary);
            transition: box-shadow 0.15s;
        }

        .pos-scan-input:focus {
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.18);
        }

        .pos-scan-input::placeholder {
            color: var(--tx-muted);
            font-family: 'DM Sans', sans-serif;
        }

        .pos-scan-wrap {
            position: relative;
            flex: 1;
        }

        .pos-scan-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--g-500);
            font-size: 1rem;
            pointer-events: none;
        }

        .pos-cats {
            display: flex;
            gap: 0.4rem;
            padding: 0.6rem 1rem;
            overflow-x: auto;
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            flex-shrink: 0;
            scrollbar-width: none;
        }

        .pos-cats::-webkit-scrollbar {
            display: none;
        }

        .pos-cat-btn {
            padding: 0.3rem 0.85rem;
            border-radius: var(--r-full);
            border: 1.5px solid var(--card-border);
            background: transparent;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--tx-secondary);
            cursor: pointer;
            white-space: nowrap;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .pos-cat-btn:hover {
            border-color: var(--g-300);
            color: var(--g-600);
            background: var(--g-50);
        }

        .pos-cat-btn.active {
            background: var(--g-600);
            border-color: var(--g-600);
            color: #fff;
        }

        .pos-grid {
            flex: 1;
            overflow-y: auto;
            padding: 0.85rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.65rem;
            align-content: start;
        }

        .pos-product-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s;
            user-select: none;
            display: flex;
            flex-direction: column;
        }

        .pos-product-card:hover {
            border-color: var(--g-400);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.15);
        }

        .pos-product-card:active {
            transform: scale(0.97);
        }

        .pos-product-card.out-of-stock {
            opacity: 0.45;
            pointer-events: none;
        }

        .pos-product-card__img {
            width: 100%;
            height: 100px; /* fixed height regardless of image */
            overflow: hidden;
            background: var(--n-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tx-muted);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .pos-product-card__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pos-product-card__body {
            padding: 0.55rem 0.65rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .pos-product-card__name {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--tx-primary);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pos-product-card__price {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--g-700);
            margin-top: auto;
            padding-top: 0.35rem;
        }

        .pos-product-card__stock {
            font-size: 0.68rem;
            color: var(--tx-muted);
            margin-top: 1px;
        }

        /* Search dropdown */
        .pos-search-results {
            position: absolute;
            top: 110%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1.5px solid var(--g-300);
            border-radius: var(--r-md);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            z-index: 50;
            max-height: 320px;
            overflow-y: auto;
        }

        .pos-search-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            cursor: pointer;
            transition: background 0.1s;
            border-bottom: 1px solid var(--n-100);
        }

        .pos-search-item:last-child {
            border-bottom: none;
        }

        .pos-search-item:hover {
            background: var(--g-50);
        }

        .pos-search-item__name {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .pos-search-item__meta {
            font-size: 0.75rem;
            color: var(--tx-muted);
        }

        .pos-search-item__price {
            margin-left: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--g-700);
            white-space: nowrap;
        }

        /* ── RIGHT: Cart panel ── */
        .pos-right {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            overflow: hidden;
        }

        .pos-cart-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .pos-cart-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--tx-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cart-count {
            background: var(--g-600);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.1rem 0.45rem;
            border-radius: var(--r-full);
            min-width: 20px;
            text-align: center;
        }

        .pos-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            border-bottom: 1px solid var(--n-100);
            transition: background 0.1s;
        }

        .cart-item:hover {
            background: var(--n-50);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item__name {
            font-size: 0.845rem;
            font-weight: 600;
            color: var(--tx-primary);
            line-height: 1.3;
        }

        .cart-item__sku {
            font-size: 0.72rem;
            color: var(--tx-muted);
            margin-top: 1px;
        }

        .cart-item__controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .qty-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1.5px solid var(--card-border);
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: var(--tx-secondary);
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .qty-btn:hover {
            background: var(--g-50);
            border-color: var(--g-400);
            color: var(--g-600);
        }

        .qty-btn.remove:hover {
            background: var(--red-50);
            border-color: var(--red-500);
            color: var(--red-500);
        }

        .qty-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            font-weight: 600;
            min-width: 22px;
            text-align: center;
        }

        .cart-item__price {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--g-700);
            white-space: nowrap;
            align-self: center;
        }

        .cart-item__disc-input {
            width: 80px;
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            border: 1px solid var(--card-border);
            border-radius: var(--r-sm);
            background: transparent;
            color: var(--tx-primary);
            outline: none;
            text-align: right;
        }

        .cart-item__disc-input:focus {
            border-color: var(--y-400);
        }

        /* Cart empty state */
        .cart-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--tx-muted);
            padding: 2rem;
            text-align: center;
        }

        .cart-empty i {
            font-size: 2.5rem;
            color: var(--n-300);
            margin-bottom: 0.75rem;
        }

        .cart-empty p {
            font-size: 0.85rem;
        }

        /* Cart totals + payment */
        .pos-totals {
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--card-border);
            background: var(--n-50);
            flex-shrink: 0;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.845rem;
            margin-bottom: 0.35rem;
        }

        .totals-row.total {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--tx-primary);
            border-top: 1.5px solid var(--card-border);
            padding-top: 0.5rem;
            margin-top: 0.35rem;
        }

        .totals-row .mono {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        /* Cash tender input */
        .pos-payment {
            padding: 0.85rem 1rem 1rem;
            border-top: 1px solid var(--card-border);
            flex-shrink: 0;
        }

        .cash-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--tx-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.5rem;
        }

        .cash-input-wrap {
            position: relative;
            margin-bottom: 0.65rem;
        }

        .cash-prefix {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--g-600);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .cash-input {
            width: 100%;
            padding: 0.8rem 0.85rem 0.8rem 2.1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid var(--card-border);
            border-radius: var(--r-sm);
            color: var(--tx-primary);
            background: var(--card-bg);
            outline: none;
            text-align: right;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .cash-input:focus {
            border-color: var(--g-400);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.14);
        }

        /* Quick cash buttons */
        .quick-cash {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.4rem;
            margin-bottom: 0.75rem;
        }

        .quick-cash-btn {
            padding: 0.42rem 0;
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-sm);
            background: transparent;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--tx-secondary);
            cursor: pointer;
            font-family: 'JetBrains Mono', monospace;
            transition: all 0.15s;
        }

        .quick-cash-btn:hover {
            background: var(--g-50);
            border-color: var(--g-400);
            color: var(--g-600);
        }

        .quick-cash-btn.exact {
            background: var(--g-50);
            border-color: var(--g-200);
            color: var(--g-700);
        }

        .change-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0.85rem;
            background: var(--g-50);
            border: 1px solid var(--g-200);
            border-radius: var(--r-sm);
            margin-bottom: 0.75rem;
        }

        .change-display.insufficient {
            background: var(--red-50);
            border-color: var(--red-100);
        }

        .change-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--g-700);
        }

        .change-display.insufficient .change-label {
            color: var(--red-600);
        }

        .change-amount {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--g-600);
        }

        .change-display.insufficient .change-amount {
            color: var(--red-600);
        }

        .btn-charge {
            width: 100%;
            padding: 0.85rem;
            background: var(--g-600);
            color: #fff;
            border: none;
            border-radius: var(--r-sm);
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.15s, box-shadow 0.15s;
            box-shadow: 0 3px 12px rgba(21, 122, 48, 0.3);
        }

        .btn-charge:hover:not(:disabled) {
            background: var(--g-700);
            box-shadow: 0 5px 20px rgba(21, 122, 48, 0.4);
        }

        .btn-charge:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Optional customer name */
        .customer-input {
            width: 100%;
            padding: 0.45rem 0.75rem;
            font-size: 0.82rem;
            font-family: 'DM Sans', sans-serif;
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-sm);
            color: var(--tx-primary);
            background: var(--card-bg);
            outline: none;
            margin-bottom: 0.65rem;
            transition: border-color 0.15s;
        }

        .customer-input:focus {
            border-color: var(--g-400);
        }

        /* ── Success modal ── */
        .receipt-modal-total {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--g-600);
            letter-spacing: -0.05em;
            text-align: center;
            line-height: 1;
        }

        .receipt-modal-change {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
            color: var(--tx-secondary);
        }

        @media (max-width: 900px) {
            .pos-shell {
                grid-template-columns: 1fr;
            }

            .pos-right {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 50vh;
                z-index: 80;
                border-top: 2px solid var(--g-400);
            }

            .pos-left {
                padding-bottom: 50vh;
            }
        }
    </style>
@endpush

@section('content')

    <div class="pos-shell">

        {{-- ════════════════════════════════════════════
         LEFT — Product selection
    ════════════════════════════════════════════ --}}
        <div class="pos-left">

            {{-- Toolbar: barcode scan + search --}}
            <div class="pos-toolbar">
                <div class="pos-scan-wrap">
                    <i class="bi bi-upc-scan pos-scan-icon"></i>
                    <input type="text" id="scanInput" class="pos-scan-input" placeholder="Scan barcode or type product name…"
                        autocomplete="off" autofocus>
                    <div class="pos-search-results" id="searchResults" style="display:none;"></div>
                </div>
                <button class="vd-btn vd-btn--ghost vd-btn--sm" onclick="document.getElementById('scanInput').focus()"
                    title="Focus scanner">
                    <i class="bi bi-upc"></i>
                </button>
            </div>

            {{-- Category filter --}}
            <div class="pos-cats">
                <button class="pos-cat-btn active" data-cat="">All</button>
                @foreach ($categories as $cat)
                    <button class="pos-cat-btn" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                @endforeach
            </div>

            {{-- Product grid --}}
            <div class="pos-grid" id="productGrid">
                @foreach ($products as $product)
                    <div class="pos-product-card {{ $product['stock'] === 0 && !$product['is_service'] ? 'out-of-stock' : '' }}"
                        onclick="addToCart({{ json_encode($product) }})">
                        <div class="pos-product-card__img">
                            @if ($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                            @else
                                <i class="bi bi-box-seam"></i>
                            @endif
                        </div>
                        <div class="pos-product-card__body">
                            <div class="pos-product-card__name">{{ $product['name'] }}</div>
                            <div class="pos-product-card__price">₱{{ number_format($product['price'], 2) }}</div>
                            <div class="pos-product-card__stock">
                                @if ($product['is_service'])
                                    Service
                                @elseif($product['stock'] === 0)
                                    Out of stock
                                @else
                                    {{ $product['stock'] }} in stock
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ════════════════════════════════════════════
         RIGHT — Cart + Payment
    ════════════════════════════════════════════ --}}
        <div class="pos-right">

            {{-- Cart header --}}
            <div class="pos-cart-header">
                <div class="pos-cart-title">
                    <i class="bi bi-cart3"></i>
                    Cart <span class="cart-count" id="cartCount">0</span>
                </div>
                <button class="vd-btn vd-btn--danger vd-btn--sm" onclick="clearCart()" id="clearCartBtn"
                    style="display:none;">
                    <i class="bi bi-trash"></i> Clear
                </button>
            </div>

            {{-- Cart items list --}}
            <div class="pos-cart-items" id="cartItems">
                <div class="cart-empty" id="cartEmpty">
                    <i class="bi bi-cart3"></i>
                    <p>Cart is empty.<br>Scan a barcode or click a product to add it.</p>
                </div>
            </div>

            {{-- Totals --}}
            <div class="pos-totals" id="cartTotals" style="display:none;">
                <div class="totals-row">
                    <span class="text-secondary">Subtotal</span>
                    <span class="mono" id="subtotalDisplay">₱0.00</span>
                </div>
                <div class="totals-row">
                    <span class="text-secondary">Discount</span>
                    <span class="mono text-red" id="discountDisplay">— ₱0.00</span>
                </div>
                <div class="totals-row total">
                    <span>Total</span>
                    <span class="mono" id="totalDisplay">₱0.00</span>
                </div>
            </div>

            {{-- Payment panel --}}
            <div class="pos-payment" id="paymentPanel" style="display:none;">
                <div class="cash-label">Cash Payment</div>

                <input type="text" class="customer-input" id="customerName" placeholder="Customer name (optional)">

                <div class="cash-input-wrap">
                    <span class="cash-prefix">₱</span>
                    <input type="number" id="cashInput" class="cash-input" placeholder="0.00" step="0.01" min="0"
                        oninput="onCashInput()">
                </div>

                <div class="quick-cash" id="quickCashBtns"></div>

                <div class="change-display" id="changeDisplay">
                    <span class="change-label">Change due</span>
                    <span class="change-amount" id="changeAmount">₱0.00</span>
                </div>

                <button class="btn-charge" id="chargeBtn" onclick="processPayment()" disabled>
                    <i class="bi bi-cash-coin"></i>
                    Charge ₱<span id="chargeTotalDisplay">0.00</span>
                </button>
            </div>

        </div>

    </div>

    {{-- ════ Success modal ════ --}}
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--r-xl)!important;">
                <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                    <div
                        style="width:64px;height:64px;background:var(--g-100);border-radius:50%;
                            display:flex;align-items:center;justify-content:center;
                            margin:0 auto 1rem;font-size:1.75rem;color:var(--g-600);">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5 class="outfit fw-700 mb-1" style="letter-spacing:-0.02em;">Payment Complete</h5>
                    <p class="text-muted mb-3" style="font-size:0.875rem;" id="successTxNum"></p>

                    <div class="receipt-modal-total mb-1" id="successTotal">₱0.00</div>
                    <div class="receipt-modal-change" id="successChange"></div>

                    <hr class="divider" style="margin:1.25rem 0;">

                    <div class="d-flex gap-2">
                        <a href="#" id="printReceiptBtn" target="_blank"
                            class="vd-btn vd-btn--ghost vd-btn--full">
                            <i class="bi bi-printer"></i> Print Receipt
                        </a>
                        <button class="vd-btn vd-btn--primary vd-btn--full" onclick="newTransaction()">
                            <i class="bi bi-plus-lg"></i> New Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ── State ──────────────────────────────────────
        const cart = {}; // { productId: { product, quantity, discount } }
        let totalAmt = 0;

        // ── Add to cart ────────────────────────────────
        function addToCart(product) {
            const id = product.id;

            if (cart[id]) {
                // Check stock
                if (!product.is_service && cart[id].quantity >= product.stock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock limit reached',
                        text: `Only ${product.stock} units available.`,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    return;
                }
                cart[id].quantity++;
            } else {
                cart[id] = {
                    product,
                    quantity: 1,
                    discount: 0
                };
            }

            renderCart();
            document.getElementById('scanInput').value = '';
            document.getElementById('searchResults').style.display = 'none';
        }

        // ── Cart rendering ─────────────────────────────
        function renderCart() {
            const itemsEl = document.getElementById('cartItems');
            const emptyEl = document.getElementById('cartEmpty');
            const totalsEl = document.getElementById('cartTotals');
            const payEl = document.getElementById('paymentPanel');
            const clearBtn = document.getElementById('clearCartBtn');
            const countEl = document.getElementById('cartCount');

            const ids = Object.keys(cart);

            if (ids.length === 0) {
                emptyEl.style.display = '';
                totalsEl.style.display = 'none';
                payEl.style.display = 'none';
                clearBtn.style.display = 'none';
                countEl.textContent = '0';
                return;
            }

            emptyEl.style.display = 'none';
            totalsEl.style.display = '';
            payEl.style.display = '';
            clearBtn.style.display = '';

            // Rebuild items
            let html = '';
            let subtotal = 0;
            let discountTotal = 0;
            let itemCount = 0;

            ids.forEach(id => {
                const {
                    product: p,
                    quantity,
                    discount
                } = cart[id];
                const lineTotal = (p.price * quantity) - discount;
                subtotal += p.price * quantity;
                discountTotal += discount;
                itemCount += quantity;

                html += `
        <div class="cart-item" data-id="${id}">
            <div>
                <div class="cart-item__name">${p.name}</div>
                <div class="cart-item__sku">${p.sku ?? '—'} · ₱${fmt(p.price)}</div>
                <div style="display:flex;align-items:center;gap:0.35rem;margin-top:0.35rem;">
                    <span style="font-size:0.7rem;color:var(--tx-muted);">Disc ₱</span>
                    <input type="number" class="cart-item__disc-input"
                           value="${discount}" min="0" max="${p.price * quantity}"
                           step="0.01" placeholder="0.00"
                           onchange="setDiscount(${id}, this.value)"
                           onclick="this.select()">
                </div>
            </div>
            <div class="cart-item__controls">
                <button class="qty-btn remove" onclick="removeFromCart(${id})" title="Remove">
                    <i class="bi bi-trash" style="font-size:0.7rem;"></i>
                </button>
                <button class="qty-btn" onclick="changeQty(${id}, -1)">
                    <i class="bi bi-dash"></i>
                </button>
                <span class="qty-display">${quantity}</span>
                <button class="qty-btn" onclick="changeQty(${id}, 1)">
                    <i class="bi bi-plus"></i>
                </button>
                <span class="cart-item__price">₱${fmt(lineTotal)}</span>
            </div>
        </div>`;
            });

            // Replace only the item rows (not the empty state)
            // Remove old item rows first
            itemsEl.querySelectorAll('.cart-item').forEach(el => el.remove());
            emptyEl.insertAdjacentHTML('beforebegin', html);

            countEl.textContent = itemCount;

            totalAmt = subtotal - discountTotal;

            document.getElementById('subtotalDisplay').textContent = '₱' + fmt(subtotal);
            document.getElementById('discountDisplay').textContent = '— ₱' + fmt(discountTotal);
            document.getElementById('totalDisplay').textContent = '₱' + fmt(totalAmt);
            document.getElementById('chargeTotalDisplay').textContent = fmt(totalAmt);

            // Quick cash buttons
            buildQuickCash(totalAmt);

            // Update change
            onCashInput();
        }

        function fmt(n) {
            return parseFloat(n).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function changeQty(id, delta) {
            if (!cart[id]) return;
            const newQty = cart[id].quantity + delta;
            if (newQty <= 0) {
                removeFromCart(id);
                return;
            }
            const stock = cart[id].product.stock;
            if (!cart[id].product.is_service && newQty > stock) {
                Swal.fire({
                    icon: 'warning',
                    text: `Only ${stock} available.`,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }
            cart[id].quantity = newQty;
            renderCart();
        }

        function removeFromCart(id) {
            delete cart[id];
            renderCart();
        }

        function setDiscount(id, val) {
            if (!cart[id]) return;
            const max = cart[id].product.price * cart[id].quantity;
            cart[id].discount = Math.min(parseFloat(val) || 0, max);
            renderCart();
        }

        function clearCart() {
            Swal.fire({
                title: 'Clear cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Clear',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (r.isConfirmed) {
                    Object.keys(cart).forEach(k => delete cart[k]);
                    document.getElementById('cashInput').value = '';
                    renderCart();
                }
            });
        }

        // ── Quick cash buttons ─────────────────────────
        function buildQuickCash(total) {
            const presets = getPresets(total);
            const el = document.getElementById('quickCashBtns');
            el.innerHTML = presets.map(v =>
                `<button class="quick-cash-btn ${v === total ? 'exact' : ''}"
                 onclick="setCash(${v})">
            ${v === total ? 'Exact' : '₱' + fmt(v)}
         </button>`
            ).join('');
        }

        function getPresets(total) {
            const bills = [20, 50, 100, 200, 500, 1000];
            const presets = new Set([total]);
            for (const b of bills) {
                let candidate = Math.ceil(total / b) * b;
                if (candidate >= total) presets.add(candidate);
                if (presets.size >= 4) break;
            }
            return [...presets].slice(0, 4).sort((a, b) => a - b);
        }

        function setCash(amount) {
            document.getElementById('cashInput').value = amount.toFixed(2);
            onCashInput();
        }

        function onCashInput() {
            const cash = parseFloat(document.getElementById('cashInput').value) || 0;
            const change = cash - totalAmt;
            const changeEl = document.getElementById('changeDisplay');
            const changeAmt = document.getElementById('changeAmount');
            const chargeBtn = document.getElementById('chargeBtn');

            if (cash <= 0) {
                changeEl.className = 'change-display';
                changeAmt.textContent = '₱0.00';
                chargeBtn.disabled = true;
                return;
            }

            if (change < 0) {
                changeEl.className = 'change-display insufficient';
                changeAmt.textContent = '— ₱' + fmt(Math.abs(change));
                chargeBtn.disabled = true;
            } else {
                changeEl.className = 'change-display';
                changeAmt.textContent = '₱' + fmt(change);
                chargeBtn.disabled = false;
            }
        }

        // ── Process payment ────────────────────────────
        function processPayment() {
            const cash = parseFloat(document.getElementById('cashInput').value);
            if (!cash || cash < totalAmt) return;

            const items = Object.values(cart).map(({
                product,
                quantity,
                discount
            }) => ({
                product_id: product.id,
                quantity,
                unit_price: product.price,
                discount,
            }));

            const btn = document.getElementById('chargeBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

            $.ajax({
                url: '{{ route('vendor.pos.process') }}',
                method: 'POST',
                data: JSON.stringify({
                    items,
                    cash_tendered: cash,
                    customer_name: document.getElementById('customerName').value || null,
                }),
                contentType: 'application/json',
                success(res) {
                    if (!res.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Transaction failed',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-cash-coin"></i> Charge ₱<span id="chargeTotalDisplay">' +
                            fmt(totalAmt) + '</span>';
                        return;
                    }

                    // Show success modal
                    document.getElementById('successTxNum').textContent = 'Transaction #' + res.transaction_number;
                    document.getElementById('successTotal').textContent = '₱' + fmt(res.total_amount);
                    document.getElementById('successChange').textContent = 'Change: ₱' + fmt(parseFloat(res.change_due));

                    document.getElementById('printReceiptBtn').href = res.receipt_url;

                    new bootstrap.Modal(document.getElementById('successModal')).show();
                },
                error(xhr) {
                    const msg = xhr.responseJSON?.message ?? 'An error occurred.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cash-coin"></i> Charge ₱<span>' + fmt(totalAmt) + '</span>';
                }
            });
        }

        function newTransaction() {
            Object.keys(cart).forEach(k => delete cart[k]);
            document.getElementById('cashInput').value = '';
            document.getElementById('customerName').value = '';
            bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
            renderCart();
            setTimeout(() => document.getElementById('scanInput').focus(), 300);
        }

        // ── Barcode / search ───────────────────────────
        let searchTimer = null;

        document.getElementById('scanInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.trim();
                if (!val) return;
                // Try exact barcode scan first
                $.get('{{ route('vendor.pos.scan') }}', {
                    barcode: val
                }, function(res) {
                    if (res.found) {
                        addToCart(res.product);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: `"${val}" not found.`,
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        document.getElementById('scanInput').addEventListener('input', function() {
            clearTimeout(searchTimer);
            const val = this.value.trim();
            const resultsEl = document.getElementById('searchResults');

            if (val.length < 2) {
                resultsEl.style.display = 'none';
                return;
            }

            searchTimer = setTimeout(() => {
                $.get('{{ route('vendor.pos.search') }}', {
                    q: val
                }, function(data) {
                    if (!data.length) {
                        resultsEl.style.display = 'none';
                        return;
                    }
                    resultsEl.innerHTML = data.map(p => `
                <div class="pos-search-item" onclick='addToCart(${JSON.stringify(p)})'>
                    <div>
                        <div class="pos-search-item__name">${p.name}</div>
                        <div class="pos-search-item__meta">
                            ${p.sku ?? '—'}
                            ${p.is_service ? '· Service' : '· Stock: ' + p.stock}
                        </div>
                    </div>
                    <div class="pos-search-item__price">₱${parseFloat(p.price).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                </div>`).join('');
                    resultsEl.style.display = 'block';
                });
            }, 250);
        });

        // Hide search results on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.pos-scan-wrap')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        // ── Category filter ────────────────────────────
        document.querySelectorAll('.pos-cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const catId = this.dataset.cat;
                loadProductGrid(catId);
            });
        });

        function loadProductGrid(catId = '') {
            const grid = document.getElementById('productGrid');
            grid.style.opacity = '0.5';
            $.get('{{ route('vendor.pos.grid') }}', {
                category_id: catId
            }, function(data) {
                grid.innerHTML = data.map(p => `
            <div class="pos-product-card ${p.stock === 0 && !p.is_service ? 'out-of-stock' : ''}"
                 onclick='addToCart(${JSON.stringify(p)})'>
                <div class="pos-product-card__img">
                    ${p.image
                        ? `<img src="${p.image}" alt="${p.name}">`
                        : '<i class="bi bi-box-seam"></i>'}
                </div>
                <div class="pos-product-card__body">
                    <div class="pos-product-card__name">${p.name}</div>
                    <div class="pos-product-card__price">₱${parseFloat(p.price).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                    <div class="pos-product-card__stock">
                        ${p.is_service ? 'Service' : (p.stock === 0 ? 'Out of stock' : p.stock + ' in stock')}
                    </div>
                </div>
            </div>`).join('');
                grid.style.opacity = '1';
            });
        }

        // Initial render
        renderCart();
        document.getElementById('scanInput').focus();
    </script>
@endpush
