<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/Supplier.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'province_state',
        'postal_code',
        'country',
        'business_permit',
        'logo',
        'description',
        'status',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function vendorContacts()
    {
        return $this->hasMany(VendorSupplier::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

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

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'badge-success',
            'pending'   => 'badge-warning',
            'suspended' => 'badge-danger',
            default     => 'badge-secondary',
        };
    }
}
