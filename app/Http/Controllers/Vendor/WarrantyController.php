<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WarrantyRequest;
use App\Services\WarrantyService;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function __construct(private readonly WarrantyService $warranty) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(WarrantyRequest $wr): void
    {
        if ($wr->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── Claim list ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = WarrantyRequest::with(['customer', 'product', 'orderItem'])
            ->forVendor($vendor->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name', 'like', "%{$request->search}%")
                  );
            });
        }

        $claims = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'submitted'    => WarrantyRequest::forVendor($vendor->id)->where('status','submitted')->count(),
            'under_review' => WarrantyRequest::forVendor($vendor->id)->where('status','under_review')->count(),
            'in_progress'  => WarrantyRequest::forVendor($vendor->id)->where('status','in_progress')->count(),
            'completed'    => WarrantyRequest::forVendor($vendor->id)->where('status','completed')->count(),
            'rejected'     => WarrantyRequest::forVendor($vendor->id)->where('status','rejected')->count(),
        ];

        return view('vendor.warranty.index', compact('claims', 'stats'));
    }

    // ── Claim detail ──────────────────────────────────────────────────────

    public function show(WarrantyRequest $warrantyRequest)
    {
        $this->authorize($warrantyRequest);
        $warrantyRequest->load(['customer.user', 'product', 'orderItem.order', 'technician']);

        $vendor    = $this->vendor();
        $employees = Employee::with('user')->forVendor($vendor->id)->active()->get();

        $allowedTransitions = WarrantyRequest::ALLOWED_TRANSITIONS[$warrantyRequest->status] ?? [];

        return view('vendor.warranty.show', compact('warrantyRequest', 'allowedTransitions', 'employees'));
    }

    // ── Update status ─────────────────────────────────────────────────────

    public function updateStatusOld(Request $request, WarrantyRequest $warrantyRequest)
    {
        $this->authorize($warrantyRequest);

        $data = $request->validate([
            'status'              => ['required', 'string'],
            'rejection_reason'    => ['nullable', 'string', 'max:1000'],
            'technician_notes'    => ['nullable', 'string', 'max:1000'],
            'assigned_technician' => ['nullable', 'exists:employees,id'],
            'technician_name'     => ['nullable', 'string', 'max:120'],
            'service_date'        => ['nullable', 'date'],
            'service_time_slot'   => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $extra = array_filter([
                'rejection_reason'    => $data['rejection_reason'] ?? null,
                'technician_notes'    => $data['technician_notes'] ?? null,
                'assigned_technician' => $data['assigned_technician'] ?? null,
                'technician_name'     => $data['technician_name'] ?? null,
                'service_date'        => $data['service_date'] ?? null,
                'service_time_slot'   => $data['service_time_slot'] ?? null,
            ], fn ($v) => $v !== null);

            $updated = $this->warranty->transition($warrantyRequest, $data['status'], $extra);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'    => true,
                    'new_status' => $updated->status,
                    'label'      => $updated->status_label,
                    'message'    => "Claim #{$warrantyRequest->request_number} moved to {$updated->status_label}.",
                ]);
            }

            return back()->with('success', "Claim updated to {$updated->status_label}.");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, WarrantyRequest $warrantyRequest)
    {
        $this->authorize($warrantyRequest);

        $action = $request->input('action', 'transition');

        // ── Assign technician only ────────────────────────────────────────────
        if ($action === 'assign') {
            $request->validate([
                'assigned_technician_id' => ['required', 'exists:tbl_employees,id'],
            ]);

            $employee = Employee::with('user')->find($request->assigned_technician_id);

            $warrantyRequest->assigned_technician_id = $request->assigned_technician_id;
            $warrantyRequest->technician_name        = $employee?->user?->name;
            $warrantyRequest->save();

            $warrantyRequest->addHistoryEntry(
                'Technician Assigned',
                'Assigned to ' . ($employee?->user?->name ?? 'Employee #' . $request->assigned_technician_id),
            );

            return back()->with('success', 'Technician assigned successfully.');
        }

        // ── Schedule only ─────────────────────────────────────────────────────
        if ($action === 'schedule') {
            $request->validate([
                'service_date'      => ['nullable', 'date'],
                'service_time_slot' => ['nullable', 'string', 'max:30'],
            ]);

            $warrantyRequest->update([
                'service_date'      => $request->service_date,
                'service_time_slot' => $request->service_time_slot,
            ]);

            $warrantyRequest->addHistoryEntry(
                'Schedule Updated',
                $request->service_date . ' · ' . $request->service_time_slot,
            );

            return back()->with('success', 'Schedule saved.');
        }

        // ── Status transition (original logic) ────────────────────────────────
        $data = $request->validate([
            'status'              => ['required', 'string'],
            'rejection_reason'    => ['nullable', 'string', 'max:1000'],
            'technician_notes'    => ['nullable', 'string', 'max:1000'],
            'assigned_technician' => ['nullable', 'exists:employees,id'],
            'technician_name'     => ['nullable', 'string', 'max:120'],
            'service_date'        => ['nullable', 'date'],
            'service_time_slot'   => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $extra = array_filter([
                'rejection_reason'    => $data['rejection_reason'] ?? null,
                'technician_notes'    => $data['technician_notes'] ?? null,
                'assigned_technician' => $data['assigned_technician'] ?? null,
                'technician_name'     => $data['technician_name'] ?? null,
                'service_date'        => $data['service_date'] ?? null,
                'service_time_slot'   => $data['service_time_slot'] ?? null,
            ], fn ($v) => $v !== null);

            $updated = $this->warranty->transition($warrantyRequest, $data['status'], $extra);

            return back()->with('success', "Claim updated to {$updated->status_label}.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Upload completion proof ───────────────────────────────────────────

    public function uploadProof(Request $request, WarrantyRequest $warrantyRequest)
    {
        $this->authorize($warrantyRequest);

        $request->validate([
            'completion_images'   => ['required', 'array', 'min:1', 'max:6'],
            'completion_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'resolution_notes'    => ['required', 'string', 'max:2000'],
            'resolution_type'     => ['required', 'in:repaired,replaced,refunded,no_fault_found'],
        ]);

        try {
            $this->warranty->uploadCompletionProof(
                $warrantyRequest,
                $request->file('completion_images', []),
                $request->resolution_notes,
                $request->resolution_type,
            );

            // Auto-advance to completed
            if ($warrantyRequest->status === 'in_progress') {
                $this->warranty->transition($warrantyRequest->fresh(), 'completed', [
                    'resolution_notes' => $request->resolution_notes,
                    'resolution_type'  => $request->resolution_type,
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Proof uploaded. Claim marked as completed.']);
            }

            return back()->with('success', 'Completion proof uploaded. Claim marked as completed.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Add internal note ─────────────────────────────────────────────────

    public function addNote(Request $request, WarrantyRequest $warrantyRequest)
    {
        $this->authorize($warrantyRequest);
        $request->validate(['note' => ['required', 'string', 'max:1000']]);

        $warrantyRequest->addHistoryEntry('Note Added', $request->note);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Note added.');
    }
}
