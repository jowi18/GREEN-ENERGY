<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\ProductCategory;
use App\Services\PosService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        private readonly PosService $posService
    ) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── POS Terminal ──────────────────────────────────────────────────────

    public function index()
    {
        $vendor     = $this->vendor();
        $categories = ProductCategory::active()
            ->whereHas('products', fn ($q) =>
                $q->forVendor($vendor->id)->where('status', 'active')
            )
            ->get(['id', 'name']);

        // Eager-load first grid of products (all categories)
        $products = $this->posService->getProductGrid($vendor->id);

        return view('vendor.pos.index', compact('categories', 'products'));
    }

    // ── AJAX: search / barcode scan ───────────────────────────────────────

    public function search(Request $request)
    {
        $vendor  = $this->vendor();
        $term    = trim($request->input('q', ''));

        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $results = $this->posService->searchProducts($term, $vendor->id);
        return response()->json($results);
    }

    public function scanBarcode(Request $request)
    {
        $request->validate(['barcode' => ['required', 'string']]);

        $vendor  = $this->vendor();
        $product = $this->posService->findByBarcode($request->barcode, $vendor->id);

        if (! $product) {
            return response()->json(['found' => false, 'message' => 'Product not found.']);
        }

        return response()->json([
            'found'   => true,
            'product' => $this->posService->productPayload($product),
        ]);
    }

    public function productGrid(Request $request)
    {
        $vendor     = $this->vendor();
        $categoryId = $request->input('category_id');
        $products   = $this->posService->getProductGrid($vendor->id, $categoryId ?: null);
        return response()->json($products);
    }

    // ── AJAX: process cash transaction ────────────────────────────────────

    public function process(Request $request)
    {
        $data = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'cash_tendered'      => ['required', 'numeric', 'min:0'],
            'customer_name'      => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $vendor = $this->vendor();

        try {
            $transaction = $this->posService->processCashTransaction(
                vendor:        $vendor,
                items:         $data['items'],
                cashTendered:  (float) $data['cash_tendered'],
                performedBy:   auth()->id(),
                customerName:  $data['customer_name'] ?? null,
                notes:         $data['notes'] ?? null,
            );

            return response()->json([
                'success'            => true,
                'transaction_id'     => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'total_amount'       => $transaction->total_amount,
                'cash_tendered'      => $data['cash_tendered'],
                'change_due'         => ($data['cash_tendered'] - $transaction->total_amount),
                'receipt_url'        => route('vendor.pos.receipt', $transaction),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ── Receipt ───────────────────────────────────────────────────────────

    public function receipt(PosTransaction $transaction)
    {
        $this->authorizeTransaction($transaction);
        $transaction->load(['items.product', 'cashier', 'vendor']);
        return view('vendor.pos.receipt', compact('transaction'));
    }

    // ── Transaction history ───────────────────────────────────────────────

    public function history(Request $request)
    {
        $vendor = $this->vendor();

        $query = PosTransaction::with(['items', 'cashier'])
            ->forVendor($vendor->id);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('cashier')) {
            $query->where('cashier_id', $request->cashier);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(30)->withQueryString();

        // Daily summary stats
        $today = now()->toDateString();
        $stats = [
            'sales_today'    => PosTransaction::forVendor($vendor->id)
                ->today()->completed()->sum('total_amount'),
            'count_today'    => PosTransaction::forVendor($vendor->id)
                ->today()->completed()->count(),
            'sales_month'    => PosTransaction::forVendor($vendor->id)
                ->whereMonth('created_at', now()->month)
                ->completed()->sum('total_amount'),
            'voided_today'   => PosTransaction::forVendor($vendor->id)
                ->today()->where('status', 'voided')->count(),
        ];

        // Cashier list for filter
        $cashiers = \App\Models\User::whereIn('id',
            PosTransaction::forVendor($vendor->id)->pluck('cashier_id')->filter()->unique()
        )->get(['id', 'name']);

        return view('vendor.pos.history', compact('transactions', 'stats', 'cashiers'));
    }

    // ── Void transaction ──────────────────────────────────────────────────

    public function void(Request $request, PosTransaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->posService->voidTransaction(
                $transaction,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => "Transaction #{$transaction->transaction_number} voided.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function authorizeTransaction(PosTransaction $tx): void
    {
        if ($tx->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }
}
