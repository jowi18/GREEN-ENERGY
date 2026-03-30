<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Supplier Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    {{-- Reuse admin CSS or create supplier.css alongside it --}}
    <link href="{{ asset('layout_css/admin.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ── Sidebar ────────────────────────────────────────────────────────── --}}
    <aside class="sidebar" id="sidebar">

        <a href="{{ route('supplier.dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand__icon">S</div>
            <div class="sidebar-brand__text">
                <span class="sidebar-brand__name">{{ config('app.name', 'SolarHub') }}</span>
                <span class="sidebar-brand__sub">Supplier Portal</span>
            </div>
        </a>

        <nav class="sidebar-nav">

            <p class="nav-section">Overview</p>

            <div class="nav-item">
                <a href="{{ route('supplier.dashboard') }}"
                    class="nav-link {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2 nav-link__icon"></i>
                    <span class="nav-link__text">Dashboard</span>
                </a>
            </div>

            <p class="nav-section">Catalogue</p>

            <div class="nav-item">
                <a href="{{ route('supplier.products.index') }}"
                    class="nav-link {{ request()->routeIs('supplier.products*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam nav-link__icon"></i>
                    <span class="nav-link__text">My Products</span>
                </a>
            </div>

            <p class="nav-section">Orders</p>

            <div class="nav-item">
                @php
                    $newOrderCount = \App\Models\PurchaseOrder::where('supplier_id', auth()->user()->supplier?->id ?? 0)
                        ->where('status', 'submitted')
                        ->count();
                @endphp
                <a href="{{ route('supplier.orders.index', ['status' => 'submitted']) }}"
                    class="nav-link {{ request()->routeIs('supplier.orders*') && request('status') === 'submitted' ? 'active' : '' }}">
                    <i class="bi bi-inbox nav-link__icon"></i>
                    <span class="nav-link__text">New Orders</span>
                    @if ($newOrderCount > 0)
                        <span class="nav-badge">{{ $newOrderCount }}</span>
                    @endif
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('supplier.orders.index') }}"
                    class="nav-link {{ request()->routeIs('supplier.orders*') && !request('status') ? 'active' : '' }}">
                    <i class="bi bi-receipt nav-link__icon"></i>
                    <span class="nav-link__text">All Orders</span>
                </a>
            </div>

        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user__avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="sidebar-user__info">
                    <div class="sidebar-user__name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user__role">Supplier</div>
                </div>
                <form method="POST" action="{{ route('vendor.logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-user__logout" title="Sign out">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ── Main wrapper ────────────────────────────────────────────────────── --}}
    <div class="main-wrapper">

        <header class="topbar">
            <div class="topbar__left">
                <button class="topbar__btn d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <span class="topbar__title">@yield('page-title', 'Dashboard')</span>
                @hasSection('breadcrumb')
                    <nav class="topbar__breadcrumb">
                        <span class="sep">›</span>
                        @yield('breadcrumb')
                    </nav>
                @endif
            </div>
            <div class="topbar__right">
                <a href="{{ route('supplier.orders.index', ['status' => 'submitted']) }}" class="topbar__btn"
                    title="New orders">
                    <i class="bi bi-bell"></i>
                    @if (($newOrderCount ?? 0) > 0)
                        <span class="notif-dot"></span>
                    @endif
                </a>
            </div>
        </header>

        @if (session()->hasAny(['success', 'error', 'warning', 'info']))
            <div class="px-4 pt-3" id="flash-area">
                @foreach (['success', 'error', 'warning', 'info'] as $type)
                    @if (session($type))
                        <div class="flash flash--{{ $type }}">
                            <i
                                class="bi bi-{{ $type === 'success' ? 'check-circle-fill' : ($type === 'error' ? 'exclamation-circle-fill' : ($type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) }}"></i>
                            <span>{{ session($type) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <main class="page-content">
            @yield('content')
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function openSidebar() {
            sidebar.classList.add('is-open');
            overlay.classList.add('is-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-open');
        }

        toggleBtn?.addEventListener('click', () =>
            sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar()
        );
        overlay?.addEventListener('click', closeSidebar);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        setTimeout(() => {
            document.querySelectorAll('#flash-area .flash').forEach(el => {
                el.style.transition = 'opacity .4s, transform .4s';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-4px)';
                setTimeout(() => el.remove(), 400);
            });
        }, 4000);
    </script>

    @stack('scripts')
</body>

</html>
