<?php


// ═══════════════════════════════════════════════════════════════════════════
// app/Models/PurchaseOrderItem.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'supplier_product_id', 'product_name',
        'sku', 'unit_price', 'quantity_ordered', 'quantity_received',
        'unit_of_measure', 'line_total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'        => 'float',
            'quantity_ordered'  => 'integer',
            'quantity_received' => 'integer',
            'line_total'        => 'float',
        ];
    }

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function product()       { return $this->belongsTo(SupplierProduct::class, 'supplier_product_id'); }

    public function recalculateLineTotal(): void
    {
        $this->line_total = $this->unit_price * $this->quantity_ordered;
        $this->save();
    }
}
