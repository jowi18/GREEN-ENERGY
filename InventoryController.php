<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    private function vendor()
    {
        $user = auth()->user();
        return $user->isEmployee() ? $user->employee->vendor : $user->vendor;
    }

    // ── Inventory overview ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Inventory::with(['product.category', 'product.primaryImage'])
            ->forVendor($vendor->id)
            ->whereHas('product');

        // Filters
        if ($request->filled('search')) {
            $query->whereHas('product', fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%")
            );
        }

        if ($request->filter === 'low_stock') {
            $query->lowStock();
        } elseif ($request->filter === 'out_of_stock') {
            $query->outOfStock();
        }

        if ($request->filled('category')) {
            $query->whereHas('product', fn ($q) =>
                $q->where('category_id', $request->category)
            );
        }

        $inventory = $query->latest('last_stock_update')->paginate(25)->withQueryString();

        $stats = [
            'total_products'  => Inventory::forVendor($vendor->id)->count(),
            'total_value' => Inventory::forVendor($vendor->id)
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->selectRaw('SUM(tbl_inventories.quantity_on_hand * tbl_products.cost_price) as val')
                ->value('val') ?? 0,
            'low_stock'       => Inventory::forVendor($vendor->id)->lowStock()->count(),
            'out_of_stock'    => Inventory::forVendor($vendor->id)->outOfStock()->count(),
        ];

        $categories = \App\Models\ProductCategory::active()->get();

        return view('vendor.inventory.index', compact('inventory', 'stats', 'categories'));
    }

    // ── Adjust stock ──────────────────────────────────────────────────────

    public function adjust(Request $request, Inventory $inventory)
    {
        $this->authorizeInventory($inventory);

        $data = $request->validate([
            'new_quantity' => ['required', 'integer', 'min:0'],
            'notes'        => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->inventoryService->adjust(
                $inventory,
                (int) $data['new_quantity'],
                $data['notes'],
                auth()->id()
            );

            $msg = "Stock for \"{$inventory->product->name}\" adjusted to {$data['new_quantity']}.";

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Add stock ─────────────────────────────────────────────────────────

    public function addStock(Request $request, Inventory $inventory)
    {
        $this->authorizeInventory($inventory);

        $data = $request->validate([
            'quantity'      => ['required', 'integer', 'min:1'],
            'movement_type' => ['required', 'in:purchase,return,transfer_in'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->inventoryService->addStock(
                $inventory,
                (int) $data['quantity'],
                $data['movement_type'],
                $data['notes'] ?? null,
                auth()->id()
            );

            $msg = "Added {$data['quantity']} units to \"{$inventory->product->name}\".";

            if ($request->expectsJson()) {
                return response()->json([
                    'success'          => true,
                    'message'          => $msg,
                    'quantity_on_hand' => $inventory->fresh()->quantity_on_hand,
                ]);
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Update reorder settings ───────────────────────────────────────────

    public function updateSettings(Request $request, Inventory $inventory)
    {
        $this->authorizeInventory($inventory);

        $data = $request->validate([
            'reorder_point'    => ['required', 'integer', 'min:0'],
            'reorder_quantity' => ['required', 'integer', 'min:1'],
            'max_stock_level'  => ['nullable', 'integer', 'min:0'],
            'storage_location' => ['nullable', 'string', 'max:100'],
        ]);

        $inventory->update($data);

        return back()->with('success', 'Reorder settings updated.');
    }

    // ── Stock movements ───────────────────────────────────────────────────

    public function movements(Request $request)
    {
        $vendor = $this->vendor();

        $query = StockMovement::with(['product', 'performer'])
            ->forVendor($vendor->id);

        if ($request->filled('product_id')) {
            $query->forProduct($request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        if ($request->filled('direction')) {
            $request->direction === 'in'
                ? $query->inbound()
                : $query->outbound();
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(30)->withQueryString();

        $products = Product::forVendor($vendor->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('vendor.inventory.movements', compact('movements', 'products'));
    }

    // ── AJAX: inventory item detail (for quick-adjust modal) ─────────────

    public function show(Inventory $inventory)
    {
        $this->authorizeInventory($inventory);

        $inventory->load(['product.category', 'product.primaryImage']);

        $recentMovements = StockMovement::forProduct($inventory->product_id)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'inventory'       => $inventory,
            'product'         => $inventory->product,
            'recent_movements'=> $recentMovements,
        ]);
    }

    // ── AJAX: DataTables server-side ──────────────────────────────────────

    public function datatable(Request $request)
    {
        $vendor = $this->vendor();

        $query = Inventory::with(['product.category'])
            ->forVendor($vendor->id)
            ->select('inventories.*');

        if ($request->filled('search.value')) {
            $term = $request->input('search.value');
            $query->whereHas('product', fn ($q) =>
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
            );
        }

        $total    = Inventory::forVendor($vendor->id)->count();
        $filtered = $query->count();

        $rows = $query
            ->offset($request->input('start', 0))
            ->limit($request->input('length', 25))
            ->latest('last_stock_update')
            ->get();

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows->map(fn ($inv) => [
                'id'               => $inv->id,
                'product_name'     => $inv->product->name,
                'sku'              => $inv->product->sku ?? '—',
                'quantity_on_hand' => $inv->quantity_on_hand,
                'reorder_point'    => $inv->reorder_point,
                'status'           => $inv->isOutOfStock() ? 'out_of_stock'
                                    : ($inv->isLowStock() ? 'low' : 'ok'),
                'last_updated'     => $inv->last_stock_update?->diffForHumans() ?? '—',
            ]),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function authorizeInventory(Inventory $inventory): void
    {
        if ($inventory->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }
}
