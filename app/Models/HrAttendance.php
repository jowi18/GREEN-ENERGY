<?php

// ════════════════════════════════════════════
// app/Models/HrAttendance.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAttendance extends Model
{
    protected $table = 'hr_attendance';
    protected $fillable = [
        'vendor_id',
        'hr_profile_id',
        'attendance_date',
        'time_in',
        'time_out',
        'time_in_lat',
        'time_in_lng',
        'time_out_lat',
        'time_out_lng',
        'time_in_valid_location',
        'time_out_valid_location',
        'minutes_late',
        'minutes_undertime',
        'minutes_overtime',
        'hours_worked',
        'status',
        'remarks',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'time_in_valid_location' => 'boolean',
        'time_out_valid_location' => 'boolean',
        'is_approved' => 'boolean',
        'hours_worked' => 'decimal:2',
    ];

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
    public function scopeForProfile($q, int $p)
    {
        return $q->where('hr_profile_id', $p);
    }
    public function scopeForDate($q, string $d)
    {
        return $q->where('attendance_date', $d);
    }
    public function scopeForPeriod($q, string $from, string $to)
    {
        return $q->whereBetween('attendance_date', [$from, $to]);
    }
}

