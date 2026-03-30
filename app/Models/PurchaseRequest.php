<?php

// ═══════════════════════════════════════════════════════════════════════════
// app/Models/PurchaseRequest.php
// ═══════════════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pr_number', 'vendor_id', 'supplier_id', 'requested_by', 'approved_by',
        'status', 'requested_delivery_date', 'notes', 'rejection_reason',
        'submitted_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_delivery_date' => 'date',
            'submitted_at'            => 'datetime',
            'approved_at'             => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()       { return $this->belongsTo(Vendor::class); }
    public function supplier()     { return $this->belongsTo(Supplier::class); }
    public function requester()    { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()     { return $this->belongsTo(User::class, 'approved_by'); }
    public function items()        { return $this->hasMany(PurchaseRequestItem::class); }
    public function purchaseOrder(){ return $this->hasOne(PurchaseOrder::class); }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isConverted(): bool { return $this->status === 'converted'; }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->unit_price * $i->quantity_requested);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'badge-secondary',
            'submitted' => 'badge-info',
            'approved'  => 'badge-success',
            'rejected'  => 'badge-danger',
            'converted' => 'badge-primary',
            'cancelled' => 'badge-warning',
            default     => 'badge-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'converted' => 'Converted to PO',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    // Auto-generate PR number
    public static function generateNumber(): string
    {
        $last = static::withTrashed()->latest('id')->value('pr_number');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'PR-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
