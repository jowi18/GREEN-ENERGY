<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'title',
        'body',
        'images',
        'order_id',
        'order_item_id',
        'vendor_reply',
        'vendor_replied_at',
        'is_verified_purchase',
        'is_visible',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'images'               => 'array',
            'vendor_replied_at'    => 'datetime',
            'is_verified_purchase' => 'boolean',
            'is_visible'           => 'boolean',
            'is_flagged'           => 'boolean',
            'rating'               => 'integer',
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function serviceRequest(){
        return $this->belongsTo(ServiceRequest::class);
    }

    /** Polymorphic target: Vendor, Product, or ServiceRequest */
    public function reviewable()
    {
        return $this->morphTo();
    }



    // ── Helpers ────────────────────────────────────────────────────────────

    public function getStarsHtmlAttribute(): string
    {
        $full  = $this->rating;
        $empty = 5 - $full;
        return str_repeat('<i class="bi bi-star-fill text-warning"></i>', $full)
             . str_repeat('<i class="bi bi-star text-secondary"></i>', $empty);
    }

    public function hasVendorReply(): bool
    {
        return !is_null($this->vendor_reply);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    // ── Observer: recalculate vendor/product average rating ───────────────

    protected static function booted(): void
    {
        $recalc = function (Review $review) {
            // Recalculate vendor average rating
            $avg = static::where('vendor_id', $review->vendor_id)
                ->where('is_visible', true)
                ->avg('rating') ?? 0;

            $count = static::where('vendor_id', $review->vendor_id)
                ->where('is_visible', true)
                ->count();

            Vendor::where('id', $review->vendor_id)->update([
                'average_rating' => round($avg, 2),
                'total_reviews'  => $count,
            ]);

            // Recalculate product average if this is a product review
            if ($review->reviewable_type === Product::class) {
                $pAvg = static::where('reviewable_type', Product::class)
                    ->where('reviewable_id', $review->reviewable_id)
                    ->where('is_visible', true)
                    ->avg('rating') ?? 0;

                $pCount = static::where('reviewable_type', Product::class)
                    ->where('reviewable_id', $review->reviewable_id)
                    ->where('is_visible', true)
                    ->count();

                Product::where('id', $review->reviewable_id)->update([
                    'average_rating' => round($pAvg, 2),
                    'total_reviews'  => $pCount,
                ]);
            }
        };

        static::created($recalc);
        static::updated($recalc);
        static::deleted($recalc);
    }
}
