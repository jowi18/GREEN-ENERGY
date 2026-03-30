@extends('layouts.vendor')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')

    {{-- ── Stats ── --}}
    <div class="row g-3 gap-section">
        <div class="col-6 col-md-4">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-people"></i></div>
                <div class="vd-stat__value">{{ $stats['total'] }}</div>
                <div class="vd-stat__label">Total Employees</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="vd-stat vd-stat--green">
                <div class="vd-stat__icon vd-stat__icon--green"><i class="bi bi-person-check"></i></div>
                <div class="vd-stat__value">{{ $stats['active'] }}</div>
                <div class="vd-stat__label">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="vd-stat vd-stat--yellow">
                <div class="vd-stat__icon vd-stat__icon--yellow"><i class="bi bi-person-dash"></i></div>
                <div class="vd-stat__value">{{ $stats['inactive'] }}</div>
                <div class="vd-stat__label">Inactive</div>
            </div>
        </div>
    </div>

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h6 class="fw-700 mb-0 outfit" style="font-size:0.95rem;">Team Members</h6>
            <p class="text-muted" style="font-size:0.8rem;margin-top:2px;">
                Employees can log in and access the portal based on their assigned roles.
            </p>
        </div>
        @if (auth()->user()->isVendor())
            <a href="{{ route('vendor.employees.create') }}" class="vd-btn vd-btn--primary">
                <i class="bi bi-person-plus"></i> Add Employee
            </a>
        @endif
    </div>

    {{-- ── Employees table ── --}}
    <div class="vd-card">
        @if ($employees->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Contact</th>
                            <th>Roles</th>
                            <th>Code</th>
                            <th>Hired</th>
                            <th>Status</th>
                            @if (auth()->user()->isVendor())
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $emp)
                            <tr id="emp-row-{{ $emp->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="vd-user__avatar"
                                            style="width:36px;height:36px;font-size:0.8rem;flex-shrink:0;">
                                            {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $emp->full_name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem;">{{ $emp->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $emp->phone ?? '—' }}
                                </td>
                                <td>
                                    @forelse($emp->roles as $role)
                                        <span class="vd-badge vd-badge--{{ $role->is_system ? 'info' : 'active' }}"
                                            style="font-size:0.68rem;margin:0.1rem;">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted" style="font-size:0.8rem;">No roles</span>
                                    @endforelse
                                </td>
                                <td class="mono text-secondary" style="font-size:0.8rem;">
                                    {{ $emp->employee_code ?? '—' }}
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $emp->hired_at ? \Carbon\Carbon::parse($emp->hired_at)->format('M d, Y') : '—' }}
                                </td>
                                <td>
                                    <span class="vd-badge {{ $emp->is_active ? 'vd-badge--active' : 'vd-badge--neutral' }}"
                                        id="emp-badge-{{ $emp->id }}">
                                        {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @if (auth()->user()->isVendor())
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('vendor.employees.edit', $emp) }}"
                                                class="vd-btn vd-btn--ghost vd-btn--icon" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="vd-btn vd-btn--ghost vd-btn--icon"
                                                title="{{ $emp->is_active ? 'Deactivate' : 'Activate' }}"
                                                onclick="toggleEmployee({{ $emp->id }}, this)">
                                                <i
                                                    class="bi bi-{{ $emp->is_active ? 'person-dash' : 'person-check' }}"></i>
                                            </button>
                                            <button class="vd-btn vd-btn--danger vd-btn--icon" title="Remove"
                                                onclick="confirmDelete({{ $emp->id }}, '{{ addslashes($emp->full_name) }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
                    </span>
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h6>No employees yet</h6>
                <p>Add your first team member to give them portal access.</p>
                @if (auth()->user()->isVendor())
                    <a href="{{ route('vendor.employees.create') }}" class="vd-btn vd-btn--primary mt-3 d-inline-flex">
                        <i class="bi bi-person-plus"></i> Add Employee
                    </a>
                @endif
            </div>
        @endif
    </div>

    <form method="POST" id="deleteForm" style="display:none;">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
    <script>
        function toggleEmployee(id, btn) {
            $.post(`/vendor/employees/${id}/toggle`, {}, function(res) {
                if (!res.success) return;
                const badge = document.getElementById(`emp-badge-${id}`);
                const icon = btn.querySelector('i');
                if (res.is_active) {
                    badge.className = 'vd-badge vd-badge--active';
                    badge.textContent = 'Active';
                    icon.className = 'bi bi-person-dash';
                    btn.title = 'Deactivate';
                } else {
                    badge.className = 'vd-badge vd-badge--neutral';
                    badge.textContent = 'Inactive';
                    icon.className = 'bi bi-person-check';
                    btn.title = 'Activate';
                }
                Swal.fire({
                    icon: 'success',
                    text: res.message,
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Remove employee?',
                text: `${name}'s account will be deactivated and removed from your team.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                const f = document.getElementById('deleteForm');
                f.action = `/vendor/employees/${id}`;
                f.submit();
            });
        }
    </script>
@endpush
