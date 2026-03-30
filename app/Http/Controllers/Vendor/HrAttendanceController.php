<?php


// ════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/HrAttendanceController.php
// ════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\HrAttendance;
use App\Models\HrAttendanceSetting;
use App\Models\HrEmployeeProfile;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Attendance overview ────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();
        $date   = $request->input('date', today()->format('Y-m-d'));

        $records = HrAttendance::with('profile')
            ->forVendor($vendor->id)
            ->where('attendance_date', $date)
            ->get();

        $profiles = HrEmployeeProfile::with(['attendance' => function ($q) use ($date) {
            $q->where('attendance_date', $date);
        }])->forVendor($vendor->id)->active()->get();

        $settings = HrAttendanceSetting::forVendor($vendor->id)->first();

        return view('vendor.hr.attendance.index', compact('records','profiles','date','settings'));
    }

    // ── Manual entry ───────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'hr_profile_id' => ['required', 'exists:hr_employee_profiles,id'],
            'date'          => ['required', 'date'],
            'time_in'       => ['nullable', 'date_format:H:i'],
            'time_out'      => ['nullable', 'date_format:H:i'],
            'status'        => ['required', 'in:present,absent,half_day,holiday,leave,rest_day'],
            'remarks'       => ['nullable', 'string', 'max:300'],
        ]);

        $profile = HrEmployeeProfile::findOrFail($data['hr_profile_id']);

        $this->attendance->manualEntry(
            $profile,
            $data['date'],
            $data['time_in'] ?? null,
            $data['time_out'] ?? null,
            $data['status'],
            $data['remarks'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attendance recorded.']);
        }

        return back()->with('success', 'Attendance entry saved.');
    }

    // ── Settings ───────────────────────────────────────────────────────

    public function settings()
    {
        $vendor   = $this->vendor();
        $settings = HrAttendanceSetting::forVendor($vendor->id)->first()
            ?? new HrAttendanceSetting(['vendor_id' => $vendor->id]);

        return view('vendor.hr.attendance.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $vendor = $this->vendor();
        $data   = $request->validate([
            'work_start'               => ['required', 'date_format:H:i'],
            'work_end'                 => ['required', 'date_format:H:i'],
            'grace_period_minutes'     => ['required', 'integer', 'min:0', 'max:60'],
            'overtime_threshold_minutes'=> ['required', 'integer', 'min:0', 'max:60'],
            'overtime_rate_multiplier' => ['required', 'numeric', 'min:1'],
            'late_deduction_rate'      => ['required', 'numeric', 'min:0'],
            'require_location'         => ['boolean'],
            'office_latitude'          => ['nullable', 'numeric'],
            'office_longitude'         => ['nullable', 'numeric'],
            'location_radius_meters'   => ['nullable', 'integer', 'min:50'],
        ]);

        HrAttendanceSetting::updateOrCreate(
            ['vendor_id' => $vendor->id],
            array_merge($data, ['require_location' => $request->boolean('require_location')])
        );

        return back()->with('success', 'Attendance settings updated.');
    }

    // ── Monthly report ─────────────────────────────────────────────────

    public function report(Request $request)
    {
        $vendor   = $this->vendor();
        $month    = $request->input('month', today()->format('Y-m'));
        $from     = $month . '-01';
        $to       = Carbon::parse($from)->endOfMonth()->format('Y-m-d');

        $profiles  = HrEmployeeProfile::with(['attendance' => function ($q) use ($from, $to) {
            $q->whereBetween('attendance_date', [$from, $to]);
        }])->forVendor($vendor->id)->active()->get();

        return view('vendor.hr.attendance.report', compact('profiles','month','from','to'));
    }


    public function poll(Request $request)
    {
        $vendor  = $this->vendor();
        $date    = today()->format('Y-m-d');

        $records = HrAttendance::with('profile')
            ->forVendor($vendor->id)
            ->where('attendance_date', $date)
            ->get();

        $settings = HrAttendanceSetting::forVendor($vendor->id)->first();
        $workStart = $settings ? $settings->work_start : '08:00';
        $workEnd   = $settings ? $settings->work_end   : '17:00';

        $payload = $records->keyBy('hr_profile_id')->map(fn ($att) => [
            'status'            => $att->status,
            'time_in'           => $att->time_in?->format('h:i A'),
            'time_out'          => $att->time_out?->format('h:i A'),
            'hours_worked'      => number_format($att->hours_worked, 2),
            'minutes_late'      => $att->minutes_late,
            'minutes_overtime'  => $att->minutes_overtime,
            'minutes_undertime' => $att->minutes_undertime,
            'valid_location'    => $att->time_in_valid_location,
            'timed_in'          => (bool) $att->time_in,
            'timed_out'         => (bool) $att->time_out,
        ]);

        // If requester is an employee — also return their own today status
        $myStatus = null;
        if (auth()->user()->isEmployee()) {
            $profile = HrEmployeeProfile::where('employee_id', auth()->user()->employee->id)->first();
            if ($profile) {
                $mine = $records->where('hr_profile_id', $profile->id)->first();
                $myStatus = [
                    'timed_in'          => (bool) $mine?->time_in,
                    'timed_out'         => (bool) $mine?->time_out,
                    'time_in'           => $mine?->time_in?->format('h:i A'),
                    'time_out'          => $mine?->time_out?->format('h:i A'),
                    'hours_worked'      => number_format($mine?->hours_worked ?? 0, 2),
                    'minutes_late'      => $mine?->minutes_late ?? 0,
                    'minutes_overtime'  => $mine?->minutes_overtime ?? 0,
                    'status'            => $mine?->status ?? 'not_in',
                ];
            }
        }

        return response()->json([
            'records'     => $payload,
            'my_status'   => $myStatus,
            'server_time' => now()->format('h:i:s A'),
            'work_start'  => $workStart,
            'work_end'    => $workEnd,
        ]);
    }

}
