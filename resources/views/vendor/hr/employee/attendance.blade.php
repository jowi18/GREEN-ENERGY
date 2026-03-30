@extends('layouts.vendor')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.self.dashboard') }}" class="text-secondary">My HR</a>
    <span class="sep"></span><span class="current">Attendance</span>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="vd-label mb-0">Month:</label>
            <input type="month" name="month" value="{{ $month }}" class="vd-input" style="width:160px;"
                onchange="this.form.submit()">
        </form>
        <a href="{{ route('vendor.hr.self.dashboard') }}" class="vd-btn vd-btn--ghost vd-btn--sm ms-auto"><i
                class="bi bi-arrow-left"></i> Back</a>
    </div>

    {{-- Monthly summary --}}
    @php
        $present = $records->whereIn('status', ['present', 'half_day'])->count();
        $absent = $records->where('status', 'absent')->count();
        $leave = $records->where('status', 'leave')->count();
        $late = $records->where('minutes_late', '>', 0)->count();
        $totOt = $records->sum('minutes_overtime');
        $totLate = $records->sum('minutes_late');
    @endphp
    <div class="row g-2 gap-section">
        @foreach ([['Present', $present, 'green', 'bi-check-circle'], ['Absent', $absent, 'danger', 'bi-x-circle'], ['On Leave', $leave, 'blue', 'bi-calendar-x'], ['Days Late', $late, 'yellow', 'bi-clock']] as [$l, $v, $c, $i])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $c }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $c }}"><i class="bi {{ $i }}"></i>
                    </div>
                    <div class="vd-stat__value">{{ $v }}</div>
                    <div class="vd-stat__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-calendar3"></i> Attendance —
                {{ \Carbon\Carbon::parse($month)->format('F Y') }}</span>
        </div>
        @if ($records->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                            <th>Late</th>
                            <th>Undertime</th>
                            <th>OT</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $att)
                            @php $sc=['present'=>'vd-badge--active','absent'=>'vd-badge--danger','leave'=>'vd-badge--info','half_day'=>'vd-badge--warning','holiday'=>'vd-badge--neutral','rest_day'=>'vd-badge--neutral']; @endphp
                            <tr>
                                <td class="fw-600 mono" style="font-size:.82rem;">
                                    {{ $att->attendance_date->format('M d, Y') }}</td>
                                <td class="text-muted" style="font-size:.78rem;">{{ $att->attendance_date->format('D') }}
                                </td>
                                <td class="mono">{{ $att->time_in?->format('h:i A') ?? '—' }}</td>
                                <td class="mono">{{ $att->time_out?->format('h:i A') ?? '—' }}</td>
                                <td class="mono fw-600">
                                    {{ $att->hours_worked > 0 ? number_format($att->hours_worked, 2) . 'h' : '—' }}</td>
                                <td
                                    style="font-size:.78rem;{{ $att->minutes_late > 0 ? 'color:var(--red-500,#ef4444);font-weight:700;' : 'color:var(--tx-muted);' }}">
                                    {{ $att->minutes_late > 0 ? $att->minutes_late . 'min' : '—' }}</td>
                                <td
                                    style="font-size:.78rem;{{ $att->minutes_undertime > 0 ? 'color:var(--red-500,#ef4444);font-weight:700;' : 'color:var(--tx-muted);' }}">
                                    {{ $att->minutes_undertime > 0 ? $att->minutes_undertime . 'min' : '—' }}</td>
                                <td
                                    style="font-size:.78rem;{{ $att->minutes_overtime > 0 ? 'color:var(--g-600);font-weight:700;' : 'color:var(--tx-muted);' }}">
                                    {{ $att->minutes_overtime > 0 ? round($att->minutes_overtime / 60, 2) . 'h' : '—' }}</td>
                                <td><span class="vd-badge {{ $sc[$att->status] ?? 'vd-badge--neutral' }}"
                                        style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $att->status)) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding:2.5rem;"><i class="bi bi-calendar3"></i>
                <h6>No attendance records for this month.</h6>
            </div>
        @endif
    </div>
@endsection
