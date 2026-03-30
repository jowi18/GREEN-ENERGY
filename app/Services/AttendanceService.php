<?php

namespace App\Services;

use App\Models\HrAttendance;
use App\Models\HrAttendanceSetting;
use App\Models\HrEmployeeProfile;
use App\Models\HrLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private readonly PayrollService $payroll) {}

    /**
     * Time in — creates or updates attendance record.
     */
    public function timeIn(
        HrEmployeeProfile $profile,
        ?float $lat = null,
        ?float $lng = null
    ): HrAttendance {
        $today    = today()->format('Y-m-d');
        $settings = HrAttendanceSetting::forVendor($profile->vendor_id)->first()
            ?? new HrAttendanceSetting(['vendor_id'=>$profile->vendor_id,'work_start'=>'08:00','work_end'=>'17:00','grace_period_minutes'=>15,'overtime_threshold_minutes'=>30,'late_deduction_rate'=>1.0,'overtime_rate_multiplier'=>1.25,'require_location'=>false]);

        // Check if already timed in today
        $existing = HrAttendance::where('hr_profile_id', $profile->id)
            ->where('attendance_date', $today)
            ->first();

        if ($existing && $existing->time_in) {
            throw new \RuntimeException('You have already timed in today.');
        }

        // Validate location
        $validLocation = true;
        if ($lat !== null && $lng !== null) {
            $validLocation = $this->payroll->validateLocation($settings, $lat, $lng);
        }

        // Check if on approved leave
        $onLeave = HrLeaveRequest::where('hr_profile_id', $profile->id)
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $today)
            ->whereDate('date_to', '>=', $today)
            ->exists();

        $now     = now();
        $computed= $this->payroll->processAttendance($settings, $now->toDateTimeString(), null, $today);

        $record = HrAttendance::updateOrCreate(
            ['hr_profile_id' => $profile->id, 'attendance_date' => $today],
            [
                'vendor_id'              => $profile->vendor_id,
                'time_in'                => $now,
                'time_in_lat'            => $lat,
                'time_in_lng'            => $lng,
                'time_in_valid_location' => $validLocation,
                'minutes_late'           => $computed['minutesLate'],
                'status'                 => $onLeave ? 'leave' : 'present',
            ]
        );

        return $record;
    }

    /**
     * Time out — updates existing attendance record.
     */
    public function timeOut(
        HrEmployeeProfile $profile,
        ?float $lat = null,
        ?float $lng = null
    ): HrAttendance {
        $today = today()->format('Y-m-d');

        $record = HrAttendance::where('hr_profile_id', $profile->id)
            ->where('attendance_date', $today)
            ->whereNotNull('time_in')
            ->firstOrFail();

        if ($record->time_out) {
            throw new \RuntimeException('You have already timed out today.');
        }

        $settings = HrAttendanceSetting::forVendor($profile->vendor_id)->first()
            ?? new HrAttendanceSetting(['vendor_id'=>$profile->vendor_id,'work_start'=>'08:00','work_end'=>'17:00','grace_period_minutes'=>15,'overtime_threshold_minutes'=>30,'late_deduction_rate'=>1.0,'overtime_rate_multiplier'=>1.25,'require_location'=>false]);

        $validLocation = true;
        if ($lat !== null && $lng !== null) {
            $validLocation = $this->payroll->validateLocation($settings, $lat, $lng);
        }

        $now      = now();
        $computed = $this->payroll->processAttendance(
            $settings,
            $record->time_in->toDateTimeString(),
            $now->toDateTimeString(),
            $today
        );

        $record->update([
            'time_out'                => $now,
            'time_out_lat'            => $lat,
            'time_out_lng'            => $lng,
            'time_out_valid_location' => $validLocation,
            'minutes_late'            => $computed['minutesLate'],
            'minutes_undertime'       => $computed['minutesUndertime'],
            'minutes_overtime'        => $computed['minutesOvertime'],
            'hours_worked'            => round($computed['hoursWorked'], 2),
        ]);

        return $record->fresh();
    }

    /**
     * Manual attendance entry by HR Officer.
     */
    public function manualEntry(
        HrEmployeeProfile $profile,
        string  $date,
        ?string $timeIn,
        ?string $timeOut,
        string  $status = 'present',
        ?string $remarks = null
    ): HrAttendance {
        $settings = HrAttendanceSetting::forVendor($profile->vendor_id)->first()
            ?? new HrAttendanceSetting(['vendor_id'=>$profile->vendor_id,'work_start'=>'08:00','work_end'=>'17:00','grace_period_minutes'=>15,'overtime_threshold_minutes'=>30,'late_deduction_rate'=>1.0,'overtime_rate_multiplier'=>1.25,'require_location'=>false]);

        $computed = ['minutesLate'=>0,'minutesUndertime'=>0,'minutesOvertime'=>0,'hoursWorked'=>0];
        if ($timeIn) {
            $computed = $this->payroll->processAttendance(
                $settings,
                $date . ' ' . $timeIn,
                $timeOut ? $date . ' ' . $timeOut : null,
                $date
            );
        }

        return HrAttendance::updateOrCreate(
            ['hr_profile_id' => $profile->id, 'attendance_date' => $date],
            [
                'vendor_id'        => $profile->vendor_id,
                'time_in'          => $timeIn ? Carbon::parse($date . ' ' . $timeIn) : null,
                'time_out'         => $timeOut ? Carbon::parse($date . ' ' . $timeOut) : null,
                'status'           => $status,
                'remarks'          => $remarks,
                'minutes_late'     => $computed['minutesLate'],
                'minutes_undertime'=> $computed['minutesUndertime'],
                'minutes_overtime' => $computed['minutesOvertime'],
                'hours_worked'     => round($computed['hoursWorked'], 2),
                'is_approved'      => true,
            ]
        );
    }

    /**
     * Get today's attendance status for an employee.
     */
    public function todayStatus(HrEmployeeProfile $profile): array
    {
        $record = HrAttendance::where('hr_profile_id', $profile->id)
            ->where('attendance_date', today()->format('Y-m-d'))
            ->first();

        return [
            'has_record'  => (bool) $record,
            'timed_in'    => (bool) ($record?->time_in),
            'timed_out'   => (bool) ($record?->time_out),
            'time_in'     => $record?->time_in?->format('h:i A'),
            'time_out'    => $record?->time_out?->format('h:i A'),
            'hours_worked'=> $record?->hours_worked ?? 0,
            'minutes_late'=> $record?->minutes_late ?? 0,
            'status'      => $record?->status ?? 'not_in',
        ];
    }
}
