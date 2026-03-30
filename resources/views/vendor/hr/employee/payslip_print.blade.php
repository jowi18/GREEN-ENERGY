<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Payslip — {{ $payrollItem->profile->full_name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
            background: #f5f5f5;
            padding: 2rem 1rem;
        }

        .payslip {
            max-width: 680px;
            margin: 0 auto;
            background: #fff;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .ps-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #157a30;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .ps-company-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: #157a30;
        }

        .ps-label {
            font-size: 1.4rem;
            font-weight: 800;
            color: #111;
            text-align: right;
        }

        .ps-period {
            font-size: .78rem;
            color: #555;
            text-align: right;
            margin-top: .2rem;
        }

        .ps-employee {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem 1.5rem;
            margin-bottom: 1.25rem;
            padding: .85rem;
            background: #f0f5f0;
            border-radius: 6px;
        }

        .ps-field label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #888;
        }

        .ps-field span {
            font-size: .82rem;
            font-weight: 600;
            color: #111;
            display: block;
        }

        .ps-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .ps-section {
            border: 1px solid #dde5de;
            border-radius: 6px;
            overflow: hidden;
        }

        .ps-section-title {
            background: #157a30;
            color: #fff;
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .07em;
            padding: .4rem .75rem;
        }

        .ps-section-title.deductions {
            background: #ef4444;
        }

        .ps-item {
            display: flex;
            justify-content: space-between;
            padding: .4rem .75rem;
            font-size: .82rem;
            border-bottom: 1px solid #eef2ee;
        }

        .ps-item:last-child {
            border-bottom: none;
        }

        .ps-item.total {
            font-weight: 800;
            background: #f9fafb;
        }

        .ps-item .amount {
            font-family: 'Courier New', monospace;
        }

        .ps-net {
            background: #157a30;
            color: #fff;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .ps-net-label {
            font-size: .85rem;
            font-weight: 700;
        }

        .ps-net-amount {
            font-size: 1.6rem;
            font-weight: 800;
            font-family: 'Courier New', monospace;
        }

        .ps-footer {
            text-align: center;
            font-size: .72rem;
            color: #888;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px dashed #ccc;
        }

        .print-btn {
            display: flex;
            justify-content: center;
            gap: .75rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: .6rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: .875rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-print {
            background: #157a30;
            color: #fff;
        }

        .btn-back {
            background: transparent;
            color: #555;
            border: 1px solid #ccc;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .payslip {
                box-shadow: none;
            }

            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="payslip">
        {{-- Header --}}
        <div class="ps-header">
            <div>
                <div class="ps-company-name">{{ $payrollItem->profile->vendor->business_name }}</div>
                <div style="font-size:.78rem;color:#555;margin-top:.2rem;">
                    {{ $payrollItem->profile->vendor->full_address ?? '' }}
                </div>
            </div>
            <div>
                <div class="ps-label">PAYSLIP</div>
                <div class="ps-period">{{ $payrollItem->period->period_name }}</div>
                <div class="ps-period">{{ $payrollItem->period->date_from->format('M d') }} –
                    {{ $payrollItem->period->date_to->format('M d, Y') }}</div>
            </div>
        </div>

        {{-- Employee info --}}
        <div class="ps-employee">
            <div class="ps-field"><label>Employee Name</label><span>{{ $payrollItem->profile->full_name }}</span></div>
            <div class="ps-field"><label>Employee
                    Number</label><span>{{ $payrollItem->profile->employee_number ?? '—' }}</span></div>
            <div class="ps-field">
                <label>Department</label><span>{{ $payrollItem->profile->department?->name ?? '—' }}</span></div>
            <div class="ps-field">
                <label>Position</label><span>{{ $payrollItem->profile->position?->title ?? '—' }}</span></div>
            <div class="ps-field"><label>Employment
                    Status</label><span>{{ ucfirst($payrollItem->profile->employment_status) }}</span></div>
            <div class="ps-field"><label>Pay
                    Frequency</label><span>{{ ucfirst(str_replace('_', ' ', $payrollItem->profile->pay_frequency)) }}</span>
            </div>
            <div class="ps-field"><label>SSS No.</label><span>{{ $payrollItem->profile->sss_number ?? '—' }}</span>
            </div>
            <div class="ps-field"><label>PhilHealth
                    No.</label><span>{{ $payrollItem->profile->philhealth_number ?? '—' }}</span></div>
            <div class="ps-field"><label>Pag-IBIG
                    No.</label><span>{{ $payrollItem->profile->pagibig_number ?? '—' }}</span></div>
            <div class="ps-field"><label>TIN</label><span>{{ $payrollItem->profile->tin_number ?? '—' }}</span></div>
        </div>

        {{-- Attendance summary --}}
        <div class="ps-employee" style="margin-bottom:1rem;">
            <div class="ps-field"><label>Days Worked</label><span>{{ $payrollItem->days_worked }}</span></div>
            <div class="ps-field"><label>Days Absent</label><span>{{ $payrollItem->days_absent }}</span></div>
            <div class="ps-field"><label>Days Leave</label><span>{{ $payrollItem->days_leave }}</span></div>
            <div class="ps-field"><label>OT
                    Hours</label><span>{{ number_format($payrollItem->hours_overtime, 2) }}h</span></div>
            <div class="ps-field"><label>Minutes Late</label><span>{{ $payrollItem->minutes_late }}min</span></div>
            <div class="ps-field"><label>Minutes Undertime</label><span>{{ $payrollItem->minutes_undertime }}min</span>
            </div>
        </div>

        <div class="ps-row">
            {{-- Earnings --}}
            <div class="ps-section">
                <div class="ps-section-title">Earnings</div>
                <div class="ps-item"><span>Basic Pay</span><span
                        class="amount">₱{{ number_format($payrollItem->basic_pay, 2) }}</span></div>
                @if ($payrollItem->overtime_pay > 0)
                    <div class="ps-item"><span>Overtime Pay</span><span
                            class="amount">₱{{ number_format($payrollItem->overtime_pay, 2) }}</span></div>
                @endif
                @if ($payrollItem->holiday_pay > 0)
                    <div class="ps-item"><span>Holiday Pay</span><span
                            class="amount">₱{{ number_format($payrollItem->holiday_pay, 2) }}</span></div>
                @endif
                @if ($payrollItem->allowances > 0)
                    <div class="ps-item"><span>Allowances</span><span
                            class="amount">₱{{ number_format($payrollItem->allowances, 2) }}</span></div>
                @endif
                @if ($payrollItem->other_earnings > 0)
                    <div class="ps-item"><span>Other Earnings</span><span
                            class="amount">₱{{ number_format($payrollItem->other_earnings, 2) }}</span></div>
                @endif
                <div class="ps-item total"><span>Gross Pay</span><span
                        class="amount">₱{{ number_format($payrollItem->gross_pay, 2) }}</span></div>
            </div>

            {{-- Deductions --}}
            <div class="ps-section">
                <div class="ps-section-title deductions">Deductions</div>
                @if ($payrollItem->late_deduction > 0)
                    <div class="ps-item"><span>Late Deduction</span><span
                            class="amount">₱{{ number_format($payrollItem->late_deduction, 2) }}</span></div>
                @endif
                @if ($payrollItem->undertime_deduction > 0)
                    <div class="ps-item"><span>Undertime Deduction</span><span
                            class="amount">₱{{ number_format($payrollItem->undertime_deduction, 2) }}</span></div>
                @endif
                @if ($payrollItem->absent_deduction > 0)
                    <div class="ps-item"><span>Absent Deduction</span><span
                            class="amount">₱{{ number_format($payrollItem->absent_deduction, 2) }}</span></div>
                @endif
                <div class="ps-item"><span>SSS Contribution</span><span
                        class="amount">₱{{ number_format($payrollItem->sss_contribution, 2) }}</span></div>
                <div class="ps-item"><span>PhilHealth</span><span
                        class="amount">₱{{ number_format($payrollItem->philhealth_contribution, 2) }}</span></div>
                <div class="ps-item"><span>Pag-IBIG (HDMF)</span><span
                        class="amount">₱{{ number_format($payrollItem->pagibig_contribution, 2) }}</span></div>
                @if ($payrollItem->withholding_tax > 0)
                    <div class="ps-item"><span>Withholding Tax</span><span
                            class="amount">₱{{ number_format($payrollItem->withholding_tax, 2) }}</span></div>
                @endif
                @if ($payrollItem->other_deductions > 0)
                    <div class="ps-item"><span>Other Deductions</span><span
                            class="amount">₱{{ number_format($payrollItem->other_deductions, 2) }}</span></div>
                @endif
                <div class="ps-item total"><span>Total Deductions</span><span
                        class="amount">₱{{ number_format($payrollItem->total_deductions, 2) }}</span></div>
            </div>
        </div>

        {{-- Net pay --}}
        <div class="ps-net">
            <div class="ps-net-label">NET PAY (Take-Home)</div>
            <div class="ps-net-amount">₱{{ number_format($payrollItem->net_pay, 2) }}</div>
        </div>

        {{-- Footer --}}
        <div class="ps-footer">
            <p>This is a computer-generated payslip. No signature required.</p>
            <p style="margin-top:.3rem;">{{ $payrollItem->profile->vendor->business_name }} —
                {{ $payrollItem->period->period_name }}</p>
            <p style="margin-top:.3rem;color:#aaa;">Generated on {{ now()->format('F d, Y g:i A') }}</p>
        </div>
    </div>

    <div class="print-btn">
        <button class="btn btn-print" onclick="window.print()">🖨 Print Payslip</button>
        <button class="btn btn-back" onclick="history.back()">← Back</button>
    </div>

</body>

</html>
