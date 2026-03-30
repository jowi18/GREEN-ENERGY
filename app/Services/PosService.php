<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(
        private readonly InventoryService $inventory
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // PRODUCT LOOKUP
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Find a product by barcode or SKU for scan-to-add.
     * Returns the product with its inventory record, or null.
     */
    public function findByBarcode(string $query, int $vendorId): ?Product
    {
        return Product::with(['inventory', 'primaryImage'])
            ->forVendor($vendorId)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('barcode', $query)
                  ->orWhere('sku', $query);
            })
            ->first();
    }

    /**
     * Search products by name / SKU / barcode for the POS product picker.
     */
    public function searchProducts(string $term, int $vendorId): Collection
    {
        return Product::with(['inventory', 'primaryImage', 'category'])
            ->forVendor($vendorId)
            ->where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('name',    'like', "%{$term}%")
                  ->orWhere('sku',     'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get()
            ->map(fn ($p) => $this->productPayload($p));
    }

    /**
     * Get all active products for the POS grid (paginated by category).
     */
    public function getProductGrid(int $vendorId, ?int $categoryId = null): Collection
    {
        $query = Product::with(['inventory', 'primaryImage'])
            ->forVendor($vendorId)
            ->where('status', 'active')
            ->whereHas('inventory', fn ($q) => $q->where('quantity_on_hand', '>', 0));

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('name')->limit(60)->get()
            ->map(fn ($p) => $this->productPayload($p));
    }

    /**
     * Format a product for JSON/JS consumption.
     */
    public function productPayload(Product $p): array
    {
        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'sku'         => $p->sku,
            'barcode'     => $p->barcode,
            'price'       => (float) $p->price,
            'stock'       => $p->inventory?->quantity_on_hand ?? 0,
            'is_service'  => $p->isService(),
            'image'       => $p->primaryImage
                ? asset('storage/' . $p->primaryImage->file_path)
                : null,
            'category_id' => $p->category_id,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // TRANSACTION PROCESSING
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Process a cash POS transaction.
     *
     * $items = [
     *   ['product_id' => 1, 'quantity' => 2, 'unit_price' => 12500.00, 'discount' => 0],
     *   ...
     * ]
     *
     * Returns the completed PosTransaction.
     */
    public function processCashTransaction(
        Vendor $vendor,
        array  $items,
        float  $cashTendered,
        ?int   $performedBy = null,
        ?string $customerName = null,
        ?string $notes = null
    ): PosTransaction {
        // Validate stock availability before touching anything
        $this->validateStock($items, $vendor->id);

        return DB::transaction(function () use (
            $vendor, $items, $cashTendered, $performedBy, $customerName, $notes
        ) {
            // Calculate totals
            $subtotal = 0;
            $discount = 0;

            foreach ($items as $item) {
                $lineTotal  = $item['unit_price'] * $item['quantity'];
                $lineDisc   = $item['discount'] ?? 0;
                $subtotal  += $lineTotal;
                $discount  += $lineDisc;
            }

            $totalAmount  = $subtotal - $discount;
            $changeDue    = $cashTendered - $totalAmount;

            if ($changeDue < 0) {
                throw new \RuntimeException(
                    'Cash tendered (₱' . number_format($cashTendered, 2) . ') is less than total (₱' . number_format($totalAmount, 2) . ').'
                );
            }

            // Create transaction header
            $transaction = PosTransaction::create([
                'vendor_id'       => $vendor->id,
                'cashier_id'      => $performedBy,
                'customer_name'   => $customerName,
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'total_amount'    => $totalAmount,
                'payment_method'  => 'cash',
                'cash_tendered'   => $cashTendered,
                'change_due'      => $changeDue,
                'notes'           => $notes,
                'status'          => 'completed',
                'completed_at'    => now(),
            ]);

            // Create line items + deduct stock
            foreach ($items as $item) {
                $product   = Product::find($item['product_id']);
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);

                PosTransactionItem::create([
                    'pos_transaction_id' => $transaction->id,
                    'product_id'         => $product->id,
                    'product_name'       => $product->name,
                    'product_sku'        => $product->sku,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'discount_amount'    => $item['discount'] ?? 0,
                    'total_price'        => $lineTotal,
                ]);

                // Deduct stock (skip for services)
                if (! $product->isService()) {
                    $inventory = Inventory::forVendor($vendor->id)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($inventory) {
                        $this->inventory->deductStock(
                            inventory:     $inventory,
                            quantity:      $item['quantity'],
                            movementType:  'pos_sale',
                            notes:         "POS Transaction #{$transaction->transaction_number}",
                            performedBy:   $performedBy,
                            referenceType: PosTransaction::class,
                            referenceId:   $transaction->id,
                        );
                    }
                }
            }

            return $transaction->load('items.product');
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // VOID / REFUND
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Void a completed transaction and restore stock.
     */
    public function voidTransaction(
        PosTransaction $transaction,
        string         $reason,
        int            $performedBy
    ): void {
        if ($transaction->status !== 'completed') {
            throw new \RuntimeException('Only completed transactions can be voided.');
        }

        DB::transaction(function () use ($transaction, $reason, $performedBy) {
            // Restore stock for each item
            foreach ($transaction->items as $item) {
                if (! $item->product || $item->product->isService()) {
                    continue;
                }

                $inventory = Inventory::forVendor($transaction->vendor_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($inventory) {
                    $this->inventory->addStock(
                        inventory:    $inventory,
                        quantity:     $item->quantity,
                        movementType: 'return',
                        notes:        "Void of POS #{$transaction->transaction_number}: {$reason}",
                        performedBy:  $performedBy,
                    );
                }
            }

            $transaction->update([
                'status'      => 'voided',
                'void_reason' => $reason,
                'voided_by'   => $performedBy,
                'voided_at'   => now(),
            ]);
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Validate that all items have sufficient stock before processing.
     * Throws on first failure.
     */
    private function validateStock(array $items, int $vendorId): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || $product->isService()) {
                continue;
            }

            $inventory = Inventory::forVendor($vendorId)
                ->where('product_id', $item['product_id'])
                ->first();

            if (! $inventory || $inventory->quantity_on_hand < $item['quantity']) {
                $available = $inventory?->quantity_on_hand ?? 0;
                throw new \RuntimeException(
                    "Insufficient stock for \"{$product->name}\". " .
                    "Requested: {$item['quantity']}, available: {$available}."
                );
            }
        }
    }
}
