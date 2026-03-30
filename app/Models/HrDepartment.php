<?php
// ════════════════════════════════════════════
// app/Models/HrDepartment.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrDepartment extends Model
{
    use SoftDeletes;
    protected $fillable = ['vendor_id', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function positions()
    {
        return $this->hasMany(HrPosition::class, 'department_id');
    }
    public function profiles()
    {
        return $this->hasMany(HrEmployeeProfile::class, 'department_id');
    }
    public function scopeForVendor($q, int $vendorId)
    {
        return $q->where('vendor_id', $vendorId);
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}





















