{{-- resources/views/vendor/solar/contract/show.blade.php --}}
@extends('layouts.vendor')
@section('title', 'Contract · ' . $solarContract->contract_number)
@section('page-title', 'Contract')

@section('breadcrumb')
    <a href="{{ route('vendor.solar.index') }}" class="text-secondary">Solar Projects</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.solar.show', $solarProject) }}" class="text-secondary">{{ $solarProject->project_number }}</a>
    <span class="sep">›</span><span class="current">{{ $solarContract->contract_number }}</span>
@endsection

@push('styles')
    <style>
        .contract-section {
            margin-bottom: 1.25rem;
        }

        .contract-section__label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tx-muted);
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .contract-section__body {
            background: var(--n-50);
            border: 1px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .85rem 1rem;
            font-size: .82rem;
            color: var(--tx-secondary);
            line-height: 1.8;
            white-space: pre-wrap;
        }

        .pay-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: .75rem;
            align-items: center;
            padding: .65rem 0;
            border-bottom: 1px solid var(--n-100);
            font-size: .82rem;
        }

        .pay-row:last-child {
            border: none;
        }

        .adj-card {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .85rem;
            margin-bottom: .65rem;
        }

        .adj-card.pending {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .adj-card.addressed {
            border-color: var(--g-200);
            background: var(--g-50);
        }
    </style>
@endpush

@section('content')
    @php $ct = $solarContract; @endphp

    {{-- Header --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="mono fw-700"
                            style="font-size:1rem;color:var(--g-700);">{{ $ct->contract_number }}</span>
                        <span class="vd-badge {{ $ct->status_badge_class }}"
                            style="font-size:.68rem;">{{ $ct->status_label }}</span>
                        <span class="vd-badge vd-badge--neutral" style="font-size:.65rem;">v{{ $ct->version }}</span>
                    </div>
                    <div class="text-muted" style="font-size:.78rem;">
                        {{ $ct->customer->full_name }} · {{ $ct->payment_mode_label }}
                        · ₱{{ number_format($ct->contract_amount, 2) }}
                        · Created {{ $ct->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if ($ct->status === 'approved')
                        <form method="POST" action="{{ route('vendor.solar.contract.sign', [$solarProject, $ct]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="vd-btn vd-btn--primary">
                                <i class="bi bi-pen"></i> Finalize & Sign
                            </button>
                        </form>
                    @endif
                    @if (in_array($ct->status, ['signed', 'approved']))
                        <a href="{{ route('vendor.solar.contract.invoice', [$solarProject, $ct]) }}" target="_blank"
                            class="vd-btn vd-btn--ghost">
                            <i class="bi bi-printer"></i> Print Invoice
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="vd-alert vd-alert--success gap-section">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-xl-8">

            {{-- Adjustment requests --}}
            @if ($ct->adjustments->isNotEmpty())
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-chat-left-dots"></i> Customer Adjustment
                            Requests</span>
                        @if ($ct->pendingAdjustments()->count() > 0)
                            <span class="vd-badge vd-badge--warning" style="font-size:.65rem;">
                                {{ $ct->pendingAdjustments()->count() }} pending
                            </span>
                        @endif
                    </div>
                    <div class="vd-card__body">
                        @foreach ($ct->adjustments as $adj)
                            <div class="adj-card {{ $adj->status }}">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;margin-bottom:.5rem;">
                                    <div>
                                        <div class="fw-700" style="font-size:.82rem;">
                                            Section: {{ $adj->section }}
                                        </div>
                                        <div class="text-muted" style="font-size:.72rem;">
                                            Requested by {{ $adj->requester->name }} ·
                                            {{ $adj->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <span class="vd-badge {{ $adj->status_badge_class }}" style="font-size:.65rem;">
                                        {{ ucfirst($adj->status) }}
                                    </span>
                                </div>
                                <div
                                    style="font-size:.82rem;color:var(--tx-secondary);line-height:1.6;margin-bottom:.65rem;">
                                    {{ $adj->notes }}
                                </div>

                                @if ($adj->vendor_response)
                                    <div
                                        style="background:var(--g-50);border:1px solid var(--g-200);
                                border-radius:var(--r-sm);padding:.6rem .75rem;
                                font-size:.78rem;color:var(--g-800);">
                                        <strong>Your response:</strong> {{ $adj->vendor_response }}
                                    </div>
                                @endif

                                @if ($adj->status === 'pending')
                                    <form method="POST"
                                        action="{{ route('vendor.solar.contract.adjustment.respond', [$solarProject, $ct, $adj]) }}"
                                        style="margin-top:.65rem;">
                                        @csrf @method('PATCH')
                                        <textarea name="vendor_response" rows="2" class="vd-input mb-2" style="font-size:.78rem;" required
                                            placeholder="Your response to this adjustment request…"></textarea>
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="action" value="addressed"
                                                class="vd-btn vd-btn--primary vd-btn--sm">
                                                <i class="bi bi-check2"></i> Mark as Addressed
                                            </button>
                                            <button type="submit" name="action" value="dismissed"
                                                class="vd-btn vd-btn--ghost vd-btn--sm">
                                                <i class="bi bi-x"></i> Dismiss
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Contract content --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-file-earmark-text"></i> Contract Content</span>
                </div>
                <div class="vd-card__body">
                    <div class="contract-section">
                        <div class="contract-section__label"><i class="bi bi-list-check"></i> Scope of Work</div>
                        <div class="contract-section__body">{{ $ct->scope_of_work }}</div>
                    </div>
                    <div class="contract-section">
                        <div class="contract-section__label"><i class="bi bi-shield-check"></i> Warranty Terms</div>
                        <div class="contract-section__body">{{ $ct->warranty_terms }}</div>
                    </div>
                    <div class="contract-section">
                        <div class="contract-section__label"><i class="bi bi-exclamation-triangle"></i> Penalties &
                            Cancellation</div>
                        <div class="contract-section__body">{{ $ct->penalties_cancellation }}</div>
                    </div>
                    @if ($ct->custom_clauses)
                        <div class="contract-section">
                            <div class="contract-section__label"><i class="bi bi-pencil-square"></i> Additional Clauses
                            </div>
                            <div class="contract-section__body">{{ $ct->custom_clauses }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment schedule --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-cash-stack"></i> Payment Schedule</span>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:.78rem;color:var(--tx-muted);">
                            Paid: <strong class="text-green">₱{{ number_format($ct->total_paid, 2) }}</strong>
                            / ₱{{ number_format($ct->contract_amount, 2) }}
                        </span>
                    </div>
                </div>
                <div class="vd-card__body">

                    @foreach ($ct->paymentSchedules as $schedule)
                        <div class="pay-row">
                            <div>
                                <div class="fw-600" style="font-size:.845rem;">{{ $schedule->label }}</div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $schedule->reference_number }}
                                    @if ($schedule->due_date)
                                        · Due {{ $schedule->due_date->format('M d, Y') }}
                                    @elseif ($schedule->milestone_status)
                                        · Upon:
                                        {{ \App\Models\SolarProject::STATUS_LABELS[$schedule->milestone_status] ?? $schedule->milestone_status }}
                                    @endif
                                </div>
                            </div>
                            <div class="mono fw-600" style="font-size:.82rem;">
                                ₱{{ number_format($schedule->amount, 2) }}
                            </div>
                            <div>
                                <span class="vd-badge {{ $schedule->status_badge_class }}" style="font-size:.65rem;">
                                    {{ ucfirst($schedule->status) }}
                                    @if ($schedule->amount_paid > 0 && $schedule->status !== 'paid')
                                        (₱{{ number_format($schedule->amount_paid, 2) }} paid)
                                    @endif
                                </span>
                            </div>
                            <div>
                                @if (!in_array($schedule->status, ['paid', 'waived']) && in_array($ct->status, ['signed', 'approved']))
                                    <button class="vd-btn vd-btn--ghost vd-btn--sm"
                                        onclick="openPaymentModal({{ $schedule->id }}, '{{ $schedule->label }}', {{ $schedule->balance }})">
                                        <i class="bi bi-plus-lg"></i> Log
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Payment records for this schedule --}}
                        @foreach ($schedule->records as $rec)
                            <div
                                style="display:flex;justify-content:space-between;align-items:center;
                            padding:.35rem .75rem .35rem 1.25rem;font-size:.75rem;
                            background:var(--g-50);border-left:3px solid var(--g-300);
                            border-radius:0 var(--r-sm) var(--r-sm) 0;margin-bottom:.25rem;">
                                <div>
                                    <span class="mono fw-600">{{ $rec->receipt_number }}</span>
                                    · {{ $rec->payment_method_label }}
                                    · {{ $rec->payment_date->format('M d, Y') }}
                                    @if ($rec->reference_code)
                                        · Ref: {{ $rec->reference_code }}
                                    @endif
                                </div>
                                <span class="mono fw-700" style="color:var(--g-700);">
                                    ₱{{ number_format($rec->amount, 2) }}
                                </span>
                            </div>
                        @endforeach
                    @endforeach

                </div>
            </div>

        </div>

        {{-- Right: contract meta --}}
        <div class="col-12 col-xl-4">
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-info-circle"></i> Contract Info</span>
                </div>
                <div class="vd-card__body">
                    <div style="display:flex;flex-direction:column;gap:.65rem;font-size:.82rem;">
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Contract #</div>
                            <div class="mono fw-700" style="color:var(--g-700);">{{ $ct->contract_number }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Customer</div>
                            <div class="fw-600">{{ $ct->customer->full_name }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Payment Mode</div>
                            <div class="fw-600">{{ $ct->payment_mode_label }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Contract Amount</div>
                            <div class="mono fw-700" style="font-size:1rem;color:var(--g-700);">
                                ₱{{ number_format($ct->contract_amount, 2) }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Total Paid</div>
                            <div class="mono fw-600" style="color:var(--g-600);">₱{{ number_format($ct->total_paid, 2) }}
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                Balance</div>
                            <div class="mono fw-700" style="color:{{ $ct->balance > 0 ? '#dc2626' : 'var(--g-600)' }};">
                                ₱{{ number_format($ct->balance, 2) }}
                            </div>
                        </div>
                        @if ($ct->customer_viewed_at)
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Customer Viewed</div>
                                <div class="fw-600" style="font-size:.78rem;">
                                    {{ $ct->customer_viewed_at->format('M d, Y · g:i A') }}</div>
                            </div>
                        @endif
                        @if ($ct->customer_approved_at)
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Customer Approved</div>
                                <div class="fw-600 text-green" style="font-size:.78rem;">
                                    {{ $ct->customer_approved_at->format('M d, Y · g:i A') }}</div>
                            </div>
                        @endif
                        @if ($ct->signed_at)
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Signed</div>
                                <div class="fw-600 text-green" style="font-size:.78rem;">
                                    {{ $ct->signed_at->format('M d, Y · g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Log payment modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="paymentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Log Payment</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="fw-600 mb-3" id="paymentScheduleLabel" style="font-size:.875rem;"></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="vd-label" style="font-size:.72rem;">Amount (₱) *</label>
                                <input type="number" name="amount" id="paymentAmount" class="vd-input mono"
                                    min="0.01" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="col-6">
                                <label class="vd-label" style="font-size:.72rem;">Date *</label>
                                <input type="date" name="payment_date" class="vd-input" required
                                    value="{{ today()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="vd-label" style="font-size:.72rem;">Payment Method *</label>
                            <select name="payment_method" class="vd-select" required>
                                @foreach ([
            'cash' => '💵 Cash',
            'bank_transfer' => '🏦 Bank Transfer',
            'gcash' => '📱 GCash',
            'maya' => '📱 Maya',
            'paypal' => '🌐 PayPal',
            'check' => '📄 Check',
            'other' => 'Other',
        ] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="vd-label" style="font-size:.72rem;">Reference Code</label>
                            <input type="text" name="reference_code" class="vd-input"
                                placeholder="Bank ref, GCash ref…">
                        </div>
                        <div class="mb-2">
                            <label class="vd-label" style="font-size:.72rem;">Notes</label>
                            <input type="text" name="notes" class="vd-input" placeholder="Optional note…">
                        </div>
                        <div>
                            <label class="vd-label" style="font-size:.72rem;">Proof of Payment (optional)</label>
                            <input type="file" name="proof" class="vd-input" accept="image/*,application/pdf"
                                style="font-size:.78rem;padding:.4rem .65rem;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-check2"></i> Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openPaymentModal(scheduleId, label, balance) {
            document.getElementById('paymentScheduleLabel').textContent = label;
            document.getElementById('paymentAmount').value = balance.toFixed(2);
            document.getElementById('paymentAmount').max = balance;
            document.getElementById('paymentForm').action =
                `{{ url('vendor/solar/' . $solarProject->id . '/contract/' . $ct->id . '/schedule') }}/${scheduleId}/payment`;
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }
    </script>
@endpush
