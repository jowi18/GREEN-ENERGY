<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolarQuotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'solar_project_id',
        'vendor_id',
        'created_by',
        'quotation_number',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'valid_until',
        'customer_rejection_reason',
        'customer_responded_at',
        'notes',
        'terms_and_conditions',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'               => 'float',
            'discount_amount'        => 'float',
            'tax_amount'             => 'float',
            'total_amount'           => 'float',
            'valid_until'            => 'date',
            'customer_responded_at'  => 'datetime',
            'attachments'            => 'array',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SolarQuotationItem::class)->orderBy('sort_order');
    }

    public function equipmentItems()
    {
        return $this->hasMany(SolarQuotationItem::class)->where('item_type', 'equipment')->orderBy('sort_order');
    }

    public function laborItems()
    {
        return $this->hasMany(SolarQuotationItem::class)->where('item_type', 'labor')->orderBy('sort_order');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'Draft',
            'sent'       => 'Awaiting Response',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'expired'    => 'Expired',
            'superseded' => 'Superseded',
            default      => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'vd-badge--neutral',
            'sent'       => 'vd-badge--warning',
            'approved'   => 'vd-badge--active',
            'rejected'   => 'vd-badge--danger',
            'expired'    => 'vd-badge--neutral',
            'superseded' => 'vd-badge--neutral',
            default      => 'vd-badge--neutral',
        };
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('total_price');
        $this->update([
            'subtotal'     => $subtotal,
            'total_amount' => $subtotal - $this->discount_amount + $this->tax_amount,
        ]);
    }

    // ── Boot ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (SolarQuotation $q) {
            if (empty($q->quotation_number)) {
                $randomDigits = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $count = static::whereDate('created_at', today())->count() + 1;
                $q->quotation_number = 'QT-' . now()->format('Ymd') . '-' . $randomDigits ;
            }
        });
    }
}

