<?php


// ════════════════════════════════════════════════════════════════════════════
// app/Models/SolarPaymentSchedule.php
// ════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarPaymentSchedule extends Model
{
    protected $fillable = [
        'solar_contract_id',
        'solar_project_id',
        'vendor_id',
        'customer_id',
        'reference_number',
        'installment_number',
        'label',
        'milestone_status',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'    => 'date',
            'amount'      => 'float',
            'amount_paid' => 'float',
        ];
    }

    public function contract()
    {
        return $this->belongsTo(SolarContract::class, 'solar_contract_id');
    }

    public function project()
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }

    public function records()
    {
        return $this->hasMany(SolarPaymentRecord::class);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->amount - $this->amount_paid);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'vd-badge--neutral',
            'partial'  => 'vd-badge--warning',
            'paid'     => 'vd-badge--active',
            'overdue'  => 'vd-badge--danger',
            'waived'   => 'vd-badge--neutral',
            default    => 'vd-badge--neutral',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['paid', 'waived']);
    }

    // ── Boot: auto-generate reference number ──────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (SolarPaymentSchedule $s) {
            if (empty($s->reference_number)) {
                $count = static::whereDate('created_at', today())->count() + 1;
                $s->reference_number = 'PAY-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
