@extends('layouts.vendor')
@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.attendance.index') }}" class="text-secondary">Attendance</a>
    <span class="sep">›</span><span class="current">Monthly Report</span>
@endsection

@section('content')

    {{-- Month filter --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                <label class="vd-label mb-0">Month:</label>
                <input type="month" name="month" value="{{ $month }}" class="vd-input" style="width:auto;">
                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-bar-chart"></i> Generate
                    Report</button>
            </form>
        </div>
    </div>

    @php
        $monthName = \Carbon\Carbon::parse($from)->format('F Y');
        $from_dt = \Carbon\Carbon::parse($from);
        $to_dt = \Carbon\Carbon::parse($to);
        // Build day columns
        $days = [];
        $cur = $from_dt->copy();
        while ($cur->lte($to_dt)) {
            $days[] = $cur->copy();
            $cur->addDay();
        }
    @endphp

    {{-- Summary table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-calendar3"></i> {{ $monthName }} Attendance Summary</span>
            <span class="vd-card__subtitle">{{ $profiles->count() }} employees</span>
        </div>

        @if ($profiles->count())
            <div class="table-responsive">
                <table class="vd-table" style="font-size:.78rem;min-width:800px;">
                    <thead>
                        <tr>
                            <th style="min-width:160px;">Employee</th>
                            <th style="text-align:center;">Present</th>
                            <th style="text-align:center;">Absent</th>
                            <th style="text-align:center;">Late</th>
                            <th style="text-align:center;">Leave</th>
                            <th style="text-align:center;">OT Hrs</th>
                            <th style="text-align:center;">Total Hrs</th>
                            <th style="text-align:center;">Min Late</th>
                            <th style="text-align:center;">Min UT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profiles as $profile)
                            @php
                                $att = $profile->attendance;
                                $present = $att->whereIn('status', ['present', 'half_day'])->count();
                                $absent = $att->where('status', 'absent')->count();
                                $late = $att->where('minutes_late', '>', 0)->count();
                                $leave = $att->where('status', 'leave')->count();
                                $otHrs = round($att->sum('minutes_overtime') / 60, 2);
                                $totHrs = round($att->sum('hours_worked'), 2);
                                $minLate = $att->sum('minutes_late');
                                $minUT = $att->sum('minutes_undertime');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-600" style="font-size:.82rem;">{{ $profile->full_name }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        {{ $profile->department?->name ?? '—' }}</div>
                                </td>
                                <td style="text-align:center;"><span
                                        class="vd-badge vd-badge--active">{{ $present }}</span></td>
                                <td style="text-align:center;"><span
                                        class="{{ $absent > 0 ? 'vd-badge vd-badge--danger' : 'text-muted' }}">{{ $absent }}</span>
                                </td>
                                <td style="text-align:center;"><span
                                        class="{{ $late > 0 ? 'vd-badge vd-badge--warning' : 'text-muted' }}">{{ $late }}d</span>
                                </td>
                                <td style="text-align:center;"><span
                                        class="vd-badge vd-badge--info">{{ $leave }}</span></td>
                                <td style="text-align:center;" class="mono fw-600 {{ $otHrs > 0 ? 'text-green' : '' }}">
                                    {{ $otHrs > 0 ? $otHrs . 'h' : '—' }}</td>
                                <td style="text-align:center;" class="mono fw-600">{{ $totHrs }}h</td>
                                <td style="text-align:center;" class="{{ $minLate > 0 ? 'text-muted' : '' }}"
                                    style="font-size:.75rem;">{{ $minLate > 0 ? $minLate . 'min' : '—' }}</td>
                                <td style="text-align:center;" class="{{ $minUT > 0 ? 'text-muted' : '' }}"
                                    style="font-size:.75rem;">{{ $minUT > 0 ? $minUT . 'min' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state"><i class="bi bi-calendar3"></i>
                <h6>No employee records</h6>
            </div>
        @endif
    </div>

    {{-- Day-by-day legend --}}
    <div class="vd-card mt-3">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-info-circle"></i> Legend</span>
        </div>
        <div class="vd-card__body">
            <div class="d-flex gap-3 flex-wrap" style="font-size:.8rem;">
                @foreach ([['vd-badge--active', 'P', 'Present'], ['vd-badge--danger', 'A', 'Absent'], ['vd-badge--warning', 'L', 'Late'], ['vd-badge--info', 'LV', 'Leave'], ['vd-badge--neutral', 'H', 'Holiday'], ['vd-badge--neutral', 'R', 'Rest Day']] as [$cls, $code, $label])
                    <div class="d-flex align-items-center gap-1">
                        <span class="vd-badge {{ $cls }}"
                            style="font-size:.65rem;min-width:24px;text-align:center;">{{ $code }}</span>
                        <span class="text-muted">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
