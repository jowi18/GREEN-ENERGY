<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
// PosTransaction
// ─────────────────────────────────────────────────────────────────────────────

class PosTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'vendor_id',
        'customer_id',
        'walk_in_name',
        'processed_by',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'payment_method',
        'amount_tendered',
        'change_given',
        'paypal_order_id',
        'paid_at',
        'notes',
        'receipt_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'float',
            'discount_amount' => 'float',
            'tax_amount'      => 'float',
            'total_amount'    => 'float',
            'amount_tendered' => 'float',
            'change_given'    => 'float',
            'paid_at'         => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isVoided(): bool    { return $this->status === 'voided'; }

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->full_name;
        }
        return $this->walk_in_name ?: 'Walk-in Customer';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'badge bg-success',
            'open'      => 'badge bg-warning text-dark',
            'voided'    => 'badge bg-danger',
            'refunded'  => 'badge bg-secondary',
            default     => 'badge bg-secondary',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ── Boot ───────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (PosTransaction $tx) {

            if (empty($tx->transaction_number)) {

                do {

                    $random3 = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
                    $random6 = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                    $transactionNumber = 'POS-' . now()->format('Y') . '' . $random3 . '-' . $random6;

                } while (self::where('transaction_number', $transactionNumber)->exists());

                $tx->transaction_number = $transactionNumber;
            }

        });
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// PosTransactionItem
// ─────────────────────────────────────────────────────────────────────────────

class PosTransactionItem extends Model
{
    protected $fillable = [
        'pos_transaction_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_barcode',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'      => 'float',
            'discount_amount' => 'float',
            'total_price'     => 'float',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
