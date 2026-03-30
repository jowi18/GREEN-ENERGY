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
    <style>
        .vd-brand__logo {
            /* ADD: ensure images don't bleed outside the circle */
            overflow: hidden;
            flex-shrink: 0;
        }

        /* ── User avatar: support real photo ── */
        .vd-user__avatar {
            /* ADD: same — photos need clipping */
            overflow: hidden;
            flex-shrink: 0;
        }
    </style>

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

        {{-- Brand / shop identity --}}
        <a href="{{ route('vendor.dashboard') }}" class="vd-brand">
            <div class="vd-brand__logo"
                style="{{ $vendor?->shop_logo ? 'background:transparent;padding:0;overflow:hidden;' : '' }}">
                @if($vendor?->shop_logo)
                    <img src="{{ asset('storage/' . $vendor->shop_logo) }}"
                        alt="{{ $vendor->business_name }}"
                        style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                @else
                    {{ strtoupper(substr($vendor?->business_name ?? 'V', 0, 1)) }}
                @endif
            </div>
            <div class="vd-brand__info" style="min-width:0;">
                <span class="vd-brand__name"
                    style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $vendor?->business_name ?? config('app.name') }}
                </span>
                @if($vendor?->tagline)
                    <span class="vd-brand__sub"
                        style="display:block;white-space:nowrap;overflow:hidden;
                                text-overflow:ellipsis;max-width:140px;font-style:italic;">
                        {{ $vendor->tagline }}
                    </span>
                @else
                    <span class="vd-brand__sub">Vendor Portal</span>
                @endif
            </div>
        </a>

        {{-- Open / closed status strip --}}
        @if($vendor?->operating_hours && $vendor->show_operating_hours)
            @php
                $isOpenNow = $vendor->is_open_now;
                $override  = $vendor->is_open_now_override;
                $todayH    = $vendor->today_hours;
            @endphp
            <div style="margin:0 .75rem .5rem;padding:.3rem .75rem;border-radius:var(--r-sm);
                        font-size:.68rem;font-weight:700;display:flex;align-items:center;gap:.4rem;
                        {{ $override
                            ? 'background:rgba(202,138,4,.12);color:#b45309;border:1px solid rgba(202,138,4,.2);'
                            : ($isOpenNow
                                ? 'background:rgba(21,128,61,.1);color:var(--g-600);border:1px solid rgba(21,128,61,.15);'
                                : 'background:rgba(0,0,0,.06);color:var(--tx-muted);border:1px solid var(--card-border);') }}">
                @if($override)
                    <i class="bi bi-clock" style="font-size:.6rem;"></i> Temporarily Closed
                @elseif($isOpenNow)
                    <i class="bi bi-circle-fill" style="font-size:.45rem;color:var(--g-500);"></i> Open Now
                    @if(!empty($todayH['to']))
                        <span style="font-weight:400;opacity:.75;">until {{ date('g:i A', strtotime($todayH['to'])) }}</span>
                    @endif
                @else
                    <i class="bi bi-circle" style="font-size:.45rem;"></i> Closed
                @endif
            </div>
        @endif

        {{-- Subscription status chip --}}
        @if($sub)
            <div class="vd-sub-chip">
                <span class="vd-sub-chip__dot vd-sub-chip__dot--{{ $chipState }}"></span>
                <span class="vd-sub-chip__label">{{ $sub->plan->name }}</span>
                <span class="vd-sub-chip__days" style="{{ $daysLeft <= 3 ? 'color:#ef4444;font-weight:800;' : '' }}">
                    {{ $daysLeft }}d left
                </span>
            </div>
        @else
            <a href="{{ route('vendor.subscription.index') }}"
            style="display:flex;align-items:center;gap:.5rem;
                    margin:.25rem .75rem .5rem;padding:.35rem .75rem;border-radius:var(--r-sm);
                    background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);
                    font-size:.7rem;font-weight:700;color:#dc2626;text-decoration:none;">
                <i class="bi bi-exclamation-circle-fill" style="font-size:.75rem;"></i>
                No active subscription
                <i class="bi bi-arrow-right ms-auto" style="font-size:.65rem;"></i>
            </a>
        @endif

        {{-- Service area --}}
        @if($vendor?->service_area)
            <div style="margin:.1rem .75rem .6rem;font-size:.67rem;color:var(--tx-muted);
                        display:flex;align-items:flex-start;gap:.3rem;line-height:1.4;">
                <i class="bi bi-map" style="font-size:.65rem;flex-shrink:0;margin-top:.1rem;"></i>
                <span style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ $vendor->service_area }}
                </span>
            </div>
        @endif

        {{-- Navigation --}}
        @php
            $isOwner = auth()->user()->isVendor();
            $employee = auth()->user()->employee ?? null;
            $navModules = $isOwner ? null : $employee?->getAccessibleModules();
            $canSee = fn(string $module): bool => $isOwner || ($navModules && $navModules->contains($module));

            // Badge counts — computed lazily only when needed
            $lowStock =
                $canSee('inventory') && $vendor
                    ? \App\Models\Inventory::forVendor($vendor->id)->lowStock()->count()
                    : 0;
            $pendingOrders =
                $canSee('orders') && $vendor
                    ? \App\Models\Order::forVendor($vendor->id)->where('status', 'pending')->count()
                    : 0;
            $openServices =
                $canSee('services') && $vendor
                    ? \App\Models\ServiceRequest::forVendor($vendor->id)->open()->count()
                    : 0;
            $chatUnread =
                ($canSee('storefront') || $isOwner) && $vendor
                    ? \App\Models\ChatRoom::forVendor($vendor->id)->sum('vendor_unread')
                    : 0;
            $pendingLeaves =
                $canSee('employees') && $vendor
                    ? \App\Models\HrLeaveRequest::forVendor($vendor->id)->pending()->count()
                    : 0;
            $pendingOT =
                $canSee('employees') && $vendor
                    ? \App\Models\HrOvertimeRequest::forVendor($vendor->id)->pending()->count()
                    : 0;
            $hrAlerts = $pendingLeaves + $pendingOT;
            $unreplied     = ($canSee('reviews') || $isOwner) && $vendor ?
                    \App\Models\Review::forVendor($vendor->id)->whereNull('vendor_reply')->count() : 0;
        @endphp

        <nav class="vd-nav">

            {{-- ── Overview ── --}}
            <p class="vd-nav__section">Overview</p>
            <div class="vd-nav__item">
                <a href="{{ route('vendor.dashboard') }}"
                    class="vd-nav__link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2 vd-nav__icon"></i>
                    <span class="vd-nav__text">Dashboard</span>
                </a>
            </div>

            {{-- ── Store ── --}}
            @if ($canSee('products') || $canSee('inventory') || $canSee('pos'))
                <p class="vd-nav__section">Store</p>

                @if ($canSee('products'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.products.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.products*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam vd-nav__icon"></i>
                            <span class="vd-nav__text">Products</span>
                        </a>
                    </div>
                @endif

                @if ($canSee('inventory'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.inventory.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.inventory*') ? 'active' : '' }}">
                            <i class="bi bi-archive vd-nav__icon"></i>
                            <span class="vd-nav__text">Inventory</span>
                            @if ($lowStock > 0)
                                <span class="vd-nav__badge vd-nav__badge--yellow">{{ $lowStock }}</span>
                            @endif
                        </a>
                    </div>
                @endif

                @if ($canSee('pos'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.pos.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.pos*') ? 'active' : '' }}">
                            <i class="bi bi-shop vd-nav__icon"></i>
                            <span class="vd-nav__text">POS System</span>
                        </a>
                    </div>
                @endif
            @endif

            {{-- ── Orders ── --}}
            @if ($canSee('orders') || $canSee('delivery'))
                <p class="vd-nav__section">Orders</p>

                @if ($canSee('orders'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.orders.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}">
                            <i class="bi bi-bag-check vd-nav__icon"></i>
                            <span class="vd-nav__text">Online Orders</span>
                            @if ($pendingOrders > 0)
                                <span class="vd-nav__badge">{{ $pendingOrders }}</span>
                            @endif
                        </a>
                    </div>
                @endif

                @if ($canSee('delivery'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.delivery.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.delivery*') ? 'active' : '' }}">
                            <i class="bi bi-truck vd-nav__icon"></i>
                            <span class="vd-nav__text">Delivery</span>
                        </a>
                    </div>
                @endif
            @endif


            {{-- ── Procurement ── --}}
            @if ($canSee('procurement'))
                <p class="vd-nav__section">Procurement</p>

                @if ($canSee('procurement'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.suppliers.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.suppliers*') ? 'active' : '' }}">
                            <i class="bi bi-building vd-nav__icon"></i>
                            <span class="vd-nav__text">Suppliers</span>

                        </a>
                    </div>

                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.procurement.pr.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.procurement.pr*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text vd-nav__icon"></i>
                            <span class="vd-nav__text">Purchase Requests</span>

                        </a>
                    </div>

                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.procurement.po.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.procurement.po*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-check vd-nav__icon"></i>
                            <span class="vd-nav__text">Purchase Orders</span>

                        </a>
                    </div>
                @endif



            @endif

            {{-- ── Services ── --}}
            @if ($canSee('services') || $canSee('warranty'))
                <p class="vd-nav__section">Services</p>

                @if ($canSee('services'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.services.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.services*') || request()->routeIs('vendor.service-catalog*') ? 'active' : '' }}">
                            <i class="bi bi-tools vd-nav__icon"></i>
                            <span class="vd-nav__text">Service Requests</span>
                            @if ($openServices > 0)
                                <span class="vd-nav__badge">{{ $openServices }}</span>
                            @endif
                        </a>
                    </div>

                    {{-- Service Catalog (owner only — managing available services) --}}
                    @if ($isOwner)
                        <div class="vd-nav__item">
                            <a href="{{ route('vendor.service-catalog.index') }}"
                                class="vd-nav__link {{ request()->routeIs('vendor.service-catalog*') ? 'active' : '' }}"
                                style="padding-left:2.1rem;">
                                <i class="bi bi-list-check vd-nav__icon" style="font-size:.8rem;"></i>
                                <span class="vd-nav__text" style="font-size:.82rem;">My Service Catalog</span>
                            </a>
                        </div>
                    @endif

                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.solar.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.solar*') ? 'active' : '' }}">
                            <i class="bi bi-lightning-charge vd-nav__icon"></i>
                            <span class="vd-nav__text">Solar Installation</span>
                        </a>
                    </div>
                @endif

                @if ($canSee('warranty'))
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.warranty.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.warranty*') ? 'active' : '' }}">
                            <i class="bi bi-shield-check vd-nav__icon"></i>
                            <span class="vd-nav__text">Warranty Claims</span>
                        </a>
                    </div>
                @endif
            @endif

            {{-- ── HR Module ── --}}
            @if ($canSee('employees') || $isOwner)
                <p class="vd-nav__section">HR & People</p>

                @if ($isOwner)
                    {{-- Owner: full HR management --}}
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.employees.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.employees*') ? 'active' : '' }}">
                            <i class="bi bi-person-plus vd-nav__icon"></i>
                            <span class="vd-nav__text">Employees</span>
                        </a>
                    </div>
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.hr.employees.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.hr.employees*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill vd-nav__icon"></i>
                            <span class="vd-nav__text">HR Records</span>
                        </a>
                    </div>
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.hr.payroll.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.hr.payroll*') ? 'active' : '' }}">
                            <i class="bi bi-currency-exchange vd-nav__icon"></i>
                            <span class="vd-nav__text">Payroll</span>
                        </a>
                    </div>
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.hr.attendance.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.hr.attendance*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check vd-nav__icon"></i>
                            <span class="vd-nav__text">Attendance</span>
                        </a>
                    </div>
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.hr.leaves.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.hr.leaves*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-x vd-nav__icon"></i>
                            <span class="vd-nav__text">Leave & Overtime</span>
                            @if ($hrAlerts > 0)
                                <span class="vd-nav__badge">{{ $hrAlerts }}</span>
                            @endif
                        </a>
                    </div>
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.roles.index') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.roles*') ? 'active' : '' }}">
                            <i class="bi bi-person-lock vd-nav__icon"></i>
                            <span class="vd-nav__text">Roles & Permissions</span>
                        </a>
                    </div>
                @else
                    {{-- Employee: self-service HR only --}}
                    <div class="vd-nav__item">
                        <a href="{{ route('vendor.hr.self.dashboard') }}"
                            class="vd-nav__link {{ request()->routeIs('vendor.hr.self*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history vd-nav__icon"></i>
                            <span class="vd-nav__text">My HR (Time & Payslip)</span>
                        </a>
                    </div>
                @endif
            @endif


            @if ($canSee('reviews') || $isOwner)
                <p class="vd-nav__section">Feedback</p>
                <div class="vd-nav__item">
                    <a href="{{ route('vendor.reviews.index') }}"
                        class="vd-nav__link {{ request()->routeIs('vendor.reviews') ? 'active' : '' }}">
                        <i class="bi bi-star vd-nav__icon"></i>
                        <span class="vd-nav__text">Reviews & Feedback</span>
                        @php $unreplied = \App\Models\Review::forVendor($vendor->id)->whereNull('vendor_reply')->count(); @endphp
                        @if ($unreplied > 0)
                            <span class="vd-nav__badge vd-nav__badge--yellow">{{ $unreplied }}</span>
                        @endif
                    </a>
                </div>
            @endif

            {{-- ── Account ── --}}
            <p class="vd-nav__section">Account</p>

            @if ($isOwner)
                <div class="vd-nav__item">
                    <a href="{{ route('vendor.subscription.index') }}"
                        class="vd-nav__link {{ request()->routeIs('vendor.subscription*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card vd-nav__icon"></i>
                        <span class="vd-nav__text">Subscription</span>
                    </a>
                </div>
            @endif

            @if ($canSee('chat') || $isOwner)
                <div class="vd-nav__item">
                    <a href="{{ route('vendor.chat.index') }}"
                        class="vd-nav__link {{ request()->routeIs('vendor.chat*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots vd-nav__icon"></i>
                        <span class="vd-nav__text">Customer Chat</span>
                        @if ($chatUnread > 0)
                            <span class="vd-nav__badge">{{ $chatUnread }}</span>
                        @endif
                    </a>
                </div>
            @endif

            @if ($isOwner)
                <div class="vd-nav__item">
                    <a href="{{ route('vendor.chat.predefined') }}"
                        class="vd-nav__link {{ request()->routeIs('vendor.chat.predefined*') ? 'active' : '' }}"
                        style="padding-left:2.1rem;">
                        <i class="bi bi-lightning vd-nav__icon" style="font-size:.8rem;"></i>
                        <span class="vd-nav__text" style="font-size:.82rem;">Quick Reply Templates</span>
                    </a>
                </div>
                <div class="vd-nav__item">
                    <a href="{{ route('vendor.settings.index') }}" class="vd-nav__link">
                        <i class="bi bi-gear vd-nav__icon"></i>
                        <span class="vd-nav__text">Store Settings</span>
                    </a>
                </div>
            @endif

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
