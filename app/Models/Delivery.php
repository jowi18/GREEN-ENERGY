<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'tracking_number',
        'order_id',
        'vendor_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'status',
        'delivery_address_line1',
        'delivery_address_line2',
        'delivery_city',
        'delivery_province',
        'delivery_postal_code',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_notes',
        'proof_of_delivery',
        'recipient_name',
        'scheduled_at',
        'dispatched_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'       => 'datetime',
            'scheduled_at'      => 'datetime',
            'dispatched_at'     => 'datetime',
            'delivered_at'      => 'datetime',
            'delivery_latitude' => 'float',
            'delivery_longitude'=> 'float',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isDelivered(): bool    { return $this->status === 'delivered'; }
    public function isInTransit(): bool    { return $this->status === 'out_for_delivery'; }
    public function isPending(): bool      { return $this->status === 'pending'; }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'badge bg-secondary',
            'preparing'        => 'badge bg-warning text-dark',
            'out_for_delivery' => 'badge bg-primary',
            'delivered'        => 'badge bg-success',
            'failed'           => 'badge bg-danger',
            'returned'         => 'badge bg-danger',
            default            => 'badge bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'Pending',
            'preparing'        => 'Preparing',
            'out_for_delivery' => 'Out for Delivery',
            'delivered'        => 'Delivered',
            'failed'           => 'Failed',
            'returned'         => 'Returned',
            default            => ucfirst($this->status),
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'returned']);
    }

    // ── Boot ───────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Delivery $delivery) {
            if (empty($delivery->tracking_number)) {
                $delivery->tracking_number = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 10));
            }
        });
    }
}
