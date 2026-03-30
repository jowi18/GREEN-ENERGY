{{-- resources/views/customer/solar/payment.blade.php --}}
@extends('layouts.customer')
@section('title', 'Pay Balance · ' . $contract->contract_number)

@push('styles')
    <style>
        .milestone-card {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            margin-bottom: .75rem;
            transition: border-color .15s, box-shadow .15s;
        }

        .milestone-card.selectable {
            cursor: pointer;
        }

        .milestone-card.selectable:hover {
            border-color: var(--cg-300);
        }

        .milestone-card.selected {
            border-color: var(--cg-500);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, .12);
        }

        .milestone-card.paid {
            opacity: .6;
            pointer-events: none;
            background: var(--n-50);
        }

        .milestone-card.overdue {
            border-color: #fca5a5;
        }

        .milestone-card__head {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .85rem 1.1rem;
        }

        .milestone-check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .15s;
        }

        .milestone-card.selected .milestone-check {
            background: var(--cg-600);
            border-color: var(--cg-600);
            color: #fff;
        }

        .milestone-card.paid .milestone-check {
            background: var(--cg-500);
            border-color: var(--cg-500);
            color: #fff;
        }

        .pay-summary {
            background: var(--cg-50);
            border: 1.5px solid var(--cg-200);
            border-radius: var(--r-xl);
            padding: 1.25rem;
            position: sticky;
            top: calc(var(--navbar-h, 60px) + 1rem);
        }

        .conversion-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: var(--r-md);
            padding: .65rem .85rem;
            font-size: .75rem;
            color: #1e40af;
            line-height: 1.6;
            margin-top: .75rem;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:780px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
            <a href="{{ route('customer.solar.contract.show', [$solarProject, $contract]) }}"
                class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">Pay Balance</h5>
                <div class="text-muted" style="font-size:.75rem;">
                    {{ $contract->contract_number }} · {{ $contract->project_title }}
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="cu-alert cu-alert--success mb-3">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="cu-alert cu-alert--danger mb-3">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="cu-alert cu-alert--info mb-3">
                <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
            </div>
        @endif

        <div class="row g-3">

            {{-- Left: milestones --}}
            <div class="col-12 col-md-7">

                {{-- Overall progress --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div
                            style="display:flex;justify-content:space-between;
                        font-size:.82rem;margin-bottom:.5rem;">
                            <span class="text-muted">Total paid</span>
                            <span class="mono fw-700" style="color:var(--cg-700);">
                                ₱{{ number_format($contract->total_paid, 2) }}
                                <span class="text-muted fw-400">/ ₱{{ number_format($contract->contract_amount, 2) }}</span>
                            </span>
                        </div>
                        <div style="height:8px;background:var(--cn-100);border-radius:4px;overflow:hidden;">
                            <div
                                style="height:100%;border-radius:4px;background:var(--cg-500);
                            width:{{ $contract->payment_progress_percent }}%;transition:width .3s;">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;
                        font-size:.72rem;color:var(--tx-muted);margin-top:.35rem;">
                            <span>{{ $contract->payment_progress_percent }}% complete</span>
                            <span>Balance: ₱{{ number_format($contract->balance, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Vendor info --}}
                <div
                    style="display:flex;align-items:center;gap:.75rem;background:var(--cg-50);
                border:1.5px solid var(--cg-200);border-radius:var(--r-xl);padding:.75rem 1rem;
                margin-bottom:1rem;">
                    <div
                        style="width:38px;height:38px;border-radius:var(--r-sm);background:var(--cg-200);
                    color:var(--cg-700);font-weight:800;font-size:.85rem;display:flex;
                    align-items:center;justify-content:center;flex-shrink:0;">
                        {{ strtoupper(substr($contract->vendor->business_name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="fw-700" style="font-size:.875rem;">{{ $contract->vendor->business_name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $contract->payment_mode_label }}
                        </div>
                    </div>
                </div>

                {{-- Milestone selection --}}
                <div class="fw-700 mb-2"
                    style="font-size:.82rem;color:var(--tx-muted);
                text-transform:uppercase;letter-spacing:.06em;">
                    Select Milestone(s) to Pay
                </div>

                <form method="POST"
                    action="{{ route('customer.solar.payment.initiate.multiple', [$solarProject, $contract]) }}"
                    id="multiPayForm">
                    @csrf

                    @forelse ($pendingSchedules as $sched)
                        @php
                            $isPaid = in_array($sched->status, ['paid', 'waived']);
                            $isOverdue = $sched->isOverdue();
                        @endphp
                        <div class="milestone-card {{ $isPaid ? 'paid' : 'selectable' }} {{ $isOverdue ? 'overdue' : '' }}"
                            id="ms-{{ $sched->id }}"
                            onclick="{{ $isPaid ? '' : "toggleMilestone({$sched->id}, {$sched->balance})" }}">

                            <div class="milestone-card__head">
                                {{-- Checkbox visual --}}
                                <div class="milestone-check" id="check-{{ $sched->id }}">
                                    @if ($isPaid)
                                        <i class="bi bi-check2" style="font-size:.7rem;"></i>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-700" style="font-size:.875rem;">
                                        {{ $sched->milestone_name }}
                                        @if ($isOverdue)
                                            <span
                                                style="font-size:.65rem;background:#fef2f2;
                                            color:#dc2626;padding:.1rem .35rem;border-radius:var(--r-full);
                                            font-weight:700;margin-left:.35rem;">OVERDUE</span>
                                        @endif
                                    </div>
                                    @if ($sched->description)
                                        <div class="text-muted" style="font-size:.75rem;">{{ $sched->description }}</div>
                                    @endif
                                    <div class="text-muted" style="font-size:.72rem;">
                                        @if ($sched->trigger_status)
                                            Triggered at:
                                            {{ \App\Models\SolarProject::STATUS_LABELS[$sched->trigger_status] ?? $sched->trigger_status }}
                                            @if ($sched->due_date)
                                                ·
                                            @endif
                                        @endif
                                        @if ($sched->due_date)
                                            Due: {{ $sched->due_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Amount --}}
                                <div style="text-align:right;flex-shrink:0;">
                                    <div class="mono fw-700"
                                        style="font-size:1rem;
                                    color:{{ $isPaid ? 'var(--cg-600)' : 'var(--tx-primary)' }};">
                                        ₱{{ number_format($sched->balance, 2) }}
                                    </div>
                                    @if ($sched->status === 'partial')
                                        <div class="text-muted" style="font-size:.68rem;">
                                            of ₱{{ number_format($sched->amount, 2) }}
                                        </div>
                                    @endif
                                    <span class="cu-badge {{ $sched->status_badge_class }}" style="font-size:.62rem;">
                                        {{ ucfirst($sched->status) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Pay single button (shown when selected) --}}
                            @if (!$isPaid)
                                <div id="single-btn-{{ $sched->id }}"
                                    style="display:none;padding:.65rem 1.1rem;border-top:1px solid var(--cg-100);">
                                    <form method="POST"
                                        action="{{ route('customer.solar.payment.initiate', [$solarProject, $contract]) }}">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $sched->id }}">
                                        <button type="submit" onclick="event.stopPropagation()"
                                            class="cu-btn cu-btn--primary cu-btn--sm">
                                            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png"
                                                alt="PayPal" style="height:14px;filter:brightness(10);">
                                            Pay ₱{{ number_format($sched->balance, 2) }} with PayPal
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>

                        {{-- Hidden checkbox for multi-pay form --}}
                        @if (!$isPaid)
                            <input type="checkbox" name="schedule_ids[]" value="{{ $sched->id }}"
                                id="cb-{{ $sched->id }}" style="display:none;">
                        @endif

                    @empty
                        <div style="text-align:center;padding:2rem;color:var(--tx-muted);">
                            <i class="bi bi-check-circle-fill" style="font-size:2rem;color:var(--cg-500);"></i>
                            <div class="fw-700 mt-2">All milestones are paid!</div>
                            <div style="font-size:.82rem;margin-top:.35rem;">
                                No outstanding balance remaining.
                            </div>
                        </div>
                    @endforelse

                </form>

            </div>

            {{-- Right: pay summary --}}
            <div class="col-12 col-md-5">
                <div class="pay-summary">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;font-size:.95rem;">
                        <i class="bi bi-receipt text-green me-1"></i> Payment Summary
                    </div>

                    {{-- Selected milestones --}}
                    <div id="selectedList" style="margin-bottom:.85rem;min-height:40px;">
                        <div class="text-muted" id="noSelectionMsg" style="font-size:.82rem;">
                            Select milestone(s) above to pay.
                        </div>
                    </div>

                    <hr style="border-color:var(--cg-200);margin:.75rem 0;">

                    <div
                        style="display:flex;justify-content:space-between;
                    font-size:1.05rem;font-weight:800;margin-bottom:.5rem;">
                        <span>Total Due</span>
                        <span class="mono" style="color:var(--cg-700);" id="totalDue">₱0.00</span>
                    </div>

                    {{-- Conversion note --}}
                    <div class="conversion-note">
                        <i class="bi bi-info-circle me-1"></i>
                        Payment is processed in <strong>USD</strong> via PayPal.
                        Your bank may apply a currency conversion rate.
                        Approximate USD amount: <strong id="usdEstimate">$0.00</strong>
                    </div>

                    {{-- Pay all selected button --}}
                    <button type="button" id="payAllBtn" onclick="submitMultiPay()"
                        class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg mt-3" style="display:none;">
                        <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png"
                            alt="PayPal" style="height:16px;filter:brightness(10);">
                        Pay Selected with PayPal
                    </button>

                    <p class="text-muted text-center mt-2" style="font-size:.72rem;">
                        <i class="bi bi-lock-fill me-1"></i>
                        Secured by PayPal. You will be redirected to complete payment.
                    </p>

                    {{-- Full balance CTA --}}
                    @if ($contract->balance > 0 && $pendingSchedules->count() > 1)
                        <hr style="border-color:var(--cg-200);margin:.75rem 0;">
                        <button type="button" class="cu-btn cu-btn--ghost cu-btn--full cu-btn--sm"
                            onclick="selectAll()">
                            <i class="bi bi-check2-all me-1"></i>
                            Pay Full Balance ₱{{ number_format($contract->balance, 2) }}
                        </button>
                    @endif

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const PHP_TO_USD = {{ config('paypal.php_to_usd_rate', env('PHP_TO_USD_RATE', 0.0175)) }};
        const selected = {}; // { scheduleId: balance }

        function toggleMilestone(id, balance) {
            if (selected[id]) {
                deselect(id);
            } else {
                selectMs(id, balance);
            }
            updateSummary();
        }

        function selectMs(id, balance) {
            selected[id] = balance;
            document.getElementById(`ms-${id}`).classList.add('selected');
            document.getElementById(`check-${id}`).innerHTML = '<i class="bi bi-check2" style="font-size:.7rem;"></i>';
            document.getElementById(`cb-${id}`).checked = true;
            const singleBtn = document.getElementById(`single-btn-${id}`);
            if (singleBtn) singleBtn.style.display = 'block';
        }

        function deselect(id) {
            delete selected[id];
            document.getElementById(`ms-${id}`).classList.remove('selected');
            document.getElementById(`check-${id}`).innerHTML = '';
            document.getElementById(`cb-${id}`).checked = false;
            const singleBtn = document.getElementById(`single-btn-${id}`);
            if (singleBtn) singleBtn.style.display = 'none';
        }

        function selectAll() {
            @foreach ($pendingSchedules as $sched)
                @if (!in_array($sched->status, ['paid', 'waived']))
                    selectMs({{ $sched->id }}, {{ $sched->balance }});
                @endif
            @endforeach
            updateSummary();
        }

        function updateSummary() {
            const ids = Object.keys(selected);
            const total = Object.values(selected).reduce((a, b) => a + b, 0);
            const usd = (total * PHP_TO_USD).toFixed(2);
            const noMsg = document.getElementById('noSelectionMsg');
            const list = document.getElementById('selectedList');
            const payBtn = document.getElementById('payAllBtn');

            document.getElementById('totalDue').textContent = '₱' + fmt(total);
            document.getElementById('usdEstimate').textContent = '$' + usd;

            if (ids.length === 0) {
                noMsg.style.display = 'block';
                payBtn.style.display = 'none';
                list.innerHTML =
                    '<div class="text-muted" id="noSelectionMsg" style="font-size:.82rem;">Select milestone(s) above to pay.</div>';
            } else {
                payBtn.style.display = ids.length > 1 ? 'block' : 'none';
                list.innerHTML = ids.map(id =>
                    `<div style="display:flex;justify-content:space-between;font-size:.82rem;padding:.25rem 0;border-bottom:1px solid var(--cg-100);">
                    <span style="color:var(--tx-secondary);">Milestone #${id}</span>
                    <span class="mono fw-600">₱${fmt(selected[id])}</span>
                </div>`
                ).join('') + (ids.length > 1 ? `
                <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:.35rem 0;font-weight:700;">
                    <span>Subtotal</span>
                    <span class="mono">₱${fmt(total)}</span>
                </div>` : '');
            }
        }

        function submitMultiPay() {
            const ids = Object.keys(selected);
            if (ids.length < 2) return;

            // Ensure all checkboxes are checked
            ids.forEach(id => {
                const cb = document.getElementById(`cb-${id}`);
                if (cb) cb.checked = true;
            });

            document.getElementById('multiPayForm').submit();
        }

        function fmt(n) {
            return parseFloat(n).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }
    </script>
@endpush
