<?php

// ════════════════════════════════════════════
// app/Models/HrEmployeeProfile.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrEmployeeProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'employee_id',
        'department_id',
        'position_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birth_date',
        'birth_place',
        'civil_status',
        'citizenship',
        'blood_type',
        'phone',
        'personal_email',
        'address_permanent',
        'address_present',
        'date_hired',
        'date_regularized',
        'date_separated',
        'employment_status',
        'employment_type',
        'work_hours_per_day',
        'work_days_per_week',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'pay_frequency',
        'allowances',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
        'profile_photo_path',
        'notes',
        'is_archived',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'date_hired' => 'date',
        'date_regularized' => 'date',
        'date_separated' => 'date',
        'monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'allowances' => 'array',
        'is_archived' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $p) {
            // Auto-compute daily and hourly rates from monthly
            if ($p->monthly_rate) {
                $p->daily_rate  = round($p->monthly_rate / 26, 4); // PH standard 26 working days
                $p->hourly_rate = round($p->daily_rate / 8, 4);
            }
        });
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }
    public function position()
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }
    public function emergencyContacts()
    {
        return $this->hasMany(HrEmergencyContact::class, 'hr_profile_id');
    }
    public function employmentHistory()
    {
        return $this->hasMany(HrEmploymentHistory::class, 'hr_profile_id');
    }
    public function attendance()
    {
        return $this->hasMany(HrAttendance::class, 'hr_profile_id');
    }
    public function leaveRequests()
    {
        return $this->hasMany(HrLeaveRequest::class, 'hr_profile_id');
    }
    public function overtimeRequests()
    {
        return $this->hasMany(HrOvertimeRequest::class, 'hr_profile_id');
    }
    public function payrollItems()
    {
        return $this->hasMany(HrPayrollItem::class, 'hr_profile_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name, $this->suffix])));
    }

    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
    public function scopeActive($q)
    {
        return $q->where('is_archived', false)->whereNot('employment_status', 'terminated');
    }
}

