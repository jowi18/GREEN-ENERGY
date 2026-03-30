<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\VendorApprovalService;
use Illuminate\Http\Request;

class VendorApprovalController extends Controller
{
    public function __construct(
        private readonly VendorApprovalService $approvalService
    ) {}

    /**
     * Approval queue — pending and under_review vendors.
     */
    public function index(Request $request)
    {
        $vendors = Vendor::with(['user', 'documents'])
            ->whereIn('status', ['pending', 'under_review'])
            ->latest()
            ->paginate(20);

        return view('admin.vendors.pending', compact('vendors'));
    }

    /**
     * Show a vendor's full application for review.
     * Automatically marks it as under_review.
     */
    public function show(Vendor $vendor)
    {
        $this->approvalService->markUnderReview($vendor);

        $vendor->load(['user', 'documents', 'user.vendor']);

        return view('admin.vendors.show', compact('vendor'));
    }

    /**
     * Approve the vendor — they can now subscribe.
     */
    public function approve(Request $request, Vendor $vendor)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = auth()->user()->admin;
        $this->approvalService->approve($vendor, $admin, $request->notes);

        return redirect()
            ->route('admin.vendors.pending')
            ->with('success', "'{$vendor->business_name}' has been approved and notified to subscribe.");
    }

    /**
     * Reject the vendor application.
     */
    public function reject(Request $request, Vendor $vendor)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $admin = auth()->user()->admin;
        $this->approvalService->reject($vendor, $admin, $request->reason);

        return redirect()
            ->route('admin.vendors.pending')
            ->with('success', "'{$vendor->business_name}' application has been rejected.");
    }

    /**
     * Request document revisions from the vendor.
     */
    public function requestRevision(Request $request, Vendor $vendor)
    {
        $request->validate([
            'notes'                => ['required', 'string', 'max:1000'],
            'document_feedback'    => ['nullable', 'array'],
            'document_feedback.*'  => ['nullable', 'string', 'max:500'],
        ]);

        $admin = auth()->user()->admin;
        $this->approvalService->requestRevision(
            $vendor,
            $admin,
            $request->notes,
            $request->document_feedback ?? []
        );

        return redirect()
            ->route('admin.vendors.pending')
            ->with('success', "Revision request sent to '{$vendor->business_name}'.");
    }

    /**
     * AJAX endpoint — returns vendor count stats for dashboard badges.
     */
    public function stats()
    {
        return response()->json([
            'pending'      => Vendor::pending()->count(),
            'under_review' => Vendor::underReview()->count(),
            'total_active' => Vendor::active()->count(),
        ]);
    }
}
