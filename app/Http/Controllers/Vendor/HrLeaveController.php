<?php

// ════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/HrLeaveController.php
// ════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\HrOvertimeRequest;
use Illuminate\Http\Request;

class HrLeaveController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Leave requests ─────────────────────────────────────────────────

    public function leaveIndex(Request $request)
    {
        $vendor  = $this->vendor();
        $status  = $request->input('status', 'pending');
        $query   = HrLeaveRequest::with(['profile','leaveType'])
            ->forVendor($vendor->id);

        if ($status !== 'all') $query->where('status', $status);

        $requests   = $query->latest()->paginate(20)->withQueryString();
        $leaveTypes = HrLeaveType::forVendor($vendor->id)->active()->get();
        $pendingCnt = HrLeaveRequest::forVendor($vendor->id)->pending()->count();

        return view('vendor.hr.leaves.index', compact('requests','leaveTypes','pendingCnt'));
    }

    public function leaveApprove(Request $request, HrLeaveRequest $leaveRequest)
    {
        if ($leaveRequest->vendor_id !== $this->vendor()->id) abort(403);

        $action = $request->input('action');
        $notes  = $request->input('notes');

        $leaveRequest->update([
            'status'         => $action === 'approve' ? 'approved' : 'rejected',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'approver_notes' => $notes,
        ]);

        $label = $action === 'approve' ? 'approved' : 'rejected';
        return back()->with('success', "Leave request {$label}.");
    }

    // ── Overtime requests ──────────────────────────────────────────────

    public function overtimeIndex(Request $request)
    {
        $vendor     = $this->vendor();
        $requests   = HrOvertimeRequest::with('profile')
            ->forVendor($vendor->id)
            ->latest()
            ->paginate(20);
        $pendingCnt = HrOvertimeRequest::forVendor($vendor->id)->pending()->count();

        return view('vendor.hr.leaves.overtime', compact('requests','pendingCnt'));
    }

    public function overtimeApprove(Request $request, HrOvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->vendor_id !== $this->vendor()->id) abort(403);

        $overtimeRequest->update([
            'status'         => $request->action === 'approve' ? 'approved' : 'rejected',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'approver_notes' => $request->notes,
        ]);

        return back()->with('success', 'Overtime request updated.');
    }
}
