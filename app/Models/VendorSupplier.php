<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorSupplier extends Model
{
    use SoftDeletes;

    protected $table = 'vendor_suppliers';

    protected $fillable = [
        'vendor_id',
        'supplier_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address_line1',
        'city',
        'province_state',
        'postal_code',
        'country',
        'account_number',
        'payment_terms',
        'payment_terms_custom',
        'credit_limit',
        'is_preferred',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_preferred' => 'boolean',
            'credit_limit' => 'float',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * The linked portal supplier account (nullable).
     * NULL means this supplier is not yet registered on the platform.
     */
    public function portalSupplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Purchase requests this vendor sent to this supplier.
     * Routed through the linked portal supplier_id.
     */
    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'supplier_id', 'supplier_id')
            ->where('vendor_id', $this->vendor_id);
    }

    /**
     * Purchase orders this vendor placed with this supplier.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id', 'supplier_id')
            ->where('vendor_id', $this->vendor_id);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool    { return $this->status === 'active'; }
    public function isBlocked(): bool   { return $this->status === 'blocked'; }

    /** True when linked to a portal supplier account */
    public function hasPortalAccount(): bool
    {
        return !is_null($this->supplier_id);
    }

    public function getPaymentTermsLabelAttribute(): string
    {
        return match ($this->payment_terms) {
            'cod'    => 'Cash on Delivery',
            'net7'   => 'Net 7 Days',
            'net15'  => 'Net 15 Days',
            'net30'  => 'Net 30 Days',
            'net60'  => 'Net 60 Days',
            'custom' => $this->payment_terms_custom ?? 'Custom',
            default  => ucfirst($this->payment_terms),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'badge-success',
            'inactive' => 'badge-secondary',
            'blocked'  => 'badge-danger',
            default    => 'badge-secondary',
        };
    }

    public function getFormattedCreditLimitAttribute(): string
    {
        return $this->credit_limit
            ? '₱' . number_format($this->credit_limit, 2)
            : 'No limit';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeLinkedToPortal($query)
    {
        return $query->whereNotNull('supplier_id');
    }
}
