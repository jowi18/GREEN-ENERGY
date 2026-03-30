<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'vendor_id',
        'subscription_plan_id',
        'paypal_subscription_id',
        'paypal_order_id',
        'paypal_payer_id',
        'status',
        'amount_paid',
        'currency',
        'starts_at',
        'expires_at',
        'cancelled_at',
        'auto_renew',
        'last_renewed_at',
        'next_renewal_at',
        'paypal_response',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid'     => 'float',
            'starts_at'       => 'datetime',
            'expires_at'      => 'datetime',
            'cancelled_at'    => 'datetime',
            'last_renewed_at' => 'datetime',
            'next_renewal_at' => 'datetime',
            'auto_renew'      => 'boolean',
            'paypal_response' => 'array',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysRemaining(): int
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }
        return (int) now()->diffInDays($this->expires_at);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'badge bg-success',
            'pending'   => 'badge bg-warning text-dark',
            'expired'   => 'badge bg-secondary',
            'cancelled' => 'badge bg-danger',
            'failed'    => 'badge bg-danger',
            default     => 'badge bg-secondary',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}
