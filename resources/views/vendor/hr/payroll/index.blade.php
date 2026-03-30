{{-- ======================================================
     resources/views/vendor/hr/payroll/index.blade.php
====================================================== --}}
@extends('layouts.vendor')
@section('title', 'HR — Payroll')
@section('page-title', 'Payroll Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0" style="font-size:.875rem;">Generate, review, and approve payroll for your employees.</p>
        <a href="{{ route('vendor.hr.payroll.create') }}" class="vd-btn vd-btn--primary">
            <i class="bi bi-plus-lg"></i> New Payroll Period
        </a>
    </div>

    <div class="vd-card">
        <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-currency-exchange"></i> Payroll
                Periods</span></div>

        @if ($periods->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Frequency</th>
                            <th>Date Range</th>
                            <th>Employees</th>
                            <th>Total Net Pay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($periods as $period)
                            @php $sc=['draft'=>'vd-badge--neutral','pending_approval'=>'vd-badge--warning','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger','paid'=>'vd-badge--info']; @endphp
                            <tr>
                                <td>
                                    <div class="fw-600" style="font-size:.875rem;">{{ $period->period_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">Created by
                                        {{ $period->creator?->name ?? 'System' }}</div>
                                </td>
                                <td><span class="vd-badge vd-badge--neutral"
                                        style="font-size:.68rem;">{{ str_replace('_', ' ', ucfirst($period->frequency)) }}</span>
                                </td>
                                <td class="text-muted" style="font-size:.82rem;">{{ $period->date_from->format('M d') }} –
                                    {{ $period->date_to->format('M d, Y') }}</td>
                                <td class="fw-600">{{ $period->items->count() }}</td>
                                <td class="mono fw-600">₱{{ number_format($period->items->sum('net_pay'), 2) }}</td>
                                <td><span class="vd-badge {{ $sc[$period->status] ?? 'vd-badge--neutral' }}"
                                        style="font-size:.68rem;">{{ ucfirst(str_replace('_', ' ', $period->status)) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.hr.payroll.show', $period) }}"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="View"><i
                                                class="bi bi-eye"></i></a>
                                        @if (in_array($period->status, ['approved', 'paid']))
                                            <a href="{{ route('vendor.hr.payroll.export', $period) }}"
                                                class="vd-btn vd-btn--ghost vd-btn--icon" title="Export Excel"><i
                                                    class="bi bi-file-earmark-excel"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">{{ $periods->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="empty-state"><i class="bi bi-currency-exchange"></i>
                <h6>No payroll periods yet</h6>
                <a href="{{ route('vendor.hr.payroll.create') }}" class="vd-btn vd-btn--primary d-inline-flex mt-2"><i
                        class="bi bi-plus-lg"></i> Create First Payroll Period</a>
            </div>
        @endif
    </div>
@endsection
