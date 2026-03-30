<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'status',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'tax_amount',
        'total_amount',
        'currency',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_country',
        'payment_method',
        'payment_status',
        'paypal_order_id',
        'paypal_payer_id',
        'paypal_response',
        'paid_at',
        'customer_notes',
        'vendor_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'float',
            'discount_amount' => 'float',
            'shipping_fee'    => 'float',
            'tax_amount'      => 'float',
            'total_amount'    => 'float',
            'paypal_response' => 'array',
            'paid_at'         => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isPaid(): bool      { return $this->payment_status === 'paid'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'badge bg-warning text-dark',
            'paid'       => 'badge bg-info text-dark',
            'processing' => 'badge bg-primary',
            'shipped'    => 'badge bg-primary',
            'delivered'  => 'badge bg-success',
            'completed'  => 'badge bg-success',
            'cancelled'  => 'badge bg-danger',
            'refunded'   => 'badge bg-secondary',
            default      => 'badge bg-secondary',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // ── Boot ───────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Order $order) {

            if (empty($order->order_number)) {

                $randomDigits = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $order->order_number = 'ORD-' . now()->format('Y') . '-' . $randomDigits;
            }

        });
    }
}
