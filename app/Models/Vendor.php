<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'owner_first_name',
        'owner_last_name',
        'owner_phone',
        'avatar',
        'business_name',
        'business_type',
        'business_registration_number',
        'business_phone',
        'business_email',
        'business_website',
        'address_line1',
        'address_line2',
        'city',
        'province_state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'shop_logo',
        'shop_banner',
        'shop_description',
        'status',
        'admin_notes',
        'rejection_reason',
        'approved_at',
        'suspended_at',
        'average_rating',
        'total_reviews',
        'cover_photo',
        'tagline',
        'about',
        'year_established',
        'service_area',
        'operating_hours',
        'is_open_now_override',
        'temporary_closure_note',
        'support_email',
        'support_phone',
        'whatsapp',
        'viber',
        'social_facebook',
        'social_instagram',
        'social_youtube',
        'social_tiktok',
        'return_policy',
        'warranty_policy',
        'payment_terms',
        'highlights',
        'seo_title',
        'seo_description',
        'certifications',
        'show_reviews_publicly',
        'show_operating_hours',
        'accept_online_orders',
        'accept_service_bookings',

    ];

    protected function casts(): array
    {
        return [
            'latitude'    => 'float',
            'longitude'   => 'float',
            'approved_at' => 'datetime',
            'suspended_at'=> 'datetime',
            'operating_hours'         => 'array',
            'highlights'              => 'array',
            'certifications'          => 'array',
            'is_open_now_override'    => 'boolean',
            'show_reviews_publicly'   => 'boolean',
            'show_operating_hours'    => 'boolean',
            'accept_online_orders'    => 'boolean',
            'accept_service_bookings' => 'boolean',

        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function hrProfiles()
    // {
    //     return $this->belongsTo(HrEmployeeProfile::class);
    // }

    public function vendorServices()
    {
        return $this->hasMany(VendorService::class)->orderBy('sort_order');
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Vendor's own supplier directory */
    public function supplierContacts()
    {
        return $this->hasMany(VendorSupplier::class);
    }

    /** Only the ones linked to portal accounts (can issue PRs/POs) */
    public function portalLinkedSuppliers()
    {
        return $this->hasMany(VendorSupplier::class)->whereNotNull('supplier_id');
    }



    // ── Status helpers ─────────────────────────────────────────────────────

    public function isPending(): bool      { return $this->status === 'pending'; }
    public function isUnderReview(): bool  { return $this->status === 'under_review'; }
    public function isApproved(): bool     { return in_array($this->status, ['approved', 'subscription_required', 'active']); }
    public function isActive(): bool       { return $this->status === 'active'; }
    public function isSuspended(): bool    { return $this->status === 'suspended'; }
    public function isRejected(): bool     { return $this->status === 'rejected'; }
    public function needsSubscription(): bool { return $this->status === 'subscription_required'; }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->province_state,
            $this->postal_code,
            $this->country,
        ])->filter()->implode(', ');
    }

    public function getOwnerFullNameAttribute(): string
    {
        return "{$this->owner_first_name} {$this->owner_last_name}";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'               => 'badge-warning',
            'under_review'          => 'badge-info',
            'approved'              => 'badge-success',
            'subscription_required' => 'badge-secondary',
            'active'                => 'badge-success',
            'suspended'             => 'badge-danger',
            'rejected'              => 'badge-danger',
            default                 => 'badge-secondary',
        };
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->shop_logo
            ? asset('storage/' . $this->shop_logo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->business_name) . '&background=157a30&color=fff&size=128';
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_photo ? asset('storage/' . $this->cover_photo) : null;
    }

    /**
     * Returns today's operating hours or null if closed.
     */
    public function getTodayHoursAttribute(): ?array
    {
        if (! $this->operating_hours) return null;
        $dow = now()->dayOfWeek; // 0=Sun
        return $this->operating_hours[$dow] ?? null;
    }

    public function getIsOpenNowAttribute(): bool
    {
        if ($this->is_open_now_override) return false;
        $today = $this->today_hours;
        if (! $today || ! $today['open']) return false;
        $now  = now()->format('H:i');
        return $now >= $today['from'] && $now <= $today['to'];
    }

    public function getFormattedAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->province_state,
            $this->postal_code,
        ]));
    }


    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        $haversine = "(
            6371 * ACOS(
                COS(RADIANS({$lat})) * COS(RADIANS(latitude))
                * COS(RADIANS(longitude) - RADIANS({$lng}))
                + SIN(RADIANS({$lat})) * SIN(RADIANS(latitude))
            )
        )";

        return $query
            ->selectRaw("*, {$haversine} AS distance_km")
            ->whereRaw("{$haversine} <= ?", [$radiusKm])
            ->orderBy('distance_km');
    }
}
