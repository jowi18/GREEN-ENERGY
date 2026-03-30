{{-- resources/views/vendor/solar/contract/invoice.blade.php --}}
{{-- Also used at: resources/views/customer/solar/invoice.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice · {{ $solarContract->contract_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #fff;
            padding: 0;
        }

        .page {
            max-width: 794px;
            margin: 0 auto;
            padding: 40px 48px;
        }

        /* ── Header ── */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #16a34a;
        }

        .vendor-logo {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            background: #dcfce7;
            color: #15803d;
            font-size: 20px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .vendor-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vendor-name {
            font-size: 16px;
            font-weight: 800;
            color: #15803d;
        }

        .vendor-meta {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.6;
            margin-top: 2px;
        }

        .inv-title-block {
            text-align: right;
        }

        .inv-title {
            font-size: 28px;
            font-weight: 900;
            color: #15803d;
            letter-spacing: -1px;
        }

        .inv-number {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
            font-family: monospace;
        }

        .inv-dates {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 6px;
            line-height: 1.7;
        }

        /* ── Parties ── */
        .inv-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .party-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .party-box__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .party-box__name {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        .party-box__meta {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.6;
            margin-top: 2px;
        }

        /* ── Project info ── */
        .inv-project {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .proj-field__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .proj-field__value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        /* ── Items table ── */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .inv-table thead th {
            background: #15803d;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 8px 10px;
            text-align: left;
        }

        .inv-table thead th:last-child,
        .inv-table thead th:nth-last-child(2),
        .inv-table thead th:nth-last-child(3) {
            text-align: right;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .inv-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .inv-table tbody td {
            padding: 7px 10px;
            font-size: 11px;
            color: #374151;
            vertical-align: top;
        }

        .inv-table tbody td:last-child,
        .inv-table tbody td:nth-last-child(2),
        .inv-table tbody td:nth-last-child(3) {
            text-align: right;
            font-family: monospace;
        }

        .type-badge {
            display: inline-block;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 5px;
            border-radius: 3px;
            margin-bottom: 2px;
        }

        .type-badge--equipment {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .type-badge--labor {
            background: #dcfce7;
            color: #15803d;
        }

        .type-badge--permit {
            background: #fef9c3;
            color: #a16207;
        }

        .type-badge--other {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ── Totals ── */
        .inv-totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .totals-box {
            width: 260px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 14px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
        }

        .total-row:last-child {
            border: none;
        }

        .total-row.grand {
            background: #15803d;
            color: #fff;
            font-weight: 800;
            font-size: 13px;
        }

        .mono {
            font-family: monospace;
        }

        /* ── Payment schedule ── */
        .inv-payment-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #374151;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .pay-table thead th {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 7px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .pay-table thead th:last-child {
            text-align: right;
        }

        .pay-table tbody td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
        }

        .pay-table tbody td:last-child {
            text-align: right;
            font-family: monospace;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-paid {
            background: #dcfce7;
            color: #15803d;
        }

        .status-partial {
            background: #fef9c3;
            color: #a16207;
        }

        .status-pending {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-overdue {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Summary bar ── */
        .pay-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }

        .pay-sum-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
        }

        .pay-sum-box__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .pay-sum-box__value {
            font-size: 14px;
            font-weight: 800;
            font-family: monospace;
            color: #111827;
        }

        .pay-sum-box__value.green {
            color: #15803d;
        }

        .pay-sum-box__value.red {
            color: #dc2626;
        }

        /* ── Footer ── */
        .inv-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 24px;
        }

        .sig-block__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 32px;
        }

        .sig-line {
            border-top: 1px solid #374151;
            padding-top: 4px;
        }

        .sig-line__name {
            font-size: 11px;
            font-weight: 700;
        }

        .sig-line__role {
            font-size: 9px;
            color: #6b7280;
        }

        .inv-ref {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 16px;
        }

        .inv-ref strong {
            color: #374151;
            font-family: monospace;
        }

        /* ── Print ── */
        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .page {
                padding: 20px 28px;
            }
        }
    </style>
</head>

<body>

    {{-- Print button --}}
    <div class="no-print" style="text-align:center;padding:16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;">
        <button onclick="window.print()"
            style="background:#15803d;color:#fff;border:none;padding:8px 24px;border-radius:6px;
               font-weight:700;font-size:13px;cursor:pointer;margin-right:8px;">
            🖨️ Print / Save as PDF
        </button>
        <button onclick="window.close()"
            style="background:#f3f4f6;color:#374151;border:none;padding:8px 16px;border-radius:6px;
               font-size:13px;cursor:pointer;">
            Close
        </button>
    </div>

    <div class="page">

        {{-- Header --}}
        <div class="inv-header">
            <div>
                <div class="vendor-logo">
                    @if ($solarContract->vendor->shop_logo)
                        <img src="{{ Storage::url($solarContract->vendor->shop_logo) }}"
                            alt="{{ $solarContract->vendor->business_name }}">
                    @else
                        {{ strtoupper(substr($solarContract->vendor->business_name, 0, 2)) }}
                    @endif
                </div>
                <div class="vendor-name">{{ $solarContract->vendor->business_name }}</div>
                <div class="vendor-meta">
                    @if ($solarContract->vendor->business_phone)
                        {{ $solarContract->vendor->business_phone }}<br>
                    @endif
                    @if ($solarContract->vendor->business_email)
                        {{ $solarContract->vendor->business_email }}<br>
                    @endif
                    @if ($solarContract->vendor->full_address)
                        {{ $solarContract->vendor->full_address }}
                    @endif
                </div>
            </div>
            <div class="inv-title-block">
                <div class="inv-title">INVOICE</div>
                <div class="inv-number">{{ $solarContract->contract_number }}</div>
                <div class="inv-dates">
                    <strong>Issued:</strong> {{ $solarContract->created_at->format('M d, Y') }}<br>
                    @if ($solarContract->signed_at)
                        <strong>Signed:</strong> {{ $solarContract->signed_at->format('M d, Y') }}<br>
                    @endif
                    <strong>Project:</strong> {{ $solarProject->project_number }}
                </div>
            </div>
        </div>

        {{-- Parties --}}
        <div class="inv-parties">
            <div class="party-box">
                <div class="party-box__label">Billed From</div>
                <div class="party-box__name">{{ $solarContract->vendor->business_name }}</div>
                <div class="party-box__meta">
                    @if ($solarContract->vendor->business_phone)
                        Tel: {{ $solarContract->vendor->business_phone }}<br>
                    @endif
                    @if ($solarContract->vendor->business_email)
                        {{ $solarContract->vendor->business_email }}
                    @endif
                </div>
            </div>
            <div class="party-box">
                <div class="party-box__label">Billed To</div>
                <div class="party-box__name">{{ $solarContract->customer->full_name }}</div>
                <div class="party-box__meta">
                    @if ($solarContract->customer->user->email ?? null)
                        {{ $solarContract->customer->user->email }}<br>
                    @endif
                    @if ($solarContract->customer->phone)
                        Tel: {{ $solarContract->customer->phone }}<br>
                    @endif
                    @if ($solarContract->customer->address_line1)
                        {{ $solarContract->customer->address_line1 }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Project info --}}
        <div class="inv-project">
            <div class="proj-field">
                <div class="proj-field__label">Project #</div>
                <div class="proj-field__value">{{ $solarProject->project_number }}</div>
            </div>
            <div class="proj-field">
                <div class="proj-field__label">Property Type</div>
                <div class="proj-field__value">{{ ucfirst($solarProject->property_type) }}</div>
            </div>
            <div class="proj-field">
                <div class="proj-field__label">System Type</div>
                <div class="proj-field__value">
                    {{ $solarProject->system_type ? ucwords(str_replace('_', '-', $solarProject->system_type)) : '—' }}
                </div>
            </div>
            <div class="proj-field">
                <div class="proj-field__label">Payment Mode</div>
                <div class="proj-field__value">{{ $solarContract->payment_mode_label }}</div>
            </div>
        </div>

        {{-- Line items --}}
        @if ($solarContract->quotation && $solarContract->quotation->items->isNotEmpty())
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width:36%;">Description</th>
                        <th>Brand / Notes</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($solarContract->quotation->items as $item)
                        <tr>
                            <td>
                                <div class="type-badge type-badge--{{ $item->item_type }}">{{ $item->item_type }}
                                </div>
                                <div style="font-weight:600;">{{ $item->description }}</div>
                            </td>
                            <td style="color:#6b7280;">{{ $item->brand ?? ($item->notes ?? '—') }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="inv-totals">
                <div class="totals-box">
                    <div class="total-row">
                        <span style="color:#6b7280;">Subtotal</span>
                        <span class="mono">₱{{ number_format($solarContract->quotation->subtotal, 2) }}</span>
                    </div>
                    @if ($solarContract->quotation->discount_amount > 0)
                        <div class="total-row">
                            <span style="color:#dc2626;">Discount</span>
                            <span class="mono" style="color:#dc2626;">—
                                ₱{{ number_format($solarContract->quotation->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if ($solarContract->quotation->tax_amount > 0)
                        <div class="total-row">
                            <span style="color:#6b7280;">Tax / VAT</span>
                            <span class="mono">₱{{ number_format($solarContract->quotation->tax_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="total-row grand">
                        <span>Contract Total</span>
                        <span class="mono">₱{{ number_format($solarContract->contract_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        @else
            {{-- No quotation linked — show contract amount only --}}
            <div class="inv-totals">
                <div class="totals-box">
                    <div class="total-row grand">
                        <span>Contract Total</span>
                        <span class="mono">₱{{ number_format($solarContract->contract_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment summary --}}
        <div class="pay-summary">
            <div class="pay-sum-box">
                <div class="pay-sum-box__label">Contract Amount</div>
                <div class="pay-sum-box__value">₱{{ number_format($solarContract->contract_amount, 2) }}</div>
            </div>
            <div class="pay-sum-box">
                <div class="pay-sum-box__label">Total Paid</div>
                <div class="pay-sum-box__value green">₱{{ number_format($solarContract->total_paid, 2) }}</div>
            </div>
            <div class="pay-sum-box">
                <div class="pay-sum-box__label">Balance Due</div>
                <div class="pay-sum-box__value {{ $solarContract->balance > 0 ? 'red' : 'green' }}">
                    ₱{{ number_format($solarContract->balance, 2) }}
                </div>
            </div>
        </div>

        {{-- Payment schedule table --}}
        <div class="inv-payment-title">💳 Payment Schedule</div>
        <table class="pay-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th>Due / Milestone</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th style="text-align:right;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($solarContract->paymentSchedules as $sch)
                    <tr>
                        <td>{{ $sch->installment_number }}</td>
                        <td style="font-weight:600;">{{ $sch->label }}</td>
                        <td style="font-family:monospace;font-size:10px;color:#6b7280;">{{ $sch->reference_number }}
                        </td>
                        <td>
                            @if ($sch->due_date)
                                {{ $sch->due_date->format('M d, Y') }}
                            @elseif ($sch->milestone_status)
                                {{ \App\Models\SolarProject::STATUS_LABELS[$sch->milestone_status] ?? $sch->milestone_status }}
                            @else
                                —
                            @endif
                        </td>
                        <td>₱{{ number_format($sch->amount, 2) }}</td>
                        <td style="color:#15803d;">₱{{ number_format($sch->amount_paid, 2) }}</td>
                        <td>
                            <span class="status-badge status-{{ $sch->status }}">{{ ucfirst($sch->status) }}</span>
                        </td>
                        <td>₱{{ number_format($sch->balance, 2) }}</td>
                    </tr>
                    {{-- Payment records --}}
                    @foreach ($sch->records as $rec)
                        <tr style="background:#f0fdf4;">
                            <td></td>
                            <td colspan="2" style="color:#15803d;font-size:10px;padding-left:20px;">
                                ✓ {{ $rec->receipt_number }} · {{ $rec->payment_method_label }}
                                @if ($rec->reference_code)
                                    · {{ $rec->reference_code }}
                                @endif
                            </td>
                            <td style="font-size:10px;color:#6b7280;">{{ $rec->payment_date->format('M d, Y') }}</td>
                            <td colspan="4" style="font-family:monospace;color:#15803d;font-size:10px;">
                                ₱{{ number_format($rec->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- Signature lines --}}
        <div class="inv-footer">
            <div class="sig-block">
                <div class="sig-block__label">Vendor / Service Provider</div>
                <div class="sig-line">
                    <div class="sig-line__name">{{ $solarContract->vendor->business_name }}</div>
                    <div class="sig-line__role">Authorized Representative</div>
                </div>
            </div>
            <div class="sig-block">
                <div class="sig-block__label">Customer / Client</div>
                <div class="sig-line">
                    <div class="sig-line__name">{{ $solarContract->customer->full_name }}</div>
                    <div class="sig-line__role">Customer</div>
                </div>
            </div>
        </div>

        {{-- Reference footer --}}
        <div class="inv-ref">
            Contract: <strong>{{ $solarContract->contract_number }}</strong>
            &nbsp;·&nbsp; Project: <strong>{{ $solarProject->project_number }}</strong>
            &nbsp;·&nbsp; Generated: <strong>{{ now()->format('M d, Y · g:i A') }}</strong>
            &nbsp;·&nbsp; {{ $solarContract->vendor->business_name }}
        </div>

    </div>
</body>

</html>
