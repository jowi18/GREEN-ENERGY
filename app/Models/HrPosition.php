<?php

// ════════════════════════════════════════════
// app/Models/HrPosition.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPosition extends Model
{
    protected $fillable = ['vendor_id', 'department_id', 'title', 'description', 'base_salary_min', 'base_salary_max', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'base_salary_min' => 'decimal:2', 'base_salary_max' => 'decimal:2'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
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
