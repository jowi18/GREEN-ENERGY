<?php

// ════════════════════════════════════════════════════════════════════════════
// app/Models/SolarContractAdjustment.php
// ════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarContractAdjustment extends Model
{
    protected $fillable = [
        'solar_contract_id',
        'requested_by',
        'status',
        'section',
        'notes',
        'vendor_response',
        'vendor_responded_at',
    ];

    protected function casts(): array
    {
        return [
            'vendor_responded_at' => 'datetime',
        ];
    }

    public function contract()
    {
        return $this->belongsTo(SolarContract::class, 'solar_contract_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'vd-badge--warning',
            'addressed' => 'vd-badge--active',
            'dismissed' => 'vd-badge--neutral',
            default     => 'vd-badge--neutral',
        };
    }
}
