<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Browse Solar Products') — {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="{{ asset('layout_css/customer.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>

    {{-- ── Navbar ── --}}
    <nav class="cu-navbar">
        <a href="{{ route('customer.dashboard') }}" class="cu-navbar__logo">
            <span class="cu-navbar__logo-dot"></span>
            {{ config('app.name', 'SolarHub') }}
        </a>

        <div class="cu-navbar__search">
            <i class="bi bi-search cu-navbar__search-icon"></i>
            <form action="{{ route('customer.products.index') }}" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" class="cu-navbar__search-input"
                    placeholder="Search solar products…">
            </form>
        </div>

        <div class="cu-navbar__actions">
            {{-- Cart --}}
            <button class="cu-navbar__btn" id="cartToggle" title="Cart" onclick="openCart()">
                <i class="bi bi-bag"></i>
                <span class="badge-dot" id="cartDot" style="display:none;"></span>
            </button>

            {{-- Orders --}}
            <a href="{{ route('customer.orders.index') }}" class="cu-navbar__btn" title="My Orders">
                <i class="bi bi-bag-check"></i>
            </a>

            <a href="{{ route('customer.chat.index') }}" class="cu-navbar__btn" title="My Orders">
                <i class="bi bi-chat-dots"></i>
            </a>

            {{-- Profile dropdown --}}
            <div class="dropdown">
                <div class="cu-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <ul class="dropdown-menu dropdown-menu-end"
                    style="font-family:'Nunito',sans-serif;font-size:0.875rem;min-width:180px;">
                    <li><span class="dropdown-item-text text-muted"
                            style="font-size:0.78rem;">{{ auth()->user()->email }}</span></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}"><i
                                class="bi bi-grid me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}"><i
                                class="bi bi-bag me-2"></i>My Orders</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.services.index') }}"><i
                                class="bi bi-tools me-2"></i>My Services</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.vendors.index') }}"><i
                                class="bi bi-geo-alt me-2"></i>Find Vendors</a></li>
                    <li>
                    <li><a class="dropdown-item" href="{{ route('customer.warranty.index') }}"><i
                                class="bi bi-shield-check me-2"></i>Warranty</a></li>
                    <li>
                    <li><a class="dropdown-item" href="{{ route('customer.solar.index') }}"><i
                                class="bi bi-book me-2"></i>Book Installation</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('customer.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ── Flash messages ── --}}
    @if (session()->hasAny(['success', 'error', 'warning', 'info']))
        <div class="cu-page" style="padding-bottom:0;" id="cu-flash">
            @foreach (['success', 'error', 'warning', 'info'] as $type)
                @if (session($type))
                    <div class="cu-flash cu-flash--{{ $type }}">
                        <i
                            class="bi bi-{{ $type === 'success' ? 'check-circle-fill' : ($type === 'error' ? 'exclamation-circle-fill' : ($type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) }}"></i>
                        <span>{{ session($type) }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ── Page content ── --}}
    @yield('content')

    {{-- ── Cart drawer ── --}}
    <div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
    <div class="cart-drawer" id="cartDrawer">
        <div class="cart-drawer__header">
            <div class="cart-drawer__title">
                <i class="bi bi-bag"></i>
                Cart <span id="drawerCount"
                    style="background:var(--cg-600);color:#fff;font-size:0.68rem;font-weight:800;padding:0.1rem 0.42rem;border-radius:999px;min-width:18px;text-align:center;">0</span>
            </div>
            <button onclick="closeCart()"
                style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--tx-muted);">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="cart-drawer__items" id="cartDrawerItems">
            <div class="cu-empty" id="cartDrawerEmpty">
                <i class="bi bi-bag"></i>
                <p>Your cart is empty</p>
            </div>
        </div>
        <div class="cart-drawer__footer" id="cartDrawerFooter" style="display:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                <span style="font-weight:700;">Total</span>
                <span class="mono fw-700" style="font-size:1.1rem;color:var(--cg-700);" id="cartTotal">₱0.00</span>
            </div>
            <a href="{{ route('customer.checkout') }}" class="cu-btn cu-btn--primary cu-btn--full"
                id="checkoutLink">
                <i class="bi bi-credit-card"></i> Proceed to Checkout
            </a>
            <button onclick="clearCart()" class="cu-btn cu-btn--ghost cu-btn--full mt-2" style="font-size:0.8rem;">
                Clear cart
            </button>
        </div>
    </div>

    {{-- ── Bottom nav (mobile) ── --}}
    <nav class="cu-bottomnav">
        <a href="{{ route('customer.dashboard') }}"
            class="cu-bottomnav__item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2{{ request()->routeIs('customer.dashboard') ? '-fill' : '' }}"></i>
            Home
        </a>
        <a href="{{ route('customer.vendors.index') }}"
            class="cu-bottomnav__item {{ request()->routeIs('customer.vendors*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt{{ request()->routeIs('customer.vendors*') ? '-fill' : '' }}"></i>
            Vendors
        </a>
        <a href="{{ route('customer.products.index') }}"
            class="cu-bottomnav__item {{ request()->routeIs('customer.products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam{{ request()->routeIs('customer.products*') ? '-fill' : '' }}"></i>
            Products
        </a>
        <a href="{{ route('customer.orders.index') }}"
            class="cu-bottomnav__item {{ request()->routeIs('customer.orders*') ? 'active' : '' }}">
            <i class="bi bi-bag-check{{ request()->routeIs('customer.orders*') ? '-fill' : '' }}"></i>
            Orders
        </a>
        <a href="{{ route('customer.services.index') }}"
            class="cu-bottomnav__item {{ request()->routeIs('customer.orders*') ? 'active' : '' }}">
            <i class="bi bi-tools{{ request()->routeIs('customer.orders*') ? '-fill' : '' }}"></i>
            Services
        </a>

    </nav>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ── CSRF ──────────────────────────────────── */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* ── Auto-dismiss flash (5 s) ──────────────── */
        setTimeout(() => {
            document.querySelectorAll('#cu-flash .cu-flash').forEach(el => {
                el.style.transition = 'opacity 0.4s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            });
        }, 5000);

        /* ═══════════════════════════════════════════
           CART  (session-backed via AJAX)
        ═══════════════════════════════════════════ */
        let cartData = {};

        function openCart() {
            document.getElementById('cartDrawer').classList.add('open');
            document.getElementById('cartOverlay').classList.add('open');
            refreshCart();
        }

        function closeCart() {
            document.getElementById('cartDrawer').classList.remove('open');
            document.getElementById('cartOverlay').classList.remove('open');
        }

        function addToCart(productId, quantity = 1) {
            $.post('{{ route('customer.cart.add') }}', {
                product_id: productId,
                quantity
            }, function(res) {
                if (res.success) {
                    updateCartBadge(res.cart_count);
                    Swal.fire({
                        icon: 'success',
                        text: 'Added to cart!',
                        toast: true,
                        position: 'top-end',
                        timer: 1800,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        text: res.message,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function removeFromCart(productId) {
            $.post('{{ route('customer.cart.remove') }}', {
                product_id: productId
            }, function(res) {
                if (res.success) {
                    updateCartBadge(res.cart_count);
                    refreshCart();
                }
            });
        }

        function updateCartQty(productId, qty) {
            $.post('{{ route('customer.cart.update') }}', {
                product_id: productId,
                quantity: qty
            }, function(res) {
                if (res.success) {
                    updateCartBadge(res.cart_count);
                    refreshCart();
                }
            });
        }

        function clearCart() {
            $.post('{{ route('customer.cart.clear') }}', {}, function(res) {
                if (res.success) {
                    updateCartBadge(0);
                    refreshCart();
                }
            });
        }

        function refreshCart() {
            $.get('{{ route('customer.cart.index') }}', function(res) {
                cartData = res.items || {};
                renderCartDrawer(res);
            });
        }

        function renderCartDrawer(res) {
            const items = res.items || {};
            const ids = Object.keys(items);
            const emptyEl = document.getElementById('cartDrawerEmpty');
            const footerEl = document.getElementById('cartDrawerFooter');
            const itemsEl = document.getElementById('cartDrawerItems');
            const countEl = document.getElementById('drawerCount');

            countEl.textContent = res.cart_count || 0;

            if (!ids.length) {
                emptyEl.style.display = '';
                footerEl.style.display = 'none';
                itemsEl.querySelectorAll('.cart-line-item').forEach(e => e.remove());
                return;
            }

            emptyEl.style.display = 'none';
            footerEl.style.display = '';

            let html = '';
            ids.forEach(id => {
                const item = items[id];
                html += `
        <div class="cart-line-item">
            <img src="${item.image || ''}" class="cart-line-item__img"
                 onerror="this.style.display='none'"
                 alt="${item.name}">
            <div style="flex:1;min-width:0;">
                <div class="cart-line-item__name">${item.name}</div>
                <div class="cart-line-item__price">₱${parseFloat(item.price).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                <div style="display:flex;align-items:center;gap:0.4rem;margin-top:0.4rem;">
                    <button class="cart-qty-btn" onclick="updateCartQty(${id}, ${item.quantity - 1})"><i class="bi bi-dash"></i></button>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:0.85rem;font-weight:700;min-width:20px;text-align:center;">${item.quantity}</span>
                    <button class="cart-qty-btn" onclick="updateCartQty(${id}, ${item.quantity + 1})"><i class="bi bi-plus"></i></button>
                    <button class="cart-qty-btn" onclick="removeFromCart(${id})" style="margin-left:auto;color:var(--red-500);"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>`;
            });

            itemsEl.querySelectorAll('.cart-line-item').forEach(e => e.remove());
            emptyEl.insertAdjacentHTML('beforebegin', html);

            document.getElementById('cartTotal').textContent = '₱' + parseFloat(res.total || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
        }

        function updateCartBadge(count) {
            document.getElementById('cartDot').style.display = count > 0 ? '' : 'none';
            document.getElementById('drawerCount').textContent = count;
        }

        // Init cart count on load
        $.get('{{ route('customer.cart.index') }}', function(res) {
            updateCartBadge(res.cart_count || 0);
        });
    </script>

    @stack('scripts')
</body>

</html>
