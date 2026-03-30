<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SolarContract;
use App\Models\SolarContractAdjustment;
use App\Models\SolarPaymentRecord;
use App\Models\SolarPaymentSchedule;
use App\Models\SolarProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VendorSolarContractController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorizeProject(SolarProject $project): void
    {
        if ($project->vendor_id !== $this->vendor()->id) abort(403);
    }

    private function authorizeContract(SolarContract $contract): void
    {
        if ($contract->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── Create contract form ──────────────────────────────────────────────

    public function create(SolarProject $solarProject)
    {
        $this->authorizeProject($solarProject);
        $solarProject->load(['customer', 'quotations.items']);
        $approvedQuotation = $solarProject->quotations->firstWhere('status', 'approved');

        return view('vendor.solar.contract.create', compact('solarProject', 'approvedQuotation'));
    }

    // ── Store contract ────────────────────────────────────────────────────

    // public function store(Request $request, SolarProject $solarProject)
    // {
    //     $this->authorizeProject($solarProject);

    //     $data = $request->validate([
    //         'scope_of_work'           => ['required', 'string'],
    //         'warranty_terms'          => ['required', 'string'],
    //         'penalties_cancellation'  => ['required', 'string'],
    //         'custom_clauses'          => ['nullable', 'string'],
    //         'payment_mode'            => ['required', 'in:full,installment,progress_based'],
    //         'contract_amount'         => ['required', 'numeric', 'min:1'],
    //         'payment_start_date'      => ['nullable', 'date'],
    //         'installment_count'       => ['required_if:payment_mode,installment', 'nullable', 'integer', 'min:2', 'max:24'],
    //         'installment_frequency'   => ['required_if:payment_mode,installment', 'nullable', 'in:weekly,monthly,quarterly'],
    //         'action'                  => ['required', 'in:draft,send'],
    //         'solar_quotation_id'      => ['nullable', 'exists:solar_quotations,id'],
    //     ]);

    //     // Supersede any existing active contracts
    //     $solarProject->contracts()
    //         ->whereIn('status', ['draft', 'sent', 'adjustment_requested'])
    //         ->update(['status' => 'cancelled']);

    //     $status   = $data['action'] === 'send' ? 'sent' : 'draft';
    //     $existing = SolarContract::forProject($solarProject->id)->count();

    //     $contract = SolarContract::create([
    //         'solar_project_id'        => $solarProject->id,
    //         'solar_quotation_id'      => $data['solar_quotation_id'] ?? null,
    //         'vendor_id'               => $this->vendor()->id,
    //         'customer_id'             => $solarProject->customer_id,
    //         'created_by'              => auth()->id(),
    //         'version'                 => $existing + 1,
    //         'status'                  => $status,
    //         'scope_of_work'           => $data['scope_of_work'],
    //         'warranty_terms'          => $data['warranty_terms'],
    //         'penalties_cancellation'  => $data['penalties_cancellation'],
    //         'custom_clauses'          => $data['custom_clauses'] ?? null,
    //         'payment_mode'            => $data['payment_mode'],
    //         'contract_amount'         => $data['contract_amount'],
    //         'payment_start_date'      => $data['payment_start_date'] ?? null,
    //         'installment_count'       => $data['installment_count'] ?? null,
    //         'installment_frequency'   => $data['installment_frequency'] ?? null,
    //     ]);

    //     // Generate payment schedule if sending
    //     if ($status === 'sent' || $status === 'draft') {
    //         $contract->generatePaymentSchedules();
    //     }

    //     // Advance project status
    //     if ($status === 'sent') {
    //         $solarProject->addHistoryEntry(
    //             $solarProject->status,
    //             'Contract ' . $contract->contract_number . ' sent to customer for review.'
    //         );
    //     }

    //     return redirect()
    //         ->route('vendor.solar.contract.show', [$solarProject, $contract])
    //         ->with('success', $status === 'sent'
    //             ? 'Contract sent to customer for review.'
    //             : 'Contract saved as draft.'
    //         );
    // }

    public function store(Request $request, SolarProject $solarProject)
    {
        Log::info('Contract store started', [
            'project_id' => $solarProject->id,
            'user_id' => auth()->id()
        ]);

        try {

            $this->authorizeProject($solarProject);

            Log::info('Authorization passed');

            $data = $request->validate([
                'scope_of_work'           => ['required', 'string'],
                'warranty_terms'          => ['required', 'string'],
                'penalties_cancellation'  => ['required', 'string'],
                'custom_clauses'          => ['nullable', 'string'],
                'payment_mode'            => ['required', 'in:full,installment,progress_based'],
                'contract_amount'         => ['required', 'numeric', 'min:1'],
                'payment_start_date'      => ['nullable', 'date'],
                'installment_count'       => ['required_if:payment_mode,installment', 'nullable', 'integer', 'min:2', 'max:24'],
                'installment_frequency'   => ['required_if:payment_mode,installment', 'nullable', 'in:weekly,monthly,quarterly'],
                'action'                  => ['required', 'in:draft,send'],
                'solar_quotation_id'      => ['nullable', 'exists:solar_quotations,id'],
            ]);

            Log::info('Validation passed', $data);


            // Cancel existing contracts
            $cancelled = $solarProject->contracts()
                ->whereIn('status', ['draft', 'sent', 'adjustment_requested'])
                ->update(['status' => 'cancelled']);

            Log::info('Existing contracts cancelled', [
                'count' => $cancelled
            ]);


            $status = $data['action'] === 'send' ? 'sent' : 'draft';

            $existing = SolarContract::forProject($solarProject->id)->count();

            Log::info('Existing contracts counted', [
                'count' => $existing
            ]);


            $contract = SolarContract::create([
                'solar_project_id'        => $solarProject->id,
                'solar_quotation_id'      => $data['solar_quotation_id'] ?? null,
                'vendor_id'               => $this->vendor()->id,
                'customer_id'             => $solarProject->customer_id,
                'created_by'              => auth()->id(),
                'version'                 => $existing + 1,
                'status'                  => $status,
                'scope_of_work'           => $data['scope_of_work'],
                'warranty_terms'          => $data['warranty_terms'],
                'penalties_cancellation'  => $data['penalties_cancellation'],
                'custom_clauses'          => $data['custom_clauses'] ?? null,
                'payment_mode'            => $data['payment_mode'],
                'contract_amount'         => $data['contract_amount'],
                'payment_start_date'      => $data['payment_start_date'] ?? null,
                'installment_count'       => $data['installment_count'] ?? null,
                'installment_frequency'   => $data['installment_frequency'] ?? null,
            ]);

            Log::info('Contract created', [
                'contract_id' => $contract->id
            ]);


            // Generate schedule
            if ($status === 'sent' || $status === 'draft') {

                Log::info('Generating payment schedules');

                $contract->generatePaymentSchedules();

                Log::info('Payment schedules generated successfully');
            }


            if ($status === 'sent') {

                Log::info('Updating project history');

                $solarProject->addHistoryEntry(
                    $solarProject->status,
                    'Contract ' . $contract->contract_number . ' sent to customer for review.'
                );
            }


            Log::info('Contract store completed successfully');

            return redirect()
                ->route('vendor.solar.contract.show', [$solarProject, $contract])
                ->with('success', $status === 'sent'
                    ? 'Contract sent to customer for review.'
                    : 'Contract saved as draft.'
                );

        } catch (\Throwable $e) {

            Log::error('Contract store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to create contract. Check logs.');
        }
    }

    // ── Show contract ─────────────────────────────────────────────────────

    public function show(SolarProject $solarProject, SolarContract $solarContract)
    {
        $this->authorizeProject($solarProject);
        $solarContract->load([
            'customer', 'quotation.items', 'adjustments.requester',
            'paymentSchedules.records',
        ]);

        return view('vendor.solar.contract.show', compact('solarProject', 'solarContract'));
    }

    // ── Respond to adjustment request ─────────────────────────────────────

    public function respondAdjustment(Request $request, SolarProject $solarProject,
                                      SolarContract $solarContract, SolarContractAdjustment $adjustment)
    {
        $this->authorizeContract($solarContract);

        $request->validate([
            'action'          => ['required', 'in:addressed,dismissed'],
            'vendor_response' => ['required', 'string', 'max:1000'],
        ]);

        $adjustment->update([
            'status'               => $request->action,
            'vendor_response'      => $request->vendor_response,
            'vendor_responded_at'  => now(),
        ]);

        // If all adjustments addressed, optionally re-send
        if ($solarContract->pendingAdjustments()->count() === 0) {
            $solarContract->update(['status' => 'sent']);
        }

        return back()->with('success', 'Adjustment request ' . $request->action . '.');
    }

    // ── Log a payment ─────────────────────────────────────────────────────

    public function logPayment(Request $request, SolarProject $solarProject,
                               SolarContract $solarContract, SolarPaymentSchedule $schedule)
    {
        $this->authorizeContract($solarContract);

        $data = $request->validate([
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'payment_method'  => ['required', 'in:cash,bank_transfer,gcash,maya,paypal,check,other'],
            'reference_code'  => ['nullable', 'string', 'max:100'],
            'payment_date'    => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'proof'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')
                ->store("solar/{$solarProject->id}/payments", 'public');
        }

        SolarPaymentRecord::create([
            'solar_payment_schedule_id' => $schedule->id,
            'solar_contract_id'         => $solarContract->id,
            'recorded_by'               => auth()->id(),
            'amount'                    => $data['amount'],
            'payment_method'            => $data['payment_method'],
            'reference_code'            => $data['reference_code'] ?? null,
            'payment_date'              => $data['payment_date'],
            'notes'                     => $data['notes'] ?? null,
            'proof_path'                => $proofPath,
        ]);

        // Trigger progress-based payment for next milestone if applicable
        if ($solarContract->payment_mode === 'progress_based') {
            $next = $solarContract->paymentSchedules()
                ->where('status', 'pending')
                ->where('installment_number', '>', $schedule->installment_number)
                ->orderBy('installment_number')
                ->first();

            if ($next && $next->milestone_status) {
                $solarProject->addHistoryEntry(
                    $solarProject->status,
                    'Payment logged for: ' . $schedule->label . '. Next milestone: ' . $next->label
                );
            }
        }

        return back()->with('success', 'Payment of ₱' . number_format($data['amount'], 2) . ' recorded.');
    }

    // ── Finalize / sign contract ──────────────────────────────────────────

    public function sign(Request $request, SolarProject $solarProject, SolarContract $solarContract)
    {
        $this->authorizeContract($solarContract);

        if (!in_array($solarContract->status, ['approved'])) {
            return back()->with('error', 'Contract must be approved by customer before signing.');
        }

        $solarContract->update([
            'status'      => 'signed',
            'approved_by' => auth()->id(),
            'signed_at'   => now(),
        ]);

        // Advance project to contract_signed
        if ($solarProject->canTransitionTo('contract_signed')) {
            $solarProject->update(['status' => 'contract_signed']);
            $solarProject->addHistoryEntry(
                'contract_signed',
                'Contract ' . $solarContract->contract_number . ' signed by both parties.'
            );
        }

        return back()->with('success', 'Contract signed. Project is now at "Contract Signed" stage.');
    }

    // ── Print invoice ─────────────────────────────────────────────────────

    public function invoice(SolarProject $solarProject, SolarContract $solarContract)
    {
        $this->authorizeContract($solarContract);
        $solarContract->load([
            'customer', 'vendor', 'quotation.items',
            'paymentSchedules.records',
        ]);

        return view('vendor.solar.contract.invoice', compact('solarProject', 'solarContract'));
    }
}
