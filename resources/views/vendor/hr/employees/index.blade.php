@extends('layouts.vendor')
@section('title', 'HR — Employees')
@section('page-title', 'Employee Records')

@section('content')

    {{-- Stats --}}
    <div class="row g-2 gap-section">
        @foreach ([['Total', $stats['total'], 'green', 'bi-people'], ['Regular', $stats['regular'], 'blue', 'bi-patch-check'], ['Probationary', $stats['probationary'], 'yellow', 'bi-hourglass'], ['Archived', $stats['archived'], 'neutral', 'bi-archive']] as [$l, $v, $c, $i])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $c }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $c }}"><i class="bi {{ $i }}"></i></div>
                    <div class="vd-stat__value">{{ $v }}</div>
                    <div class="vd-stat__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Search by name or employee #…">
                </div>
                <select name="department" class="vd-select" style="width:auto;">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">Active Employees</option>
                    @foreach (['probationary', 'regular', 'contractual', 'part_time', 'resigned', 'terminated'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>
                @if (request()->hasAny(['search', 'department', 'status']))
                    <a href="{{ route('vendor.hr.employees.index') }}" class="vd-btn vd-btn--ghost"><i class="bi bi-x"></i>
                        Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-people"></i> Employees</span>
            <a href="{{ route('vendor.hr.employees.create') }}" class="vd-btn vd-btn--primary vd-btn--sm">
                <i class="bi bi-plus-lg"></i> Add Employee Record
            </a>
        </div>

        @if ($profiles->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Dept / Position</th>
                            <th>Status</th>
                            <th>Date Hired</th>
                            <th>Monthly Rate</th>
                            <th>Gov IDs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profiles as $profile)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="vd-user__avatar"
                                            style="width:36px;height:36px;font-size:0.78rem;flex-shrink:0;">
                                            {{ strtoupper(substr($profile->first_name, 0, 1) . substr($profile->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-600" style="font-size:.875rem;">{{ $profile->full_name }}</div>
                                            <div class="mono text-muted" style="font-size:.72rem;">
                                                {{ $profile->employee_number ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:.82rem;">{{ $profile->department?->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        {{ $profile->position?->title ?? '—' }}</div>
                                </td>
                                <td>
                                    @php $sc=['regular'=>'vd-badge--active','probationary'=>'vd-badge--warning','terminated'=>'vd-badge--danger']; @endphp
                                    <span class="vd-badge {{ $sc[$profile->employment_status] ?? 'vd-badge--neutral' }}"
                                        style="font-size:.68rem;">
                                        {{ ucfirst(str_replace('_', ' ', $profile->employment_status)) }}
                                    </span>
                                    @if ($profile->is_archived)
                                        <span class="vd-badge vd-badge--neutral" style="font-size:.65rem;">Archived</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:.82rem;">
                                    {{ $profile->date_hired?->format('M d, Y') ?? '—' }}</td>
                                <td class="mono fw-600" style="font-size:.82rem;">
                                    {{ $profile->monthly_rate ? '₱' . number_format($profile->monthly_rate, 2) : '—' }}
                                </td>
                                <td style="font-size:.72rem;color:var(--tx-muted);">
                                    <div>SSS: {{ $profile->sss_number ? '✓' : '—' }}</div>
                                    <div>PhilHealth: {{ $profile->philhealth_number ? '✓' : '—' }}</div>
                                    <div>Pag-IBIG: {{ $profile->pagibig_number ? '✓' : '—' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vendor.hr.employees.show', $profile) }}"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="View"><i
                                                class="bi bi-eye"></i></a>
                                        <a href="{{ route('vendor.hr.employees.edit', $profile) }}"
                                            class="vd-btn vd-btn--ghost vd-btn--icon" title="Edit"><i
                                                class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('vendor.hr.employees.archive', $profile) }}"
                                            style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="vd-btn vd-btn--ghost vd-btn--icon"
                                                title="{{ $profile->is_archived ? 'Restore' : 'Archive' }}"
                                                onclick="return confirm('{{ $profile->is_archived ? 'Restore' : 'Archive' }} this employee?')">
                                                <i
                                                    class="bi bi-{{ $profile->is_archived ? 'arrow-counterclockwise' : 'archive' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">{{ $profiles->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="empty-state"><i class="bi bi-people"></i>
                <h6>No employee records</h6>
                <a href="{{ route('vendor.hr.employees.create') }}" class="vd-btn vd-btn--primary d-inline-flex mt-2"><i
                        class="bi bi-plus-lg"></i> Add First Employee</a>
            </div>
        @endif
    </div>
@endsection
