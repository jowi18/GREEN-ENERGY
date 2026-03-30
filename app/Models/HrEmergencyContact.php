<?php


// ════════════════════════════════════════════
// app/Models/HrEmergencyContact.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmergencyContact extends Model
{
    protected $fillable = ['hr_profile_id', 'name', 'relationship', 'phone', 'address', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];
    public function profile()
    {
        return $this->belongsTo(HrEmployeeProfile::class, 'hr_profile_id');
    }
}

