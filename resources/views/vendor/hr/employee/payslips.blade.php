@extends('layouts.vendor')
@section('title', 'My Payslips')
@section('page-title', 'My Payslips')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.self.dashboard') }}" class="text-secondary">My HR</a>
    <span class="sep">›</span><span class="current">Payslips</span>
@endsection

@section('content')

    <p class="text-muted gap-section" style="font-size:.875rem;">
        <i class="bi bi-info-circle me-1"></i>
        Payslips are available only after payroll is approved by your employer. Click on any payslip to view or print it.
    </p>

    @if ($payslips->count())
        <div class="vd-card">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-receipt"></i> My Payslips</span>
                <span class="vd-card__subtitle">{{ $payslips->total() }} payslips</span>
            </div>
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Dates</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th class="fw-800">Net Pay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslips as $item)
                            <tr>
                                <td>
                                    <div class="fw-600" style="font-size:.875rem;">{{ $item->period->period_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ ucfirst(str_replace('_', ' ', $item->period->frequency)) }}</div>
                                </td>
                                <td class="text-muted" style="font-size:.82rem;">
                                    {{ $item->period->date_from->format('M d') }} –
                                    {{ $item->period->date_to->format('M d, Y') }}
                                </td>
                                <td class="mono">₱{{ number_format($item->gross_pay, 2) }}</td>
                                <td class="mono" style="color:var(--red-500,#ef4444);">
                                    -₱{{ number_format($item->total_deductions, 2) }}</td>
                                <td class="mono fw-800 text-green" style="font-size:.95rem;">
                                    ₱{{ number_format($item->net_pay, 2) }}</td>
                                <td>
                                    <a href="{{ route('vendor.hr.self.payslip.show', $item) }}" target="_blank"
                                        class="vd-btn vd-btn--primary vd-btn--sm">
                                        <i class="bi bi-printer"></i> View / Print
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">{{ $payslips->links('pagination::bootstrap-5') }}</div>
        </div>

        {{-- YTD summary --}}
        <div class="row g-2 mt-3">
            @php
                $ytdGross = $payslips->sum('gross_pay');
                $ytdNet = $payslips->sum('net_pay');
                $ytdSss = $payslips->sum('sss_contribution');
                $ytdPh = $payslips->sum('philhealth_contribution');
                $ytdPf = $payslips->sum('pagibig_contribution');
                $ytdTax = $payslips->sum('withholding_tax');
            @endphp
            <div class="col-12">
                <div class="vd-card">
                    <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-bar-chart"></i> Year-to-Date
                            Summary (Shown Payslips)</span></div>
                    <div class="vd-card__body">
                        <div class="row g-3" style="font-size:.875rem;">
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    Total Gross</div>
                                <div class="mono fw-700 text-green">₱{{ number_format($ytdGross, 2) }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    Total Net</div>
                                <div class="mono fw-700">₱{{ number_format($ytdNet, 2) }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    SSS</div>
                                <div class="mono">₱{{ number_format($ytdSss, 2) }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    PhilHealth</div>
                                <div class="mono">₱{{ number_format($ytdPh, 2) }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    Pag-IBIG</div>
                                <div class="mono">₱{{ number_format($ytdPf, 2) }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    Withholding Tax</div>
                                <div class="mono">₱{{ number_format($ytdTax, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="vd-card">
            <div class="empty-state" style="padding:3rem;">
                <i class="bi bi-receipt"></i>
                <h6>No payslips available yet</h6>
                <p>Your payslips will appear here once your employer processes and approves payroll.</p>
            </div>
        </div>
    @endif
@endsection
