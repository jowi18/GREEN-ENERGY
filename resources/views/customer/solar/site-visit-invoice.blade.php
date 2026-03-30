{{-- resources/views/customer/solar/site-visit-invoice.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Visit Invoice · {{ $project->project_number }}</title>
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
        }

        /* ── Print toolbar ── */
        .toolbar {
            background: #f0fdf4;
            border-bottom: 2px solid #16a34a;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .toolbar__title {
            font-size: 13px;
            font-weight: 700;
            color: #15803d;
        }

        .toolbar__actions {
            display: flex;
            gap: 8px;
        }

        .btn-print {
            background: #15803d;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Page ── */
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
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 3px solid #16a34a;
        }

        .vendor-logo {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            background: #dcfce7;
            color: #15803d;
            font-size: 18px;
            font-weight: 900;
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
            font-size: 15px;
            font-weight: 800;
            color: #15803d;
        }

        .vendor-meta {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.65;
            margin-top: 3px;
        }

        .inv-title-block {
            text-align: right;
        }

        .inv-title {
            font-size: 30px;
            font-weight: 900;
            color: #15803d;
            letter-spacing: -1px;
            line-height: 1;
        }

        .inv-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .inv-number {
            font-size: 12px;
            font-family: monospace;
            font-weight: 700;
            color: #374151;
            margin-top: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
        }

        .inv-dates {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 6px;
            line-height: 1.75;
        }

        /* ── Paid stamp ── */
        .paid-stamp {
            position: absolute;
            right: 48px;
            top: 140px;
            border: 3px solid #16a34a;
            color: #16a34a;
            font-size: 22px;
            font-weight: 900;
            padding: 6px 16px;
            border-radius: 4px;
            opacity: .18;
            transform: rotate(-15deg);
            letter-spacing: 4px;
            pointer-events: none;
            text-transform: uppercase;
        }

        /* ── Status banner ── */
        .status-banner {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-banner__label {
            font-weight: 800;
            color: #15803d;
            font-size: 12px;
        }

        .status-banner__sub {
            color: #6b7280;
            margin-top: 1px;
        }

        /* ── Parties ── */
        .inv-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
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
            line-height: 1.65;
            margin-top: 3px;
        }

        /* ── Service details ── */
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
            padding: 9px 12px;
            text-align: left;
        }

        .inv-table thead th:last-child {
            text-align: right;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .inv-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .inv-table tbody td {
            padding: 10px 12px;
            font-size: 11px;
            color: #374151;
            vertical-align: top;
        }

        .inv-table tbody td:last-child {
            text-align: right;
            font-family: monospace;
            font-weight: 700;
        }

        .service-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 3px;
            margin-bottom: 3px;
        }

        /* ── Totals ── */
        .inv-totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .totals-box {
            width: 240px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 14px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-row.grand {
            background: #15803d;
            color: #fff;
            font-weight: 800;
            font-size: 13px;
        }

        .total-row.paid-row {
            background: #f0fdf4;
            color: #15803d;
            font-weight: 700;
        }

        /* ── What's included section ── */
        .includes-box {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .includes-box__title {
            font-size: 11px;
            font-weight: 800;
            color: #15803d;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .includes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
        }

        .include-item {
            font-size: 10px;
            color: #374151;
            display: flex;
            align-items: flex-start;
            gap: 6px;
            line-height: 1.5;
        }

        .include-item i {
            color: #15803d;
            font-size: 9px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ── Project info ── */
        .inv-project {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .proj-field__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        .proj-field__value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        /* ── Notice ── */
        .notice-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 10px;
            color: #92400e;
            line-height: 1.65;
        }

        .notice-box strong {
            color: #78350f;
        }

        /* ── Footer ── */
        .inv-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 20px;
        }

        .sig-block__label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 28px;
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
            padding-top: 14px;
        }

        .inv-ref strong {
            color: #374151;
            font-family: monospace;
        }

        /* ── Print ── */
        @media print {
            .toolbar {
                display: none !important;
            }

            body {
                padding: 0;
            }

            .page {
                padding: 20px 28px;
                max-width: 100%;
            }

            .paid-stamp {
                opacity: .12;
            }
        }
    </style>
</head>

<body>

    {{-- Print Toolbar --}}
    <div class="toolbar no-print">
        <div class="toolbar__title">
            🧾 Site Visit Invoice — {{ $project->project_number }}
        </div>
        <div class="toolbar__actions">
            <a href="{{ route('customer.solar.show', $project) }}" class="btn-back">
                ← Back to Project
            </a>
            <button onclick="window.print()" class="btn-print">
                🖨️ Print / Save PDF
            </button>
        </div>
    </div>

    <div class="page" style="position:relative;">

        {{-- Paid stamp watermark --}}
        <div class="paid-stamp">PAID</div>

        {{-- ── Header ────────────────────────────────────────────────── --}}
        <div class="inv-header">
            <div>
                <div class="vendor-logo">
                    @if ($project->vendor->shop_logo)
                        <img src="{{ Storage::url($project->vendor->shop_logo) }}"
                            alt="{{ $project->vendor->business_name }}">
                    @else
                        {{ strtoupper(substr($project->vendor->business_name, 0, 2)) }}
                    @endif
                </div>
                <div class="vendor-name">{{ $project->vendor->business_name }}</div>
                <div class="vendor-meta">
                    @if ($project->vendor->business_phone)
                        {{ $project->vendor->business_phone }}<br>
                    @endif
                    @if ($project->vendor->business_email)
                        {{ $project->vendor->business_email }}<br>
                    @endif
                    {{ $project->vendor->city }}, {{ $project->vendor->province_state }}
                </div>
            </div>

            <div class="inv-title-block">
                <div class="inv-title">INVOICE</div>
                <div class="inv-subtitle">Site Visit & Assessment Fee</div>
                <div class="inv-number">{{ $project->project_number }}-SV</div>
                <div class="inv-dates">
                    <strong>Date Issued:</strong>
                    {{ $project->site_visit_paid_at?->format('F d, Y') ?? now()->format('F d, Y') }}<br>
                    <strong>Project Ref:</strong> {{ $project->project_number }}<br>
                    @if ($project->site_visit_txn_id)
                        <strong>Transaction ID:</strong>
                        <span style="font-family:monospace;">{{ $project->site_visit_txn_id }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Payment status banner ──────────────────────────────────── --}}
        <div class="status-banner">
            <div class="status-icon">✅</div>
            <div>
                <div class="status-banner__label">Payment Confirmed</div>
                <div class="status-banner__sub">
                    Site visit fee of <strong>₱{{ number_format(1500, 2) }}</strong>
                    was successfully paid via PayPal on
                    {{ $project->site_visit_paid_at?->format('F d, Y \a\t g:i A') ?? now()->format('F d, Y') }}.
                </div>
            </div>
        </div>

        {{-- ── Parties ─────────────────────────────────────────────────── --}}
        <div class="inv-parties">
            <div class="party-box">
                <div class="party-box__label">Service Provider</div>
                <div class="party-box__name">{{ $project->vendor->business_name }}</div>
                <div class="party-box__meta">
                    @if ($project->vendor->business_phone)
                        Tel: {{ $project->vendor->business_phone }}<br>
                    @endif
                    @if ($project->vendor->business_email)
                        {{ $project->vendor->business_email }}<br>
                    @endif
                    {{ $project->vendor->city }}, Cavite
                </div>
            </div>
            <div class="party-box">
                <div class="party-box__label">Client</div>
                <div class="party-box__name">
                    {{ $project->customer->first_name }} {{ $project->customer->last_name }}
                </div>
                <div class="party-box__meta">
                    @if ($project->customer->user->email ?? null)
                        {{ $project->customer->user->email }}<br>
                    @endif
                    @if ($project->customer->phone)
                        Tel: {{ $project->customer->phone }}<br>
                    @endif
                    {{ $project->installation_address }},
                    {{ $project->installation_city }},
                    {{ $project->installation_province }}
                </div>
            </div>
        </div>

        {{-- ── Project details ─────────────────────────────────────────── --}}
        <div class="inv-project">
            <div class="proj-field">
                <div class="proj-field__label">Project #</div>
                <div class="proj-field__value">{{ $project->project_number }}</div>
            </div>
            <div class="proj-field">
                <div class="proj-field__label">Property Type</div>
                <div class="proj-field__value">{{ ucfirst($project->property_type) }}</div>
            </div>
            <div class="proj-field">
                <div class="proj-field__label">System Type</div>
                <div class="proj-field__value">
                    {{ $project->system_type ? ucwords(str_replace('_', '-', $project->system_type)) : 'To be determined' }}
                </div>
            </div>
        </div>

        {{-- ── Line items ──────────────────────────────────────────────── --}}
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:50%">Service Description</th>
                    <th>Details</th>
                    <th>Qty</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="service-badge">Site Assessment</div>
                        <div style="font-weight:700;">Solar Site Visit & Assessment Fee</div>
                        <div style="font-size:10px;color:#6b7280;margin-top:2px;line-height:1.5;">
                            On-site evaluation of property for solar panel installation feasibility,
                            roof inspection, shading analysis, and load assessment.
                        </div>
                    </td>
                    <td style="color:#6b7280;font-size:10px;">
                        {{ $project->installation_address }},<br>
                        {{ $project->installation_city }}
                    </td>
                    <td>1</td>
                    <td>₱1,500.00</td>
                </tr>
            </tbody>
        </table>

        {{-- ── Totals ──────────────────────────────────────────────────── --}}
        <div class="inv-totals">
            <div class="totals-box">
                <div class="total-row">
                    <span style="color:#6b7280;">Subtotal</span>
                    <span style="font-family:monospace;">₱1,500.00</span>
                </div>
                <div class="total-row">
                    <span style="color:#6b7280;">VAT (0%)</span>
                    <span style="font-family:monospace;">₱0.00</span>
                </div>
                <div class="total-row grand">
                    <span>Total</span>
                    <span style="font-family:monospace;">₱1,500.00</span>
                </div>
                <div class="total-row paid-row">
                    <span>✓ Paid via PayPal</span>
                    <span style="font-family:monospace;">₱1,500.00</span>
                </div>
            </div>
        </div>

        {{-- ── What's included ─────────────────────────────────────────── --}}
        <div class="includes-box">
            <div class="includes-box__title">
                ☀️ What's Included in the Site Visit
            </div>
            <div class="includes-grid">
                <div class="include-item">
                    <i>✓</i>
                    <span>Roof inspection & structural assessment</span>
                </div>
                <div class="include-item">
                    <i>✓</i>
                    <span>Shading & solar irradiance analysis</span>
                </div>
                <div class="include-item">
                    <i>✓</i>
                    <span>Electrical panel & load evaluation</span>
                </div>
                <div class="include-item">
                    <i>✓</i>
                    <span>System size & energy yield estimation</span>
                </div>
                <div class="include-item">
                    <i>✓</i>
                    <span>Installation feasibility report</span>
                </div>
                <div class="include-item">
                    <i>✓</i>
                    <span>Formal quotation preparation</span>
                </div>
            </div>
        </div>

        {{-- ── Notice ──────────────────────────────────────────────────── --}}
        <div class="notice-box">
            <strong>📌 Next Steps:</strong> A licensed solar engineer will contact you within
            <strong>1–2 business days</strong> to schedule the actual site visit.
            The ₱1,500 site visit fee is <strong>non-refundable</strong> once the engineer
            has been dispatched. If the project proceeds to full installation,
            this fee will be <strong>credited</strong> toward your total project cost.
        </div>

        {{-- ── Signature lines ─────────────────────────────────────────── --}}
        <div class="inv-footer">
            <div class="sig-block">
                <div class="sig-block__label">Service Provider</div>
                <div class="sig-line">
                    <div class="sig-line__name">{{ $project->vendor->business_name }}</div>
                    <div class="sig-line__role">Authorized Representative</div>
                </div>
            </div>
            <div class="sig-block">
                <div class="sig-block__label">Client Acknowledgement</div>
                <div class="sig-line">
                    <div class="sig-line__name">
                        {{ $project->customer->first_name }} {{ $project->customer->last_name }}
                    </div>
                    <div class="sig-line__role">Customer</div>
                </div>
            </div>
        </div>

        {{-- ── Reference footer ────────────────────────────────────────── --}}
        <div class="inv-ref">
            Invoice Ref: <strong>{{ $project->project_number }}-SV</strong>
            &nbsp;·&nbsp; Project: <strong>{{ $project->project_number }}</strong>
            @if ($project->site_visit_txn_id)
                &nbsp;·&nbsp; PayPal TXN: <strong>{{ $project->site_visit_txn_id }}</strong>
            @endif
            &nbsp;·&nbsp; Generated: <strong>{{ now()->format('M d, Y · g:i A') }}</strong>
        </div>

    </div>

</body>

</html>
