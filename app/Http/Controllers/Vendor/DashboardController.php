<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosTransaction;
use App\Models\Inventory;
use App\Models\ServiceRequest;
use App\Models\WarrantyRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $vendor = $user->isEmployee()
            ? $user->employee->vendor
            : $user->vendor;

        $vendorId = $vendor->id;

        // ── Summary cards ──────────────────────────────────────────────────
        $stats = [
            'total_products'       => $vendor->products()->where('status', 'active')->count(),
            'total_orders'         => Order::forVendor($vendorId)->count(),
            'low_stock_count'      => Inventory::forVendor($vendorId)->lowStock()->count(),
            'out_of_stock_count'   => Inventory::forVendor($vendorId)->outOfStock()->count(),
            'orders_pending'       => Order::forVendor($vendorId)->where('status', 'pending')->count(),
            'orders_today'         => Order::forVendor($vendorId)->whereDate('created_at', today())->count(),
            'pos_sales_today'      => PosTransaction::forVendor($vendorId)->today()->completed()->count(),
            'pos_revenue_today'    => PosTransaction::forVendor($vendorId)->today()->completed()->sum('total_amount'),
            'open_service_requests'=> ServiceRequest::forVendor($vendorId)->open()->count(),
            'open_warranty_claims' => WarrantyRequest::forVendor($vendorId)->open()->count(),
            'total_employees'      => $vendor->employees()->where('is_active', true)->count(),
        ];

        // ── Subscription status ────────────────────────────────────────────
        $subscription = $vendor->activeSubscription?->load('plan');

        // ── Sales chart: online orders + POS revenue last 30 days ─────────
        $salesChart = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as online_revenue'),
                DB::raw('COUNT(*) as online_count')
            )
            ->forVendor($vendorId)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $posChart = PosTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as pos_revenue'),
                DB::raw('COUNT(*) as pos_count')
            )
            ->forVendor($vendorId)
            ->completed()
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Merge into unified 30-day array
        $chartData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData->push([
                'date'           => $date,
                'label'          => now()->subDays($i)->format('M d'),
                'online_revenue' => (float) ($salesChart[$date]->online_revenue ?? 0),
                'pos_revenue'    => (float) ($posChart[$date]->pos_revenue ?? 0),
            ]);
        }

        // ── Recent orders (latest 8) ───────────────────────────────────────
        $recentOrders = Order::forVendor($vendorId)
            ->with('customer')
            ->latest()
            ->limit(8)
            ->get();

        // ── Low stock products ─────────────────────────────────────────────
        $lowStockItems = Inventory::forVendor($vendorId)
            ->lowStock()
            ->with('product')
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get();

        // ── Recent service requests ────────────────────────────────────────
        $recentServices = ServiceRequest::forVendor($vendorId)
            ->with('customer')
            ->latest()
            ->limit(5)
            ->get();

        return view('vendor.dashboard', compact(
            'vendor',
            'stats',
            'subscription',
            'chartData',
            'recentOrders',
            'lowStockItems',
            'recentServices'
        ));
    }
}
