<?php

// ════════════════════════════════════════════
// app/Models/HrOvertimeRequest.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrOvertimeRequest extends Model
{
    protected $fillable = [
        'vendor_id',
        'hr_profile_id',
        'overtime_date',
        'start_time',
        'end_time',
        'hours_requested',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approver_notes',
    ];
    protected $casts = ['overtime_date' => 'date', 'approved_at' => 'datetime', 'hours_requested' => 'decimal:2'];
    public function profile()
    {
        return $this->belongsTo(HrEmployeeProfile::class, 'hr_profile_id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
}

