<?php
// ════════════════════════════════════════════════════════════════
// app/Models/ServiceRequest.php
// ════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number', 'customer_id', 'vendor_id', 'product_id',
        'service_type', 'service_title', 'description',
        'preferred_date', 'preferred_time',
        'scheduled_date', 'scheduled_time', 'assigned_to',
        'service_address', 'service_city', 'service_province',
        'service_postal', 'service_latitude', 'service_longitude',
        'status', 'quoted_amount', 'quote_details',
        'quote_valid_until', 'quoted_at',
        'customer_notes', 'vendor_notes', 'rejection_reason',
        'started_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'preferred_date'    => 'date',
        'scheduled_date'    => 'date',
        'quote_valid_until' => 'date',
        'quoted_at'         => 'datetime',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
        'quoted_amount'     => 'decimal:2',
        'service_latitude'  => 'decimal:7',
        'service_longitude' => 'decimal:7',
    ];

    // ── Auto-generate request number ──────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $sr) {
            if (! $sr->request_number) {
                $sr->request_number = 'SR-' . now()->format('Ymd') . '-'
                    . str_pad(
                        (static::whereDate('created_at', today())->count() + 1),
                        4, '0', STR_PAD_LEFT
                    );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function proofs()
    {
        return $this->hasMany(ServiceRequestProof::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(
            \App\Models\Employee::class,
            'service_request_assignees',
            'service_request_id',
            'employee_id'
        )->withPivot(['role', 'assigned_at', 'assigned_by'])
         ->withTimestamps();
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
        return $query->whereNotIn('status', ['completed', 'cancelled', 'rejected']);
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'submitted'      => 'yellow',
            'reviewing'      => 'blue',
            'quoted'         => 'orange',
            'quote_accepted' => 'green',
            'quote_rejected' => 'red',
            'scheduled'      => 'blue',
            'in_progress'    => 'orange',
            'completed'      => 'green',
            'rejected'       => 'red',
            'cancelled'      => 'neutral',
            default          => 'neutral',
        };
    }

    public function getIsOpenAttribute(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled', 'rejected']);
    }
}



