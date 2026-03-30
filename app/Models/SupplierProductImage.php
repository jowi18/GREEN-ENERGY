<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/SupplierProductImage.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProductImage extends Model
{
    protected $fillable = ['supplier_product_id', 'file_path', 'alt_text', 'is_primary', 'sort_order'];

    protected function casts(): array { return ['is_primary' => 'boolean']; }

    public function product() { return $this->belongsTo(SupplierProduct::class, 'supplier_product_id'); }

    public function getUrlAttribute(): string { return asset('storage/' . $this->file_path); }
}
