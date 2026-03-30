<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SolarProject;
use App\Models\SolarProjectDocument;
use App\Models\SolarQuotation;
use App\Models\Vendor;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CustomerSolarController extends Controller
{

    private const SITE_VISIT_FEE      = 1500.00;
    private const SITE_VISIT_CURRENCY = 'PHP';
    private const SITE_VISIT_LABEL    = 'Solar Site Visit & Assessment Fee';

    public function __construct(
        private readonly PayPalService $payPal
    ) {}

    private function customer()
    {
        return auth()->user()->customer;
    }

    // ── Project list ──────────────────────────────────────────────────────

    public function index()
    {
        $customer = $this->customer();

        $projects = SolarProject::with(['vendor', 'activeQuotation'])
            ->forCustomer($customer->id)
            ->latest()
            ->paginate(10);

        return view('customer.solar.index', compact('projects'));
    }

    // ── Inquiry form ──────────────────────────────────────────────────────

    public function create(Request $request)
    {
        // Vendor can be pre-selected from a shop page
        $vendor = $request->filled('vendor')
            ? Vendor::active()->findOrFail($request->vendor)
            : null;

        $vendors = Vendor::active()->orderBy('business_name')->get();

        return view('customer.solar.create', compact('vendor', 'vendors'));
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'vendor_id'               => ['required', 'exists:vendors,id'],
    //         'property_type'           => ['required', 'in:residential,commercial,industrial,agricultural'],
    //         'system_type'             => ['nullable', 'in:grid_tie,off_grid,hybrid'],
    //         'estimated_monthly_bill'  => ['nullable', 'numeric', 'min:0'],
    //         'desired_system_size_kw'  => ['nullable', 'numeric', 'min:0'],
    //         'roof_area_sqm'           => ['nullable', 'integer', 'min:0'],
    //         'roof_type'               => ['nullable', 'in:concrete,metal,asphalt_shingle,clay_tile,other'],
    //         'installation_address'    => ['required', 'string', 'max:500'],
    //         'installation_city'       => ['required', 'string', 'max:100'],
    //         'installation_province'   => ['required', 'string', 'max:100'],
    //         'customer_notes'          => ['nullable', 'string', 'max:2000'],
    //         // Documents
    //         'site_photos'             => ['nullable', 'array', 'max:6'],
    //         'site_photos.*'           => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    //         'electric_bill'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
    //     ]);

    //     $customer = $this->customer();

    //     $project = SolarProject::create([
    //         'customer_id'            => $customer->id,
    //         'vendor_id'              => $data['vendor_id'],
    //         'property_type'          => $data['property_type'],
    //         'system_type'            => $data['system_type'] ?? null,
    //         'estimated_monthly_bill' => $data['estimated_monthly_bill'] ?? null,
    //         'desired_system_size_kw' => $data['desired_system_size_kw'] ?? null,
    //         'roof_area_sqm'          => $data['roof_area_sqm'] ?? null,
    //         'roof_type'              => $data['roof_type'] ?? null,
    //         'installation_address'   => $data['installation_address'],
    //         'installation_city'      => $data['installation_city'],
    //         'installation_province'  => $data['installation_province'],
    //         'customer_notes'         => $data['customer_notes'] ?? null,
    //         'status'                 => 'inquiry_submitted',
    //     ]);

    //     $project->addHistoryEntry('inquiry_submitted', 'Customer submitted inquiry.');

    //     // Store site photos
    //     if ($request->hasFile('site_photos')) {
    //         foreach ($request->file('site_photos') as $photo) {
    //             $path = $photo->store("solar/{$project->id}/customer", 'public');
    //             SolarProjectDocument::create([
    //                 'solar_project_id'  => $project->id,
    //                 'uploaded_by'       => auth()->id(),
    //                 'document_type'     => 'site_photo',
    //                 'uploaded_by_role'  => 'customer',
    //                 'file_name'         => $photo->getClientOriginalName(),
    //                 'file_path'         => $path,
    //                 'mime_type'         => $photo->getMimeType(),
    //                 'file_size'         => $photo->getSize(),
    //             ]);
    //         }
    //     }

    //     // Store electric bill
    //     if ($request->hasFile('electric_bill')) {
    //         $bill = $request->file('electric_bill');
    //         $path = $bill->store("solar/{$project->id}/customer", 'public');
    //         SolarProjectDocument::create([
    //             'solar_project_id'  => $project->id,
    //             'uploaded_by'       => auth()->id(),
    //             'document_type'     => 'electric_bill',
    //             'uploaded_by_role'  => 'customer',
    //             'file_name'         => $bill->getClientOriginalName(),
    //             'file_path'         => $path,
    //             'mime_type'         => $bill->getMimeType(),
    //             'file_size'         => $bill->getSize(),
    //         ]);
    //     }

    //     return redirect()
    //         ->route('customer.solar.show', $project)
    //         ->with('success', 'Your solar installation inquiry has been submitted! The vendor will contact you shortly.');
    // }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id'              => ['required', 'exists:vendors,id'],
            'property_type'          => ['required', 'in:residential,commercial,industrial,agricultural'],
            'system_type'            => ['nullable', 'in:grid_tie,off_grid,hybrid'],
            'estimated_monthly_bill' => ['nullable', 'numeric', 'min:0'],
            'desired_system_size_kw' => ['nullable', 'numeric', 'min:0'],
            'roof_area_sqm'          => ['nullable', 'integer', 'min:0'],
            'roof_type'              => ['nullable', 'in:concrete,metal,asphalt_shingle,clay_tile,other'],
            'installation_address'   => ['required', 'string', 'max:500'],
            'installation_city'      => ['required', 'string', 'max:100'],
            'installation_province'  => ['required', 'string', 'max:100'],
            'customer_notes'         => ['nullable', 'string', 'max:2000'],
            'site_photos'            => ['nullable', 'array', 'max:6'],
            'site_photos.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'electric_bill'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $customer = $this->customer();

        // ── 1. Create project in PENDING PAYMENT status ───────────────────
        $project = DB::transaction(function () use ($data, $customer, $request) {

            $project = SolarProject::create([
                'customer_id'            => $customer->id,
                'vendor_id'              => $data['vendor_id'],
                'property_type'          => $data['property_type'],
                'system_type'            => $data['system_type'] ?? null,
                'estimated_monthly_bill' => $data['estimated_monthly_bill'] ?? null,
                'desired_system_size_kw' => $data['desired_system_size_kw'] ?? null,
                'roof_area_sqm'          => $data['roof_area_sqm'] ?? null,
                'roof_type'              => $data['roof_type'] ?? null,
                'installation_address'   => $data['installation_address'],
                'installation_city'      => $data['installation_city'],
                'installation_province'  => $data['installation_province'],
                'customer_notes'         => $data['customer_notes'] ?? null,
                'status'                 => 'pending_payment', // waiting for site visit payment
            ]);

            $project->addHistoryEntry('pending_payment', 'Inquiry created. Awaiting site visit fee payment.');

            // Store site photos
            if ($request->hasFile('site_photos')) {
                foreach ($request->file('site_photos') as $photo) {
                    $path = $photo->store("solar/{$project->id}/customer", 'public');
                    SolarProjectDocument::create([
                        'solar_project_id' => $project->id,
                        'uploaded_by'      => auth()->id(),
                        'document_type'    => 'site_photo',
                        'uploaded_by_role' => 'customer',
                        'file_name'        => $photo->getClientOriginalName(),
                        'file_path'        => $path,
                        'mime_type'        => $photo->getMimeType(),
                        'file_size'        => $photo->getSize(),
                    ]);
                }
            }

            // Store electric bill
            if ($request->hasFile('electric_bill')) {
                $bill = $request->file('electric_bill');
                $path = $bill->store("solar/{$project->id}/customer", 'public');
                SolarProjectDocument::create([
                    'solar_project_id' => $project->id,
                    'uploaded_by'      => auth()->id(),
                    'document_type'    => 'electric_bill',
                    'uploaded_by_role' => 'customer',
                    'file_name'        => $bill->getClientOriginalName(),
                    'file_path'        => $path,
                    'mime_type'        => $bill->getMimeType(),
                    'file_size'        => $bill->getSize(),
                ]);
            }

            return $project;
        });

        // ── 2. Create PayPal order for ₱1,500 site visit fee ─────────────
        $result = $this->payPal->createPayment(
            amount:      self::SITE_VISIT_FEE,
            currency:    self::SITE_VISIT_CURRENCY,
            description: self::SITE_VISIT_LABEL . ' — Project #' . $project->project_number,
            returnUrl:   route('customer.solar.paypal.success', ['project' => $project->id]),
            cancelUrl:   route('customer.solar.paypal.cancel',  ['project' => $project->id]),
        );

        if (!$result['success']) {
            Log::error('PayPal order creation failed for solar project', [
                'project_id' => $project->id,
                'error'      => $result['message'] ?? 'unknown',
            ]);

            // Clean up the pending project since payment initiation failed
            $project->update(['status' => 'payment_failed']);

            return back()
                ->withInput()
                ->withErrors(['paypal' => 'Could not initiate payment. Please try again.']);
        }

        // ── 3. Store PayPal order ID on the project ───────────────────────
        $project->update(['paypal_order_id' => $result['order_id']]);

        // Store project ID in session as fallback
        Session::put('solar_pending_project_id', $project->id);

        // ── 4. Redirect to PayPal ─────────────────────────────────────────
        return redirect($result['approval_url']);
    }

    /**
     * STEP 2 — PayPal returns here after customer approves payment.
     *           Capture the payment, activate the project, show invoice.
     */
    public function paypalSuccess(Request $request, SolarProject $project)
    {
        // Guard: already paid (page refresh)
        if ($project->status === 'inquiry_submitted') {
            return redirect()->route('customer.solar.show', $project)
                ->with('info', 'Your inquiry is already active.');
        }

        // // Must belong to this customer
        // if ($project->customer_id !== $this->customer()->id) {
        //     abort(403);
        // }

        $token = $request->query('token'); // PayPal order token

        // Capture payment
        $result = $this->payPal->capturePayment($project->paypal_order_id ?? $token);

        if (!$result['success']) {
            Log::error('PayPal capture failed for solar project', [
                'project_id' => $project->id,
                'error'      => $result['message'] ?? 'unknown',
            ]);

            $project->update(['status' => 'payment_failed']);

            return redirect()->route('customer.solar.create')
                ->withErrors(['paypal' => 'Payment could not be confirmed. Please try again or contact support.']);
        }

        // ── Activate project ──────────────────────────────────────────────
        $project->update([
            'status'              => 'inquiry_submitted',
            'site_visit_fee'      => self::SITE_VISIT_FEE,
            'site_visit_paid_at'  => now(),
            'site_visit_txn_id'   => $result['transaction_id'] ?? null,
            'site_visit_payer_id' => $result['payer_id']       ?? null,
        ]);

        $project->addHistoryEntry(
            'inquiry_submitted',
            'Site visit fee paid (₱' . number_format(self::SITE_VISIT_FEE, 2) . '). Inquiry is now active.'
        );

        Session::forget('solar_pending_project_id');

        Log::info('Solar project site visit payment captured', [
            'project_id'     => $project->id,
            'transaction_id' => $result['transaction_id'] ?? null,
            'amount'         => self::SITE_VISIT_FEE,
        ]);

        // Redirect to invoice page
        return redirect()->route('customer.solar.invoice', $project)
            ->with('success', 'Payment confirmed! Your inquiry is now active.');
    }

    /**
     * STEP 2b — Customer cancelled on PayPal.
     */
    public function paypalCancel(Request $request, SolarProject $project)
    {
        if ($project->customer_id !== $this->customer()->id) {
            abort(403);
        }

        // Mark as cancelled — clean up pending project
        $project->update(['status' => 'payment_cancelled']);
        $project->addHistoryEntry('payment_cancelled', 'Customer cancelled the site visit fee payment on PayPal.');

        Session::forget('solar_pending_project_id');

        return redirect()->route('customer.solar.create')
            ->with('warning', 'Payment was cancelled. Your inquiry has not been submitted. You may try again.');
    }

    /**
     * Printable site visit invoice.
     */
    public function invoice(SolarProject $project)
    {
        // Must be paid
        if (!in_array($project->status, ['inquiry_submitted', 'site_visit_scheduled', 'site_visit_completed'])) {
            abort(403, 'Invoice not available — payment not confirmed.');
        }

        // if ($project->customer_id !== $this->customer()->id) {
        //     abort(403);
        // }

        $project->load(['customer.user', 'vendor']);

        return view('customer.solar.site-visit-invoice', compact('project'));
    }

    // ── Project detail ────────────────────────────────────────────────────

    public function show(SolarProject $solarProject)
    {
        $this->authorizeProject($solarProject);

        $solarProject->load([
            'vendor',
            'engineer.user',
            'quotations.items',
            'documents',
            'activeContract',
        ]);

        $activeQuotation = $solarProject->quotations
            ->whereIn('status', ['sent', 'approved', 'rejected'])
            ->first();

        return view('customer.solar.show', [
            'project'         => $solarProject,
            'activeQuotation' => $activeQuotation,
            'pipeline'        => SolarProject::STATUS_PIPELINE,
            'statusLabels'    => SolarProject::STATUS_LABELS,
        ]);
    }

    // ── Quotation response ────────────────────────────────────────────────

    public function respondQuotation(Request $request, SolarProject $solarProject, SolarQuotation $solarQuotation)
    {
        $this->authorizeProject($solarProject);

        if ($solarQuotation->solar_project_id !== $solarProject->id) {
            abort(403);
        }

        $data = $request->validate([
            'response'         => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:response,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $newProjectStatus = $data['response'] === 'approved'
            ? 'proposal_approved'
            : 'proposal_rejected';

        if (!$solarProject->customerCanTransitionTo($newProjectStatus)) {
            return back()->with('error', 'This quotation can no longer be responded to.');
        }

        $solarQuotation->update([
            'status'                    => $data['response'],
            'customer_rejection_reason' => $data['rejection_reason'] ?? null,
            'customer_responded_at'     => now(),
        ]);

        $solarProject->update(['status' => $newProjectStatus]);
        $solarProject->addHistoryEntry(
            $newProjectStatus,
            $data['response'] === 'approved'
                ? 'Customer approved the quotation.'
                : 'Customer rejected the quotation. Reason: ' . ($data['rejection_reason'] ?? '—'),
        );

        $message = $data['response'] === 'approved'
            ? 'Quotation approved! The vendor will proceed with contract signing.'
            : 'Quotation rejected. The vendor will prepare a revised proposal.';

        return back()->with('success', $message);
    }

    // ── Upload documents ──────────────────────────────────────────────────

    public function uploadDocument(Request $request, SolarProject $solarProject)
    {
        $this->authorizeProject($solarProject);

        $request->validate([
            'document_type' => ['required', 'in:site_photo,electric_bill,government_id,lot_plan,barangay_clearance,other'],
            'file'          => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'description'   => ['nullable', 'string', 'max:300'],
        ]);

        $file = $request->file('file');
        $path = $file->store("solar/{$solarProject->id}/customer", 'public');

        SolarProjectDocument::create([
            'solar_project_id'  => $solarProject->id,
            'uploaded_by'       => auth()->id(),
            'document_type'     => $request->document_type,
            'uploaded_by_role'  => 'customer',
            'file_name'         => $file->getClientOriginalName(),
            'file_path'         => $path,
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'description'       => $request->description,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    // ── Authorization ─────────────────────────────────────────────────────

    private function authorizeProject(SolarProject $project): void
    {
        if ($project->customer_id !== $this->customer()->id) {
            abort(403);
        }
    }
}
