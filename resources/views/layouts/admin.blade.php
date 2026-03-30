<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Admin Panel</title>

    {{-- Vendor CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    {{-- Admin theme — place admin.css in public/css/admin.css --}}
    <link href="{{ asset('layout_css/admin.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ════════════════════════════════════════════════ --}}
    {{-- SIDEBAR                                          --}}
    {{-- ════════════════════════════════════════════════ --}}
    <aside class="sidebar" id="sidebar">

        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand__icon">S</div>
            <div class="sidebar-brand__text">
                <span class="sidebar-brand__name">{{ config('app.name', 'SolarHub') }}</span>
                <span class="sidebar-brand__sub">Admin Panel</span>
            </div>
        </a>

        <nav class="sidebar-nav">

            <p class="nav-section">Overview</p>

            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2 nav-link__icon"></i>
                    <span class="nav-link__text">Dashboard</span>
                </a>
            </div>

            <p class="nav-section">Vendors</p>

            <div class="nav-item">
                @php $pendingCount = \App\Models\Vendor::whereIn('status',['pending','under_review'])->count(); @endphp
                <a href="{{ route('admin.vendors.pending') }}"
                    class="nav-link {{ request()->routeIs('admin.vendors.pending*') ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split nav-link__icon"></i>
                    <span class="nav-link__text">Approval Queue</span>
                    @if ($pendingCount > 0)
                        <span class="nav-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.vendors.index') }}"
                    class="nav-link {{ request()->routeIs('admin.vendors.index') ? 'active' : '' }}">
                    <i class="bi bi-shop nav-link__icon"></i>
                    <span class="nav-link__text">All Vendors</span>
                </a>
            </div>

            <p class="nav-section">Users</p>

            <div class="nav-item">
                <a href="{{ route('admin.customers.index') }}"
                    class="nav-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
                    <i class="bi bi-people nav-link__icon"></i>
                    <span class="nav-link__text">Customers</span>
                </a>
            </div>

            <p class="nav-section">Platform</p>

            <div class="nav-item">
                <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card nav-link__icon"></i>
                    <span class="nav-link__text">Subscriptions</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-bar-chart-line nav-link__icon"></i>
                    <span class="nav-link__text">Analytics</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link">
                    <i class="bi bi-gear nav-link__icon"></i>
                    <span class="nav-link__text">Settings</span>
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
                    <div class="sidebar-user__role">
                        {{ auth()->user()->admin?->is_super_admin ? 'Super Admin' : 'Admin' }}
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-user__logout" title="Sign out">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ════════════════════════════════════════════════ --}}
    {{-- MAIN WRAPPER                                     --}}
    {{-- ════════════════════════════════════════════════ --}}
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
                <a href="{{ route('admin.vendors.pending') }}" class="topbar__btn" title="Pending approvals">
                    <i class="bi bi-bell"></i>
                    @if (($pendingCount ?? 0) > 0)
                        <span class="notif-dot"></span>
                    @endif
                </a>
                <a href="#" class="topbar__btn" title="Help">
                    <i class="bi bi-question-circle"></i>
                </a>
            </div>
        </header>

        @if (session()->hasAny(['success', 'error', 'warning', 'info']))
            <div class="px-4 pt-3" id="flash-area">
                @if (session('success'))
                    <div class="flash flash--success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="flash flash--error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="flash flash--warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif
                @if (session('info'))
                    <div class="flash flash--info">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif
            </div>
        @endif

        <main class="page-content">
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
        /* ── Sidebar toggle ─────────────────────────────── */
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

        /* ── jQuery CSRF ────────────────────────────────── */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* ── Auto-dismiss flash (4 s) ───────────────────── */
        setTimeout(() => {
            document.querySelectorAll('#flash-area .flash').forEach(el => {
                el.style.transition = 'opacity 0.4s, transform 0.4s';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-4px)';
                setTimeout(() => el.remove(), 400);
            });
        }, 4000);

        /* ── Chart.js global defaults ───────────────────── */
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#8fa695';
        }
    </script>

    @stack('scripts')
</body>

</html>
