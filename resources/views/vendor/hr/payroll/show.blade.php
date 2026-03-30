@extends('layouts.vendor')
@section('title', 'Payroll — ' . $payrollPeriod->period_name)
@section('page-title', 'Payroll Period')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.payroll.index') }}" class="text-secondary">Payroll</a>
    <span class="sep">›</span><span class="current">{{ $payrollPeriod->period_name }}</span>
@endsection

@push('styles')
    <style>
        .payroll-total-card {
            background: linear-gradient(135deg, var(--g-700) 0%, var(--g-900, #0a3d1a) 100%);
            color: #fff;
            border-radius: var(--r-lg);
            padding: 1.5rem;
        }

        .payroll-total-card__label {
            font-size: .75rem;
            opacity: .7;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .payroll-total-card__val {
            font-size: 2rem;
            font-weight: 800;
            font-family: monospace;
            margin-top: .2rem;
        }
    </style>
@endpush

@section('content')

    {{-- Header actions --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="mono fw-700">{{ $payrollPeriod->period_name }}</span>
                @php $sc=['draft'=>'vd-badge--neutral','pending_approval'=>'vd-badge--warning','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger']; @endphp
                <span
                    class="vd-badge {{ $sc[$payrollPeriod->status] ?? 'vd-badge--neutral' }}">{{ ucfirst(str_replace('_', ' ', $payrollPeriod->status)) }}</span>
                <span class="vd-badge vd-badge--neutral">{{ str_replace('_', ' ', ucfirst($payrollPeriod->frequency)) }}</span>
            </div>
            <div class="text-muted mt-1" style="font-size:.8rem;">
                {{ $payrollPeriod->date_from->format('F d') }} – {{ $payrollPeriod->date_to->format('F d, Y') }}
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if ($payrollPeriod->is_editable)
                <form method="POST" action="{{ route('vendor.hr.payroll.compute', $payrollPeriod) }}">
                    @csrf
                    <button class="vd-btn vd-btn--primary"><i class="bi bi-calculator"></i> Compute All
                        ({{ $totalEmployees }})</button>
                </form>
            @endif
            @if ($payrollPeriod->status === 'draft' && $payrollPeriod->items->count() > 0)
                <form method="POST" action="{{ route('vendor.hr.payroll.submit', $payrollPeriod) }}">
                    @csrf
                    <button class="vd-btn vd-btn--secondary"><i class="bi bi-send-check"></i> Submit for Approval</button>
                </form>
            @endif
            @if ($payrollPeriod->status === 'pending_approval' && auth()->user()->isVendor())
                <button class="vd-btn vd-btn--primary" data-bs-toggle="modal" data-bs-target="#approveModal"><i
                        class="bi bi-check-circle"></i> Approve Payroll</button>
            @endif
            @if (in_array($payrollPeriod->status, ['approved', 'paid']))
                <a href="{{ route('vendor.hr.payroll.export', $payrollPeriod) }}" class="vd-btn vd-btn--ghost"><i
                        class="bi bi-file-earmark-excel"></i> Export Excel</a>
            @endif
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 gap-section">
        <div class="col-12 col-md-4">
            <div class="payroll-total-card">
                <div class="payroll-total-card__label">Total Net Pay</div>
                <div class="payroll-total-card__val">₱{{ number_format($payrollPeriod->items->sum('net_pay'), 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="vd-stat vd-stat--blue">
                <div class="vd-stat__value">{{ $payrollPeriod->items->count() }}</div>
                <div class="vd-stat__label">Employees</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__value">₱{{ number_format($payrollPeriod->items->sum('gross_pay'), 2) }}</div>
                <div class="vd-stat__label">Gross Pay</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="vd-stat vd-stat--yellow">
                <div class="vd-stat__value">₱{{ number_format($payrollPeriod->items->sum('total_deductions'), 2) }}</div>
                <div class="vd-stat__label">Total Deductions</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="vd-stat vd-stat--neutral">
                <div class="vd-stat__value">
                    ₱{{ number_format($payrollPeriod->items->sum('sss_contribution') + $payrollPeriod->items->sum('philhealth_contribution') + $payrollPeriod->items->sum('pagibig_contribution'), 2) }}
                </div>
                <div class="vd-stat__label">Gov Contributions</div>
            </div>
        </div>
    </div>

    {{-- Payroll items table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-table"></i> Employee Payroll Details</span>
            @if ($payrollPeriod->is_editable)
                <span class="vd-card__subtitle">Click compute to generate/refresh individual employee payroll</span>
            @endif
        </div>

        @if ($payrollPeriod->items->count())
            <div class="table-responsive">
                <table class="vd-table" style="font-size:.82rem;">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Basic Pay</th>
                            <th>OT Pay</th>
                            <th>Allowances</th>
                            <th>Gross</th>
                            <th>Late/Absent</th>
                            <th>SSS</th>
                            <th>PH</th>
                            <th>PI</th>
                            <th>Tax</th>
                            <th>Total Ded.</th>
                            <th class="fw-800">Net Pay</th>
                            <th>Days</th>
                            @if ($payrollPeriod->is_editable)
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payrollPeriod->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $item->profile->full_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $item->profile->department?->name ?? '—' }}</div>
                                </td>
                                <td class="mono">₱{{ number_format($item->basic_pay, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->overtime_pay, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->allowances, 2) }}</td>
                                <td class="mono fw-600">₱{{ number_format($item->gross_pay, 2) }}</td>
                                <td class="mono text-red" style="color:var(--red-500,#ef4444);">
                                    -₱{{ number_format($item->late_deduction + $item->undertime_deduction + $item->absent_deduction, 2) }}
                                </td>
                                <td class="mono">₱{{ number_format($item->sss_contribution, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->philhealth_contribution, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->pagibig_contribution, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->withholding_tax, 2) }}</td>
                                <td class="mono">₱{{ number_format($item->total_deductions, 2) }}</td>
                                <td class="mono fw-800 text-green">₱{{ number_format($item->net_pay, 2) }}</td>
                                <td class="text-muted" style="font-size:.72rem;">
                                    {{ $item->days_worked }}d worked<br>
                                    {{ $item->days_absent }}d absent
                                </td>
                                @if ($payrollPeriod->is_editable)
                                    <td>
                                        <form method="POST"
                                            action="{{ route('vendor.hr.payroll.compute', $payrollPeriod) }}">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $item->hr_profile_id }}">
                                            <button class="vd-btn vd-btn--ghost vd-btn--sm" title="Recompute"><i
                                                    class="bi bi-arrow-clockwise"></i></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding:2.5rem;">
                <i class="bi bi-calculator"></i>
                <h6>No payroll computed yet</h6>
                <p>Click "Compute All" to generate payroll for all active employees in this period.</p>
            </div>
        @endif
    </div>

    {{-- Approve modal --}}
    @if ($payrollPeriod->status === 'pending_approval')
        <div class="modal fade" id="approveModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('vendor.hr.payroll.approve', $payrollPeriod) }}">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title fw-700"><i class="bi bi-check-circle me-2 text-green"></i>Approve Payroll
                            </h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p style="font-size:.875rem;color:var(--tx-secondary);">
                                Approving this payroll will finalize all
                                <strong>{{ $payrollPeriod->items->count() }}</strong> payslips and make them available to
                                employees.
                                Total net pay:
                                <strong>₱{{ number_format($payrollPeriod->items->sum('net_pay'), 2) }}</strong>
                            </p>
                            <label class="vd-label mt-2">Notes (optional)</label>
                            <textarea name="notes" rows="3" class="vd-input" placeholder="Approval notes…"></textarea>
                            <input type="hidden" name="action" value="approve">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-check-circle"></i>
                                Approve Payroll</button>
                            <button type="submit" name="action" value="reject" class="vd-btn vd-btn--danger"
                                onclick="this.form.querySelector('[name=action]').value='reject'"><i
                                    class="bi bi-x-circle"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
