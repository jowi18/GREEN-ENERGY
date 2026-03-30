<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a product (purchase / transfer in / opening stock).
     */
    public function addStock(
        Inventory $inventory,
        int       $quantity,
        string    $movementType = 'purchase',
        ?string   $notes        = null,
        ?int      $performedBy  = null
    ): StockMovement {
        return DB::transaction(function () use ($inventory, $quantity, $movementType, $notes, $performedBy) {
            $before = $inventory->quantity_on_hand;
            $after  = $before + $quantity;

            $inventory->update([
                'quantity_on_hand'  => $after,
                'last_stock_update' => now(),
            ]);

            // Sync product status
            $this->syncProductStatus($inventory->product);

            return StockMovement::create([
                'inventory_id'    => $inventory->id,
                'product_id'      => $inventory->product_id,
                'vendor_id'       => $inventory->vendor_id,
                'movement_type'   => $movementType,
                'quantity_change' => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'notes'           => $notes,
                'performed_by'    => $performedBy,
            ]);
        });
    }

    /**
     * Deduct stock (sale / damage / transfer out).
     * Throws if not enough available stock.
     */
    public function deductStock(
        Inventory $inventory,
        int       $quantity,
        string    $movementType       = 'sale',
        ?string   $notes              = null,
        ?int      $performedBy        = null,
        ?string   $referenceType      = null,
        ?int      $referenceId        = null
    ): StockMovement {
        if ($inventory->quantity_on_hand < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock for \"{$inventory->product->name}\". " .
                "Available: {$inventory->quantity_on_hand}, requested: {$quantity}."
            );
        }

        return DB::transaction(function () use (
            $inventory, $quantity, $movementType, $notes,
            $performedBy, $referenceType, $referenceId
        ) {
            $before = $inventory->quantity_on_hand;
            $after  = $before - $quantity;

            $inventory->update([
                'quantity_on_hand'  => $after,
                'last_stock_update' => now(),
            ]);

            $this->syncProductStatus($inventory->product);

            return StockMovement::create([
                'inventory_id'    => $inventory->id,
                'product_id'      => $inventory->product_id,
                'vendor_id'       => $inventory->vendor_id,
                'movement_type'   => $movementType,
                'quantity_change' => -$quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'notes'           => $notes,
                'performed_by'    => $performedBy,
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
            ]);
        });
    }

    /**
     * Manual stock adjustment — can be positive (add) or negative (remove).
     */
    public function adjust(
        Inventory $inventory,
        int       $newQuantity,
        ?string   $notes       = null,
        ?int      $performedBy = null
    ): StockMovement {
        $before = $inventory->quantity_on_hand;
        $diff   = $newQuantity - $before;

        if ($diff === 0) {
            throw new \RuntimeException('New quantity is the same as current stock. No adjustment made.');
        }

        $type = $diff > 0 ? 'adjustment_add' : 'adjustment_sub';

        return DB::transaction(function () use ($inventory, $newQuantity, $diff, $type, $before, $notes, $performedBy) {
            $inventory->update([
                'quantity_on_hand'  => $newQuantity,
                'last_stock_update' => now(),
            ]);

            $this->syncProductStatus($inventory->product);

            return StockMovement::create([
                'inventory_id'    => $inventory->id,
                'product_id'      => $inventory->product_id,
                'vendor_id'       => $inventory->vendor_id,
                'movement_type'   => $type,
                'quantity_change' => $diff,
                'quantity_before' => $before,
                'quantity_after'  => $newQuantity,
                'notes'           => $notes,
                'performed_by'    => $performedBy,
            ]);
        });
    }

    /**
     * Reserve stock for a pending order (reduces quantity_available without
     * touching quantity_on_hand).
     */
    public function reserve(Inventory $inventory, int $quantity): void
    {
        if ($inventory->quantity_available < $quantity) {
            throw new \RuntimeException(
                "Cannot reserve {$quantity} units of \"{$inventory->product->name}\". " .
                "Available: {$inventory->quantity_available}."
            );
        }

        $inventory->increment('quantity_reserved', $quantity);
    }

    /**
     * Release a reservation (order cancelled / payment failed).
     */
    public function release(Inventory $inventory, int $quantity): void
    {
        $release = min($quantity, $inventory->quantity_reserved);
        $inventory->decrement('quantity_reserved', $release);
    }

    /**
     * Confirm fulfilment — deducts on_hand AND releases the reservation.
     */
    public function fulfil(
        Inventory $inventory,
        int       $quantity,
        string    $movementType  = 'sale',
        ?string   $notes         = null,
        ?int      $performedBy   = null,
        ?string   $referenceType = null,
        ?int      $referenceId   = null
    ): StockMovement {
        return DB::transaction(function () use (
            $inventory, $quantity, $movementType, $notes,
            $performedBy, $referenceType, $referenceId
        ) {
            // Release reservation first
            $this->release($inventory, $quantity);

            // Then deduct from on_hand
            return $this->deductStock(
                $inventory->fresh(),
                $quantity,
                $movementType,
                $notes,
                $performedBy,
                $referenceType,
                $referenceId
            );
        });
    }

    /**
     * Update reorder settings for an inventory record.
     */
    public function updateReorderSettings(
        Inventory $inventory,
        int       $reorderPoint,
        int       $reorderQuantity,
        ?int      $maxStockLevel  = null
    ): void {
        $inventory->update([
            'reorder_point'    => $reorderPoint,
            'reorder_quantity' => $reorderQuantity,
            'max_stock_level'  => $maxStockLevel,
        ]);
    }

    /**
     * Get all low-stock items for a vendor, ordered by urgency.
     */
    public function getLowStockAlerts(int $vendorId): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::forVendor($vendorId)
            ->with('product.category')
            ->lowStock()
            ->orderBy('quantity_on_hand')
            ->get();
    }

    /**
     * Get out-of-stock items for a vendor.
     */
    public function getOutOfStock(int $vendorId): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::forVendor($vendorId)
            ->with('product.category')
            ->outOfStock()
            ->orderBy('last_stock_update')
            ->get();
    }

    /**
     * Sync product status based on current stock level.
     */
    private function syncProductStatus(Product $product): void
    {
        $inventory = $product->inventory;

        if (! $inventory || $product->isService()) {
            return;
        }

        $newStatus = $inventory->quantity_on_hand <= 0 ? 'out_of_stock' : 'active';

        if ($product->status !== 'draft') {
            $product->update(['status' => $newStatus]);
        }
    }
}
