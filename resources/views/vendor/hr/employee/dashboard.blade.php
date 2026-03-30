{{-- ====================================================
     resources/views/vendor/hr/employee/dashboard.blade.php
     Employee self-service: Time In/Out + overview
==================================================== --}}
@extends('layouts.vendor')
@section('title', 'My HR Dashboard')
@section('page-title', 'My HR Dashboard')

@push('styles')
    <style>
        .clock-card {
            background: linear-gradient(135deg, #0a2e14 0%, #157a30 100%);
            color: #fff;
            border-radius: var(--r-xl);
            padding: 2rem;
            text-align: center;
        }

        .clock-card__time {
            font-size: 3rem;
            font-weight: 800;
            font-family: monospace;
            letter-spacing: .05em;
        }

        .clock-card__date {
            font-size: .875rem;
            opacity: .75;
            margin-top: .25rem;
        }

        .clock-card__status {
            font-size: .875rem;
            margin: 1rem 0;
            font-weight: 600;
        }

        .time-btn {
            padding: .9rem 2rem;
            font-size: 1rem;
            font-weight: 800;
            border-radius: var(--r-full);
            border: none;
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .time-btn--in {
            background: #22c55e;
            color: #fff;
        }

        .time-btn--in:hover {
            background: #16a34a;
        }

        .time-btn--out {
            background: #ef4444;
            color: #fff;
        }

        .time-btn--out:hover {
            background: #dc2626;
        }

        .time-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .loc-status {
            font-size: .75rem;
            margin-top: .65rem;
            opacity: .8;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">

        {{-- ── Clock in/out ── --}}
        <div class="col-12 col-md-5">
            <div class="clock-card">
                <div class="clock-card__time" id="clockDisplay">--:--:--</div>
                <div class="clock-card__date" id="dateDisplay"></div>

                <div class="clock-card__status" id="clockStatus">
                    @if ($status['timed_in'] && !$status['timed_out'])
                        ✅ Timed in at <strong>{{ $status['time_in'] }}</strong>
                        @if ($status['minutes_late'] > 0)
                            — <span style="color:#fde68a;">{{ $status['minutes_late'] }}min late</span>
                        @endif
                    @elseif($status['timed_out'])
                        ✅ Shift complete — {{ $status['hours_worked'] }}h worked
                    @else
                        Ready to clock in
                    @endif
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button class="time-btn time-btn--in" id="timeInBtn"
                        {{ $status['timed_in'] || !auth()->user()->employee ? 'disabled' : '' }} onclick="doTimeIn()">
                        <i class="bi bi-box-arrow-in-right"></i> Time In
                    </button>
                    <button class="time-btn time-btn--out" id="timeOutBtn"
                        {{ !$status['timed_in'] || $status['timed_out'] ? 'disabled' : '' }} onclick="doTimeOut()">
                        <i class="bi bi-box-arrow-right"></i> Time Out
                    </button>
                </div>

                <div class="loc-status" id="locStatus">
                    <i class="bi bi-geo-alt me-1"></i> Location: detecting…
                </div>
            </div>
        </div>

        {{-- ── Quick stats ── --}}
        <div class="col-12 col-md-7">
            <div class="row g-2">
                <div class="col-6">
                    <div class="vd-stat vd-stat--green">
                        <div class="vd-stat__label">My Monthly Rate</div>
                        <div class="vd-stat__value">₱{{ number_format($profile->monthly_rate ?? 0, 0) }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="vd-stat vd-stat--blue">
                        <div class="vd-stat__label">Pending Leaves</div>
                        <div class="vd-stat__value">{{ $pendingLeave }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="vd-stat vd-stat--yellow">
                        <div class="vd-stat__label">Gov ID: SSS</div>
                        <div class="vd-stat__value" style="font-size:1rem;">{{ $profile->sss_number ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="vd-stat vd-stat--neutral">
                        <div class="vd-stat__label">Employment Status</div>
                        <div class="vd-stat__value" style="font-size:1rem;">{{ ucfirst($profile->employment_status) }}</div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="vd-card mt-2">
                <div class="vd-card__body">
                    <div class="fw-700 mb-2" style="font-size:.875rem;">Quick Actions</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('vendor.hr.self.leaves') }}" class="vd-btn vd-btn--ghost vd-btn--sm"><i
                                class="bi bi-calendar-x me-1"></i>Apply Leave</a>
                        <a href="{{ route('vendor.hr.self.overtime') }}" class="vd-btn vd-btn--ghost vd-btn--sm"><i
                                class="bi bi-clock-history me-1"></i>File Overtime</a>
                        <a href="{{ route('vendor.hr.self.payslips') }}" class="vd-btn vd-btn--ghost vd-btn--sm"><i
                                class="bi bi-receipt me-1"></i>My Payslips</a>
                        <a href="{{ route('vendor.hr.self.attendance') }}" class="vd-btn vd-btn--ghost vd-btn--sm"><i
                                class="bi bi-calendar3 me-1"></i>My Attendance</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Recent attendance ── --}}
        <div class="col-12">
            <div class="vd-card">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-clock-history"></i> Recent
                        Attendance (Last 7 Days)</span></div>
                @if ($recentAttendance->count())
                    <div class="table-responsive">
                        <table class="vd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours</th>
                                    <th>Late</th>
                                    <th>OT</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentAttendance as $att)
                                    <tr>
                                        <td class="fw-600" style="font-size:.82rem;">
                                            {{ $att->attendance_date->format('M d, Y (D)') }}</td>
                                        <td class="mono">{{ $att->time_in?->format('h:i A') ?? '—' }}</td>
                                        <td class="mono">{{ $att->time_out?->format('h:i A') ?? '—' }}</td>
                                        <td class="mono fw-600">
                                            {{ $att->hours_worked > 0 ? number_format($att->hours_worked, 2) . 'h' : '—' }}
                                        </td>
                                        <td class="text-muted" style="font-size:.78rem;">
                                            {{ $att->minutes_late > 0 ? $att->minutes_late . 'min' : '—' }}</td>
                                        <td class="text-muted" style="font-size:.78rem;">
                                            {{ $att->minutes_overtime > 0 ? round($att->minutes_overtime / 60, 2) . 'h' : '—' }}
                                        </td>
                                        <td>
                                            @php $sc=['present'=>'vd-badge--active','absent'=>'vd-badge--danger','leave'=>'vd-badge--info','half_day'=>'vd-badge--warning','holiday'=>'vd-badge--neutral']; @endphp
                                            <span class="vd-badge {{ $sc[$att->status] ?? 'vd-badge--neutral' }}"
                                                style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $att->status)) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state" style="padding:2rem;"><i class="bi bi-clock"></i>
                        <p>No attendance records this week.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let userLat = null,
            userLng = null;

        // Real-time clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clockDisplay').textContent = now.toLocaleTimeString('en-PH');
            document.getElementById('dateDisplay').textContent = now.toLocaleDateString('en-PH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                document.getElementById('locStatus').innerHTML =
                    '<i class="bi bi-geo-alt-fill me-1"></i>Location acquired ✓';
            }, () => {
                document.getElementById('locStatus').innerHTML =
                    '<i class="bi bi-geo-alt me-1"></i>Location unavailable — will time in without location';
            });
        }

        function doTimeIn() {
            const btn = document.getElementById('timeInBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

            $.ajax({
                url: '{{ route('vendor.hr.self.time-in') }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    latitude: userLat,
                    longitude: userLng
                }),
                success(res) {
                    if (res.success) {
                        Swal.fire({
                                icon: 'success',
                                title: 'Timed In!',
                                text: res.message,
                                confirmButtonColor: '#157a30'
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Time In';
                    }
                },
                error(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed.'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Time In';
                }
            });
        }

        function doTimeOut() {
            Swal.fire({
                title: 'Confirm Time Out?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Time Out'
            }).then(r => {
                if (!r.isConfirmed) return;
                const btn = document.getElementById('timeOutBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

                $.ajax({
                    url: '{{ route('vendor.hr.self.time-out') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        latitude: userLat,
                        longitude: userLng
                    }),
                    success(res) {
                        if (res.success) {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Timed Out!',
                                    text: res.message,
                                    confirmButtonColor: '#157a30'
                                })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Time Out';
                        }
                    }
                });
            });
        }
    </script>
@endpush
