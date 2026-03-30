<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\HrAttendance;
use App\Models\HrEmployeeProfile;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\HrOvertimeRequest;
use App\Models\HrPayrollItem;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrSelfServiceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    private function profile(): ?HrEmployeeProfile
    {
        $employee = auth()->user()->employee;
        if (! $employee) return null;
        return HrEmployeeProfile::where('employee_id', $employee->id)->first();
    }

    // ── Dashboard / Time-in-out ────────────────────────────────────────

    public function dashboard()
    {
        $profile = $this->profile();
        if (! $profile) {
            return redirect()->back()->with('error', 'HR profile not found. Please contact HR.');
        }

        $status = $this->attendance->todayStatus($profile);
        $recentAttendance = HrAttendance::forProfile($profile->id)
            ->orderByDesc('attendance_date')
            ->limit(7)
            ->get();

        $pendingLeave = HrLeaveRequest::where('hr_profile_id', $profile->id)
            ->where('status', 'pending')->count();

        return view('vendor.hr.employee.dashboard', compact(
            'profile','status','recentAttendance','pendingLeave'
        ));
    }

    // ── Time In ───────────────────────────────────────────────────────

    public function timeIn(Request $request)
    {
        $profile = $this->profile();
        if (! $profile) {
            return response()->json(['success'=>false,'message'=>'HR profile not found.'], 404);
        }

        $request->validate([
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        try {
            $record = $this->attendance->timeIn(
                $profile,
                $request->filled('latitude')  ? (float) $request->latitude  : null,
                $request->filled('longitude') ? (float) $request->longitude : null,
            );

            return response()->json([
                'success'              => true,
                'time_in'              => $record->time_in->format('h:i A'),
                'minutes_late'         => $record->minutes_late,
                'valid_location'       => $record->time_in_valid_location,
                'message'              => 'Time in recorded at ' . $record->time_in->format('h:i A'),
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 422);
        }
    }

    // ── Time Out ──────────────────────────────────────────────────────

    public function timeOut(Request $request)
    {
        $profile = $this->profile();
        if (! $profile) {
            return response()->json(['success'=>false,'message'=>'HR profile not found.'], 404);
        }

        try {
            $record = $this->attendance->timeOut(
                $profile,
                $request->filled('latitude')  ? (float) $request->latitude  : null,
                $request->filled('longitude') ? (float) $request->longitude : null,
            );

            return response()->json([
                'success'      => true,
                'time_out'     => $record->time_out->format('h:i A'),
                'hours_worked' => round($record->hours_worked, 2),
                'message'      => 'Time out recorded. Total hours: ' . round($record->hours_worked, 2) . 'h',
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 422);
        }
    }

    // ── My Attendance ─────────────────────────────────────────────────

    public function myAttendance(Request $request)
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $month = $request->input('month', today()->format('Y-m'));
        $from  = $month . '-01';
        $to    = Carbon::parse($from)->endOfMonth()->format('Y-m-d');

        $records = HrAttendance::forProfile($profile->id)
            ->whereBetween('attendance_date', [$from, $to])
            ->orderBy('attendance_date')
            ->get();

        return view('vendor.hr.employee.attendance', compact('profile','records','month'));
    }

    // ── Leave Requests ────────────────────────────────────────────────

    public function myLeaves()
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $requests   = HrLeaveRequest::with('leaveType')
            ->where('hr_profile_id', $profile->id)
            ->latest()->paginate(15);

        $leaveTypes = HrLeaveType::forVendor($profile->vendor_id)->active()->get();

        return view('vendor.hr.employee.leaves', compact('profile','requests','leaveTypes'));
    }

    public function storeLeave(Request $request)
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $data = $request->validate([
            'leave_type_id' => ['required','exists:hr_leave_types,id'],
            'date_from'     => ['required','date','after_or_equal:today'],
            'date_to'       => ['required','date','after_or_equal:date_from'],
            'reason'        => ['required','string','max:1000'],
        ]);

        $days = Carbon::parse($data['date_from'])->diffInWeekdays(
            Carbon::parse($data['date_to'])->addDay()
        );

        HrLeaveRequest::create(array_merge($data, [
            'vendor_id'     => $profile->vendor_id,
            'hr_profile_id' => $profile->id,
            'days_requested'=> max(1, $days),
            'status'        => 'pending',
        ]));

        return back()->with('success', 'Leave request submitted for approval.');
    }

    public function cancelLeave(HrLeaveRequest $leaveRequest)
    {
        if ($leaveRequest->hr_profile_id !== $this->profile()?->id) abort(403);
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error','Only pending requests can be cancelled.');
        }
        $leaveRequest->update(['status'=>'cancelled']);
        return back()->with('success','Leave request cancelled.');
    }

    // ── Overtime Requests ──────────────────────────────────────────────

    public function myOvertime()
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $requests = HrOvertimeRequest::where('hr_profile_id',$profile->id)->latest()->paginate(15);

        return view('vendor.hr.employee.overtime', compact('profile','requests'));
    }

    public function storeOvertime(Request $request)
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $data = $request->validate([
            'overtime_date'  => ['required','date'],
            'start_time'     => ['required','date_format:H:i'],
            'end_time'       => ['required','date_format:H:i','after:start_time'],
            'reason'         => ['required','string','max:1000'],
        ]);

        $hours = Carbon::parse($data['start_time'])->diffInMinutes(Carbon::parse($data['end_time'])) / 60;

        HrOvertimeRequest::create(array_merge($data, [
            'vendor_id'       => $profile->vendor_id,
            'hr_profile_id'   => $profile->id,
            'hours_requested' => round($hours, 2),
            'status'          => 'pending',
        ]));

        return back()->with('success','Overtime request submitted.');
    }

    // ── My Payslips ───────────────────────────────────────────────────

    public function myPayslips()
    {
        $profile = $this->profile();
        if (! $profile) return back()->with('error','HR profile not found.');

        $payslips = HrPayrollItem::with('period')
            ->where('hr_profile_id', $profile->id)
            ->where('status','final')
            ->whereHas('period', fn($q) => $q->where('status','approved'))
            ->latest()
            ->paginate(15);

        return view('vendor.hr.employee.payslips', compact('profile','payslips'));
    }

    public function showPayslip(HrPayrollItem $payrollItem)
    {
        $profile = $this->profile();
        if ($payrollItem->hr_profile_id !== $profile?->id) abort(403);
        if ($payrollItem->status !== 'final') abort(404,'Payslip not yet available.');

        $payrollItem->load(['period','profile.department','profile.position']);

        return view('vendor.hr.employee.payslip_print', compact('payrollItem'));
    }
}
