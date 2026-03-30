<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/PurchaseOrder.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'purchase_request_id', 'vendor_id', 'supplier_id',
        'created_by', 'status', 'subtotal', 'tax_amount', 'shipping_cost',
        'total_amount', 'currency', 'expected_delivery_date', 'actual_delivery_date',
        'delivery_address', 'supplier_notes', 'vendor_notes', 'rejection_reason',
        'tracking_number', 'courier', 'submitted_at', 'supplier_responded_at',
        'shipped_at', 'delivered_at', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'               => 'float',
            'tax_amount'             => 'float',
            'shipping_cost'          => 'float',
            'total_amount'           => 'float',
            'expected_delivery_date' => 'date',
            'actual_delivery_date'   => 'date',
            'submitted_at'           => 'datetime',
            'supplier_responded_at'  => 'datetime',
            'shipped_at'             => 'datetime',
            'delivered_at'           => 'datetime',
            'confirmed_at'           => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()          { return $this->belongsTo(Vendor::class); }
    public function supplier()        { return $this->belongsTo(Supplier::class); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by'); }
    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class); }
    public function items()           { return $this->hasMany(PurchaseOrderItem::class); }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isShipped(): bool    { return $this->status === 'shipped'; }
    public function isDelivered(): bool  { return $this->status === 'delivered'; }
    public function isConfirmed(): bool  { return $this->status === 'confirmed'; }

    public function recalculateTotals(): void
    {
        $subtotal           = $this->items->sum('line_total');
        $this->subtotal     = $subtotal;
        $this->total_amount = $subtotal + $this->tax_amount + $this->shipping_cost;
        $this->save();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'       => 'badge-secondary',
            'submitted'   => 'badge-info',
            'approved'    => 'badge-primary',
            'rejected'    => 'badge-danger',
            'processing'  => 'badge-warning',
            'shipped'     => 'badge-info',
            'delivered'   => 'badge-success',
            'confirmed'   => 'badge-success',
            'cancelled'   => 'badge-danger',
            default       => 'badge-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'Draft',
            'submitted'  => 'Sent to Supplier',
            'approved'   => 'Supplier Approved',
            'rejected'   => 'Rejected',
            'processing' => 'Processing',
            'shipped'    => 'Shipped',
            'delivered'  => 'Delivered — Pending Confirmation',
            'confirmed'  => 'Confirmed & Received',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->status),
        };
    }

    public static function generateNumber(): string
    {
        $last = static::withTrashed()->latest('id')->value('po_number');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'PO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
