<?php

// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/Customer/DashboardController.php
// ════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;

        $stats = [
            'total_orders'    => Order::forCustomer($customer->id)->count(),
            'active_orders'   => Order::forCustomer($customer->id)->whereIn('status', ['paid','processing','shipped'])->count(),
            'completed_orders'=> Order::forCustomer($customer->id)->where('status','completed')->count(),
            'total_spent'     => Order::forCustomer($customer->id)->where('payment_status','paid')->sum('total_amount'),
        ];

        $recentOrders = Order::with(['vendor','items'])
            ->forCustomer($customer->id)
            ->latest()->limit(5)->get();

        $nearbyVendors = Vendor::active()
            ->with('products')
            ->when($customer->latitude && $customer->longitude,
                fn ($q) => $q->nearby($customer->latitude, $customer->longitude, 50)
            )
            ->limit(6)->get();

        $featuredProducts = Product::with(['vendor','primaryImage'])
            ->where('status','active')
            ->where('is_featured', true)
            ->latest()->limit(8)->get();

        return view('customer.dashboard.index', compact(
            'customer', 'stats', 'recentOrders',
            'nearbyVendors', 'featuredProducts'
        ));
    }
}
