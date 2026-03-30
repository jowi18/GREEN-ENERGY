<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Vendor;
use App\Models\Product;
use App\Services\ServiceManagementService;
use Illuminate\Http\Request;

class ServiceBookingController extends Controller
{
    public function __construct(
        private readonly ServiceManagementService $svc
    ) {}

    private function customer()
    {
        return auth()->user()->customer;
    }

    private function authorize(ServiceRequest $sr): void
    {
        if ($sr->customer_id !== $this->customer()->id) abort(403);
    }

    // ── My bookings list ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $customer = $this->customer();

        $query = ServiceRequest::with(['vendor', 'proofs'])
            ->where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('customer.services.index', compact('bookings'));
    }

    // ── Booking detail ────────────────────────────────────────────────────

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);
        $serviceRequest->load(['vendor', 'proofs', 'product', 'assignees.roles', 'assignees.user']);

        $customerTransitions = $this->svc->allowedTransitions($serviceRequest->status, false);

        return view('customer.services.show', compact('serviceRequest', 'customerTransitions'));
    }

    // ── Book a service ────────────────────────────────────────────────────

    public function create(Request $request)
    {
        // Vendor pre-selected from vendor profile page
        $vendor = null;
        if ($request->filled('vendor')) {
            $vendor = Vendor::active()->findOrFail($request->vendor);
        }

        // Service product pre-selected
        $product = null;
        if ($request->filled('product')) {
            $product = Product::where('product_type', 'service')
                ->where('status', 'active')
                ->findOrFail($request->product);

            if (! $vendor) {
                $vendor = $product->vendor;
            }
        }

        // All active vendors (for vendor picker if none pre-selected)
        $vendors = Vendor::active()->orderBy('business_name')->get(['id', 'business_name', 'city']);

        $serviceTypes = [
            'installation'   => 'Solar Panel Installation',
            'maintenance'    => 'Preventive Maintenance',
            'repair'         => 'Repair & Troubleshooting',
            'inspection'     => 'System Inspection',
            'consultation'   => 'Energy Consultation',
            'cleaning'       => 'Panel Cleaning',
            'upgrade'        => 'System Upgrade',
            'warranty_claim' => 'Warranty Claim Service',
            'other'          => 'Other Service',
        ];

        $customer = $this->customer();

        // Pre-selected service type and title from vendor profile "Book" button
        // URL params: ?vendor=1&service_type=installation
        $preSelectedType  = $request->input('service_type');
        $preSelectedTitle = $preSelectedType && isset($serviceTypes[$preSelectedType])
            ? $serviceTypes[$preSelectedType]
            : null;

        // If vendor has a matching catalog entry, use its name as the title
        if ($vendor && $preSelectedType) {
            $catalogEntry = \App\Models\VendorService::forVendor($vendor->id)
                ->active()
                ->where('service_type', $preSelectedType)
                ->orderBy('is_featured', 'desc')
                ->first();

            if ($catalogEntry) {
                $preSelectedTitle = $catalogEntry->name;
            }
        }

        return view('customer.services.create', compact(
            'vendor', 'product', 'vendors', 'serviceTypes', 'customer',
            'preSelectedType', 'preSelectedTitle'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id'        => ['required', 'exists:vendors,id'],
            'product_id'       => ['nullable', 'exists:products,id'],
            'service_type'     => ['required', 'string', 'max:50'],
            'service_title'    => ['required', 'string', 'max:200'],
            'description'      => ['required', 'string', 'max:2000'],
            'preferred_date'   => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time'   => ['nullable', 'string'],
            'service_address'  => ['required', 'string', 'max:255'],
            'service_city'     => ['required', 'string', 'max:100'],
            'service_province' => ['required', 'string', 'max:100'],
            'service_postal'   => ['nullable', 'string', 'max:20'],
            'customer_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $vendor   = Vendor::active()->findOrFail($data['vendor_id']);
        $customer = $this->customer();

        $sr = $this->svc->createBooking($customer, $vendor, $data);

        return redirect()
            ->route('customer.services.show', $sr)
            ->with('success', "Service request #{$sr->request_number} submitted! The vendor will review and respond shortly.");
    }

    // ── Customer actions (accept/reject quote, cancel) ────────────────────

    public function action(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize($serviceRequest);

        $request->validate([
            'action' => ['required', 'string'],
        ]);

        try {
            $updated = $this->svc->updateStatus(
                $serviceRequest,
                $request->action,
                [],
                false // byVendor = false
            );

            $messages = [
                'quote_accepted' => 'Quote accepted! The vendor will schedule your service.',
                'quote_rejected' => 'Quote rejected. The vendor will be notified.',
                'cancelled'      => "Service request #{$serviceRequest->request_number} cancelled.",
            ];

            return back()->with('success', $messages[$request->action] ?? 'Updated.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
