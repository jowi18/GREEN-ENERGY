{{-- resources/views/customer/solar/payment_success.blade.php --}}
@extends('layouts.customer')
@section('title', 'Payment Successful')

@section('content')
    <div class="cu-page" style="max-width:560px;text-align:center;padding-top:2rem;">

        {{-- Success icon --}}
        <div
            style="width:80px;height:80px;background:var(--cg-100);border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        margin:0 auto 1.25rem;font-size:2rem;color:var(--cg-600);">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h4 class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;letter-spacing:-.02em;">
            Payment Successful!
        </h4>
        <p class="text-muted mb-1" style="font-size:.875rem;">
            Your payment has been received and recorded.
        </p>
        <div class="mono fw-700 mb-4" style="font-size:.82rem;color:var(--cg-700);">
            OR: {{ $payment->or_number }}
            · Ref: {{ $payment->reference_number ?? '—' }}
        </div>

        {{-- Receipt summary --}}
        <div
            style="background:var(--card-bg);border:1.5px solid var(--card-border);
        border-radius:var(--r-xl);padding:1.25rem 1.5rem;margin-bottom:1.5rem;text-align:left;">

            <div
                style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
            color:var(--tx-muted);margin-bottom:.85rem;">
                Payment Receipt</div>

            @foreach ([['Amount Paid', '₱' . number_format($payment->amount_paid, 2)], ['Payment Method', $payment->payment_method_label], ['Payment Date', $payment->payment_date->format('F d, Y')], ['Contract', $contract->contract_number], ['Milestone', $payment->schedule?->milestone_name ?? 'General Payment'], ['PayPal Txn', $payment->paypal_transaction_id ?? '—']] as [$label, $value])
                <div
                    style="display:flex;justify-content:space-between;
                font-size:.82rem;padding:.35rem 0;border-bottom:1px solid var(--n-100);">
                    <span class="text-muted">{{ $label }}</span>
                    <span class="fw-600 {{ $label === 'Amount Paid' ? 'mono' : '' }}"
                        style="{{ $label === 'Amount Paid' ? 'color:var(--cg-700);font-size:.95rem;' : '' }}">
                        {{ $value }}
                    </span>
                </div>
            @endforeach

            {{-- Remaining balance --}}
            @php $contract->refresh(); @endphp
            <div
                style="display:flex;justify-content:space-between;
            font-size:.875rem;padding:.5rem 0;font-weight:700;">
                <span>Remaining Balance</span>
                <span class="mono" style="color:{{ $contract->balance > 0 ? '#dc2626' : 'var(--cg-600)' }};">
                    {{ $contract->balance > 0 ? '₱' . number_format($contract->balance, 2) : 'Fully Paid ✅' }}
                </span>
            </div>

            {{-- Payment progress bar --}}
            <div style="height:6px;background:var(--n-100);border-radius:3px;margin-top:.5rem;overflow:hidden;">
                <div
                    style="height:100%;border-radius:3px;background:var(--cg-500);
                width:{{ $contract->payment_progress_percent }}%;">
                </div>
            </div>
            <div style="font-size:.7rem;color:var(--tx-muted);text-align:right;margin-top:.25rem;">
                {{ $contract->payment_progress_percent }}% of ₱{{ number_format($contract->contract_amount, 2) }} paid
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('customer.solar.contract.show', [$solarProject, $contract]) }}"
                class="cu-btn cu-btn--primary">
                <i class="bi bi-file-earmark-text"></i> View Contract
            </a>
            <a href="{{ route('customer.solar.show', $solarProject) }}" class="cu-btn cu-btn--ghost">
                <i class="bi bi-arrow-left"></i> Back to Project
            </a>
        </div>

        @if ($contract->balance > 0)
            <div style="margin-top:1.25rem;">
                <a href="{{ route('customer.solar.payment.show', [$solarProject, $contract]) }}"
                    class="cu-btn cu-btn--ghost cu-btn--sm">
                    <i class="bi bi-cash-coin me-1"></i>
                    Pay Next Milestone
                </a>
            </div>
        @endif

        {{-- Fully paid CTA --}}
        @if ($contract->isFullyPaid())
            <div
                style="margin-top:1.25rem;background:var(--cg-50);border:1.5px solid var(--cg-200);
            border-radius:var(--r-xl);padding:1rem 1.25rem;">
                <div class="fw-700 mb-1" style="color:var(--cg-700);">🎉 Fully Paid!</div>
                <div class="text-muted" style="font-size:.82rem;">
                    Your contract is fully settled. After project turnover you can submit a review.
                </div>
            </div>
        @endif

    </div>
@endsection
