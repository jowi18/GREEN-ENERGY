<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\OrderManagementService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderManagementService $orderService
    ) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorizeOrder(Order $order): void
    {
        if ($order->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }

    // ── Order list ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Order::with(['customer', 'items', 'delivery'])
            ->forVendor($vendor->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name',  'like', "%{$request->search}%")
                  );
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending'    => Order::forVendor($vendor->id)->where('status', 'pending')->count(),
            'paid'       => Order::forVendor($vendor->id)->where('status', 'paid')->count(),
            'processing' => Order::forVendor($vendor->id)->where('status', 'processing')->count(),
            'shipped'    => Order::forVendor($vendor->id)->where('status', 'shipped')->count(),
            'completed'  => Order::forVendor($vendor->id)->where('status', 'completed')->count(),
            'cancelled'  => Order::forVendor($vendor->id)->where('status', 'cancelled')->count(),
            'revenue_today' => Order::forVendor($vendor->id)
                ->whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        return view('vendor.orders.index', compact('orders', 'stats'));
    }

    // ── Order detail ──────────────────────────────────────────────────────

    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['customer', 'items.product', 'delivery']);

        $allowedTransitions = $this->orderService->allowedTransitions($order->status);

        return view('vendor.orders.show', compact('order', 'allowedTransitions'));
    }

    // ── Update status (AJAX) ──────────────────────────────────────────────

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $request->validate([
            'status' => ['required', 'string'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->orderService->updateStatus(
                $order,
                $request->status,
                $request->notes
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'new_status'  => $updated->status,
                    'message'     => "Order #{$order->order_number} moved to " . ucfirst($updated->status) . '.',
                    'transitions' => $this->orderService->allowedTransitions($updated->status),
                ]);
            }

            return back()->with('success', "Order #{$order->order_number} updated to " . ucfirst($updated->status) . '.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Assign delivery ───────────────────────────────────────────────────

    public function assignDelivery(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $data = $request->validate([
            'courier_name'    => ['required', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'estimated_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $delivery = $this->orderService->assignDelivery(
                order:          $order,
                courierName:    $data['courier_name'],
                trackingNumber: $data['tracking_number'] ?? null,
                estimatedDate:  $data['estimated_date']  ?? null,
                notes:          $data['notes']           ?? null,
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => "Delivery assigned for Order #{$order->order_number}.",
                    'delivery' => $delivery,
                ]);
            }

            return back()->with('success', "Delivery assigned for Order #{$order->order_number}.");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Mark delivered ────────────────────────────────────────────────────

    public function markDelivered(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if (! $order->delivery) {
            return response()->json(['success' => false, 'message' => 'No delivery record found.'], 422);
        }

        try {
            $this->orderService->updateDeliveryStatus(
                $order->delivery,
                'delivered',
                $request->input('notes')
            );

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} marked as delivered.",
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Print invoice (standalone page) ──────────────────────────────────

    public function invoice(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['customer', 'items.product', 'vendor', 'delivery']);

        return view('vendor.orders.invoice', compact('order'));
    }
}
