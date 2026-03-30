<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class WarrantyRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number',
        'customer_id',
        'vendor_id',
        'order_item_id',
        'product_id',
        'request_type',
        'status',
        'issue_description',
        'serial_number',
        'issue_images',
        'proof_of_purchase_images',
        'product_images',
        'purchase_receipt_path',
        'customer_notes',
        'assigned_technician',
        'technician_name',
        'scheduled_at',
        'service_date',
        'service_time_slot',
        'completed_at',
        'technician_notes',
        'resolution_notes',
        'resolution_type',
        'rejection_reason',
        'completion_images',
        'service_history',
        'warranty_expires_at',
        'is_within_warranty',
        'approved_by',
        'approved_at',
        'assigned_technician_id',
    ];

    protected function casts(): array
    {
        return [
            'issue_images'              => 'array',
            'proof_of_purchase_images'  => 'array',
            'product_images'            => 'array',
            'completion_images'         => 'array',
            'service_history'           => 'array',
            'scheduled_at'              => 'datetime',
            'completed_at'              => 'datetime',
            'approved_at'               => 'datetime',
            'warranty_expires_at'       => 'date',
            'is_within_warranty'        => 'boolean',
            'service_date' => 'date',
        ];
    }

    // ── Status pipeline ───────────────────────────────────────────────────

    public const STATUS_LABELS = [
        'submitted'    => 'Submitted',
        'under_review' => 'Under Review',
        'approved'     => 'Approved',
        'scheduled'    => 'Scheduled',
        'in_progress'  => 'In Progress',
        'completed'    => 'Completed',
        'rejected'     => 'Rejected',
        'closed'       => 'Closed',
    ];

    public const STATUS_PIPELINE = [
        'submitted', 'under_review', 'approved', 'scheduled', 'in_progress', 'completed',
    ];

    public const ALLOWED_TRANSITIONS = [
        'submitted'    => ['under_review', 'rejected'],
        'under_review' => ['approved', 'rejected'],
        'approved'     => ['scheduled', 'rejected'],
        'scheduled'    => ['in_progress', 'rejected'],
        'in_progress'  => ['completed', 'rejected'],
        'completed'    => ['closed'],
        'rejected'     => [],
        'closed'       => [],
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }


    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'assigned_technician');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'submitted'    => 'pending',
            'under_review' => 'info',
            'approved'     => 'blue',
            'scheduled'    => 'warning',
            'in_progress'  => 'orange',
            'completed'    => 'active',
            'rejected'     => 'danger',
            'closed'       => 'neutral',
            default        => 'neutral',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'submitted'    => 'vd-badge--pending',
            'under_review' => 'vd-badge--info',
            'approved'     => 'vd-badge--info',
            'scheduled'    => 'vd-badge--warning',
            'in_progress'  => 'vd-badge--warning',
            'completed'    => 'vd-badge--active',
            'rejected'     => 'vd-badge--danger',
            'closed'       => 'vd-badge--neutral',
            default        => 'vd-badge--neutral',
        };
    }

    public function getRequestTypeLabelAttribute(): string
    {
        return match($this->request_type) {
            'warranty_repair'      => '🔧 Repair',
            'warranty_replacement' => '🔁 Replacement',
            'warranty_inspection'  => '🔍 Inspection',
            default                => ucfirst(str_replace('_',' ',$this->request_type)),
        };
    }

    public function getIsExpiredWarrantyAttribute(): bool
    {
        if (! $this->warranty_expires_at) return false;
        return $this->warranty_expires_at->isPast();
    }

    public function getAllImagesAttribute(): array
    {
        return array_merge(
            $this->issue_images ?? [],
            $this->product_images ?? [],
            $this->completion_images ?? [],
        );
    }

    // ── Service history log ───────────────────────────────────────────────

    public function addHistoryEntry(string $action, string $notes = '', ?int $userId = null): void
    {
        $history   = $this->service_history ?? [];
        $history[] = [
            'timestamp' => now()->toISOString(),
            'action'    => $action,
            'notes'     => $notes,
            'user_id'   => $userId ?? auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
        ];
        $this->update(['service_history' => $history]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['completed', 'closed', 'rejected']);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }

    public function getPipelineIndexAttribute(): int
    {
        return array_search($this->status, self::STATUS_PIPELINE) ?: 0;
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['completed', 'closed', 'rejected']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    // ── Boot ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (WarrantyRequest $wr) {
            if (empty($wr->request_number)) {
                $count = static::whereDate('created_at', today())->count() + 1;
                $wr->request_number = 'WR-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
