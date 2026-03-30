<?php

// ════════════════════════════════════════════
// app/Models/HrAttendanceSetting.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAttendanceSetting extends Model
{
    protected $fillable = [
        'vendor_id',
        'work_start',
        'work_end',
        'grace_period_minutes',
        'overtime_threshold_minutes',
        'late_deduction_rate',
        'overtime_rate_multiplier',
        'holiday_rate_multiplier',
        'office_latitude',
        'office_longitude',
        'location_radius_meters',
        'require_location',
    ];
    protected $casts = ['require_location' => 'boolean'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
}

