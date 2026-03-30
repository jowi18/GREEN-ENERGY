<?php

// ════════════════════════════════════════════════════════════════════════════
// app/Models/SolarPaymentRecord.php
// ════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarPaymentRecord extends Model
{
    protected $fillable = [
        'solar_payment_schedule_id',
        'solar_contract_id',
        'recorded_by',
        'receipt_number',
        'amount',
        'payment_method',
        'reference_code',
        'payment_date',
        'notes',
        'proof_path',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount'       => 'float',
        ];
    }

    public function schedule()
    {
        return $this->belongsTo(SolarPaymentSchedule::class, 'solar_payment_schedule_id');
    }

    public function contract()
    {
        return $this->belongsTo(SolarContract::class, 'solar_contract_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => '💵 Cash',
            'bank_transfer' => '🏦 Bank Transfer',
            'gcash'         => '📱 GCash',
            'maya'          => '📱 Maya',
            'paypal'        => '🌐 PayPal',
            'check'         => '📄 Check',
            default         => ucfirst($this->payment_method),
        };
    }

    // ── Boot: auto-update schedule amount_paid on save ────────────────────

    protected static function booted(): void
    {
        static::creating(function (SolarPaymentRecord $r) {
            if (empty($r->receipt_number)) {
                $count = static::whereDate('created_at', today())->count() + 1;
                $r->receipt_number = 'REC-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        $updateSchedule = function (SolarPaymentRecord $r) {
            $schedule = SolarPaymentSchedule::find($r->solar_payment_schedule_id);
            if (!$schedule) return;

            $totalPaid = SolarPaymentRecord::where('solar_payment_schedule_id', $r->solar_payment_schedule_id)
                ->sum('amount');

            $status = 'pending';
            if ($totalPaid >= $schedule->amount)       $status = 'paid';
            elseif ($totalPaid > 0)                    $status = 'partial';
            elseif ($schedule->isOverdue())            $status = 'overdue';

            $schedule->update(['amount_paid' => $totalPaid, 'status' => $status]);
        };

        static::created($updateSchedule);
        static::deleted($updateSchedule);
    }
}
