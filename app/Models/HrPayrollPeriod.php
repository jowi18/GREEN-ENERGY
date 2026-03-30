<?php

// ════════════════════════════════════════════
// app/Models/HrPayrollPeriod.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayrollPeriod extends Model
{
    protected $fillable = [
        'vendor_id',
        'period_name',
        'date_from',
        'date_to',
        'frequency',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];
    protected $casts = ['date_from' => 'date', 'date_to' => 'date', 'approved_at' => 'datetime'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function items()
    {
        return $this->hasMany(HrPayrollItem::class, 'payroll_period_id');
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
    public function getTotalNetPayAttribute()
    {
        return $this->items->sum('net_pay');
    }
    public function getTotalGrossPayAttribute()
    {
        return $this->items->sum('gross_pay');
    }
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }
}
