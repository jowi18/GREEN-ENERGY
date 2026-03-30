<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\OrderManagementService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly OrderManagementService $orderService
    ) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Delivery listing (all deliveries across orders) ─────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Delivery::with(['order.customer', 'assignedEmployee'])
            ->forVendor($vendor->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by tracking number or order number
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tracking_number', 'like', "%{$request->search}%")
                  ->orWhereHas('order', fn ($oq) =>
                      $oq->where('order_number', 'like', "%{$request->search}%")
                  )
                  ->orWhereHas('order.customer', fn ($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name', 'like', "%{$request->search}%")
                  );
            });
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $deliveries = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending'          => Delivery::forVendor($vendor->id)->where('status', 'pending')->count(),
            'preparing'        => Delivery::forVendor($vendor->id)->where('status', 'preparing')->count(),
            'out_for_delivery' => Delivery::forVendor($vendor->id)->where('status', 'out_for_delivery')->count(),
            'delivered'        => Delivery::forVendor($vendor->id)->where('status', 'delivered')->count(),
            'failed'           => Delivery::forVendor($vendor->id)->where('status', 'failed')->count(),
            'returned'         => Delivery::forVendor($vendor->id)->where('status', 'returned')->count(),
        ];

        return view('vendor.delivery.index', compact('deliveries', 'stats'));
    }

    // ── Single delivery detail ──────────────────────────────────────────

    public function show(Delivery $delivery)
    {
        if ($delivery->vendor_id !== $this->vendor()->id) {
            abort(403);
        }

        $delivery->load(['order.customer', 'order.items.product', 'assignedEmployee', 'assignedByUser']);

        return view('vendor.delivery.show', compact('delivery'));
    }

    // ── Update delivery status (AJAX) ───────────────────────────────────

    public function updateStatus(Request $request, Delivery $delivery)
    {
        if ($delivery->vendor_id !== $this->vendor()->id) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:pending,preparing,out_for_delivery,delivered,failed,returned'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->orderService->updateDeliveryStatus(
                $delivery,
                $request->status,
                $request->notes
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => "Delivery {$delivery->tracking_number} updated to " . $updated->status_label . '.',
                    'new_status' => $updated->status,
                    'badge'      => $updated->status_badge_class,
                    'label'      => $updated->status_label,
                ]);
            }

            return back()->with('success', "Delivery updated to {$updated->status_label}.");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
