<?php


// ═══════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/PurchaseOrderController.php
// Step 2 & 3: Convert approved PR → PO, then confirm delivery → stock
// ═══════════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\StockMovement;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function vendor()
    {
        return auth()->user()->vendor;
    }

    public function index(Request $request)
    {
        $orders = PurchaseOrder::where('vendor_id', $this->vendor()->id)
            ->with(['supplier', 'items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('vendor.procurement.po.index', compact('orders'));
    }

    /** Generate a PO from an approved PR */
    public function createFromPR(PurchaseRequest $pr)
    {
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);
        abort_if(!$pr->isApproved(), 422, 'PR must be approved before generating a PO.');
        abort_if($pr->isConverted(), 422, 'A PO already exists for this PR.');

        $pr->load(['supplier', 'items.product']);

        return view('vendor.procurement.po.create', compact('pr'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_request_id'    => ['required', 'exists:purchase_requests,id'],
            'delivery_address'       => ['nullable', 'string', 'max:1000'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'vendor_notes'           => ['nullable', 'string', 'max:2000'],
            'tax_amount'             => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.supplier_product_id' => ['required', 'exists:supplier_products,id'],
            'items.*.quantity_ordered'    => ['required', 'integer', 'min:1'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'items.*.notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $pr = PurchaseRequest::where('vendor_id', $this->vendor()->id)
            ->findOrFail($request->purchase_request_id);

        $order = DB::transaction(function () use ($request, $pr) {
            $taxAmount    = (float) ($request->tax_amount    ?? 0);
            $shippingCost = (float) ($request->shipping_cost ?? 0);
            $subtotal     = 0;

            $order = PurchaseOrder::create([
                'po_number'              => PurchaseOrder::generateNumber(),
                'purchase_request_id'    => $pr->id,
                'vendor_id'              => $this->vendor()->id,
                'supplier_id'            => $pr->supplier_id,
                'created_by'             => auth()->id(),
                'status'                 => 'draft',
                'tax_amount'             => $taxAmount,
                'shipping_cost'          => $shippingCost,
                'currency'               => 'PHP',
                'delivery_address'       => $request->delivery_address,
                'expected_delivery_date' => $request->expected_delivery_date,
                'vendor_notes'           => $request->vendor_notes,
            ]);

            foreach ($request->items as $item) {
                $product   = SupplierProduct::findOrFail($item['supplier_product_id']);
                $lineTotal = (float) $item['unit_price'] * (int) $item['quantity_ordered'];
                $subtotal += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id'    => $order->id,
                    'supplier_product_id'  => $product->id,
                    'product_name'         => $product->name,
                    'sku'                  => $product->sku,
                    'unit_price'           => $item['unit_price'],
                    'quantity_ordered'     => $item['quantity_ordered'],
                    'unit_of_measure'      => $product->unit_of_measure,
                    'line_total'           => $lineTotal,
                    'notes'                => $item['notes'] ?? null,
                ]);
            }

            $order->update([
                'subtotal'     => $subtotal,
                'total_amount' => $subtotal + $taxAmount + $shippingCost,
            ]);

            // Mark PR as converted
            $pr->update(['status' => 'converted']);

            return $order;
        });

        return redirect()->route('vendor.procurement.po.show', $order)
            ->with('success', "PO #{$order->po_number} created from PR #{$pr->pr_number}.");
    }

    public function show(PurchaseOrder $order)
    {
        abort_if($order->vendor_id !== $this->vendor()->id, 403);
        $order->load(['supplier', 'items.product', 'purchaseRequest', 'creator']);

        return view('vendor.procurement.po.show', compact('order'));
    }

    /** Vendor submits PO to supplier */
    public function submit(PurchaseOrder $order)
    {
        abort_if($order->vendor_id !== $this->vendor()->id, 403);
        abort_if(!$order->isDraft(), 422, 'Only draft POs can be submitted.');

        $order->update(['status' => 'submitted', 'submitted_at' => now()]);

        // event(new PurchaseOrderSubmittedToSupplier($order));

        return back()->with('success', "PO #{$order->po_number} sent to supplier.");
    }

    /**
     * ── Step 3: Vendor confirms delivery and triggers inventory update ──
     *
     * For each PO item the vendor receives, the system:
     * 1. Records quantity_received on the PO item
     * 2. Finds or creates the matching vendor Product (by supplier_product_id linkage)
     * 3. Upserts Inventory record
     * 4. Creates a StockMovement audit trail
     */
    public function confirmDelivery(Request $request, PurchaseOrder $order)
    {
        abort_if($order->vendor_id !== $this->vendor()->id, 403);
        abort_if($order->status !== 'delivered', 422, 'Order must be marked as delivered by supplier first.');

        $request->validate([
            'items'                    => ['required', 'array'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity_received'      => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $order) {
            foreach ($request->items as $itemData) {
                /** @var \App\Models\PurchaseOrderItem $poItem */
                $poItem = $order->items()->findOrFail($itemData['purchase_order_item_id']);
                $qtyReceived = (int) $itemData['quantity_received'];

                $poItem->update(['quantity_received' => $qtyReceived]);

                if ($qtyReceived <= 0) continue;

                // ── Find matching vendor Product ─────────────────────────
                // Convention: supplier_product is linked to a vendor Product
                // via the same name+sku. Adjust this lookup to your own linking
                // strategy (e.g., a supplier_product_id foreign key on products).
                $vendorProduct = Product::where('vendor_id', $order->vendor_id)
                    ->where(function ($q) use ($poItem) {
                        $q->where('sku', $poItem->sku)
                          ->orWhere('name', $poItem->product_name);
                    })
                    ->first();

                // If no matching product exists, skip inventory (or auto-create — your call)
                if (!$vendorProduct) continue;

                // ── Upsert Inventory ─────────────────────────────────────
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $vendorProduct->id, 'vendor_id' => $order->vendor_id],
                    [
                        'quantity_on_hand'  => 0,
                        'quantity_reserved' => 0,
                        'unit_of_measure'   => $poItem->unit_of_measure,
                    ]
                );

                $before = $inventory->quantity_on_hand;
                $after  = $before + $qtyReceived;

                $inventory->update([
                    'quantity_on_hand'  => $after,
                    'last_stock_update' => now(),
                ]);

                // ── Create StockMovement ─────────────────────────────────
                StockMovement::create([
                    'inventory_id'    => $inventory->id,
                    'product_id'      => $vendorProduct->id,
                    'vendor_id'       => $order->vendor_id,
                    'movement_type'   => 'purchase',
                    'quantity_change' => $qtyReceived,
                    'quantity_before' => $before,
                    'quantity_after'  => $after,
                    'reference_type'  => PurchaseOrder::class,
                    'reference_id'    => $order->id,
                    'notes'           => "Delivery confirmed for PO #{$order->po_number}",
                    'performed_by'    => auth()->id(),
                ]);
            }

            $order->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);
        });

        return redirect()->route('vendor.procurement.po.show', $order)
            ->with('success', "Delivery confirmed. Inventory has been updated automatically.");
    }

    public function cancel(Request $request, PurchaseOrder $order)
    {
        abort_if($order->vendor_id !== $this->vendor()->id, 403);
        abort_if(!in_array($order->status, ['draft', 'submitted']), 422, 'Cannot cancel at this stage.');

        $order->update(['status' => 'cancelled']);
        return back()->with('success', "PO #{$order->po_number} cancelled.");
    }
}
