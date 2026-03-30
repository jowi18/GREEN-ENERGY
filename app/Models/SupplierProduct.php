<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/SupplierProduct.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id', 'category_id', 'name', 'sku', 'short_description',
        'description', 'unit_price', 'currency', 'unit_of_measure',
        'minimum_order_qty', 'stock_available', 'lead_time_days',
        'product_type', 'weight_kg', 'warranty_months', 'spec_sheet',
        'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'   => 'float',
            'weight_kg'    => 'float',
            'is_featured'  => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function supplier()  { return $this->belongsTo(Supplier::class); }

    public function category()  { return $this->belongsTo(ProductCategory::class, 'category_id'); }

    public function images()
    {
        return $this->hasMany(SupplierProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(SupplierProductImage::class)->where('is_primary', true);
    }

    public function purchaseRequestItems() { return $this->hasMany(PurchaseRequestItem::class); }

    public function purchaseOrderItems()   { return $this->hasMany(PurchaseOrderItem::class); }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool   { return $this->status === 'active'; }
    public function isInStock(): bool  { return $this->stock_available > 0; }

    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query) { return $query->where('status', 'active'); }
}
