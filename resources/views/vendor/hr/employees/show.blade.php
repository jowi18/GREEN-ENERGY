@extends('layouts.vendor')
@section('title', $profile->full_name)
@section('page-title', 'Employee Profile')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.employees.index') }}" class="text-secondary">
        HR Records</a>
    <span class="sep">›</span><span class="current">{{ $profile->full_name }}</span>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div
                style="width:56px;height:56px;border-radius:50%;background:var(--g-100);color:var(--g-700);
                    display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;">
                {{ strtoupper(substr($profile->first_name, 0, 1) . substr($profile->last_name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:1.1rem;font-weight:800;font-family:'Outfit',sans-serif;">{{ $profile->full_name }}
                </div>
                <div class="text-muted" style="font-size:.82rem;">
                    {{ $profile->position?->title ?? '—' }} · {{ $profile->department?->name ?? 'No Department' }}
                    @if ($profile->employee_number)
                        · <span class="mono">{{ $profile->employee_number }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            @php $sc=['regular'=>'vd-badge--active','probationary'=>'vd-badge--warning','terminated'=>'vd-badge--danger','resigned'=>'vd-badge--neutral']; @endphp
            <span
                class="vd-badge {{ $sc[$profile->employment_status] ?? 'vd-badge--neutral' }}">{{ ucfirst(str_replace('_', ' ', $profile->employment_status)) }}</span>
            <a href="{{ route('vendor.hr.employees.edit', $profile) }}" class="vd-btn vd-btn--primary vd-btn--sm"><i
                    class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('vendor.hr.employees.archive', $profile) }}">
                @csrf @method('PATCH')
                <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm"
                    onclick="return confirm('{{ $profile->is_archived ? 'Restore' : 'Archive' }} this employee?')">
                    <i class="bi bi-{{ $profile->is_archived ? 'arrow-counterclockwise' : 'archive' }}"></i>
                    {{ $profile->is_archived ? 'Restore' : 'Archive' }}
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">

            {{-- Personal Info --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-person"></i> Personal
                        Information</span></div>
                <div class="vd-card__body">
                    <div class="row g-3" style="font-size:.875rem;">
                        @php
                            $fields = [
                                'Sex' => ucfirst($profile->sex ?? '—'),
                                'Birth Date' => $profile->birth_date?->format('F d, Y') ?? '—',
                                'Birth Place' => $profile->birth_place ?? '—',
                                'Civil Status' => ucfirst($profile->civil_status ?? '—'),
                                'Blood Type' => $profile->blood_type ?? '—',
                                'Citizenship' => $profile->citizenship ?? '—',
                                'Phone' => $profile->phone ?? '—',
                                'Personal Email' => $profile->personal_email ?? '—',
                            ];
                        @endphp
                        @foreach ($fields as $label => $value)
                            <div class="col-6 col-md-4">
                                <div class="text-muted"
                                    style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                    {{ $label }}</div>
                                <div style="font-weight:600;">{{ $value }}</div>
                            </div>
                        @endforeach
                        <div class="col-12 col-md-6">
                            <div class="text-muted"
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                Permanent Address</div>
                            <div>{{ $profile->address_permanent ?? '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted"
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                Present Address</div>
                            <div>{{ $profile->address_present ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Employment & Compensation --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-briefcase"></i> Employment &
                        Compensation</span></div>
                <div class="vd-card__body">
                    <div class="row g-3" style="font-size:.875rem;">
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">Date
                                Hired</div>
                            <div class="fw-600">{{ $profile->date_hired?->format('M d, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                Regularized</div>
                            <div class="fw-600">{{ $profile->date_regularized?->format('M d, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                Employment Type</div>
                            <div class="fw-600">{{ ucfirst(str_replace('_', ' ', $profile->employment_type)) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">Work
                                Schedule</div>
                            <div class="fw-600">{{ $profile->work_hours_per_day }}h / {{ $profile->work_days_per_week }}d
                                per week</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                Monthly Rate</div>
                            <div class="fw-600 text-green">₱{{ number_format($profile->monthly_rate, 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">Daily
                                Rate</div>
                            <div class="fw-600">₱{{ number_format($profile->daily_rate, 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                Hourly Rate</div>
                            <div class="fw-600">₱{{ number_format($profile->hourly_rate, 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">Pay
                                Frequency</div>
                            <div class="fw-600">{{ ucfirst(str_replace('_', ' ', $profile->pay_frequency)) }}</div>
                        </div>
                    </div>
                    @if ($profile->allowances)
                        <div class="mt-3">
                            <div class="text-muted mb-1" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                Allowances</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($profile->allowances as $a)
                                    <span class="vd-badge vd-badge--active" style="font-size:.72rem;">{{ $a['name'] }}:
                                        ₱{{ number_format($a['amount'], 2) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Government IDs --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-id-card"></i> Government
                        IDs</span></div>
                <div class="vd-card__body">
                    <div class="row g-3" style="font-size:.875rem;">
                        @foreach (['SSS Number' => $profile->sss_number, 'PhilHealth Number' => $profile->philhealth_number, 'Pag-IBIG Number' => $profile->pagibig_number, 'TIN' => $profile->tin_number] as $label => $value)
                            <div class="col-6 col-md-3">
                                <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                    {{ $label }}</div>
                                <div class="mono fw-600">{{ $value ?? '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Emergency Contacts --}}
            @if ($profile->emergencyContacts->count())
                <div class="vd-card gap-section">
                    <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-telephone-outbound"></i>
                            Emergency Contacts</span></div>
                    <div class="vd-card__body" style="padding:0;">
                        @foreach ($profile->emergencyContacts as $ec)
                            <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--n-100);">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="fw-600">{{ $ec->name }} @if ($ec->is_primary)
                                                <span class="vd-badge vd-badge--active"
                                                    style="font-size:.62rem;">Primary</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem;">{{ $ec->relationship ?? '' }}
                                            @if ($ec->phone)
                                                · {{ $ec->phone }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Employment History --}}
            @if ($profile->employmentHistory->count())
                <div class="vd-card">
                    <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-clock-history"></i>
                            Employment History</span></div>
                    <div class="vd-card__body" style="padding:0;">
                        @foreach ($profile->employmentHistory as $h)
                            <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--n-100);">
                                <div class="fw-600" style="font-size:.875rem;">{{ $h->company }}</div>
                                <div class="text-muted" style="font-size:.78rem;">
                                    {{ $h->position ?? '' }}
                                    @if ($h->date_from)
                                        · {{ $h->date_from->format('M Y') }} –
                                        {{ $h->date_to?->format('M Y') ?? 'Present' }}
                                    @endif
                                </div>
                                @if ($h->reason_for_leaving)
                                    <div class="text-muted" style="font-size:.75rem;">Reason:
                                        {{ $h->reason_for_leaving }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- Right sidebar --}}
        <div class="col-12 col-xl-4">

            {{-- Recent attendance --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-calendar-check"></i> Recent
                        Attendance</span></div>
                @if ($profile->attendance->count())
                    <div style="padding:0;">
                        @foreach ($profile->attendance->take(10) as $att)
                            @php $sc=['present'=>'vd-badge--active','absent'=>'vd-badge--danger','leave'=>'vd-badge--info','half_day'=>'vd-badge--warning']; @endphp
                            <div
                                style="padding:.6rem 1rem;border-bottom:1px solid var(--n-100);display:flex;align-items:center;justify-content:space-between;font-size:.78rem;">
                                <div>
                                    <div class="fw-600">{{ $att->attendance_date->format('M d, D') }}</div>
                                    <div class="text-muted">{{ $att->time_in?->format('h:i A') ?? '—' }} –
                                        {{ $att->time_out?->format('h:i A') ?? '—' }}</div>
                                </div>
                                <span class="vd-badge {{ $sc[$att->status] ?? 'vd-badge--neutral' }}"
                                    style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $att->status)) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding:1.5rem;">
                        <p>No attendance records.</p>
                    </div>
                @endif
            </div>

            {{-- Leave requests --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-calendar-x"></i> Leave
                        Requests</span></div>
                @if ($profile->leaveRequests->count())
                    @foreach ($profile->leaveRequests->take(5) as $lr)
                        @php $sc=['pending'=>'vd-badge--pending','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger','cancelled'=>'vd-badge--neutral']; @endphp
                        <div
                            style="padding:.6rem 1rem;border-bottom:1px solid var(--n-100);font-size:.78rem;display:flex;align-items:center;justify-content:space-between;">
                            <div>
                                <div class="fw-600">{{ $lr->leaveType?->name ?? 'Leave' }}</div>
                                <div class="text-muted">{{ $lr->date_from->format('M d') }} –
                                    {{ $lr->date_to->format('M d, Y') }}</div>
                            </div>
                            <span class="vd-badge {{ $sc[$lr->status] }}"
                                style="font-size:.65rem;">{{ ucfirst($lr->status) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state" style="padding:1.5rem;">
                        <p>No leave requests.</p>
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            @if ($profile->notes)
                <div class="vd-card">
                    <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-sticky"></i> Notes</span>
                    </div>
                    <div class="vd-card__body">
                        <p style="font-size:.875rem;color:var(--tx-secondary);">{{ $profile->notes }}</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
