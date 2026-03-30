<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'warranty_months',
        'warranty_starts_at',
        'warranty_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'          => 'float',
            'discount_amount'     => 'float',
            'total_price'         => 'float',
            'warranty_starts_at'  => 'datetime',
            'warranty_expires_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warrantyRequests()
    {
        return $this->hasMany(WarrantyRequest::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expires_at
            && $this->warranty_expires_at->isFuture();
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_expires_at) return 'No warranty';
        return $this->isUnderWarranty()
            ? 'Active (expires ' . $this->warranty_expires_at->format('M d, Y') . ')'
            : 'Expired';
    }
}
