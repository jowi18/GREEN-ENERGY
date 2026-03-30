<?php

// ════════════════════════════════════════════
// app/Models/HrPayrollItem.php
// ════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayrollItem extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'vendor_id',
        'hr_profile_id',
        'basic_pay',
        'overtime_pay',
        'holiday_pay',
        'allowances',
        'other_earnings',
        'gross_pay',
        'late_deduction',
        'undertime_deduction',
        'absent_deduction',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'days_worked',
        'days_absent',
        'days_leave',
        'hours_overtime',
        'minutes_late',
        'minutes_undertime',
        'status',
        'computation_details',
    ];
    protected $casts = [
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'hours_overtime' => 'decimal:2',
        'computation_details' => 'array',
    ];
    public function period()
    {
        return $this->belongsTo(HrPayrollPeriod::class, 'payroll_period_id');
    }
    public function profile()
    {
        return $this->belongsTo(HrEmployeeProfile::class, 'hr_profile_id');
    }
    public function scopeForVendor($q, int $v)
    {
        return $q->where('vendor_id', $v);
    }
}
