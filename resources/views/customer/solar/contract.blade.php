{{-- resources/views/customer/solar/contract.blade.php --}}
@extends('layouts.customer')
@section('title', 'Contract · ' . $solarContract->contract_number)

@push('styles')
    <style>
        .ct-block {
            background: var(--n-50);
            border: 1px solid var(--card-border);
            border-radius: var(--r-md);
            padding: 1rem;
            font-size: .82rem;
            color: var(--tx-secondary);
            line-height: 1.8;
            white-space: pre-wrap;
            margin-bottom: 1rem;
        }

        .ct-block-label {
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

        .pay-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .65rem 0;
            border-bottom: 1px solid var(--cn-100);
            font-size: .82rem;
        }

        .pay-row:last-child {
            border: none;
        }
    </style>
@endpush

@section('content')
    @php $ct = $solarContract; @endphp

    <div class="cu-page" style="max-width:760px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
            <a href="{{ route('customer.solar.show', $solarProject) }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                    <span class="fw-800" style="font-family:'Nunito',sans-serif;">Contract</span>
                    <span class="mono fw-700" style="color:var(--cg-700);">{{ $ct->contract_number }}</span>
                    <span class="cu-badge {{ $ct->status_badge_class }}" style="font-size:.65rem;">
                        {{ $ct->status_label }}
                    </span>
                </div>
                <div class="text-muted" style="font-size:.75rem;">
                    {{ $ct->vendor->business_name }} · ₱{{ number_format($ct->contract_amount, 2) }}
                    · {{ $ct->payment_mode_label }}
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="cu-alert cu-alert--success mb-3">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Action banner --}}
        @if ($ct->status === 'sent')
            <div
                style="background:var(--cg-50);border:2px solid var(--cg-300);
            border-radius:var(--r-xl);padding:1.25rem;margin-bottom:1.25rem;">
                <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;font-size:.95rem;">
                    <i class="bi bi-file-earmark-check text-green me-1"></i>
                    Contract Ready for Review
                </div>
                <div class="text-muted mb-3" style="font-size:.82rem;line-height:1.6;">
                    Please read through all sections carefully before approving.
                    You can also request adjustments to specific sections if needed.
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('customer.solar.contract.approve', [$solarProject, $ct]) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="cu-btn cu-btn--primary">
                            <i class="bi bi-check-circle"></i> Approve Contract
                        </button>
                    </form>
                    <button class="cu-btn cu-btn--ghost"
                        onclick="document.getElementById('adjustmentForm').scrollIntoView({behavior:'smooth'})">
                        <i class="bi bi-chat-left-dots"></i> Request Adjustment
                    </button>
                    <a href="{{ route('customer.solar.contract.invoice', [$solarProject, $ct]) }}" target="_blank"
                        class="cu-btn cu-btn--ghost">
                        <i class="bi bi-printer"></i> Print
                    </a>
                </div>
            </div>
        @elseif ($ct->status === 'approved' || $ct->status === 'signed')
            <div
                style="background:var(--cg-50);border:1.5px solid var(--cg-200);
            border-radius:var(--r-md);padding:.85rem 1.1rem;margin-bottom:1.25rem;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <i class="bi bi-patch-check-fill" style="color:var(--cg-500);font-size:1.2rem;"></i>
                    <div>
                        <div class="fw-700" style="font-size:.875rem;">
                            {{ $ct->status === 'signed' ? 'Contract Signed' : 'You approved this contract' }}
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $ct->customer_approved_at?->format('M d, Y · g:i A') }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('customer.solar.contract.invoice', [$solarProject, $ct]) }}" target="_blank"
                    class="cu-btn cu-btn--ghost cu-btn--sm">
                    <i class="bi bi-printer"></i> Print Invoice
                </a>
            </div>
        @elseif ($ct->status === 'adjustment_requested')
            <div
                style="background:#fffbeb;border:1.5px solid #fde68a;
            border-radius:var(--r-md);padding:.85rem 1.1rem;margin-bottom:1.25rem;">
                <div class="fw-700" style="font-size:.875rem;color:#92400e;">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Adjustment requested — waiting for vendor response
                </div>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-12 col-lg-8">

                {{-- Contract sections --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-file-earmark-text text-green me-1"></i> Contract Terms
                        </div>

                        <div>
                            <div class="ct-block-label"><i class="bi bi-list-check"></i> Scope of Work</div>
                            <div class="ct-block">{{ $ct->scope_of_work }}</div>
                        </div>
                        <div>
                            <div class="ct-block-label"><i class="bi bi-shield-check"></i> Warranty Terms</div>
                            <div class="ct-block">{{ $ct->warranty_terms }}</div>
                        </div>
                        <div>
                            <div class="ct-block-label"><i class="bi bi-exclamation-triangle"></i> Penalties & Cancellation
                            </div>
                            <div class="ct-block">{{ $ct->penalties_cancellation }}</div>
                        </div>
                        @if ($ct->custom_clauses)
                            <div>
                                <div class="ct-block-label"><i class="bi bi-pencil-square"></i> Additional Clauses</div>
                                <div class="ct-block">{{ $ct->custom_clauses }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Adjustment history --}}
                @if ($ct->adjustments->isNotEmpty())
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-chat-left-dots text-green me-1"></i> Your Adjustment Requests
                            </div>
                            @foreach ($ct->adjustments as $adj)
                                <div
                                    style="border:1.5px solid var(--card-border);border-radius:var(--r-md);
                                padding:.85rem;margin-bottom:.65rem;
                                {{ $adj->status === 'pending' ? 'border-color:#fde68a;background:#fffbeb;' : '' }}
                                {{ $adj->status === 'addressed' ? 'border-color:var(--cg-200);background:var(--cg-50);' : '' }}">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:.35rem;">
                                        <span class="fw-700" style="font-size:.82rem;">{{ $adj->section }}</span>
                                        <span class="cu-badge {{ $adj->status_badge_class }}" style="font-size:.65rem;">
                                            {{ ucfirst($adj->status) }}
                                        </span>
                                    </div>
                                    <div style="font-size:.8rem;color:var(--tx-secondary);">{{ $adj->notes }}</div>
                                    @if ($adj->vendor_response)
                                        <div
                                            style="margin-top:.5rem;padding:.5rem .75rem;
                                        background:var(--card-bg);border-radius:var(--r-sm);
                                        font-size:.78rem;color:var(--tx-secondary);border:1px solid var(--card-border);">
                                            <strong>Vendor response:</strong> {{ $adj->vendor_response }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Request adjustment form --}}
                @if (in_array($ct->status, ['sent', 'adjustment_requested']))
                    <div class="cu-card" id="adjustmentForm">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-chat-left-text text-green me-1"></i> Request an Adjustment
                            </div>
                            <form method="POST"
                                action="{{ route('customer.solar.contract.adjust', [$solarProject, $ct]) }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="cu-label">Which section needs adjustment? *</label>
                                    <select name="section" class="cu-input" required>
                                        <option value="">— Select section —</option>
                                        <option value="Scope of Work">Scope of Work</option>
                                        <option value="Warranty Terms">Warranty Terms</option>
                                        <option value="Penalties & Cancellation">Penalties & Cancellation</option>
                                        <option value="Payment Terms">Payment Terms</option>
                                        <option value="Additional Clauses">Additional Clauses</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="cu-label">Describe the adjustment needed *</label>
                                    <textarea name="notes" rows="4" class="cu-input" required maxlength="2000"
                                        placeholder="Explain clearly what you'd like to be changed and why…"></textarea>
                                </div>
                                <button type="submit" class="cu-btn cu-btn--primary">
                                    <i class="bi bi-send"></i> Send Adjustment Request
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right: payment schedule --}}
            <div class="col-12 col-lg-4">

                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-cash-stack text-green me-1"></i> Payment Schedule
                        </div>
                        <div class="text-muted mb-3" style="font-size:.75rem;">
                            {{ $ct->payment_mode_label }}
                        </div>

                        @foreach ($ct->paymentSchedules as $sch)
                            <div class="pay-row">
                                <div>
                                    <div class="fw-600" style="font-size:.82rem;">{{ $sch->label }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        @if ($sch->due_date)
                                            Due {{ $sch->due_date->format('M d, Y') }}
                                        @elseif ($sch->milestone_status)
                                            Upon:
                                            {{ \App\Models\SolarProject::STATUS_LABELS[$sch->milestone_status] ?? $sch->milestone_status }}
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div class="mono fw-700" style="font-size:.82rem;">
                                        ₱{{ number_format($sch->amount, 2) }}
                                    </div>
                                    <span class="cu-badge {{ $sch->status_badge_class }}" style="font-size:.6rem;">
                                        {{ ucfirst($sch->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <div
                            style="display:flex;justify-content:space-between;
                        padding:.75rem 0 0;font-weight:800;font-size:.9rem;
                        border-top:2px solid var(--card-border);margin-top:.35rem;">
                            <span>Total</span>
                            <span class="mono" style="color:var(--cg-700);">
                                ₱{{ number_format($ct->contract_amount, 2) }}
                            </span>
                        </div>

                        @if ($ct->total_paid > 0)
                            <div
                                style="margin-top:.5rem;padding:.6rem .75rem;
                            background:var(--cg-50);border-radius:var(--r-sm);
                            border:1px solid var(--cg-200);font-size:.78rem;">
                                <div style="display:flex;justify-content:space-between;">
                                    <span class="text-muted">Paid so far</span>
                                    <span class="mono fw-700 text-green">₱{{ number_format($ct->total_paid, 2) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-top:.2rem;">
                                    <span class="text-muted">Balance</span>
                                    <span class="mono fw-700"
                                        style="color:{{ $ct->balance > 0 ? '#dc2626' : 'var(--cg-600)' }};">
                                        ₱{{ number_format($ct->balance, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Contract meta --}}
                <div class="cu-card">
                    <div class="cu-card__body">
                        <div style="display:flex;flex-direction:column;gap:.65rem;font-size:.82rem;">
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Contract #</div>
                                <div class="mono fw-700" style="color:var(--cg-700);">{{ $ct->contract_number }}</div>
                            </div>
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Version</div>
                                <div class="fw-600">v{{ $ct->version }}</div>
                            </div>
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                    Issued</div>
                                <div class="fw-600">{{ $ct->created_at->format('M d, Y') }}</div>
                            </div>
                            @if ($ct->signed_at)
                                <div>
                                    <div
                                        style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                        Signed</div>
                                    <div class="fw-600 text-green">{{ $ct->signed_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
