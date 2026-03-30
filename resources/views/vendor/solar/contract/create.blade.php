{{-- resources/views/vendor/solar/contract/create.blade.php --}}
@extends('layouts.vendor')
@section('title', 'Create Contract · ' . $solarProject->project_number)
@section('page-title', 'Create Contract')

@section('breadcrumb')
    <a href="{{ route('vendor.solar.index') }}" class="text-secondary">Solar Projects</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.solar.show', $solarProject) }}" class="text-secondary">{{ $solarProject->project_number }}</a>
    <span class="sep">›</span><span class="current">Contract</span>
@endsection

@push('styles')
    <style>
        .ct-section {
            margin-bottom: 1.25rem;
        }

        .ct-section-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tx-muted);
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: .5rem;
        }

        .payment-mode-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .65rem;
        }

        .pm-card {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: 1rem .85rem;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
            user-select: none;
        }

        .pm-card:hover {
            border-color: var(--g-300);
            background: var(--g-50);
        }

        .pm-card.selected {
            border-color: var(--g-500);
            background: var(--g-50);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, .15);
        }

        .pm-card input {
            display: none;
        }

        .pm-card__emoji {
            font-size: 1.6rem;
            margin-bottom: .4rem;
        }

        .pm-card__label {
            font-size: .82rem;
            font-weight: 700;
            color: var(--tx-primary);
        }

        .pm-card__sub {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .15rem;
        }

        .schedule-preview {
            background: var(--n-50);
            border: 1px solid var(--card-border);
            border-radius: var(--r-md);
            padding: 1rem;
            margin-top: .75rem;
        }

        .sch-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .82rem;
            padding: .35rem 0;
            border-bottom: 1px solid var(--n-100);
        }

        .sch-row:last-child {
            border: none;
        }
    </style>
@endpush

