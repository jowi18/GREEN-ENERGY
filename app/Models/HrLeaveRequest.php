<?php


// ════════════════════════════════════════════
// app/Models/HrLeaveRequest.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveRequest extends Model
{
    protected $fillable = [
        'vendor_id',
        'hr_profile_id',
        'leave_type_id',
        'date_from',
        'date_to',
        'days_requested',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approver_notes',
    ];
    protected $casts = ['date_from' => 'date', 'date_to' => 'date', 'approved_at' => 'datetime'];
    public function profile()
    {
        return $this->belongsTo(HrEmployeeProfile::class, 'hr_profile_id');
    }
    public function leaveType()
    {
        return $this->belongsTo(HrLeaveType::class, 'leave_type_id');
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
