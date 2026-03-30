<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\WarrantyRequest;
use App\Services\WarrantyService;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function __construct(private readonly WarrantyService $warranty) {}

    private function customer() { return auth()->user()->customer; }

    // ── My warranty claims ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $customer = $this->customer();

        $query = WarrantyRequest::with(['product', 'orderItem', 'vendor'])
            ->forCustomer($customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $claims = $query->latest()->paginate(15)->withQueryString();

        return view('customer.warranty.index', compact('claims'));
    }

    // ── Show claim detail ─────────────────────────────────────────────────

    public function show(WarrantyRequest $warrantyRequest)
    {
        if ($warrantyRequest->customer_id !== $this->customer()->id) abort(403);
        $warrantyRequest->load(['product', 'orderItem.order', 'vendor', 'technician']);

        return view('customer.warranty.show', compact('warrantyRequest'));
    }

    // ── File a new claim — item picker ────────────────────────────────────

    public function create(Request $request)
    {
        $customer     = $this->customer();
        $eligibleItems= $this->warranty->eligibleItemsForCustomer($customer);

        // Pre-select an item if ?item_id=X
        $selectedItem = null;
        if ($request->filled('item_id')) {
            $selectedItem = $eligibleItems->firstWhere('id', $request->item_id);
        }

        return view('customer.warranty.create', compact('eligibleItems', 'selectedItem'));
    }

    // ── Submit claim ──────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_item_id'     => ['required', 'exists:order_items,id'],
            'request_type'      => ['required', 'in:warranty_repair,warranty_replacement,warranty_inspection'],
            'issue_description' => ['required', 'string', 'max:2000'],
            'serial_number'     => ['nullable', 'string', 'max:100'],
            'customer_notes'    => ['nullable', 'string', 'max:1000'],
            'issue_images'      => ['nullable', 'array', 'max:5'],
            'issue_images.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_images'    => ['nullable', 'array', 'max:3'],
            'receipt_images.*'  => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'product_images'    => ['nullable', 'array', 'max:5'],
            'product_images.*'  => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $customer = $this->customer();
        $item     = OrderItem::with('order')->findOrFail($data['order_item_id']);

        try {
            $wr = $this->warranty->createClaim(
                customer:         $customer,
                item:             $item,
                requestType:      $data['request_type'],
                issueDescription: $data['issue_description'],
                serialNumber:     $data['serial_number'] ?? null,
                customerNotes:    $data['customer_notes'] ?? null,
                issueImages:      $request->file('issue_images', []),
                receiptImages:    $request->file('receipt_images', []),
                productImages:    $request->file('product_images', []),
            );

            return redirect()
                ->route('customer.warranty.show', $wr)
                ->with('success', "Warranty claim #{$wr->request_number} submitted! The vendor will review your claim shortly.");

        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Cancel claim (while still submitted/under_review) ─────────────────

    public function cancel(WarrantyRequest $warrantyRequest)
    {
        if ($warrantyRequest->customer_id !== $this->customer()->id) abort(403);

        if (! in_array($warrantyRequest->status, ['submitted', 'under_review'])) {
            return back()->with('error', 'This claim can no longer be cancelled.');
        }

        $warrantyRequest->update(['status' => 'closed']);
        $warrantyRequest->addHistoryEntry('Claim Cancelled', 'Cancelled by customer.');

        return redirect()
            ->route('customer.warranty.index')
            ->with('success', "Claim #{$warrantyRequest->request_number} cancelled.");
    }

    public function terms(Vendor $vendor)
    {
        $vendor->load(['vendorServices', 'activeSubscription']);
        return view('customer.warranty.terms', compact('vendor'));
    }
}
