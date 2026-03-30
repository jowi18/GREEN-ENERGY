<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'inventory_id',
        'product_id',
        'vendor_id',
        'movement_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after'  => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** Polymorphic: resolves to Order, PosTransaction, etc. */
    public function reference()
    {
        return $this->morphTo('reference');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isInbound(): bool
    {
        return $this->quantity_change > 0;
    }

    public function isOutbound(): bool
    {
        return $this->quantity_change < 0;
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase'        => 'Purchase (Stock In)',
            'sale'            => 'Online Sale',
            'pos_sale'        => 'POS Sale',
            'adjustment_add'  => 'Manual Adjustment (+)',
            'adjustment_sub'  => 'Manual Adjustment (-)',
            'return'          => 'Customer Return',
            'damage'          => 'Damage / Write-Off',
            'transfer_in'     => 'Transfer In',
            'transfer_out'    => 'Transfer Out',
            'opening_stock'   => 'Opening Stock',
            default           => ucwords(str_replace('_', ' ', $this->movement_type)),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return $this->isInbound() ? 'badge bg-success' : 'badge bg-danger';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeInbound($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeOutbound($query)
    {
        return $query->where('quantity_change', '<', 0);
    }
}
