<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'duration_days',
        'paypal_plan_id',
        'max_products',
        'max_employees',
        'features',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'         => 'float',
            'features'      => 'array',
            'is_featured'   => 'boolean',
            'is_active'     => 'boolean',
            'max_products'  => 'integer',
            'max_employees' => 'integer',
            'duration_days' => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getFormattedPriceAttribute(): string
    {
        return strtoupper($this->currency) . ' ' . number_format($this->price, 2);
    }

    public function getBillingLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'monthly'   => 'per month',
            'quarterly' => 'per 3 months',
            'annual'    => 'per year',
            default     => 'per period',
        };
    }
}