@section('content')
    <div style="max-width:100%;">

        {{-- Customer summary --}}
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:.85rem 1.25rem;">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <div class="text-muted"
                            style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Project
                        </div>
                        <div class="mono fw-700" style="color:var(--g-700);">{{ $solarProject->project_number }}</div>
                    </div>
                    <div style="width:1px;height:32px;background:var(--card-border);"></div>
                    <div>
                        <div class="text-muted"
                            style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Customer
                        </div>
                        <div class="fw-600">{{ $solarProject->customer->full_name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $solarProject->customer->user->email ?? '—' }}
                        </div>
                    </div>
                    <div style="width:1px;height:32px;background:var(--card-border);"></div>
                    <div>
                        <div class="text-muted"
                            style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Property
                        </div>
                        <div class="fw-600">{{ ucfirst($solarProject->property_type) }}
                            @if ($solarProject->system_type)
                                · {{ ucwords(str_replace('_', '-', $solarProject->system_type)) }}
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $solarProject->installation_address }}</div>
                    </div>
                    @if ($approvedQuotation)
                        <div style="width:1px;height:32px;background:var(--card-border);"></div>
                        <div>
                            <div class="text-muted"
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                Approved Quotation</div>
                            <div class="mono fw-700" style="color:var(--g-700);">
                                ₱{{ number_format($approvedQuotation->total_amount, 2) }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $approvedQuotation->quotation_number }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor.solar.contract.store', $solarProject) }}" id="contractForm">
            @csrf

            @if ($approvedQuotation)
                <input type="hidden" name="solar_quotation_id" value="{{ $approvedQuotation->id }}">
            @endif
            <input type="hidden" name="action" id="actionField">
            <div class="row g-3">
                <div class="col-12 col-lg-8">

                    {{-- Scope of Work --}}
                    <div class="vd-card ct-section">
                        <div class="vd-card__body">
                            <div class="ct-section-label"><i class="bi bi-list-check"></i> Scope of Work</div>
                            <div class="text-muted mb-2" style="font-size:.78rem;">
                                Describe exactly what will be installed and the work to be performed.
                                @if ($approvedQuotation)
                                    Pulled from approved quotation below — edit as needed.
                                @endif
                            </div>
                            <textarea name="scope_of_work" rows="7" class="vd-input @error('scope_of_work') is-invalid @enderror"
                                style="font-size:.82rem;resize:vertical;" required>{{ old(
                                    'scope_of_work',
                                    $approvedQuotation
                                        ? $approvedQuotation->items->map(
                                                fn($i) => "• [{$i->item_type}] {$i->description}" .
                                                    ($i->brand ? " ({$i->brand})" : '') .
                                                    " — {$i->quantity} {$i->unit} @ ₱" .
                                                    number_format($i->unit_price, 2),
                                            )->implode("\n")
                                        : "• Supply and install solar panels\n• Supply and install solar inverter\n• Supply and install mounting structure\n• Electrical wiring and conduit\n• System testing and commissioning\n• Customer orientation and handover",
                                ) }}</textarea>
                            @error('scope_of_work')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Warranty Terms --}}
                    <div class="vd-card ct-section">
                        <div class="vd-card__body">
                            <div class="ct-section-label"><i class="bi bi-shield-check"></i> Warranty Terms</div>
                            <textarea name="warranty_terms" rows="5" class="vd-input @error('warranty_terms') is-invalid @enderror"
                                style="font-size:.82rem;resize:vertical;" required>{{ old(
                                    'warranty_terms',
                                    "1. Workmanship Warranty: 1 year from system activation date.\n" .
                                        "2. Solar Panel Performance Warranty: As per manufacturer (typically 25 years, ≥80% output).\n" .
                                        "3. Inverter Warranty: As per manufacturer (typically 5 years).\n" .
                                        "4. Battery Warranty: As per manufacturer (typically 3–10 years).\n" .
                                        "5. Warranty is void if the system is modified, repaired, or tampered with by unauthorized personnel.\n" .
                                        '6. Warranty does not cover damage due to Acts of God, improper use, or negligence.',
                                ) }}</textarea>
                            @error('warranty_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Penalties & Cancellation --}}
                    <div class="vd-card ct-section">
                        <div class="vd-card__body">
                            <div class="ct-section-label"><i class="bi bi-exclamation-triangle"></i> Penalties &
                                Cancellation Policy</div>
                            <textarea name="penalties_cancellation" rows="5"
                                class="vd-input @error('penalties_cancellation') is-invalid @enderror" style="font-size:.82rem;resize:vertical;"
                                required>{{ old(
                                    'penalties_cancellation',
                                    "1. Cancellation before installation: Down payment is non-refundable.\n" .
                                        "2. Cancellation during installation: Customer is liable for materials delivered and labor rendered.\n" .
                                        "3. Delayed payments beyond 30 days from due date are subject to 2% monthly interest.\n" .
                                        "4. Vendor reserves the right to suspend installation if payments are more than 15 days overdue.\n" .
                                        '5. Force majeure events exempt either party from penalty clauses.',
                                ) }}</textarea>
                            @error('penalties_cancellation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Custom Clauses --}}
                    <div class="vd-card ct-section">
                        <div class="vd-card__body">
                            <div class="ct-section-label">
                                <i class="bi bi-pencil-square"></i> Additional / Custom Clauses
                                <span class="fw-400 text-muted">(optional)</span>
                            </div>
                            <textarea name="custom_clauses" rows="4" class="vd-input" style="font-size:.82rem;resize:vertical;"
                                placeholder="Add any project-specific terms, special conditions, or notes…">{{ old('custom_clauses') }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- Right: payment config --}}
                <div class="col-12 col-lg-4">
                    <div class="vd-card" style="position:sticky;top:calc(var(--tb-height,60px) + 1rem);">
                        <div class="vd-card__header">
                            <span class="vd-card__title"><i class="bi bi-cash-stack"></i> Payment Terms</span>
                        </div>
                        <div class="vd-card__body">

                            {{-- Contract amount --}}
                            <div class="mb-3">
                                <label class="vd-label">Contract Amount (₱) <span style="color:#ef4444;">*</span></label>
                                <input type="number" name="contract_amount" id="contractAmount"
                                    class="vd-input mono fw-700" style="font-size:1.1rem;" placeholder="0.00"
                                    min="1" step="0.01" required
                                    value="{{ old('contract_amount', $approvedQuotation?->total_amount ?? '') }}"
                                    oninput="updatePreview()">
                            </div>

                            {{-- Payment mode --}}
                            <div class="mb-3">
                                <label class="vd-label">Payment Mode <span style="color:#ef4444;">*</span></label>
                                <div class="payment-mode-grid">
                                    @foreach ([['full', '💵', 'Full', 'Single payment'], ['installment', '📅', 'Installment', 'Fixed payments'], ['progress_based', '📊', 'Progress', 'By milestone']] as [$val, $emoji, $label, $sub])
                                        <label class="pm-card {{ old('payment_mode') === $val ? 'selected' : '' }}"
                                            onclick="selectPaymentMode('{{ $val }}', this)">
                                            <input type="radio" name="payment_mode" value="{{ $val }}"
                                                {{ old('payment_mode') === $val ? 'checked' : '' }} required>
                                            <div class="pm-card__emoji">{{ $emoji }}</div>
                                            <div class="pm-card__label">{{ $label }}</div>
                                            <div class="pm-card__sub">{{ $sub }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Payment start date --}}
                            <div class="mb-3" id="startDateWrap">
                                <label class="vd-label">Payment Start Date</label>
                                <input type="date" name="payment_start_date" class="vd-input"
                                    value="{{ old('payment_start_date', today()->format('Y-m-d')) }}"
                                    oninput="updatePreview()">
                            </div>

                            {{-- Installment options --}}
                            <div id="installmentOptions" style="display:none;">
                                <div class="mb-2">
                                    <label class="vd-label" style="font-size:.72rem;">Number of payments</label>
                                    <select name="installment_count" class="vd-select" id="installmentCount"
                                        onchange="updatePreview()">
                                        @foreach ([2, 3, 4, 6, 12] as $n)
                                            <option value="{{ $n }}"
                                                {{ old('installment_count') == $n ? 'selected' : '' }}>
                                                {{ $n }} payments
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="vd-label" style="font-size:.72rem;">Frequency</label>
                                    <select name="installment_frequency" class="vd-select" id="installmentFreq"
                                        onchange="updatePreview()">
                                        <option value="monthly"
                                            {{ old('installment_frequency') === 'monthly' ? 'selected' : '' }}>Monthly
                                        </option>
                                        <option value="weekly"
                                            {{ old('installment_frequency') === 'weekly' ? 'selected' : '' }}>Weekly
                                        </option>
                                        <option value="quarterly"
                                            {{ old('installment_frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- Preview --}}
                            <div class="schedule-preview" id="schedulePreview">
                                <div
                                    style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.5rem;">
                                    Payment Preview
                                </div>
                                <div id="previewRows">
                                    <div class="text-muted" style="font-size:.78rem;">
                                        Select a payment mode to see schedule.
                                    </div>
                                </div>
                            </div>

                            <hr style="border-color:var(--card-border);margin:1rem 0;">

                            <button type="submit" id="sendBtn" class="vd-btn vd-btn--primary vd-btn--full mb-2"
                                onclick="document.getElementById('actionField').value='send'">
                                <i class="bi bi-send"></i> Send to Customer
                            </button>
                            <button type="submit" id="draftBtn" class="vd-btn vd-btn--ghost vd-btn--full"
                                onclick="document.getElementById('actionField').value='draft'">
                                <i class="bi bi-save"></i> Save as Draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let currentMode = '{{ old('payment_mode', '') }}';

        function selectPaymentMode(mode, el) {
            currentMode = mode;
            document.querySelectorAll('.pm-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
            document.getElementById('installmentOptions').style.display =
                mode === 'installment' ? 'block' : 'none';
            updatePreview();
        }

        function fmt(n) {
            return '₱' + parseFloat(n).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
        }

        function addDays(date, days) {
            const d = new Date(date);
            d.setDate(d.getDate() + days);
            return d;
        }

        function addMonths(date, months) {
            const d = new Date(date);
            d.setMonth(d.getMonth() + months);
            return d;
        }

        function fmtDate(d) {
            return d.toLocaleDateString('en-PH', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function updatePreview() {
            const amount = parseFloat(document.getElementById('contractAmount').value) || 0;
            const startStr = document.querySelector('[name="payment_start_date"]').value;
            const startDate = startStr ? new Date(startStr) : new Date();
            const container = document.getElementById('previewRows');

            if (!currentMode || amount <= 0) {
                container.innerHTML =
                    '<div class="text-muted" style="font-size:.78rem;">Enter amount and select mode.</div>';
                return;
            }

            let rows = [];

            if (currentMode === 'full') {
                rows = [{
                    label: 'Full Payment',
                    amount,
                    date: fmtDate(startDate)
                }];

            } else if (currentMode === 'installment') {
                const count = parseInt(document.getElementById('installmentCount').value) || 3;
                const freq = document.getElementById('installmentFreq').value;
                const per = Math.floor((amount / count) * 100) / 100;

                for (let i = 0; i < count; i++) {
                    let date;
                    if (freq === 'weekly') date = addDays(startDate, i * 7);
                    else if (freq === 'quarterly') date = addMonths(startDate, i * 3);
                    else date = addMonths(startDate, i);

                    const amt = i === count - 1 ? amount - (per * (count - 1)) : per;
                    rows.push({
                        label: i === 0 ? 'Down Payment' : `Payment #${i + 1}`,
                        amount: amt,
                        date: fmtDate(date)
                    });
                }

            } else if (currentMode === 'progress_based') {
                const milestones = [{
                        label: 'Down Payment (Contract Signing)',
                        pct: 50
                    },
                    {
                        label: 'Progress Payment (Installation)',
                        pct: 30
                    },
                    {
                        label: 'Final Payment (System Activation)',
                        pct: 20
                    },
                ];
                rows = milestones.map(m => ({
                    label: m.label,
                    amount: Math.round(amount * (m.pct / 100) * 100) / 100,
                    date: 'Upon milestone',
                }));
            }

            container.innerHTML = rows.map(r => `
            <div class="sch-row">
                <div>
                    <div style="font-size:.78rem;font-weight:600;">${r.label}</div>
                    <div style="font-size:.68rem;color:var(--tx-muted);">${r.date}</div>
                </div>
                <div class="mono fw-700" style="font-size:.82rem;color:var(--g-700);">${fmt(r.amount)}</div>
            </div>
        `).join('');
        }

        document.getElementById('contractForm').addEventListener('submit', function(e) {

            const clickedBtn = e.submitter;

            if (!clickedBtn) return;

            clickedBtn.disabled = true;

            clickedBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                (clickedBtn.id === 'sendBtn'
                    ? 'Sending…'
                    : 'Saving draft…');
        });

        // Init if old value
        if (currentMode) {
            document.getElementById('installmentOptions').style.display =
                currentMode === 'installment' ? 'block' : 'none';
            updatePreview();
        }
    </script>
@endpush
