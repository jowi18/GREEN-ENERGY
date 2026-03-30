<?php



// ════════════════════════════════════════════════════════════════════════════
// SolarQuotationItem
// ════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarQuotationItem extends Model
{
    protected $fillable = [
        'product_id',
        'solar_quotation_id',
        'item_type',
        'description',
        'brand',
        'model',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'float',
            'unit_price' => 'float',
            'total_price'=> 'float',
            'product_id' => 'array'
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(SolarQuotation::class, 'solar_quotation_id');
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'equipment' => '⚙️ Equipment',
            'labor'     => '🔧 Labor',
            'permit'    => '📄 Permit',
            'other'     => '📦 Other',
            default     => ucfirst($this->item_type),
        };
    }

    protected static function booted(): void
    {
        // Auto-compute total_price on save
        static::saving(function (SolarQuotationItem $item) {
            $item->total_price = round($item->quantity * $item->unit_price, 2);
        });
    }
}
