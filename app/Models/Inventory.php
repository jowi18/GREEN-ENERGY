<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';
    protected $fillable = [
        'product_id',
        'vendor_id',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_point',
        'reorder_quantity',
        'max_stock_level',
        'storage_location',
        'unit_of_measure',
        'last_stock_update',
    ];

    protected function casts(): array
    {
        return [
            'last_stock_update' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getQuantityAvailableAttribute(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    public function canFulfill(int $qty): bool
    {
        return $this->quantity_available >= $qty;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_point')
                     ->where('quantity_on_hand', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', 0);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('inventories.vendor_id', $vendorId);
    }
}
