<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Supplier/SupplierOrderController.php
// Handles PO approval/rejection, processing, shipping, delivery marking
// ═══════════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierOrderController extends Controller
{
    private function supplier()
    {
        return auth()->user()->supplier;
    }

    private function order(int $id): PurchaseOrder
    {
        return PurchaseOrder::where('supplier_id', $this->supplier()->id)
            ->findOrFail($id);
    }

    public function index(Request $request)
    {
        $orders = PurchaseOrder::where('supplier_id', $this->supplier()->id)
            ->with(['vendor', 'items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('supplier.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = $this->order($id);
        $order->load(['vendor', 'items.product', 'purchaseRequest']);

        return view('supplier.orders.show', compact('order'));
    }

    /** Supplier approves a submitted PO */
    public function approve(Request $request, int $id)
    {
        $order = $this->order($id);
        abort_if($order->status !== 'submitted', 422, 'Order is not in submitted state.');

        $order->update([
            'status'                => 'approved',
            'supplier_responded_at' => now(),
            'supplier_notes'        => $request->input('supplier_notes'),
        ]);

        // Notify vendor (queue a notification / event here)
        // event(new PurchaseOrderApproved($order));

        return back()->with('success', "PO #{$order->po_number} approved.");
    }

    /** Supplier rejects a submitted PO */
    public function reject(Request $request, int $id)
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $order = $this->order($id);
        abort_if($order->status !== 'submitted', 422, 'Order is not in submitted state.');

        $order->update([
            'status'                => 'rejected',
            'rejection_reason'      => $request->rejection_reason,
            'supplier_responded_at' => now(),
        ]);

        return back()->with('success', "PO #{$order->po_number} rejected.");
    }

    /** Mark order as processing (preparing shipment) */
    public function markProcessing(int $id)
    {
        $order = $this->order($id);
        abort_if($order->status !== 'approved', 422, 'Order must be approved first.');
        $order->update(['status' => 'processing']);

        return back()->with('success', 'Order marked as processing.');
    }

    /** Mark order as shipped */
    public function markShipped(Request $request, int $id)
    {
        $request->validate([
            'tracking_number'      => ['nullable', 'string', 'max:100'],
            'courier'              => ['nullable', 'string', 'max:100'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $order = $this->order($id);
        abort_if($order->status !== 'processing', 422, 'Order must be in processing state.');

        $order->update([
            'status'                 => 'shipped',
            'tracking_number'        => $request->tracking_number,
            'courier'                => $request->courier,
            'expected_delivery_date' => $request->expected_delivery_date,
            'shipped_at'             => now(),
        ]);

        // event(new PurchaseOrderShipped($order));

        return back()->with('success', "PO #{$order->po_number} marked as shipped.");
    }

    /** Mark order as delivered (supplier side) */
    public function markDelivered(Request $request, int $id)
    {
        $order = $this->order($id);
        abort_if($order->status !== 'shipped', 422, 'Order must be in shipped state.');

        $order->update([
            'status'               => 'delivered',
            'actual_delivery_date' => now()->toDateString(),
            'delivered_at'         => now(),
        ]);

        // event(new PurchaseOrderDelivered($order));

        return back()->with('success', "PO #{$order->po_number} marked as delivered. Awaiting vendor confirmation.");
    }
}
