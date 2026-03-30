<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SolarProject;
use App\Models\SolarProjectDocument;
use App\Models\SolarQuotation;
use App\Models\SolarQuotationItem;
use Illuminate\Http\Request;

class VendorSolarController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(SolarProject $project): void
    {
        if ($project->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = SolarProject::with(['customer', 'activeQuotation', 'engineer.user'])
            ->forVendor($vendor->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('project_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name',  'like', "%{$request->search}%")
                  );
            });
        }

        $projects = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'new'         => SolarProject::forVendor($vendor->id)
                                ->where('status', 'inquiry_submitted')->count(),
            'in_progress' => SolarProject::forVendor($vendor->id)
                                ->whereNotIn('status', [
                                    'inquiry_submitted','warranty_support_active',
                                    'project_turnover','cancelled',
                                ])->count(),
            'pending_qt'  => SolarProject::forVendor($vendor->id)
                                ->where('status', 'quotation_generated')->count(),
            'completed'   => SolarProject::forVendor($vendor->id)
                                ->whereIn('status', ['warranty_support_active','project_turnover'])->count(),
        ];

        return view('vendor.solar.index', compact('projects', 'stats'));
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $vendor = $this->vendor();

        $solarProject->load([
            'customer.user',
            'engineer.user',
            'quotations.items',
            'quotations.creator',
            'documents',
            'activeContract'
        ]);

        $employees          = Employee::with('user')->forVendor($vendor->id)->active()->get();
        $allowedTransitions = SolarProject::ALLOWED_TRANSITIONS[$solarProject->status] ?? [];

        return view('vendor.solar.show', compact(
            'solarProject', 'employees', 'allowedTransitions'
        ));
    }

    // ── Update status ─────────────────────────────────────────────────────

    // public function updateStatus(Request $request, SolarProject $solarProject)
    // {
    //     $this->authorize($solarProject);

    //     $data = $request->validate([
    //         'status'       => ['required', 'string'],
    //         'notes'        => ['nullable', 'string', 'max:1000'],
    //         'permit_number'=> ['nullable', 'string', 'max:100'],
    //         'utility_application_number' => ['nullable', 'string', 'max:100'],
    //     ]);

    //     if (!$solarProject->canTransitionTo($data['status'])) {
    //         return back()->with('error', "Cannot move project to \"{$data['status']}\" from current status.");
    //     }

    //     $solarProject->update([
    //         'status'         => $data['status'],
    //         'vendor_notes'   => $data['notes'] ?? $solarProject->vendor_notes,
    //         'permit_number'  => $data['permit_number'] ?? $solarProject->permit_number,
    //         'utility_application_number' => $data['utility_application_number']
    //                             ?? $solarProject->utility_application_number,
    //     ]);

    //     $solarProject->addHistoryEntry(
    //         $data['status'],
    //         $data['notes'] ?? SolarProject::STATUS_LABELS[$data['status']],
    //     );

    //     return back()->with('success', 'Project status updated to "' . $solarProject->fresh()->status_label . '".');
    // }

    public function updateStatus(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $data = $request->validate([
            'status'       => ['required', 'string'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'permit_number'=> ['nullable', 'string', 'max:100'],
            'utility_application_number' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $solarProject->canTransitionTo($data['status']);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        $solarProject->update([
            'status'       => $data['status'],
            'vendor_notes' => $data['notes'] ?? $solarProject->vendor_notes,
            'permit_number'=> $data['permit_number'] ?? $solarProject->permit_number,
            'utility_application_number' => $data['utility_application_number']
                                ?? $solarProject->utility_application_number,
        ]);

        $solarProject->addHistoryEntry(
            $data['status'],
            $data['notes'] ?? SolarProject::STATUS_LABELS[$data['status']],
        );

        return back()->with('success', 'Project moved to "' . $solarProject->fresh()->status_label . '".');
    }

    // ── Assign engineer ───────────────────────────────────────────────────

    public function assignEngineer(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $request->validate([
            'assigned_engineer_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::with('user')->find($request->assigned_engineer_id);

        $solarProject->update(['assigned_engineer_id' => $request->assigned_engineer_id]);
        $solarProject->addHistoryEntry(
            $solarProject->status,
            'Engineer assigned: ' . ($employee->user->name ?? 'Employee #' . $request->assigned_engineer_id),
        );

        return back()->with('success', 'Engineer assigned successfully.');
    }

    // ── Schedule (survey or installation) ────────────────────────────────

    public function schedule(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $data = $request->validate([
            'schedule_type'    => ['required', 'in:survey,installation'],
            'date'             => ['required', 'date', 'after_or_equal:today'],
            'time_slot'        => ['nullable', 'string', 'max:30'],
        ]);

        if ($data['schedule_type'] === 'survey') {
            $solarProject->update([
                'survey_date'      => $data['date'],
                'survey_time_slot' => $data['time_slot'],
            ]);
            $label = 'site_survey_scheduled';
        } else {
            $solarProject->update([
                'installation_date'      => $data['date'],
                'installation_time_slot' => $data['time_slot'],
            ]);
            $label = 'installation_scheduled';
        }

        // Auto-advance status if applicable
        if ($solarProject->canTransitionTo($label)) {
            $solarProject->update(['status' => $label]);
        }

        $solarProject->addHistoryEntry(
            $solarProject->fresh()->status,
            ucfirst($data['schedule_type']) . ' scheduled for '
                . \Carbon\Carbon::parse($data['date'])->format('M d, Y')
                . ($data['time_slot'] ? ' · ' . $data['time_slot'] : ''),
        );

        return back()->with('success', ucfirst($data['schedule_type']) . ' scheduled successfully.');
    }

    // ── Create quotation form ─────────────────────────────────────────────

    public function createQuotation(SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $products = $this->vendor()
            ->products()
            ->with('inventory')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'sku'         => $p->sku,
                'brand'       => $p->meta_title ?? '',   // or add a brand column
                'unit_price'  => $p->price,
                'unit'        => $p->inventory?->unit_of_measure ?? 'pc',
                'stock'       => $p->inventory?->quantity_available ?? 0,
                'low_stock'   => $p->inventory?->isLowStock() ?? false,
                'out_of_stock'=> $p->inventory?->isOutOfStock() ?? false,
            ]);

        return view('vendor.solar.quotation', compact('solarProject', 'products'));
    }

    // ── Store quotation ───────────────────────────────────────────────────

    // public function storeQuotation(Request $request, SolarProject $solarProject)
    // {
    //     $this->authorize($solarProject);

    //     $data = $request->validate([
    //         'items'                => ['required', 'array', 'min:1'],
    //         'items.*.product_id' =>   ['nullable', 'integer', 'exists:products,id'],
    //         'items.*.item_type'    => ['required', 'in:equipment,labor,permit,other'],
    //         'items.*.description'  => ['required', 'string', 'max:300'],
    //         'items.*.brand'        => ['nullable', 'string', 'max:100'],
    //         'items.*.model'        => ['nullable', 'string', 'max:100'],
    //         'items.*.unit'         => ['required', 'string', 'max:20'],
    //         'items.*.quantity'     => ['required', 'numeric', 'min:0.01'],
    //         'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
    //         'discount_amount'      => ['nullable', 'numeric', 'min:0'],
    //         'tax_amount'           => ['nullable', 'numeric', 'min:0'],
    //         'valid_until'          => ['nullable', 'date', 'after:today'],
    //         'notes'                => ['nullable', 'string', 'max:2000'],
    //         'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
    //         'action'               => ['required', 'in:draft,send'],
    //     ]);

    //     // Mark previous sent quotations as superseded
    //     $solarProject->quotations()
    //         ->whereIn('status', ['draft', 'sent'])
    //         ->update(['status' => 'superseded']);

    //     $status   = $data['action'] === 'send' ? 'sent' : 'draft';
    //     $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
    //     $discount = $data['discount_amount'] ?? 0;
    //     $tax      = $data['tax_amount'] ?? 0;

    //     $quotation = SolarQuotation::create([
    //         'solar_project_id'     => $solarProject->id,
    //         'vendor_id'            => $this->vendor()->id,
    //         'created_by'           => auth()->id(),
    //         'status'               => $status,
    //         'subtotal'             => $subtotal,
    //         'discount_amount'      => $discount,
    //         'tax_amount'           => $tax,
    //         'total_amount'         => $subtotal - $discount + $tax,
    //         'valid_until'          => $data['valid_until'] ?? null,
    //         'notes'                => $data['notes'] ?? null,
    //         'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
    //     ]);

    //     foreach ($data['items'] as $i => $item) {
    //         SolarQuotationItem::create([
    //             'solar_quotation_id' => $quotation->id,
    //             'product_id'         => $item['product_id'] ?? null,  // ← add this
    //             'item_type'          => $item['item_type'],
    //             'description'        => $item['description'],
    //             'brand'              => $item['brand'] ?? null,
    //             'model'              => $item['model'] ?? null,
    //             'unit'               => $item['unit'],
    //             'quantity'           => $item['quantity'],
    //             'unit_price'         => $item['unit_price'],
    //             'sort_order'         => $i,
    //         ]);
    //     }

    //     // Advance project status
    //     if ($status === 'sent' && $solarProject->canTransitionTo('quotation_generated')) {
    //         $solarProject->update(['status' => 'quotation_generated']);
    //         $solarProject->addHistoryEntry(
    //             'quotation_generated',
    //             'Quotation ' . $quotation->quotation_number . ' sent to customer. Total: ₱' . number_format($quotation->total_amount, 2)
    //         );
    //     }

    //     return redirect()
    //         ->route('vendor.solar.show', $solarProject)
    //         ->with('success', $status === 'sent'
    //             ? 'Quotation sent to customer successfully.'
    //             : 'Quotation saved as draft.'
    //         );
    // }

    public function storeQuotation(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        \Log::info('storeQuotation START', [
            'solar_project_id' => $solarProject->id,
            'user_id'          => auth()->id(),
            'raw_items_count'  => count($request->input('items', [])),
            'action'           => $request->input('action'),
        ]);

        // ── Validation ─────────────────────────────────────────────────────
        try {
            $data = $request->validate([
                'items'                => ['required', 'array', 'min:1'],
                'items.*.product_id'   => ['nullable', 'integer', 'exists:products,id'],
                'items.*.item_type'    => ['required', 'in:equipment,labor,permit,other'],
                'items.*.description'  => ['required', 'string', 'max:300'],
                'items.*.brand'        => ['nullable', 'string', 'max:100'],
                'items.*.model'        => ['nullable', 'string', 'max:100'],
                'items.*.unit'         => ['required', 'string', 'max:20'],
                'items.*.quantity'     => ['required', 'numeric', 'min:0.01'],
                'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
                'discount_amount'      => ['nullable', 'numeric', 'min:0'],
                'tax_amount'           => ['nullable', 'numeric', 'min:0'],
                'valid_until'          => ['nullable', 'date', 'after:today'],
                'notes'                => ['nullable', 'string', 'max:2000'],
                'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
                'action'               => ['required', 'in:draft,send'],
            ]);

            \Log::info('storeQuotation VALIDATION PASSED', [
                'items_count' => count($data['items']),
                'action'      => $data['action'],
                'items'       => $data['items'], // full items dump
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('storeQuotation VALIDATION FAILED', [
                'errors'   => $e->errors(),
                'raw_input'=> $request->input('items'),
            ]);
            throw $e;
        }

        // ── Supersede old quotations ────────────────────────────────────────
        try {
            $superseded = $solarProject->quotations()
                ->whereIn('status', ['draft', 'sent'])
                ->update(['status' => 'superseded']);

            \Log::info('storeQuotation SUPERSEDED old quotations', [
                'count' => $superseded,
            ]);
        } catch (\Exception $e) {
            \Log::error('storeQuotation SUPERSEDE FAILED', ['error' => $e->getMessage()]);
            throw $e;
        }

        $status   = $data['action'] === 'send' ? 'sent' : 'draft';
        $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $discount = $data['discount_amount'] ?? 0;
        $tax      = $data['tax_amount'] ?? 0;

        \Log::info('storeQuotation TOTALS', [
            'status'   => $status,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax'      => $tax,
            'total'    => $subtotal - $discount + $tax,
            'vendor_id'=> $this->vendor()->id,
        ]);

        // ── Create quotation ───────────────────────────────────────────────
        try {
            $quotation = SolarQuotation::create([
                'solar_project_id'     => $solarProject->id,
                'vendor_id'            => $this->vendor()->id,
                'created_by'           => auth()->id(),
                'status'               => $status,
                'subtotal'             => $subtotal,
                'discount_amount'      => $discount,
                'tax_amount'           => $tax,
                'total_amount'         => $subtotal - $discount + $tax,
                'valid_until'          => $data['valid_until'] ?? null,
                'notes'                => $data['notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            \Log::info('storeQuotation QUOTATION CREATED', [
                'quotation_id'     => $quotation->id,
                'quotation_number' => $quotation->quotation_number ?? 'N/A',
            ]);

        } catch (\Exception $e) {
            \Log::error('storeQuotation QUOTATION CREATE FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // ── Create line items ──────────────────────────────────────────────
        foreach ($data['items'] as $i => $item) {
            try {
                SolarQuotationItem::create([
                    'solar_quotation_id' => $quotation->id,
                    'product_id'         => $item['product_id'] ?? null,
                    'item_type'          => $item['item_type'],
                    'description'        => $item['description'],
                    'brand'              => $item['brand'] ?? null,
                    'model'              => $item['model'] ?? null,
                    'unit'               => $item['unit'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'sort_order'         => $i,
                ]);

                \Log::info("storeQuotation ITEM [{$i}] CREATED", [
                    'description' => $item['description'],
                    'item_type'   => $item['item_type'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                ]);

            } catch (\Exception $e) {
                \Log::error("storeQuotation ITEM [{$i}] FAILED", [
                    'item'  => $item,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        // ── Advance project status ─────────────────────────────────────────
        try {
            if ($status === 'sent' && $solarProject->canTransitionTo('quotation_generated')) {
                $solarProject->update(['status' => 'quotation_generated']);
                $solarProject->addHistoryEntry(
                    'quotation_generated',
                    'Quotation ' . $quotation->quotation_number . ' sent to customer. Total: ₱' . number_format($quotation->total_amount, 2)
                );
                \Log::info('storeQuotation PROJECT STATUS ADVANCED to quotation_generated');
            }
        } catch (\Exception $e) {
            \Log::error('storeQuotation PROJECT STATUS UPDATE FAILED', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        \Log::info('storeQuotation COMPLETE', ['quotation_id' => $quotation->id]);

        return redirect()
            ->route('vendor.solar.show', $solarProject)
            ->with('success', $status === 'sent'
                ? 'Quotation sent to customer successfully.'
                : 'Quotation saved as draft.'
            );
    }

    // ── Upload completion proof ───────────────────────────────────────────

    public function uploadProof(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);

        $request->validate([
            'document_type'  => ['required', 'in:completion_photo,commissioning_report,net_metering_certificate,contract,permit,site_survey_report,system_design,other'],
            'files'          => ['required', 'array', 'min:1', 'max:8'],
            'files.*'        => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
            'description'    => ['nullable', 'string', 'max:300'],
            'advance_status' => ['nullable', 'string'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store("solar/{$solarProject->id}/vendor", 'public');
            SolarProjectDocument::create([
                'solar_project_id' => $solarProject->id,
                'uploaded_by'      => auth()->id(),
                'document_type'    => $request->document_type,
                'uploaded_by_role' => 'vendor',
                'file_name'        => $file->getClientOriginalName(),
                'file_path'        => $path,
                'mime_type'        => $file->getMimeType(),
                'file_size'        => $file->getSize(),
                'description'      => $request->description,
            ]);
        }

        // Optionally advance status
        if ($request->filled('advance_status')
            && $solarProject->canTransitionTo($request->advance_status)) {
            $solarProject->update(['status' => $request->advance_status]);
            $solarProject->addHistoryEntry(
                $request->advance_status,
                'Documents uploaded and status advanced.'
            );
        }

        return back()->with('success', 'Documents uploaded successfully.');
    }

    // ── Add internal note ─────────────────────────────────────────────────

    public function addNote(Request $request, SolarProject $solarProject)
    {
        $this->authorize($solarProject);
        $request->validate(['note' => ['required', 'string', 'max:1000']]);

        $solarProject->addHistoryEntry('Note', $request->note);

        return back()->with('success', 'Note added.');
    }

    public function edit(SolarProject $solarProject)
    {

        // $editableStatuses = [
        //     'pending_payment',
        //     'pending_review',
        //     'under_review',
        // ];

        // abort_if(
        //     ! in_array($project->status, $editableStatuses, true),
        //     403,
        //     'This project can no longer be edited at its current status.'
        // );

        $solarProject->loadMissing('vendor');

        return view('vendor.solar.edit-project', compact('solarProject'));
    }

    public function update(Request $request, SolarProject $solarProject)
    {

        // $editableStatuses = [
        //     'pending_payment',
        //     'pending_review',
        //     'under_review',
        //     'survey_scheduled',
        // ];

        // abort_if(
        //     ! in_array($project->status, $editableStatuses, true),
        //     403,
        //     'This project can no longer be edited at its current status.'
        // );

        $data = $request->validate([
            'roof_type'              => ['nullable', 'in:concrete,metal,asphalt_shingle,clay_tile,other'],
            'roof_area_sqm'          => ['nullable', 'integer', 'min:0'],
            'system_type'            => ['nullable', 'in:grid_tie,off_grid,hybrid'],
            'estimated_monthly_bill' => ['nullable', 'numeric', 'min:0'],
            'desired_system_size_kw' => ['nullable', 'numeric', 'min:0'],
            'customer_notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $solarProject->update([
            'roof_type'              => $data['roof_type']              ?: null,
            'roof_area_sqm'          => $data['roof_area_sqm']          ?: null,
            'system_type'            => isset($data['system_type']) && $data['system_type'] !== ''
                                            ? $data['system_type']
                                            : null,
            'estimated_monthly_bill' => $data['estimated_monthly_bill'] ?: null,
            'desired_system_size_kw' => $data['desired_system_size_kw'] ?: null,
            'customer_notes'         => $data['customer_notes']         ?: null,
        ]);


        return redirect()
            ->route('vendor.solar.show', $solarProject)
            ->with('success', 'Project updated successfully.');
    }

}
