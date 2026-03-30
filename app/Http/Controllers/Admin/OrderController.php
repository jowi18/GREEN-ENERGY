<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'vendor'])->latest();

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('vendor')) $query->where('vendor_id', $request->vendor);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name','like',"%{$request->search}%")
                         ->orWhere('last_name','like',"%{$request->search}%")
                  );
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => Order::count(),
            'pending'   => Order::where('status','pending')->count(),
            'completed' => Order::where('status','completed')->count(),
            'cancelled' => Order::where('status','cancelled')->count(),
            'revenue'   => Order::where('payment_status','paid')->sum('total_amount'),
            'today'     => Order::whereDate('created_at',today())->count(),
        ];

        $vendors = Vendor::where('status','active')->orderBy('business_name')->get(['id','business_name']);

        return view('admin.orders.index', compact('orders','stats','vendors'));
    }

    public function show(Order $order)
    {
        $order->load(['customer','vendor','items.product','items.productImage']);
        return view('admin.orders.show', compact('order'));
    }
}
