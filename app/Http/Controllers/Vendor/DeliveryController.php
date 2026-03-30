<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Delivery list ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Delivery::forVendor($vendor->id)
            ->with(['order', 'assignedEmployee'])
            ->latest();

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        // Stats
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

    // ── Show single delivery ────────────────────────────────────────────────

    public function show(Delivery $delivery)
    {
        if ($delivery->vendor_id !== $this->vendor()->id) {
            abort(403);
        }

        $delivery->load(['order.items.product', 'assignedEmployee', 'assignedByUser']);

        return view('vendor.delivery.show', compact('delivery'));
    }

    // ── Update delivery status ──────────────────────────────────────────────

    public function updateStatus(Request $request, Delivery $delivery)
    {
        if ($delivery->vendor_id !== $this->vendor()->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,preparing,out_for_delivery,delivered,failed,returned',
        ]);

        $delivery->update([
            'status' => $request->status,
            'dispatched_at' => $request->status === 'out_for_delivery' ? now() : $delivery->dispatched_at,
            'delivered_at'  => $request->status === 'delivered' ? now() : $delivery->delivered_at,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $delivery->status_label]);
        }

        return back()->with('success', 'Delivery status updated.');
    }
}
