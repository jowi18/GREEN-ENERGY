<?php

// ════════════════════════════════════════════
// app/Models/HrLeaveType.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveType extends Model
{
    protected $fillable = ['vendor_id', 'name', 'code', 'days_per_year', 'is_paid', 'is_active'];
    protected $casts = ['is_paid' => 'boolean', 'is_active' => 'boolean'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function requests()
    {
        return $this->hasMany(HrLeaveRequest::class, 'leave_type_id');
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}

