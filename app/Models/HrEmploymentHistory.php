<?php


// ════════════════════════════════════════════
// app/Models/HrEmploymentHistory.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmploymentHistory extends Model
{
    protected $table = 'hr_employment_history';
    protected $fillable = ['hr_profile_id', 'company', 'position', 'date_from', 'date_to', 'salary', 'reason_for_leaving'];
    protected $casts = ['date_from' => 'date', 'date_to' => 'date', 'salary' => 'decimal:2'];
    public function profile()
    {
        return $this->belongsTo(HrEmployeeProfile::class, 'hr_profile_id');
    }
}
