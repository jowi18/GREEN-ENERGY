<?php

// ════════════════════════════════════════════
// app/Models/HrHoliday.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrHoliday extends Model
{
    protected $fillable = ['vendor_id', 'name', 'holiday_date', 'type'];
    protected $casts = ['holiday_date' => 'date'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
    public static function isHoliday(int $vendorId, string $date): bool
    {
        return static::where('vendor_id', $vendorId)->whereDate('holiday_date', $date)->exists();
    }
    public static function getHoliday(int $vendorId, string $date): ?self
    {
        return static::where('vendor_id', $vendorId)->whereDate('holiday_date', $date)->first();
    }
}

