<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';
    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'currency',
        'product_type',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'warranty_months',
        'warranty_terms',
        'status',
        'is_featured',
        'average_rating',
        'total_reviews',
        'total_sold',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'float',
            'compare_price'  => 'float',
            'cost_price'     => 'float',
            'weight_kg'      => 'float',
            'is_featured'    => 'boolean',
            'average_rating' => 'float',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function posItems()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isInStock(): bool
    {
        return $this->inventory && $this->inventory->quantity_on_hand > 0;
    }

    public function getStockQuantityAttribute(): int
    {
        return $this->inventory?->quantity_on_hand ?? 0;
    }

    public function isOnSale(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->isOnSale()) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    public function getWarrantyLabelAttribute(): ?string
    {
        if (!$this->warranty_months) return null;
        $years  = intdiv($this->warranty_months, 12);
        $months = $this->warranty_months % 12;
        $parts  = [];
        if ($years)  $parts[] = $years  . ' ' . str($years)->plural('year');
        if ($months) $parts[] = $months . ' ' . str($months)->plural('month');
        return implode(' ', $parts) . ' warranty';
    }

    public function isPhysical(): bool { return $this->product_type === 'physical'; }
    public function isService(): bool  { return $this->product_type === 'service'; }
}
