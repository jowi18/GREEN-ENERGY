@extends('layouts.vendor')
@section('title', 'Attendance Board')
@section('page-title', 'Attendance Board')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* ── Biometric board layout ──────────────────────── */
        .bio-shell {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .bio-shell {
                grid-template-columns: 1fr;
            }
        }

        /* ── Clock terminal ──────────────────────────────── */
        .bio-terminal {
            background: linear-gradient(160deg, #071a0c 0%, #0e3318 50%, #157a30 100%);
            border-radius: var(--r-xl);
            padding: 1.75rem 1.5rem;
            color: #fff;
            position: sticky;
            top: calc(var(--topbar-h, 64px) + 1rem);
        }

        .bio-terminal__date {
            font-size: .75rem;
            opacity: .6;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-weight: 700;
            margin-bottom: .3rem;
        }

        .bio-terminal__clock {
            font-size: 2.6rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            letter-spacing: .04em;
            line-height: 1;
        }

        .bio-terminal__day {
            font-size: .82rem;
            opacity: .65;
            margin-top: .3rem;
        }

        .bio-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, .12);
            margin: 1.25rem 0;
        }

        /* Location status */
        .bio-loc {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .8rem;
            padding: .65rem .85rem;
            border-radius: var(--r-md);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .bio-loc--detecting {
            background: rgba(255, 255, 255, .08);
            color: rgba(255, 255, 255, .7);
        }

        .bio-loc--ok {
            background: rgba(34, 197, 94, .2);
            color: #86efac;
        }

        .bio-loc--outside {
            background: rgba(239, 68, 68, .2);
            color: #fca5a5;
        }

        .bio-loc--error {
            background: rgba(245, 158, 11, .2);
            color: #fcd34d;
        }

        .bio-loc__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .bio-loc--detecting .bio-loc__dot {
            background: rgba(255, 255, 255, .4);
        }

        .bio-loc--ok .bio-loc__dot {
            background: #22c55e;
            animation: locPulse 2s infinite;
        }

        .bio-loc--outside .bio-loc__dot {
            background: #ef4444;
        }

        .bio-loc--error .bio-loc__dot {
            background: #f59e0b;
        }

        @keyframes locPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, .6);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
            }
        }

        /* Time-in/out buttons */
        .bio-action-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: var(--r-lg);
            font-size: .95rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            transition: all .15s;
            margin-bottom: .65rem;
            font-family: 'Outfit', 'Nunito', sans-serif;
        }

        .bio-action-btn--in {
            background: #22c55e;
            color: #fff;
        }

        .bio-action-btn--in:hover:not(:disabled) {
            background: #16a34a;
            transform: translateY(-1px);
        }

        .bio-action-btn--out {
            background: #ef4444;
            color: #fff;
        }

        .bio-action-btn--out:hover:not(:disabled) {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .bio-action-btn:disabled {
            background: rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .35);
            cursor: not-allowed;
        }

        .bio-action-btn .btn-icon {
            font-size: 1.1rem;
        }

        /* My status (employee view) */
        .bio-my-status {
            background: rgba(255, 255, 255, .07);
            border-radius: var(--r-md);
            padding: .85rem 1rem;
            font-size: .82rem;
        }

        .bio-my-status__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .2rem 0;
        }

        .bio-my-status__label {
            opacity: .6;
            font-size: .73rem;
        }

        .bio-my-status__val {
            font-weight: 700;
            font-family: monospace;
        }

        .bio-my-status__val--late {
            color: #fca5a5;
        }

        .bio-my-status__val--ot {
            color: #86efac;
        }

        /* ── Employee board (right panel) ──────────────── */
        .bio-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .65rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 640px) {
            .bio-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .bio-stat {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .85rem 1rem;
            text-align: center;
        }

        .bio-stat__val {
            font-size: 1.6rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
        }

        .bio-stat__label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--tx-muted);
            margin-top: .1rem;
        }

        .bio-stat--present {
            border-color: var(--g-300);
        }

        .bio-stat--present .bio-stat__val {
            color: var(--g-700);
        }

        .bio-stat--absent {
            border-color: #fca5a5;
        }

        .bio-stat--absent .bio-stat__val {
            color: #ef4444;
        }

        .bio-stat--late {
            border-color: var(--y-300);
        }

        .bio-stat--late .bio-stat__val {
            color: var(--y-600);
        }

        .bio-stat--ot {
            border-color: var(--g-200);
        }

        .bio-stat--ot .bio-stat__val {
            color: var(--g-600);
        }

        /* Employee cards grid */
        .bio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: .75rem;
        }

        .bio-card {
            background: var(--card-bg);
            border: 2px solid var(--card-border);
            border-radius: var(--r-lg);
            padding: 1.1rem;
            transition: border-color .2s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }

        .bio-card.status-present {
            border-color: var(--g-300);
        }

        .bio-card.status-absent {
            border-color: #fecaca;
        }

        .bio-card.status-leave {
            border-color: #bfdbfe;
        }

        .bio-card.status-not_in {
            border-color: var(--card-border);
        }

        /* Pulse for currently working */
        .bio-card.status-present::before {
            content: '';
            position: absolute;
            top: .6rem;
            right: .6rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .2);
            animation: locPulse 2s infinite;
        }

        .bio-card__avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--g-100);
            color: var(--g-700);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .85rem;
            margin-bottom: .75rem;
        }

        .bio-card__name {
            font-weight: 700;
            font-size: .875rem;
            line-height: 1.25;
        }

        .bio-card__role {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: 2px;
        }

        .bio-card__times {
            margin-top: .65rem;
            padding-top: .65rem;
            border-top: 1px solid var(--card-border);
            font-size: .78rem;
        }

        .bio-card__time-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .2rem;
        }

        .bio-card__time-label {
            color: var(--tx-muted);
            font-size: .72rem;
        }

        .bio-card__time-val {
            font-family: monospace;
            font-weight: 700;
        }

        .bio-badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .65rem;
            font-weight: 700;
            padding: .15rem .5rem;
            border-radius: var(--r-full);
            margin-top: .5rem;
        }

        .bio-badge--present {
            background: var(--g-50);
            color: var(--g-700);
            border: 1px solid var(--g-200);
        }

        .bio-badge--absent {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .bio-badge--leave {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .bio-badge--not_in {
            background: var(--n-50);
            color: var(--tx-muted);
            border: 1px solid var(--n-200);
        }

        .bio-badge--late {
            background: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
        }

        .bio-badge--ot {
            background: var(--g-50);
            color: var(--g-700);
            border: 1px solid var(--g-200);
        }

        .hours-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--card-border);
            margin-top: .5rem;
            overflow: hidden;
        }

        .hours-bar__fill {
            height: 100%;
            border-radius: 2px;
            background: var(--g-500);
            transition: width .5s;
        }

        /* List view toggle */
        .view-toggle {
            display: flex;
            gap: .25rem;
        }

        .view-btn {
            width: 32px;
            height: 32px;
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-sm);
            background: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tx-muted);
            font-size: .9rem;
            transition: all .15s;
        }

        .view-btn.active {
            background: var(--g-600);
            border-color: var(--g-600);
            color: #fff;
        }

        /* Spin animation */
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')

    @php
        $isOwner = auth()->user()->isVendor();
        $isEmployee = auth()->user()->isEmployee();
        $isToday = $date === today()->format('Y-m-d');

        // If employee — get their own profile
        $myProfile = null;
        if ($isEmployee) {
            $myProfile = \App\Models\HrEmployeeProfile::where('employee_id', auth()->user()->employee->id)->first();
        }
        $myAtt = $myProfile ? $records->where('hr_profile_id', $myProfile->id)->first() : null;

        $present = $records->whereIn('status', ['present', 'half_day'])->count();
        $absent = max(0, $profiles->count() - $records->where('status', '!=', 'rest_day')->count());
        $late = $records->where('minutes_late', '>', 0)->count();
        $otCount = $records->where('minutes_overtime', '>', 0)->count();

        $officeSet = $settings && $settings->office_latitude && $settings->office_longitude;
        $workHours = 8; // default, overridden by settings
        if ($settings && $settings->work_start && $settings->work_end) {
            $s = \Carbon\Carbon::parse($settings->work_start);
            $e = \Carbon\Carbon::parse($settings->work_end);
            $workHours = $s->diffInMinutes($e) / 60;
        }
    @endphp

    <div class="bio-shell">

        {{-- ══════════════════════════════════════
         LEFT: Clock Terminal
    ══════════════════════════════════════ --}}
        <div class="bio-terminal">

            {{-- Live clock --}}
            <div class="bio-terminal__date" id="clockDate"></div>
            <div class="bio-terminal__clock" id="clockTime">--:--:--</div>
            <div class="bio-terminal__day" id="clockDay"></div>

            <hr class="bio-divider">

            {{-- Work schedule --}}
            @if ($settings)
                <div style="font-size:.75rem;opacity:.65;margin-bottom:.75rem;">
                    <i class="bi bi-clock me-1"></i>
                    Work Hours: <strong style="opacity:1;">{{ $settings->work_start }} – {{ $settings->work_end }}</strong>
                    &nbsp;·&nbsp; Grace: {{ $settings->grace_period_minutes }}min
                </div>
            @endif

            {{-- Location status --}}
            <div class="bio-loc bio-loc--detecting" id="locStatus">
                <div class="bio-loc__dot"></div>
                <span id="locText"><i class="bi bi-arrow-repeat spin me-1"></i>Detecting location…</span>
            </div>

            @if ($officeSet && $isToday)
                {{-- Map mini preview --}}
                <div id="miniMap" style="height:140px;border-radius:var(--r-md);overflow:hidden;margin-bottom:.75rem;">
                </div>
            @endif

            <hr class="bio-divider">

            {{-- TIME IN / OUT BUTTONS --}}
            @if ($isToday)
                <button class="bio-action-btn bio-action-btn--in" id="timeInBtn" disabled onclick="doTimeIn()">
                    <i class="bi bi-box-arrow-in-right btn-icon"></i>
                    <span id="timeInLabel">TIME IN</span>
                </button>

                <button class="bio-action-btn bio-action-btn--out" id="timeOutBtn" disabled onclick="doTimeOut()">
                    <i class="bi bi-door-open btn-icon"></i>
                    <span id="timeOutLabel">TIME OUT</span>
                </button>
            @else
                <div style="text-align:center;font-size:.82rem;opacity:.5;padding:.5rem 0;">
                    <i class="bi bi-calendar-x me-1"></i>
                    Viewing {{ \Carbon\Carbon::parse($date)->format('M d, Y') }} — Time In/Out only available today.
                </div>
            @endif

            <hr class="bio-divider">

            {{-- My status panel --}}
            <div class="bio-my-status" id="myStatusPanel">
                @if ($myAtt)
                    @php
                        $pct = $myAtt->hours_worked > 0 ? min(100, ($myAtt->hours_worked / $workHours) * 100) : 0;
                    @endphp
                    <div class="bio-my-status__row">
                        <span class="bio-my-status__label">Time In</span>
                        <span class="bio-my-status__val"
                            id="myTimeIn">{{ $myAtt->time_in?->format('h:i A') ?? '—' }}</span>
                    </div>
                    <div class="bio-my-status__row">
                        <span class="bio-my-status__label">Time Out</span>
                        <span class="bio-my-status__val"
                            id="myTimeOut">{{ $myAtt->time_out?->format('h:i A') ?? '—' }}</span>
                    </div>
                    <div class="bio-my-status__row">
                        <span class="bio-my-status__label">Hours Worked</span>
                        <span class="bio-my-status__val" id="myHours">{{ number_format($myAtt->hours_worked, 2) }}h</span>
                    </div>
                    <div class="bio-my-status__row">
                        <span class="bio-my-status__label">Late</span>
                        <span class="bio-my-status__val {{ $myAtt->minutes_late > 0 ? 'bio-my-status__val--late' : '' }}"
                            id="myLate">
                            {{ $myAtt->minutes_late > 0 ? $myAtt->minutes_late . 'min' : '—' }}
                        </span>
                    </div>
                    @if ($myAtt->minutes_overtime > 0)
                        <div class="bio-my-status__row">
                            <span class="bio-my-status__label">Overtime</span>
                            <span class="bio-my-status__val bio-my-status__val--ot"
                                id="myOT">{{ round($myAtt->minutes_overtime / 60, 2) }}h</span>
                        </div>
                    @endif
                    <div class="hours-bar mt-2">
                        <div class="hours-bar__fill" id="hoursBar" style="width:{{ $pct }}%"></div>
                    </div>
                    <div style="font-size:.65rem;opacity:.5;margin-top:.2rem;text-align:right">{{ round($pct) }}% of
                        {{ $workHours }}h shift</div>
                @else
                    <div style="text-align:center;opacity:.5;padding:.5rem 0;font-size:.8rem;">
                        <i class="bi bi-person-clock"></i>&nbsp; Not clocked in yet
                    </div>
                @endif
            </div>

            <hr class="bio-divider">

            {{-- HR quick actions --}}
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('vendor.hr.attendance.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}"
                    class="vd-btn vd-btn--ghost vd-btn--sm"
                    style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-color:rgba(255,255,255,.15);">
                    <i class="bi bi-chevron-left me-1"></i> Previous Day
                </a>
                @if ($isOwner || auth()->user()->isEmployee())
                    <button class="vd-btn vd-btn--ghost vd-btn--sm"
                        style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-color:rgba(255,255,255,.15);"
                        data-bs-toggle="modal" data-bs-target="#manualModal">
                        <i class="bi bi-pencil-square me-1"></i> Manual Entry (HR)
                    </button>
                @endif
                <a href="{{ route('vendor.hr.attendance.settings') }}" class="vd-btn vd-btn--ghost vd-btn--sm"
                    style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-color:rgba(255,255,255,.15);">
                    <i class="bi bi-gear me-1"></i> Attendance Settings
                </a>
                <a href="{{ route('vendor.hr.attendance.report') }}" class="vd-btn vd-btn--ghost vd-btn--sm"
                    style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-color:rgba(255,255,255,.15);">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Monthly Report
                </a>
            </div>

        </div>

        {{-- ══════════════════════════════════════
         RIGHT: Employee Board
    ══════════════════════════════════════ --}}
        <div>

            {{-- Date nav + view toggle --}}
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="vd-input" style="width:160px;"
                        onchange="this.form.submit()">
                </form>
                <span class="fw-700" style="font-size:.875rem;">
                    {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                </span>
                @if (!$isToday)
                    <a href="{{ route('vendor.hr.attendance.index') }}" class="vd-btn vd-btn--ghost vd-btn--sm">← Today</a>
                @else
                    <span class="vd-badge vd-badge--active" style="font-size:.68rem;">
                        <i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i> LIVE
                    </span>
                @endif
                <div class="ms-auto view-toggle">
                    <button class="view-btn active" id="btnGrid" onclick="setView('grid')" title="Card view">
                        <i class="bi bi-grid"></i>
                    </button>
                    <button class="view-btn" id="btnList" onclick="setView('list')" title="List view">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>

            {{-- Summary stats --}}
            <div class="bio-stats" id="statsRow">
                <div class="bio-stat bio-stat--present">
                    <div class="bio-stat__val" id="statPresent">{{ $present }}</div>
                    <div class="bio-stat__label">Present</div>
                </div>
                <div class="bio-stat bio-stat--absent">
                    <div class="bio-stat__val" id="statAbsent">{{ $absent }}</div>
                    <div class="bio-stat__label">Absent</div>
                </div>
                <div class="bio-stat bio-stat--late">
                    <div class="bio-stat__val" id="statLate">{{ $late }}</div>
                    <div class="bio-stat__label">Late</div>
                </div>
                <div class="bio-stat bio-stat--ot">
                    <div class="bio-stat__val" id="statOT">{{ $otCount }}</div>
                    <div class="bio-stat__label">Overtime</div>
                </div>
            </div>

            {{-- Employee cards --}}
            <div class="bio-grid" id="empGrid">
                @foreach ($profiles as $profile)
                    @php
                        $att = $records->where('hr_profile_id', $profile->id)->first();
                        $cardStatus = $att ? $att->status : 'not_in';
                        $pct = $att && $att->hours_worked > 0 ? min(100, ($att->hours_worked / $workHours) * 100) : 0;
                    @endphp
                    <div class="bio-card status-{{ $cardStatus }}" id="card-{{ $profile->id }}">
                        <div class="d-flex align-items-start gap-2">
                            <div class="bio-card__avatar">
                                {{ strtoupper(substr($profile->first_name, 0, 1) . substr($profile->last_name, 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="bio-card__name">{{ $profile->full_name }}</div>
                                <div class="bio-card__role">
                                    {{ $profile->position?->title ?? ($profile->department?->name ?? '—') }}</div>
                            </div>
                        </div>

                        {{-- Status badge --}}
                        <span class="bio-badge bio-badge--{{ $cardStatus }}" id="badge-{{ $profile->id }}">
                            @if ($cardStatus === 'present')
                                ✓ Present
                            @elseif($cardStatus === 'absent')
                                ✗ Absent
                            @elseif($cardStatus === 'leave')
                                📅 On Leave
                            @else
                                ◌ Not In
                            @endif
                        </span>

                        @if ($att && $att->minutes_late > 0)
                            <span class="bio-badge bio-badge--late" id="late-{{ $profile->id }}">
                                <i class="bi bi-clock"></i> Late {{ $att->minutes_late }}min
                            </span>
                        @endif

                        @if ($att && $att->minutes_overtime > 0)
                            <span class="bio-badge bio-badge--ot">
                                <i class="bi bi-plus-circle"></i> OT {{ round($att->minutes_overtime / 60, 2) }}h
                            </span>
                        @endif

                        <div class="bio-card__times">
                            <div class="bio-card__time-row">
                                <span class="bio-card__time-label"><i class="bi bi-box-arrow-in-right"
                                        style="font-size:.65rem;"></i> In</span>
                                <span class="bio-card__time-val" id="tin-{{ $profile->id }}">
                                    {{ $att?->time_in?->format('h:i A') ?? '—' }}
                                </span>
                            </div>
                            <div class="bio-card__time-row">
                                <span class="bio-card__time-label"><i class="bi bi-box-arrow-right"
                                        style="font-size:.65rem;"></i> Out</span>
                                <span class="bio-card__time-val" id="tout-{{ $profile->id }}">
                                    {{ $att?->time_out?->format('h:i A') ?? '—' }}
                                </span>
                            </div>
                            <div class="bio-card__time-row">
                                <span class="bio-card__time-label"><i class="bi bi-hourglass"
                                        style="font-size:.65rem;"></i> Hours</span>
                                <span class="bio-card__time-val" id="hrs-{{ $profile->id }}"
                                    style="{{ $att && $att->hours_worked >= $workHours ? 'color:var(--g-600);' : '' }}">
                                    {{ $att?->hours_worked > 0 ? number_format($att->hours_worked, 2) . 'h' : '—' }}
                                </span>
                            </div>

                            {{-- Hours progress bar --}}
                            <div class="hours-bar">
                                <div class="hours-bar__fill" id="bar-{{ $profile->id }}"
                                    style="width:{{ $pct }}%"></div>
                            </div>

                            @if ($att?->time_in_lat && !$att->time_in_valid_location)
                                <div style="font-size:.65rem;color:#ef4444;margin-top:.35rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Clocked in outside office radius
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- List view (hidden by default) --}}
            <div id="empList" style="display:none;">
                <div class="vd-card">
                    <div class="table-responsive">
                        <table class="vd-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours</th>
                                    <th>Late</th>
                                    <th>OT</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody id="listBody">
                                @foreach ($profiles as $profile)
                                    @php $att = $records->where('hr_profile_id', $profile->id)->first(); @endphp
                                    <tr id="row-{{ $profile->id }}">
                                        <td>
                                            <div class="fw-600">{{ $profile->full_name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">
                                                {{ $profile->position?->title ?? '—' }}</div>
                                        </td>
                                        <td>
                                            @if ($att)
                                                <span class="vd-badge bio-badge--{{ $att->status }}"
                                                    style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $att->status)) }}</span>
                                            @else
                                                <span class="vd-badge vd-badge--neutral" style="font-size:.65rem;">Not
                                                    In</span>
                                            @endif
                                        </td>
                                        <td class="mono" id="ltin-{{ $profile->id }}">
                                            {{ $att?->time_in?->format('h:i A') ?? '—' }}</td>
                                        <td class="mono" id="ltout-{{ $profile->id }}">
                                            {{ $att?->time_out?->format('h:i A') ?? '—' }}</td>
                                        <td class="mono fw-600" id="lhrs-{{ $profile->id }}">
                                            {{ $att?->hours_worked > 0 ? number_format($att->hours_worked, 2) . 'h' : '—' }}
                                        </td>
                                        <td style="{{ ($att?->minutes_late ?? 0) > 0 ? 'color:#ef4444;font-weight:700;' : 'color:var(--tx-muted);' }}"
                                            id="llate-{{ $profile->id }}">
                                            {{ ($att?->minutes_late ?? 0) > 0 ? $att->minutes_late . 'min' : '—' }}
                                        </td>
                                        <td style="{{ ($att?->minutes_overtime ?? 0) > 0 ? 'color:var(--g-600);font-weight:700;' : 'color:var(--tx-muted);' }}"
                                            id="lot-{{ $profile->id }}">
                                            {{ ($att?->minutes_overtime ?? 0) > 0 ? round($att->minutes_overtime / 60, 2) . 'h' : '—' }}
                                        </td>
                                        <td style="font-size:.72rem;">
                                            @if ($att?->time_in_lat)
                                                <span
                                                    style="color:{{ $att->time_in_valid_location ? 'var(--g-600)' : '#ef4444' }};">
                                                    <i
                                                        class="bi bi-geo-alt"></i>{{ $att->time_in_valid_location ? 'Valid' : 'Outside' }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>{{-- /right --}}
    </div>{{-- /bio-shell --}}

    {{-- ══════════ Manual Entry Modal ══════════ --}}
    <div class="modal fade" id="manualModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.hr.attendance.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700"><i class="bi bi-pencil-square me-2 text-green"></i>Manual
                            Attendance Entry</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="vd-label">Employee <span class="req">*</span></label>
                            <select name="hr_profile_id" class="vd-select" required>
                                <option value="">Select employee…</option>
                                @foreach ($profiles as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Date <span class="req">*</span></label>
                            <input type="date" name="date" class="vd-input" value="{{ $date }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="vd-label">Time In</label>
                                <input type="time" name="time_in" class="vd-input"
                                    value="{{ $settings?->work_start ?? '08:00' }}">
                            </div>
                            <div class="col-6">
                                <label class="vd-label">Time Out</label>
                                <input type="time" name="time_out" class="vd-input"
                                    value="{{ $settings?->work_end ?? '17:00' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Status <span class="req">*</span></label>
                            <select name="status" class="vd-select" required>
                                @foreach (['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'leave' => 'On Leave', 'holiday' => 'Holiday', 'rest_day' => 'Rest Day'] as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="vd-label">Remarks</label>
                            <input type="text" name="remarks" class="vd-input" placeholder="Optional…">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save
                            Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ── Config from PHP ─────────────────────────────────
        const IS_TODAY = {{ $isToday ? 'true' : 'false' }};
        const REQUIRE_LOC = {{ $settings?->require_location ?? false ? 'true' : 'false' }};
        const OFFICE_LAT = {{ $settings?->office_latitude ?? 'null' }};
        const OFFICE_LNG = {{ $settings?->office_longitude ?? 'null' }};
        const OFFICE_RADIUS = {{ $settings?->location_radius_meters ?? 200 }};
        const GRACE_MINUTES = {{ $settings?->grace_period_minutes ?? 15 }};
        const WORK_HOURS = {{ $workHours }};
        const WORK_START = '{{ $settings?->work_start ?? '08:00' }}';
        const WORK_END = '{{ $settings?->work_end ?? '17:00' }}';
        const POLL_URL = '{{ route('vendor.hr.attendance.poll') }}';
        const TIMEIN_URL = '{{ route('vendor.hr.self.time-in') }}';
        const TIMEOUT_URL = '{{ route('vendor.hr.self.time-out') }}';
        const MY_TIMED_IN = {{ $myAtt && $myAtt->time_in ? 'true' : 'false' }};
        const MY_TIMED_OUT = {{ $myAtt && $myAtt->time_out ? 'true' : 'false' }};

        let userLat = null,
            userLng = null;
        let locationValid = false;
        let miniMap = null,
            userMarker = null,
            officeMarker = null;

        // ── Live clock ──────────────────────────────────────
        function updateClock() {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const h = now.getHours(),
                m = now.getMinutes(),
                s = now.getSeconds();
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            document.getElementById('clockTime').textContent = `${pad(h12)}:${pad(m)}:${pad(s)} ${ampm}`;
            document.getElementById('clockDate').textContent = now.toLocaleDateString('en-PH', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            document.getElementById('clockDay').textContent = now.toLocaleDateString('en-PH', {
                weekday: 'long'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ── Map init ────────────────────────────────────────
        function initMiniMap() {
            if (!OFFICE_LAT || !OFFICE_LNG) return;
            const mapEl = document.getElementById('miniMap');
            if (!mapEl) return;

            miniMap = L.map('miniMap', {
                    zoomControl: false,
                    attributionControl: false
                })
                .setView([OFFICE_LAT, OFFICE_LNG], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

            // Office marker
            officeMarker = L.circle([OFFICE_LAT, OFFICE_LNG], {
                radius: OFFICE_RADIUS,
                color: '#157a30',
                fillColor: '#157a30',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(miniMap);

            L.marker([OFFICE_LAT, OFFICE_LNG]).addTo(miniMap)
                .bindPopup('Office / Shop Location');
        }
        initMiniMap();

        // ── Geolocation ─────────────────────────────────────
        function setLocStatus(type, text) {
            const el = document.getElementById('locStatus');
            el.className = `bio-loc bio-loc--${type}`;
            document.getElementById('locText').innerHTML = text;
        }

        function haversineMeters(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const φ1 = lat1 * Math.PI / 180,
                φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function updateButtons() {
            if (!IS_TODAY) return;

            const tiBtn = document.getElementById('timeInBtn');
            const toBtn = document.getElementById('timeOutBtn');
            if (!tiBtn || !toBtn) return;

            const canAct = locationValid || !REQUIRE_LOC;

            // Time In: enabled if not yet timed in AND location OK
            tiBtn.disabled = MY_TIMED_IN || !canAct;

            // Time Out: enabled if timed in but not yet timed out AND location OK
            toBtn.disabled = !MY_TIMED_IN || MY_TIMED_OUT || !canAct;

            if (MY_TIMED_IN && MY_TIMED_OUT) {
                document.getElementById('timeInLabel').textContent = 'Already Timed In';
                document.getElementById('timeOutLabel').textContent = 'Shift Complete ✓';
            }
        }

        function startGeolocation() {
            if (!navigator.geolocation) {
                setLocStatus('error', '<i class="bi bi-exclamation-triangle me-1"></i>Geolocation not supported');
                if (!REQUIRE_LOC) {
                    locationValid = true;
                    updateButtons();
                }
                return;
            }

            navigator.geolocation.watchPosition(
                pos => {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;

                    // Update map user marker
                    if (miniMap) {
                        if (userMarker) miniMap.removeLayer(userMarker);
                        userMarker = L.circleMarker([userLat, userLng], {
                            radius: 7,
                            fillColor: '#3b82f6',
                            color: '#fff',
                            fillOpacity: 1,
                            weight: 2
                        }).addTo(miniMap).bindPopup('Your Location');
                    }

                    if (OFFICE_LAT && OFFICE_LNG) {
                        const dist = Math.round(haversineMeters(userLat, userLng, OFFICE_LAT, OFFICE_LNG));
                        const inside = dist <= OFFICE_RADIUS;
                        locationValid = inside;

                        if (inside) {
                            setLocStatus('ok',
                                `<div class="bio-loc__dot"></div><span>Within range — ${dist}m from office ✓</span>`
                            );
                        } else {
                            setLocStatus('outside',
                                `<div class="bio-loc__dot"></div><span>Outside range — ${dist}m away (max ${OFFICE_RADIUS}m)</span>`
                            );
                        }
                    } else {
                        // No office set — location acquired but no fence to check
                        locationValid = true;
                        setLocStatus('ok',
                            `<div class="bio-loc__dot"></div><span>Location acquired (${userLat.toFixed(4)}, ${userLng.toFixed(4)})</span>`
                        );
                    }

                    updateButtons();
                },
                err => {
                    const msgs = {
                        1: 'Location permission denied.',
                        2: 'Location unavailable.',
                        3: 'Location timed out.'
                    };
                    setLocStatus('error',
                        `<i class="bi bi-exclamation-triangle me-1"></i>${msgs[err.code] || 'Location error.'}`);
                    if (!REQUIRE_LOC) {
                        locationValid = true;
                        updateButtons();
                    }
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }

        startGeolocation();
        updateButtons();

        // ── Time In ─────────────────────────────────────────
        function doTimeIn() {
            const btn = document.getElementById('timeInBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

            $.ajax({
                url: TIMEIN_URL,
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
                            title: '✅ Timed In!',
                            html: `<div style="font-size:1.2rem;font-weight:800;font-family:monospace;">${res.time_in}</div>
                           ${res.minutes_late > 0 ? `<div style="color:#f59e0b;margin-top:.35rem;">⚠ ${res.minutes_late} minutes late</div>` : ''}
                           ${!res.valid_location ? `<div style="color:#ef4444;margin-top:.35rem;">📍 Clocked in outside office radius</div>` : ''}`,
                            confirmButtonColor: '#157a30',
                            confirmButtonText: 'OK',
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-box-arrow-in-right btn-icon"></i><span>TIME IN</span>';
                    }
                },
                error(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed.'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-box-arrow-in-right btn-icon"></i><span>TIME IN</span>';
                }
            });
        }

        // ── Time Out ─────────────────────────────────────────
        function doTimeOut() {
            Swal.fire({
                title: 'Confirm Time Out?',
                text: 'Are you sure you want to clock out for today?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Clock Out'
            }).then(r => {
                if (!r.isConfirmed) return;

                const btn = document.getElementById('timeOutBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

                $.ajax({
                    url: TIMEOUT_URL,
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
                                title: '👋 Clocked Out!',
                                html: `<div style="font-size:1.2rem;font-weight:800;font-family:monospace;">${res.time_out}</div>
                               <div style="margin-top:.5rem;">Total: <strong>${res.hours_worked}h</strong> worked</div>`,
                                confirmButtonColor: '#157a30',
                                confirmButtonText: 'OK',
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-door-open btn-icon"></i><span>TIME OUT</span>';
                        }
                    }
                });
            });
        }

        // ── Live board polling (every 10s when today) ─────────
        function pollBoard() {
            $.get(POLL_URL, function(data) {
                // Update each employee card
                (data.records || {});
                Object.entries(data.records || {}).forEach(([profileId, att]) => {
                    const card = document.getElementById(`card-${profileId}`);
                    if (!card) return;

                    // Update card status class
                    card.className = `bio-card status-${att.status}`;

                    // Badge
                    const badge = document.getElementById(`badge-${profileId}`);
                    if (badge) {
                        badge.className = `bio-badge bio-badge--${att.status}`;
                        const labels = {
                            present: '✓ Present',
                            absent: '✗ Absent',
                            leave: '📅 On Leave',
                            not_in: '◌ Not In'
                        };
                        badge.textContent = labels[att.status] || att.status;
                    }

                    // Late badge
                    const lateBadge = document.getElementById(`late-${profileId}`);
                    if (att.minutes_late > 0) {
                        if (lateBadge) {
                            lateBadge.textContent = `⏰ Late ${att.minutes_late}min`;
                        }
                    }

                    // Times
                    ['tin', 'tout', 'hrs'].forEach(key => {
                        const el = document.getElementById(`${key}-${profileId}`);
                        if (!el) return;
                        if (key === 'tin') el.textContent = att.time_in || '—';
                        if (key === 'tout') el.textContent = att.time_out || '—';
                        if (key === 'hrs') el.textContent = att.hours_worked > 0 ? att
                            .hours_worked + 'h' : '—';
                    });

                    // Progress bar
                    const bar = document.getElementById(`bar-${profileId}`);
                    if (bar) {
                        const pct = att.hours_worked > 0 ? Math.min(100, (parseFloat(att.hours_worked) /
                            WORK_HOURS) * 100) : 0;
                        bar.style.width = pct + '%';
                    }

                    // List view
                    ['ltin', 'ltout', 'lhrs', 'llate', 'lot'].forEach(key => {
                        const el = document.getElementById(`${key}-${profileId}`);
                        if (!el) return;
                        if (key === 'ltin') el.textContent = att.time_in || '—';
                        if (key === 'ltout') el.textContent = att.time_out || '—';
                        if (key === 'lhrs') el.textContent = att.hours_worked > 0 ? att
                            .hours_worked + 'h' : '—';
                        if (key === 'llate') el.textContent = att.minutes_late > 0 ? att
                            .minutes_late + 'min' : '—';
                        if (key === 'lot') el.textContent = att.minutes_overtime > 0 ? (att
                            .minutes_overtime / 60).toFixed(2) + 'h' : '—';
                    });
                });

                // Update stats
                const recs = Object.values(data.records || {});
                document.getElementById('statPresent').textContent = recs.filter(r => r.status === 'present' || r
                    .status === 'half_day').length;
                document.getElementById('statLate').textContent = recs.filter(r => r.minutes_late > 0).length;
                document.getElementById('statOT').textContent = recs.filter(r => r.minutes_overtime > 0).length;
            });
        }

        if (IS_TODAY) {
            setInterval(pollBoard, 10000);
        }

        // ── View toggle ──────────────────────────────────────
        function setView(v) {
            document.getElementById('empGrid').style.display = v === 'grid' ? '' : 'none';
            document.getElementById('empList').style.display = v === 'list' ? '' : 'none';
            document.getElementById('btnGrid').classList.toggle('active', v === 'grid');
            document.getElementById('btnList').classList.toggle('active', v === 'list');
            localStorage.setItem('att_view', v);
        }

        // Restore last view preference
        const savedView = localStorage.getItem('att_view') || 'grid';
        setView(savedView);
    </script>
@endpush
