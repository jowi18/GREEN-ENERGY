<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestProof;
use App\Services\ServiceManagementService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceManagementService $svc
    ) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(ServiceRequest $sr): void
    {
        if ($sr->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = ServiceRequest::with(['customer', 'proofs'])
            ->forVendor($vendor->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('service_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhere('service_title', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name',  'like', "%{$request->search}%")
                  );
            });
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'submitted'   => ServiceRequest::forVendor($vendor->id)->where('status', 'submitted')->count(),
            'scheduled'   => ServiceRequest::forVendor($vendor->id)->where('status', 'scheduled')->count(),
            'in_progress' => ServiceRequest::forVendor($vendor->id)->where('status', 'in_progress')->count(),
            'completed'   => ServiceRequest::forVendor($vendor->id)->where('status', 'completed')->count(),
        ];

        $serviceTypes = ServiceRequest::forVendor($vendor->id)
            ->distinct()->pluck('service_type');

        return view('vendor.services.index', compact('requests', 'stats', 'serviceTypes'));
    }

    // ── Detail ────────────────────────────────────────────────────────────

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);
        $serviceRequest->load(['customer.user', 'proofs', 'product', 'assignees.user']);
        $transitions = $this->svc->allowedTransitions($serviceRequest->status);

        // Active employees of this vendor for the assignment picker
        $employees = Employee::with('user')
            ->forVendor($this->vendor()->id)
            ->active()
            ->orderBy('first_name')
            ->get();

        $assignedIds = $serviceRequest->assignees->pluck('id')->toArray();

        return view('vendor.services.show', compact(
            'serviceRequest', 'transitions', 'employees', 'assignedIds'
        ));
    }

    // ── Update status ─────────────────────────────────────────────────────

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);

         $data = $request->validate([
            'status'         => ['required', 'string'],
            'vendor_notes'   => ['nullable', 'string', 'max:1000'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'string'],
            'employee_ids'   => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);



        try {
            $extra = array_filter([
                'vendor_notes'   => $data['vendor_notes'] ?? null,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'employee_ids'   => $data['employee_ids'] ?? [],
            ], fn ($v) => $v !== null && $v !== []);

            $updated = $this->svc->updateStatus($serviceRequest, $data['status'], $extra);

            return response()->json([
                'success'     => true,
                'new_status'  => $updated->status,
                'message'     => "Service request #{$serviceRequest->request_number} updated to " . ucwords(str_replace('_', ' ', $updated->status)) . '.',
                'transitions' => $this->svc->allowedTransitions($updated->status),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Submit quote ──────────────────────────────────────────────────────

    public function submitQuote(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);

        $data = $request->validate([
            'quoted_amount'  => ['required', 'numeric', 'min:0'],
            'quote_details'  => ['required', 'string', 'max:2000'],
            'quote_valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        try {
            $updated = $this->svc->submitQuote(
                $serviceRequest,
                (float) $data['quoted_amount'],
                $data['quote_details'],
                $data['quote_valid_until'] ?? null,
            );

            return response()->json([
                'success'       => true,
                'message'       => "Quote submitted for #{$serviceRequest->request_number}.",
                'quoted_amount' => $updated->quoted_amount,
                'new_status'    => $updated->status,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Upload proof ──────────────────────────────────────────────────────

    public function uploadProof(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);

        $request->validate([
            'proofs'    => ['required', 'array', 'min:1', 'max:8'],
            'proofs.*'  => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption'   => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $proofs = $this->svc->uploadProof(
                $serviceRequest,
                $request->file('proofs'),
                $request->caption,
                auth()->id(),
            );

            return response()->json([
                'success'    => true,
                'message'    => count($proofs) . ' proof image(s) uploaded. Service marked as completed.',
                'proofs'     => collect($proofs)->map(fn ($p) => [
                    'id'        => $p->id,
                    'url'       => asset('storage/' . $p->file_path),
                    'caption'   => $p->caption,
                ]),
                'new_status' => $serviceRequest->fresh()->status,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Delete proof ──────────────────────────────────────────────────────

    public function deleteProof(ServiceRequest $serviceRequest, ServiceRequestProof $proof)
    {
        $this->authorize($serviceRequest);

        if ($proof->service_request_id !== $serviceRequest->id) abort(403);

        $this->svc->deleteProof($proof);

        return response()->json(['success' => true]);
    }
}
