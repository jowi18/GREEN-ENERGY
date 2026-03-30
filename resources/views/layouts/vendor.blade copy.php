<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Vendor Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    {{-- Vendor portal theme --}}
    <link href="{{ asset('layout_css/vendor.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>

    {{-- Mobile overlay --}}
    <div class="vd-overlay" id="vdOverlay"></div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SIDEBAR                                  --}}
    {{-- ════════════════════════════════════════ --}}
    <aside class="vd-sidebar" id="vdSidebar">

        @php
            $vendor = auth()->user()->vendor ?? auth()->user()->employee?->vendor;
            $sub = $vendor?->activeSubscription?->load('plan');
            $daysLeft = $sub ? $sub->daysRemaining() : 0;
            $chipState = !$sub ? 'expired' : ($daysLeft <= 7 ? 'expiring' : 'active');
        @endphp

        {{-- Brand --}}
        <a href="{{ route('vendor.dashboard') }}" class="vd-brand">
            <div class="vd-brand__logo">
                {{ strtoupper(substr($vendor?->business_name ?? 'V', 0, 1)) }}
            </div>
            <div class="vd-brand__info">
                <span class="vd-brand__name">{{ $vendor?->business_name ?? config('app.name') }}</span>
                <span class="vd-brand__sub">Vendor Portal</span>
            </div>
        </a>

        {{-- Subscription status chip --}}
        @if ($sub)
            <div class="vd-sub-chip">
                <span class="vd-sub-chip__dot vd-sub-chip__dot--{{ $chipState }}"></span>
                <span class="vd-sub-chip__label">{{ $sub->plan->name }}</span>
                <span class="vd-sub-chip__days">{{ $daysLeft }}d left</span>
            </div>
        @endif

        {{-- Navigation --}}
        <nav class="vd-nav">

            <p class="vd-nav__section">Overview</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.dashboard') }}"
                    class="vd-nav__link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2 vd-nav__icon"></i>
                    <span class="vd-nav__text">Dashboard</span>
                </a>
            </div>

            <p class="vd-nav__section">Store</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.products.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.products*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam vd-nav__icon"></i>
                    <span class="vd-nav__text">Products</span>
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.inventory.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.inventory*') ? 'active' : '' }}">
                    <i class="bi bi-archive vd-nav__icon"></i>
                    <span class="vd-nav__text">Inventory</span>
                    @php $lowStock = $vendor ? \App\Models\Inventory::forVendor($vendor->id)->lowStock()->count() : 0; @endphp
                    @if ($lowStock > 0)
                        <span class="vd-nav__badge vd-nav__badge--yellow">{{ $lowStock }}</span>
                    @endif
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.pos.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.pos*') ? 'active' : '' }}">
                    <i class="bi bi-shop vd-nav__icon"></i>
                    <span class="vd-nav__text">POS System</span>
                </a>
            </div>

            <p class="vd-nav__section">Orders</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.orders.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}">
                    <i class="bi bi-bag-check vd-nav__icon"></i>
                    <span class="vd-nav__text">Online Orders</span>
                    @php $pendingOrders = $vendor ? \App\Models\Order::forVendor($vendor->id)->pending()->count() : 0; @endphp
                    @if ($pendingOrders > 0)
                        <span class="vd-nav__badge">{{ $pendingOrders }}</span>
                    @endif
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="#" class="vd-nav__link {{ request()->routeIs('vendor.delivery*') ? 'active' : '' }}">
                    <i class="bi bi-truck vd-nav__icon"></i>
                    <span class="vd-nav__text">Delivery</span>
                </a>
            </div>

            <p class="vd-nav__section">Services</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.service-catalog.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.service-catalog*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam vd-nav__icon"></i>
                    <span class="vd-nav__text">Service Catalog</span>
                </a>
            </div>


            <div class="vd-nav__item">
                <a href="{{ route('vendor.services.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.services*') ? 'active' : '' }}">
                    <i class="bi bi-tools vd-nav__icon"></i>
                    <span class="vd-nav__text">Service Requests</span>
                    @php $openServices = $vendor ? \App\Models\ServiceRequest::forVendor($vendor->id)->open()->count() : 0; @endphp
                    @if ($openServices > 0)
                        <span class="vd-nav__badge">{{ $openServices }}</span>
                    @endif
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="#" class="vd-nav__link {{ request()->routeIs('vendor.warranty*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check vd-nav__icon"></i>
                    <span class="vd-nav__text">Warranty Claims</span>
                </a>
            </div>

            <p class="vd-nav__section">Team</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.employees.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.employees*') ? 'active' : '' }}">
                    <i class="bi bi-people vd-nav__icon"></i>
                    <span class="vd-nav__text">Employees</span>
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.roles.index') }}" class="vd-nav__link {{ request()->routeIs('vendor.roles*') ? 'active' : '' }}">
                    <i class="bi bi-person-lock vd-nav__icon"></i>
                    <span class="vd-nav__text">Roles & Permissions</span>
                </a>
            </div>

            <p class="vd-nav__section">Account</p>

            <div class="vd-nav__item">
                <a href="{{ route('vendor.subscription.index') }}"
                    class="vd-nav__link {{ request()->routeIs('vendor.subscription*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card vd-nav__icon"></i>
                    <span class="vd-nav__text">Subscription</span>
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="#" class="vd-nav__link {{ request()->routeIs('vendor.chat*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots vd-nav__icon"></i>
                    <span class="vd-nav__text">Customer Chat</span>
                </a>
            </div>

            <div class="vd-nav__item">
                <a href="#" class="vd-nav__link">
                    <i class="bi bi-gear vd-nav__icon"></i>
                    <span class="vd-nav__text">Store Settings</span>
                </a>
            </div>

        </nav>

        {{-- User footer --}}
        <div class="vd-sidebar__footer">
            <div class="vd-user">
                <div class="vd-user__avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="vd-user__info">
                    <div class="vd-user__name">{{ auth()->user()->name }}</div>
                    <div class="vd-user__role">
                        @if (auth()->user()->isVendor())
                            Owner
                        @elseif(auth()->user()->isEmployee())
                            {{ auth()->user()->employee?->roles->pluck('name')->first() ?? 'Employee' }}
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('vendor.logout') }}">
                    @csrf
                    <button type="submit" class="vd-user__logout" title="Sign out">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ════════════════════════════════════════ --}}
    {{-- MAIN                                     --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="vd-main">

        {{-- Topbar --}}
        <header class="vd-topbar">
            <div class="vd-topbar__left">
                <button class="vd-hamburger" id="vdHamburger" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <span class="vd-topbar__title">@yield('page-title', 'Dashboard')</span>
                @hasSection('breadcrumb')
                    <nav class="vd-topbar__breadcrumb">
                        <span class="sep">›</span>
                        @yield('breadcrumb')
                    </nav>
                @endif
            </div>

            <div class="vd-topbar__right">
                {{-- Low stock alert button --}}
                @if (($lowStock ?? 0) > 0)
                    <a href="#" class="vd-topbar__btn" title="{{ $lowStock }} low stock items">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span class="badge-dot"></span>
                    </a>
                @endif

                {{-- Pending orders button --}}
                @if (($pendingOrders ?? 0) > 0)
                    <a href="#" class="vd-topbar__btn" title="{{ $pendingOrders }} pending orders">
                        <i class="bi bi-bag-check"></i>
                        <span class="badge-dot"></span>
                    </a>
                @endif

                <a href="{{ route('vendor.subscription.index') }}" class="vd-topbar__btn" title="Subscription">
                    <i class="bi bi-credit-card"></i>
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session()->hasAny(['success', 'error', 'warning', 'info']))
            <div class="px-3 pt-3" id="vd-flash">
                @foreach (['success', 'error', 'warning', 'info'] as $type)
                    @if (session($type))
                        <div class="vd-flash vd-flash--{{ $type }}">
                            <i
                                class="bi bi-{{ $type === 'success' ? 'check-circle-fill' : ($type === 'error' ? 'exclamation-circle-fill' : ($type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) }}"></i>
                            <span>{{ session($type) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Page content --}}
        <main class="vd-content">
            @yield('content')
        </main>

    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ── Sidebar toggle ─────────────────────── */
        const sidebar = document.getElementById('vdSidebar');
        const overlay = document.getElementById('vdOverlay');
        const hamburger = document.getElementById('vdHamburger');

        const openSB = () => {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        };
        const closeSB = () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        };

        hamburger?.addEventListener('click', () => sidebar.classList.contains('open') ? closeSB() : openSB());
        overlay?.addEventListener('click', closeSB);

        /* ── CSRF ───────────────────────────────── */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* ── Auto-dismiss flash (5 s) ───────────── */
        setTimeout(() => {
            document.querySelectorAll('#vd-flash .vd-flash').forEach(el => {
                el.style.transition = 'opacity 0.4s, transform 0.4s';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-4px)';
                setTimeout(() => el.remove(), 400);
            });
        }, 5000);

        /* ── Chart.js defaults ──────────────────── */
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#6e7a70';
        }
    </script>

    @stack('scripts')
</body>

</html>
