<?php

namespace App\Http\Controllers\Vendor;

// ═══════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/PurchaseRequestController.php
// Step 1 of vendor procurement: create & manage Purchase Requests
// ═══════════════════════════════════════════════════════════════════════════

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\VendorSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    private function vendor()
    {
        return auth()->user()->vendor;
    }

    public function index(Request $request)
    {
        $prs = PurchaseRequest::where('vendor_id', $this->vendor()->id)
            ->with(['supplier', 'items', 'requester'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('vendor.procurement.pr.index', compact('prs'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        return view('vendor.procurement.pr.create', compact('suppliers'));
    }

    /** AJAX: load products for selected supplier */
    public function supplierProducts(Supplier $supplier)
    {
        $products = $supplier->products()
            ->active()
            ->with('primaryImage')
            ->get(['id', 'name', 'sku', 'unit_price', 'unit_of_measure', 'minimum_order_qty', 'stock_available']);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'              => ['required', 'exists:suppliers,id'],
            'requested_delivery_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.supplier_product_id' => ['required', 'exists:supplier_products,id'],
            'items.*.quantity_requested'  => ['required', 'integer', 'min:1'],
            'items.*.notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $pr = DB::transaction(function () use ($request) {
            $pr = PurchaseRequest::create([
                'pr_number'               => PurchaseRequest::generateNumber(),
                'vendor_id'               => $this->vendor()->id,
                'supplier_id'             => $request->supplier_id,
                'requested_by'            => auth()->id(),
                'status'                  => $request->input('action') === 'submit' ? 'submitted' : 'draft',
                'requested_delivery_date' => $request->requested_delivery_date,
                'notes'                   => $request->notes,
                'submitted_at'            => $request->input('action') === 'submit' ? now() : null,
            ]);

            foreach ($request->items as $item) {
                $product = SupplierProduct::findOrFail($item['supplier_product_id']);
                PurchaseRequestItem::create([
                    'purchase_request_id'  => $pr->id,
                    'supplier_product_id'  => $product->id,
                    'product_name'         => $product->name,
                    'sku'                  => $product->sku,
                    'unit_price'           => $product->unit_price,
                    'quantity_requested'   => $item['quantity_requested'],
                    'unit_of_measure'      => $product->unit_of_measure,
                    'notes'                => $item['notes'] ?? null,
                ]);
            }

            return $pr;
        });

        $message = $pr->status === 'submitted'
            ? "PR #{$pr->pr_number} submitted for approval."
            : "PR #{$pr->pr_number} saved as draft.";

        return redirect()->route('vendor.procurement.pr.show', $pr)->with('success', $message);
    }

    public function show(PurchaseRequest $pr)
    {
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);
        $pr->load(['supplier', 'items.product', 'requester', 'approver', 'purchaseOrder']);

        return view('vendor.procurement.pr.show', compact('pr'));
    }

    /** Submit a saved draft */
    public function submit(PurchaseRequest $pr)
    {
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);
        abort_if(!$pr->isDraft(), 422, 'Only draft PRs can be submitted.');

        $pr->update(['status' => 'submitted', 'submitted_at' => now()]);

        return back()->with('success', "PR #{$pr->pr_number} submitted for approval.");
    }

    /** Internal approval (vendor manager approves their own PR) */
    public function approve(PurchaseRequest $pr)
    {
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);
        abort_if($pr->status !== 'submitted', 422, 'PR is not in submitted state.');

        $pr->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "PR #{$pr->pr_number} approved. You can now generate a Purchase Order.");
    }

    /** Reject PR with reason */
    public function reject(Request $request, PurchaseRequest $pr)
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);

        $pr->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', "PR #{$pr->pr_number} rejected.");
    }

    public function cancel(PurchaseRequest $pr)
    {
        abort_if($pr->vendor_id !== $this->vendor()->id, 403);
        abort_if(!in_array($pr->status, ['draft', 'submitted']), 422, 'Cannot cancel at this stage.');

        $pr->update(['status' => 'cancelled']);
        return back()->with('success', "PR #{$pr->pr_number} cancelled.");
    }
}

