<?php

namespace App\Services;

use App\Models\HrAttendance;
use App\Models\HrAttendanceSetting;
use App\Models\HrEmployeeProfile;
use App\Models\HrHoliday;
use App\Models\HrLeaveRequest;
use App\Models\HrOvertimeRequest;
use App\Models\HrPayrollItem;
use App\Models\HrPayrollPeriod;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    // ─────────────────────────────────────────────────────────────────
    // PHILIPPINE GOVERNMENT CONTRIBUTION TABLES (2024 rates)
    // ─────────────────────────────────────────────────────────────────

    /**
     * SSS Monthly contribution — employee share.
     * Table: https://www.sss.gov.ph/sss/appmanager/pages.do?page=contributionSchedule
     */
    public static function computeSss(float $monthlyRate): float
    {
        // Simplified SSS table (effective 2024)
        $table = [
            [4249.99,  180],
            [4749.99,  202.50],
            [5249.99,  225],
            [5749.99,  247.50],
            [6249.99,  270],
            [6749.99,  292.50],
            [7249.99,  315],
            [7749.99,  337.50],
            [8249.99,  360],
            [8749.99,  382.50],
            [9249.99,  405],
            [9749.99,  427.50],
            [10249.99, 450],
            [10749.99, 472.50],
            [11249.99, 495],
            [11749.99, 517.50],
            [12249.99, 540],
            [12749.99, 562.50],
            [13249.99, 585],
            [13749.99, 607.50],
            [14249.99, 630],
            [14749.99, 652.50],
            [15249.99, 675],
            [15749.99, 697.50],
            [16249.99, 720],
            [16749.99, 742.50],
            [17249.99, 765],
            [17749.99, 787.50],
            [18249.99, 810],
            [18749.99, 832.50],
            [19249.99, 855],
            [19749.99, 877.50],
            [20249.99, 900],
            [20749.99, 922.50],
            [21249.99, 945],
            [21749.99, 967.50],
            [22249.99, 990],
            [22749.99, 1012.50],
            [23249.99, 1035],
            [23749.99, 1057.50],
            [24249.99, 1080],
            [24749.99, 1102.50],
            [INF,      1125], // max
        ];

        if ($monthlyRate < 4250) return 180.00;

        foreach ($table as [$max, $contribution]) {
            if ($monthlyRate <= $max) return (float) $contribution;
        }

        return 1125.00;
    }

    /**
     * PhilHealth Monthly contribution — employee share (4% total, half from employee).
     * Effective 2024: 4% of basic salary, split 50/50.
     */
    public static function computePhilhealth(float $monthlyRate): float
    {
        $min = 10000; $max = 100000;
        $rate = 0.04;

        if ($monthlyRate < $min) return round($min * $rate / 2, 2);   // ₱200
        if ($monthlyRate > $max) return round($max * $rate / 2, 2);   // ₱2,000

        return round($monthlyRate * $rate / 2, 2);
    }

    /**
     * Pag-IBIG — employee share.
     * ≤ ₱1,500/mo → 1% | > ₱1,500/mo → 2% (max ₱100 for ≥ ₱5,000)
     */
    public static function computePagibig(float $monthlyRate): float
    {
        if ($monthlyRate <= 1500) return round($monthlyRate * 0.01, 2);
        return min(100.00, round($monthlyRate * 0.02, 2));
    }

    /**
     * Withholding tax — annual tax / 12 or / 24 for semi-monthly.
     * Uses TRAIN Law tax table (RA 10963).
     */
    public static function computeWithholdingTax(float $taxableIncome, string $frequency = 'semi_monthly'): float
    {
        // Annualize
        $multiplier    = $frequency === 'semi_monthly' ? 24 : 12;
        $annualIncome  = $taxableIncome * $multiplier;

        $annualTax = match(true) {
            $annualIncome <= 250_000          => 0,
            $annualIncome <= 400_000          => ($annualIncome - 250_000) * 0.20,
            $annualIncome <= 800_000          => 30_000 + ($annualIncome - 400_000) * 0.25,
            $annualIncome <= 2_000_000        => 130_000 + ($annualIncome - 800_000) * 0.30,
            $annualIncome <= 8_000_000        => 490_000 + ($annualIncome - 2_000_000) * 0.32,
            default                           => 2_410_000 + ($annualIncome - 8_000_000) * 0.35,
        };

        return round($annualTax / $multiplier, 2);
    }

    // ─────────────────────────────────────────────────────────────────
    // PAYROLL PERIOD GENERATION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Generate a payroll period for a vendor.
     * @param  'semi_monthly'|'monthly'  $frequency
     */
    public function createPeriod(
        int    $vendorId,
        string $frequency,
        string $dateFrom,
        string $dateTo,
        int    $createdBy
    ): HrPayrollPeriod {
        $from  = Carbon::parse($dateFrom);
        $to    = Carbon::parse($dateTo);
        $name  = $frequency === 'semi_monthly'
            ? $from->format('F d') . '–' . $to->format('d, Y')
            : $from->format('F Y');

        return HrPayrollPeriod::create([
            'vendor_id'   => $vendorId,
            'period_name' => $name,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'frequency'   => $frequency,
            'status'      => 'draft',
            'created_by'  => $createdBy,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // COMPUTE SINGLE EMPLOYEE PAYROLL ITEM
    // ─────────────────────────────────────────────────────────────────

    public function computeEmployee(
        HrPayrollPeriod    $period,
        HrEmployeeProfile  $profile
    ): HrPayrollItem {
        $dateFrom = $period->date_from->format('Y-m-d');
        $dateTo   = $period->date_to->format('Y-m-d');
        $vendorId = $period->vendor_id;

        $settings = HrAttendanceSetting::forVendor($vendorId)->first()
            ?? new HrAttendanceSetting([
                'work_start'              => '08:00',
                'work_end'                => '17:00',
                'overtime_rate_multiplier'=> 1.25,
                'late_deduction_rate'     => 1.0,
            ]);

        // ── Count working days in period ──────────────────────────────
        $workingDays = $this->countWorkingDays($vendorId, $dateFrom, $dateTo);

        // ── Basic pay for period ──────────────────────────────────────
        $monthlyRate = (float) $profile->monthly_rate;
        $dailyRate   = (float) $profile->daily_rate;
        $hourlyRate  = (float) $profile->hourly_rate;
        $frequency   = $period->frequency;

        // Semi-monthly = monthly / 2; monthly = full monthly rate
        $periodBasic = $frequency === 'semi_monthly'
            ? $monthlyRate / 2
            : $monthlyRate;

        // ── Fetch attendance for period ───────────────────────────────
        $attendances = HrAttendance::forProfile($profile->id)
            ->forPeriod($dateFrom, $dateTo)
            ->get();

        $daysWorked      = $attendances->whereIn('status', ['present','half_day'])->count();
        $daysAbsent      = $workingDays - $daysWorked - $attendances->where('status','leave')->count();
        $daysLeave       = $attendances->where('status','leave')->count();
        $minutesLate     = $attendances->sum('minutes_late');
        $minutesUndertime= $attendances->sum('minutes_undertime');
        $minutesOvertime = $attendances->sum('minutes_overtime');

        // ── Approved overtime requests ────────────────────────────────
        $approvedOT = HrOvertimeRequest::where('hr_profile_id', $profile->id)
            ->where('status', 'approved')
            ->whereBetween('overtime_date', [$dateFrom, $dateTo])
            ->sum('hours_requested');

        $totalOtHours = max((float)($minutesOvertime / 60), (float)$approvedOT);

        // ── Deductions from attendance ────────────────────────────────
        $lateDeduction      = round(($minutesLate / 60) * $hourlyRate * $settings->late_deduction_rate, 2);
        $undertimeDeduction = round(($minutesUndertime / 60) * $hourlyRate, 2);
        $absentDeduction    = round(max(0, $daysAbsent) * $dailyRate, 2);

        // ── Overtime pay ──────────────────────────────────────────────
        $overtimePay = round($totalOtHours * $hourlyRate * $settings->overtime_rate_multiplier, 2);

        // ── Allowances ────────────────────────────────────────────────
        $allowancesArr   = $profile->allowances ?? [];
        $allowancesTotal = collect($allowancesArr)->sum('amount');
        $allowancesPeriod= $frequency === 'semi_monthly' ? $allowancesTotal / 2 : $allowancesTotal;

        // ── Gross pay ─────────────────────────────────────────────────
        $grossPay = $periodBasic + $overtimePay + $allowancesPeriod;

        // ── Government contributions (based on monthly; divided for period) ─
        $sssMo       = self::computeSss($monthlyRate);
        $phMo        = self::computePhilhealth($monthlyRate);
        $pfMo        = self::computePagibig($monthlyRate);
        $divisor     = $frequency === 'semi_monthly' ? 2 : 1;
        $sssShare    = round($sssMo / $divisor, 2);
        $phShare     = round($phMo  / $divisor, 2);
        $pfShare     = round($pfMo  / $divisor, 2);

        // ── Withholding tax ───────────────────────────────────────────
        $taxableIncome = $grossPay - $sssShare - $phShare - $pfShare;
        $withholdingTax= max(0, self::computeWithholdingTax($taxableIncome, $frequency));

        // ── Totals ────────────────────────────────────────────────────
        $totalDeductions = $lateDeduction + $undertimeDeduction + $absentDeduction
            + $sssShare + $phShare + $pfShare + $withholdingTax;

        $netPay = round($grossPay - $totalDeductions, 2);

        // ── Computation details (for payslip transparency) ────────────
        $details = [
            'period'           => ['from'=>$dateFrom, 'to'=>$dateTo, 'working_days'=>$workingDays],
            'rates'            => ['monthly'=>$monthlyRate,'daily'=>$dailyRate,'hourly'=>$hourlyRate],
            'attendance'       => ['days_worked'=>$daysWorked,'days_absent'=>$daysAbsent,'days_leave'=>$daysLeave],
            'time_issues'      => ['minutes_late'=>$minutesLate,'minutes_undertime'=>$minutesUndertime],
            'overtime'         => ['hours'=>$totalOtHours,'rate_multiplier'=>$settings->overtime_rate_multiplier],
            'government'       => ['sss_monthly'=>$sssMo,'ph_monthly'=>$phMo,'pf_monthly'=>$pfMo],
            'tax'              => ['taxable_income'=>$taxableIncome,'annual_multiplier'=>$divisor === 2 ? 24 : 12],
        ];

        return HrPayrollItem::updateOrCreate(
            ['payroll_period_id' => $period->id, 'hr_profile_id' => $profile->id],
            [
                'vendor_id'             => $vendorId,
                'basic_pay'             => round($periodBasic, 2),
                'overtime_pay'          => $overtimePay,
                'holiday_pay'           => 0,
                'allowances'            => round($allowancesPeriod, 2),
                'other_earnings'        => 0,
                'gross_pay'             => round($grossPay, 2),
                'late_deduction'        => $lateDeduction,
                'undertime_deduction'   => $undertimeDeduction,
                'absent_deduction'      => $absentDeduction,
                'sss_contribution'      => $sssShare,
                'philhealth_contribution'=> $phShare,
                'pagibig_contribution'  => $pfShare,
                'withholding_tax'       => round($withholdingTax, 2),
                'other_deductions'      => 0,
                'total_deductions'      => round($totalDeductions, 2),
                'net_pay'               => $netPay,
                'days_worked'           => $daysWorked,
                'days_absent'           => max(0, $daysAbsent),
                'days_leave'            => $daysLeave,
                'hours_overtime'        => round($totalOtHours, 2),
                'minutes_late'          => $minutesLate,
                'minutes_undertime'     => $minutesUndertime,
                'status'                => 'draft',
                'computation_details'   => $details,
            ]
        );
    }

    /**
     * Process all active employees in a payroll period.
     */
    public function computeAll(HrPayrollPeriod $period): array
    {
        $profiles = HrEmployeeProfile::forVendor($period->vendor_id)
            ->active()
            ->get();

        $results = [];
        foreach ($profiles as $profile) {
            $results[] = $this->computeEmployee($period, $profile);
        }

        return $results;
    }

    // ─────────────────────────────────────────────────────────────────
    // PERIOD APPROVAL WORKFLOW
    // ─────────────────────────────────────────────────────────────────

    public function submitForApproval(HrPayrollPeriod $period, int $userId): HrPayrollPeriod
    {
        if ($period->status !== 'draft') {
            throw new \RuntimeException('Payroll must be in draft status to submit for approval.');
        }
        $period->update(['status' => 'pending_approval']);
        return $period->fresh();
    }

    public function approve(HrPayrollPeriod $period, int $approverId, ?string $notes = null): HrPayrollPeriod
    {
        if ($period->status !== 'pending_approval') {
            throw new \RuntimeException('Payroll must be pending approval to approve.');
        }
        DB::transaction(function () use ($period, $approverId, $notes) {
            $period->update([
                'status'      => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'notes'       => $notes,
            ]);
            // Finalize all items
            HrPayrollItem::where('payroll_period_id', $period->id)
                ->update(['status' => 'final']);
        });
        return $period->fresh();
    }

    public function reject(HrPayrollPeriod $period, int $approverId, string $notes): HrPayrollPeriod
    {
        $period->update([
            'status'      => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'notes'       => $notes,
        ]);
        return $period->fresh();
    }

    // ─────────────────────────────────────────────────────────────────
    // ATTENDANCE COMPUTATION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Process a time-in/out record and compute late, undertime, overtime.
     */
    public function processAttendance(
        HrAttendanceSetting $settings,
        string $timeIn,
        ?string $timeOut,
        string $date
    ): array {
        $workStart  = Carbon::parse($date . ' ' . $settings->work_start);
        $workEnd    = Carbon::parse($date . ' ' . $settings->work_end);
        $schedHours = $workStart->diffInMinutes($workEnd) / 60;

        $tIn  = Carbon::parse($timeIn);
        $tOut = $timeOut ? Carbon::parse($timeOut) : null;

        $grace = $settings->grace_period_minutes;

        // Late: if time-in is after work start + grace
        $minutesLate = 0;
        if ($tIn->gt($workStart->copy()->addMinutes($grace))) {
            $minutesLate = $tIn->diffInMinutes($workStart);
        }

        $minutesUndertime = 0;
        $minutesOvertime  = 0;
        $hoursWorked      = 0;

        if ($tOut) {
            $hoursWorked = $tIn->diffInMinutes($tOut) / 60;

            // Undertime: left before work end
            if ($tOut->lt($workEnd)) {
                $minutesUndertime = $tOut->diffInMinutes($workEnd);
            }

            // Overtime: worked more than schedule + OT threshold
            $otThreshold = $settings->overtime_threshold_minutes;
            $totalMins   = $tIn->diffInMinutes($tOut);
            $scheduleMins= $schedHours * 60;
            if ($totalMins > $scheduleMins + $otThreshold) {
                $minutesOvertime = $totalMins - $scheduleMins;
            }
        }

        return compact('minutesLate', 'minutesUndertime', 'minutesOvertime', 'hoursWorked');
    }

    /**
     * Count working days between two dates (Mon–Fri, excluding holidays).
     */
    public function countWorkingDays(int $vendorId, string $from, string $to): int
    {
        $period    = CarbonPeriod::create($from, $to);
        $holidays  = HrHoliday::forVendor($vendorId)
            ->whereBetween('holiday_date', [$from, $to])
            ->where('type', 'regular')
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $count = 0;
        foreach ($period as $day) {
            if ($day->isWeekend()) continue;
            if (in_array($day->format('Y-m-d'), $holidays)) continue;
            $count++;
        }
        return $count;
    }

    /**
     * Validate employee location against office fence.
     */
    public function validateLocation(
        HrAttendanceSetting $settings,
        float $lat,
        float $lng
    ): bool {
        if (! $settings->require_location) return true;
        if (! $settings->office_latitude || ! $settings->office_longitude) return true;

        // Haversine distance in meters
        $R     = 6371000;
        $φ1    = deg2rad($settings->office_latitude);
        $φ2    = deg2rad($lat);
        $Δφ    = deg2rad($lat  - $settings->office_latitude);
        $Δλ    = deg2rad($lng  - $settings->office_longitude);
        $a     = sin($Δφ/2)**2 + cos($φ1)*cos($φ2)*sin($Δλ/2)**2;
        $dist  = $R * 2 * atan2(sqrt($a), sqrt(1-$a));

        return $dist <= $settings->location_radius_meters;
    }
}
