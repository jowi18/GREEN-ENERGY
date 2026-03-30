<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/PurchaseRequestItem.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id', 'supplier_product_id', 'product_name',
        'sku', 'unit_price', 'quantity_requested', 'unit_of_measure', 'notes',
    ];

    protected function casts(): array
    {
        return ['unit_price' => 'float', 'quantity_requested' => 'integer'];
    }

    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class); }
    public function product()         { return $this->belongsTo(SupplierProduct::class, 'supplier_product_id'); }

    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity_requested;
    }
}
