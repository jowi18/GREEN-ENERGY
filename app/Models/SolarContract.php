<?php

// ════════════════════════════════════════════════════════════════════════════
// app/Models/SolarContract.php
// ════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolarContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_number',
        'solar_project_id',
        'solar_quotation_id',
        'vendor_id',
        'customer_id',
        'created_by',
        'version',
        'status',
        'scope_of_work',
        'warranty_terms',
        'penalties_cancellation',
        'custom_clauses',
        'payment_mode',
        'contract_amount',
        'payment_start_date',
        'installment_count',
        'installment_frequency',
        'customer_adjustment_notes',
        'customer_viewed_at',
        'customer_approved_at',
        'customer_signature',
        'approved_by',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_start_date'     => 'date',
            'customer_viewed_at'     => 'datetime',
            'customer_approved_at'   => 'datetime',
            'signed_at'              => 'datetime',
            'contract_amount'        => 'float',
        ];
    }

    // ── Status labels ─────────────────────────────────────────────────────

    public const STATUS_LABELS = [
        'draft'                => 'Draft',
        'sent'                 => 'Sent to Customer',
        'adjustment_requested' => 'Adjustment Requested',
        'approved'             => 'Approved by Customer',
        'signed'               => 'Signed',
        'cancelled'            => 'Cancelled',
    ];

    public const PAYMENT_MODE_LABELS = [
        'full'           => 'Full Payment',
        'installment'    => 'Installment',
        'progress_based' => 'Progress-Based',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }

    public function quotation()
    {
        return $this->belongsTo(SolarQuotation::class, 'solar_quotation_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustments()
    {
        return $this->hasMany(SolarContractAdjustment::class)->latest();
    }

    public function pendingAdjustments()
    {
        return $this->hasMany(SolarContractAdjustment::class)->where('status', 'pending');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(SolarPaymentSchedule::class)->orderBy('installment_number');
    }

    public function paymentRecords()
    {
        return $this->hasMany(SolarPaymentRecord::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft'                => 'vd-badge--neutral',
            'sent'                 => 'vd-badge--warning',
            'adjustment_requested' => 'vd-badge--warning',
            'approved'             => 'vd-badge--info',
            'signed'               => 'vd-badge--active',
            'cancelled'            => 'vd-badge--danger',
            default                => 'vd-badge--neutral',
        };
    }

    public function getPaymentModeLabelAttribute(): string
    {
        return self::PAYMENT_MODE_LABELS[$this->payment_mode] ?? ucfirst($this->payment_mode);
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->paymentSchedules->sum('amount_paid');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->contract_amount - $this->total_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }

    public function isApprovedOrSigned(): bool
    {
        return in_array($this->status, ['approved', 'signed']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Generate payment schedules based on payment_mode.
     */
    public function generatePaymentSchedules(): void
    {
        $this->paymentSchedules()->delete();

        $project = $this->project;

        if ($this->payment_mode === 'full') {
            SolarPaymentSchedule::create([
                'solar_contract_id'  => $this->id,
                'solar_project_id'   => $this->solar_project_id,
                'vendor_id'          => $this->vendor_id,
                'customer_id'        => $this->customer_id,
                'installment_number' => 1,
                'label'              => 'Full Payment',
                'amount'             => $this->contract_amount,
                'due_date'           => $this->payment_start_date,
                'status'             => 'pending',
            ]);

        } elseif ($this->payment_mode === 'installment') {
            $count     = $this->installment_count ?? 3;
            $perAmount = round($this->contract_amount / $count, 2);
            $startDate = $this->payment_start_date ?? now();

            for ($i = 1; $i <= $count; $i++) {
                $dueDate = match ($this->installment_frequency) {
                    'weekly'    => $startDate->copy()->addWeeks($i - 1),
                    'quarterly' => $startDate->copy()->addMonths(($i - 1) * 3),
                    default     => $startDate->copy()->addMonths($i - 1),
                };

                SolarPaymentSchedule::create([
                    'solar_contract_id'  => $this->id,
                    'solar_project_id'   => $this->solar_project_id,
                    'vendor_id'          => $this->vendor_id,
                    'customer_id'        => $this->customer_id,
                    'installment_number' => $i,
                    'label'              => $i === 1 ? 'Down Payment' : "Payment #{$i}",
                    'amount'             => $i === $count
                        ? $this->contract_amount - ($perAmount * ($count - 1)) // fix rounding
                        : $perAmount,
                    'due_date'           => $dueDate->format('Y-m-d'),
                    'status'             => 'pending',
                ]);
            }

        } elseif ($this->payment_mode === 'progress_based') {
            // Standard solar progress payments
            $milestones = [
                ['label' => 'Down Payment (Contract Signing)',  'milestone' => 'contract_signed',          'pct' => 50],
                ['label' => 'Progress Payment (Installation)',  'milestone' => 'installation_completed',   'pct' => 30],
                ['label' => 'Final Payment (System Activation)','milestone' => 'system_activated',         'pct' => 20],
            ];

            foreach ($milestones as $i => $m) {
                SolarPaymentSchedule::create([
                    'solar_contract_id'  => $this->id,
                    'solar_project_id'   => $this->solar_project_id,
                    'vendor_id'          => $this->vendor_id,
                    'customer_id'        => $this->customer_id,
                    'installment_number' => $i + 1,
                    'label'              => $m['label'],
                    'milestone_status'   => $m['milestone'],
                    'amount'             => round($this->contract_amount * ($m['pct'] / 100), 2),
                    'due_date'           => null, // triggered by milestone
                    'status'             => 'pending',
                ]);
            }
        }
    }

    // ── Boot ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (SolarContract $c) {
            if (empty($c->contract_number)) {
                $randomDigits = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $count = static::whereDate('created_at', today())->count() + 1;
                $c->contract_number = 'CN-' . now()->format('Ymd') . '-' .$randomDigits;
            }
        });
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('solar_project_id', $projectId);
    }
}









