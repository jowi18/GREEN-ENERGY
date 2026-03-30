<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SolarContract;
use App\Models\SolarPayment;
use App\Models\SolarPaymentSchedule;
use App\Models\SolarProject;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerSolarPaymentController extends Controller
{
    public function __construct(private readonly PayPalService $paypal) {}

    private function customer()
    {
        return auth()->user()->customer;
    }

    private function authorizeContract(SolarContract $contract): void
    {
        if ($contract->customer_id !== $this->customer()->id) abort(403);
    }

    // ── Payment selection page ────────────────────────────────────────────

    public function show(SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        if (!in_array($contract->status, ['approved', 'signed', 'active'])) {
            return redirect()
                ->route('customer.solar.contract.show', [$solarProject, $contract])
                ->with('error', 'Contract must be approved before payment can be made.');
        }

        $contract->load(['paymentSchedules.payments', 'vendor']);

        // Only show pending/partial milestones
        $pendingSchedules = $contract->paymentSchedules
            ->whereIn('status', ['pending', 'invoiced', 'partial', 'overdue']);

        return view('customer.solar.payment', compact('solarProject', 'contract', 'pendingSchedules'));
    }

    // ── Initiate PayPal payment for a single milestone ────────────────────

    public function initiateMilestone(Request $request, SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        $request->validate([
            'schedule_id' => ['required', 'exists:solar_payment_schedules,id'],
        ]);

        $schedule = SolarPaymentSchedule::findOrFail($request->schedule_id);

        if ($schedule->solar_contract_id !== $contract->id) abort(403);
        if (in_array($schedule->status, ['paid', 'waived'])) {
            return back()->with('error', 'This milestone has already been paid.');
        }

        $amountDue = $schedule->balance;
        if ($amountDue <= 0) {
            return back()->with('error', 'No outstanding balance for this milestone.');
        }

        // Convert PHP to USD (PayPal requires USD for PH merchants without local currency)
        $usdAmount = $this->convertToUsd($amountDue);

        $result = $this->paypal->createPayment(
            amount:      $usdAmount,
            currency:    'USD',
            description: "Payment: {$schedule->milestone_name} — {$contract->contract_number}",
            returnUrl:   route('customer.solar.payment.capture', [
                $solarProject,
                $contract,
                'schedule_id' => $schedule->id,
            ]),
            cancelUrl:   route('customer.solar.payment.cancel', [$solarProject, $contract]),
        );

        if (!$result['success']) {
            Log::error('Solar PayPal initiate failed', $result);
            return back()->with('error', 'Could not initiate PayPal payment: ' . ($result['message'] ?? 'Unknown error.'));
        }

        // Store pending PayPal order ID on schedule
        $schedule->update(['pending_paypal_order_id' => $result['order_id']]);

        return redirect($result['approval_url']);
    }

    // ── Initiate PayPal payment for multiple milestones at once ───────────

    public function initiateMultiple(Request $request, SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        $request->validate([
            'schedule_ids'   => ['required', 'array', 'min:1'],
            'schedule_ids.*' => ['exists:solar_payment_schedules,id'],
        ]);

        $schedules = SolarPaymentSchedule::whereIn('id', $request->schedule_ids)
            ->where('solar_contract_id', $contract->id)
            ->whereNotIn('status', ['paid', 'waived'])
            ->get();

        if ($schedules->isEmpty()) {
            return back()->with('error', 'No valid milestones selected.');
        }

        $items = $schedules->map(fn($s) => [
            'name'        => $s->milestone_name,
            'description' => "Contract: {$contract->contract_number}",
            'quantity'    => 1,
            'price'       => $this->convertToUsd($s->balance),
        ])->toArray();

        $result = $this->paypal->createPaymentWithItems(
            items:     $items,
            currency:  'USD',
            returnUrl: route('customer.solar.payment.capture.multiple', [
                $solarProject,
                $contract,
                'schedule_ids' => implode(',', $request->schedule_ids),
            ]),
            cancelUrl: route('customer.solar.payment.cancel', [$solarProject, $contract]),
        );

        if (!$result['success']) {
            Log::error('Solar PayPal multi-initiate failed', $result);
            return back()->with('error', 'Could not initiate PayPal payment: ' . ($result['message'] ?? 'Unknown error.'));
        }

        // Tag all selected schedules with the pending order
        SolarPaymentSchedule::whereIn('id', $request->schedule_ids)->update([
            'pending_paypal_order_id' => $result['order_id'],
        ]);

        return redirect($result['approval_url']);
    }

    // ── Capture: single milestone ─────────────────────────────────────────

    public function capture(Request $request, SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        $orderId    = $request->query('token'); // PayPal passes 'token' on return
        $scheduleId = $request->query('schedule_id');

        if (!$orderId || !$scheduleId) {
            return redirect()
                ->route('customer.solar.payment.show', [$solarProject, $contract])
                ->with('error', 'Invalid payment return. Please try again.');
        }

        $schedule = SolarPaymentSchedule::findOrFail($scheduleId);
        if ($schedule->solar_contract_id !== $contract->id) abort(403);

        $capture = $this->paypal->capturePayment($orderId);

        if (!$capture['success']) {
            Log::error('Solar PayPal capture failed', $capture);
            $schedule->update(['pending_paypal_order_id' => null]);

            return redirect()
                ->route('customer.solar.payment.show', [$solarProject, $contract])
                ->with('error', 'Payment capture failed: ' . ($capture['message'] ?? 'Please contact support.'));
        }

        // Convert USD back to PHP amount (stored in PHP)
        $usdPaid = (float) $capture['amount'];
        $phpPaid = $this->convertFromUsd($usdPaid);

        $payment = SolarPayment::create([
            'solar_contract_id'         => $contract->id,
            'solar_payment_schedule_id' => $schedule->id,
            'recorded_by'               => auth()->id(),
            'amount_paid'               => $phpPaid,
            'currency'                  => 'PHP',
            'payment_method'            => 'paypal',
            'payment_source'            => 'paypal',
            'reference_number'          => $capture['transaction_id'],
            'paypal_order_id'           => $orderId,
            'paypal_transaction_id'     => $capture['transaction_id'],
            'paypal_payer_id'           => $capture['payer_id'],
            'paypal_payer_email'        => $capture['payer_email'],
            'payment_date'              => today(),
            'invoice_generated_at'      => now(),
            'notes'                     => 'PayPal payment by customer.',
        ]);

        $schedule->update(['pending_paypal_order_id' => null]);

        // Auto-complete contract if fully paid
        $contract->refresh();
        if ($contract->isFullyPaid() && $contract->status === 'active') {
            $contract->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return redirect()
            ->route('customer.solar.payment.success', [$solarProject, $contract, $payment])
            ->with('success', 'Payment of ₱' . number_format($phpPaid, 2) . ' received successfully!');
    }

    // ── Capture: multiple milestones ──────────────────────────────────────

    public function captureMultiple(Request $request, SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        $orderId      = $request->query('token');
        $scheduleIds  = explode(',', $request->query('schedule_ids', ''));

        if (!$orderId || empty($scheduleIds)) {
            return redirect()
                ->route('customer.solar.payment.show', [$solarProject, $contract])
                ->with('error', 'Invalid payment return. Please try again.');
        }

        $capture = $this->paypal->capturePayment($orderId);

        if (!$capture['success']) {
            Log::error('Solar PayPal multi-capture failed', $capture);
            SolarPaymentSchedule::whereIn('id', $scheduleIds)->update(['pending_paypal_order_id' => null]);

            return redirect()
                ->route('customer.solar.payment.show', [$solarProject, $contract])
                ->with('error', 'Payment capture failed: ' . ($capture['message'] ?? 'Please contact support.'));
        }

        $schedules    = SolarPaymentSchedule::whereIn('id', $scheduleIds)
            ->where('solar_contract_id', $contract->id)->get();
        $totalPhp     = $schedules->sum('balance');
        $usdPaid      = (float) $capture['amount'];
        $phpPaid      = $this->convertFromUsd($usdPaid);

        // Distribute payment proportionally across milestones
        $lastPayment  = null;
        foreach ($schedules as $i => $schedule) {
            $proportion  = $totalPhp > 0 ? ($schedule->balance / $totalPhp) : (1 / $schedules->count());
            $scheduleAmt = round($phpPaid * $proportion, 2);

            // Give rounding remainder to last schedule
            if ($i === $schedules->count() - 1) {
                $scheduleAmt = $phpPaid - $schedules->take($i)->sum('balance');
            }

            $lastPayment = SolarPayment::create([
                'solar_contract_id'         => $contract->id,
                'solar_payment_schedule_id' => $schedule->id,
                'recorded_by'               => auth()->id(),
                'amount_paid'               => $scheduleAmt,
                'currency'                  => 'PHP',
                'payment_method'            => 'paypal',
                'payment_source'            => 'paypal',
                'reference_number'          => $capture['transaction_id'],
                'paypal_order_id'           => $orderId,
                'paypal_transaction_id'     => $capture['transaction_id'],
                'paypal_payer_id'           => $capture['payer_id'],
                'paypal_payer_email'        => $capture['payer_email'],
                'payment_date'              => today(),
                'invoice_generated_at'      => now(),
                'notes'                     => 'Multi-milestone PayPal payment by customer.',
            ]);
        }

        SolarPaymentSchedule::whereIn('id', $scheduleIds)->update(['pending_paypal_order_id' => null]);

        $contract->refresh();
        if ($contract->isFullyPaid() && $contract->status === 'active') {
            $contract->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return redirect()
            ->route('customer.solar.payment.success', [$solarProject, $contract, $lastPayment])
            ->with('success', 'Total payment of ₱' . number_format($phpPaid, 2) . ' received successfully!');
    }

    // ── Cancel ────────────────────────────────────────────────────────────

    public function cancel(SolarProject $solarProject, SolarContract $contract)
    {
        $this->authorizeContract($contract);

        // Clear any pending PayPal order IDs
        $contract->paymentSchedules()
            ->whereNotNull('pending_paypal_order_id')
            ->update(['pending_paypal_order_id' => null]);

        return redirect()
            ->route('customer.solar.payment.show', [$solarProject, $contract])
            ->with('info', 'Payment was cancelled. No charges were made.');
    }

    // ── Success page ──────────────────────────────────────────────────────

    public function success(SolarProject $solarProject, SolarContract $contract, SolarPayment $payment)
    {
        $this->authorizeContract($contract);
        $contract->load(['paymentSchedules.payments', 'vendor']);

        return view('customer.solar.payment_success', compact(
            'solarProject', 'contract', 'payment'
        ));
    }

    // ── Currency conversion helpers ───────────────────────────────────────

    /**
     * Convert PHP to USD for PayPal.
     * Uses config or falls back to env rate.
     */
    private function convertToUsd(float $php): float
    {
        $rate = (float) config('paypal.php_to_usd_rate', env('PHP_TO_USD_RATE', 0.0175));
        return round($php * $rate, 2);
    }

    /**
     * Convert USD back to PHP after capture.
     */
    private function convertFromUsd(float $usd): float
    {
        $rate = (float) config('paypal.php_to_usd_rate', env('PHP_TO_USD_RATE', 0.0175));
        return round($usd / $rate, 2);
    }
}
