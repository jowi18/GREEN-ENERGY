<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Summary cards ──────────────────────────────────────────────────
        $stats = [
            'total_vendors'         => Vendor::count(),
            'active_vendors'        => Vendor::where('status', 'active')->count(),
            'pending_approvals'     => Vendor::whereIn('status', ['pending', 'under_review'])->count(),
            'total_customers'       => Customer::count(),
            'active_subscriptions'  => Subscription::where('status', 'active')
                                            ->where('expires_at', '>', now())->count(),
            'total_orders'          => Order::count(),
            'orders_today'          => Order::whereDate('created_at', today())->count(),
            'revenue_this_month'    => Subscription::where('status', 'active')
                                            ->whereMonth('created_at', now()->month)
                                            ->sum('amount_paid'),
        ];

        // ── Pending approval queue (latest 5) ─────────────────────────────
        $pendingVendors = Vendor::with('user')
            ->whereIn('status', ['pending', 'under_review'])
            ->latest()
            ->limit(5)
            ->get();

        // ── Subscriptions expiring within 7 days ──────────────────────────
        $expiringSoon = Subscription::with(['vendor', 'plan'])
            ->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->orderBy('expires_at')
            ->limit(5)
            ->get();

        // ── Monthly subscription revenue for chart (last 6 months) ────────
        $revenueChart = Subscription::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount_paid) as total')
            )
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'label'  => date('M Y', mktime(0, 0, 0, $r->month, 1, $r->year)),
                'total'  => (float) $r->total,
            ]);

        // ── New vendors per month (last 6 months) ─────────────────────────
        $vendorGrowth = Vendor::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'label' => date('M Y', mktime(0, 0, 0, $r->month, 1, $r->year)),
                'total' => (int) $r->total,
            ]);

        // ── Recent orders across platform ─────────────────────────────────
        $recentOrders = Order::with(['customer', 'vendor'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingVendors',
            'expiringSoon',
            'revenueChart',
            'vendorGrowth',
            'recentOrders'
        ));
    }
}
