@extends('layouts.vendor')

@section('title', 'Edit: ' . $employee->full_name)
@section('page-title', 'Edit Employee')
@section('breadcrumb')
    <a href="{{ route('vendor.employees.index') }}" class="text-secondary">Employees</a>
    <span class="sep">›</span><span class="current">{{ $employee->full_name }}</span>
@endsection

@section('content')

    <div class="row g-3">

        {{-- ── Left: profile ── --}}
        <div class="col-12 col-xl-8">

            {{-- Profile form --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person"></i> Employee Profile</span>
                </div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.employees.update', $employee) }}" id="profileForm">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">First name <span class="req">*</span></label>
                                <input type="text" name="first_name"
                                    value="{{ old('first_name', $employee->first_name) }}"
                                    class="vd-input @error('first_name') is-invalid @enderror" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Last name <span class="req">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                                    class="vd-input @error('last_name') is-invalid @enderror" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone', $employee->phone) }}"
                                    class="vd-input" placeholder="+63 9XX XXX XXXX">
                            </div>
                            <div class="col-md-3">
                                <label class="vd-label">Employee code</label>
                                <input type="text" name="employee_code"
                                    value="{{ old('employee_code', $employee->employee_code) }}" class="vd-input">
                            </div>
                            <div class="col-md-3">
                                <label class="vd-label">Hire date</label>
                                <input type="date" name="hired_at"
                                    value="{{ old('hired_at', $employee->hired_at?->format('Y-m-d')) }}" class="vd-input">
                            </div>

                            {{-- Roles --}}
                            <div class="col-12">
                                <label class="vd-label" style="margin-bottom:0.6rem;">Assigned Roles</label>
                                <div
                                    style="border:1.5px solid var(--card-border);border-radius:var(--r-sm);
                                        padding:0.75rem 1rem;max-height:260px;overflow-y:auto;">
                                    @foreach ($roles as $role)
                                        <label
                                            style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.4rem 0;
                                              border-bottom:1px solid var(--n-100);cursor:pointer;">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                {{ in_array($role->id, old('roles', $assignedRoleIds)) ? 'checked' : '' }}
                                                style="accent-color:var(--g-500);width:15px;height:15px;margin-top:2px;">
                                            <div>
                                                <div class="fw-600" style="font-size:0.84rem;">
                                                    {{ $role->name }}
                                                    @if ($role->is_system)
                                                        <span class="vd-badge vd-badge--info ms-1"
                                                            style="font-size:0.63rem;">System</span>
                                                    @endif
                                                </div>
                                                @if ($role->description)
                                                    <div class="text-muted" style="font-size:0.74rem;">
                                                        {{ $role->description }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Active status --}}
                            <div class="col-12">
                                <label
                                    style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.875rem;">
                                    <input type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                        style="accent-color:var(--g-500);width:16px;height:16px;">
                                    <span>Account is active (employee can log in)</span>
                                </label>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="vd-btn vd-btn--primary" id="saveProfileBtn">
                                    <i class="bi bi-check-lg"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Reset password --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-key"></i> Reset Password</span>
                </div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.employees.reset-password', $employee) }}" id="pwForm">
                        @csrf @method('PATCH')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">New password <span class="req">*</span></label>
                                <div style="position:relative;">
                                    <input type="password" name="password" id="newPw"
                                        class="vd-input @error('password') is-invalid @enderror"
                                        placeholder="Min 8 chars, mixed case + number" required>
                                    <button type="button" onclick="togglePw('newPw','eyeNew')" id="eyeNew"
                                        style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
                                               background:none;border:none;cursor:pointer;color:var(--tx-muted);">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Confirm new password <span class="req">*</span></label>
                                <input type="password" name="password_confirmation" class="vd-input"
                                    placeholder="Repeat new password" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="vd-btn vd-btn--warning">
                                    <i class="bi bi-key"></i> Reset Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── Right: info card ── --}}
        <div class="col-12 col-xl-4">

            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title">Account Details</span>
                </div>
                <div class="vd-card__body">

                    {{-- Avatar --}}
                    <div class="text-center mb-3">
                        <div class="vd-user__avatar mx-auto"
                            style="width:60px;height:60px;font-size:1.2rem;font-weight:700;
                                border-radius:50%;background:var(--g-100);border:2px solid var(--g-300);
                                display:flex;align-items:center;justify-content:center;color:var(--g-700);">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </div>
                        <div class="fw-700 mt-2" style="font-family:'Outfit',sans-serif;">{{ $employee->full_name }}
                        </div>
                        <div class="text-muted" style="font-size:0.8rem;">{{ $employee->user->email }}</div>
                        <div class="mt-2">
                            <span class="vd-badge {{ $employee->is_active ? 'vd-badge--active' : 'vd-badge--neutral' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="detail-grid">
                        <div>
                            <div class="detail-item__label">Employee ID</div>
                            <div class="detail-item__value mono">{{ $employee->employee_code ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Hired</div>
                            <div class="detail-item__value" style="font-size:0.82rem;">
                                {{ $employee->hired_at?->format('M d, Y') ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Roles assigned</div>
                            <div class="detail-item__value">{{ $employee->roles->count() }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Added</div>
                            <div class="detail-item__value" style="font-size:0.82rem;">
                                {{ $employee->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions preview --}}
            @php $permissions = $employee->getPermissions(); @endphp
            @if ($permissions->count())
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-shield-check"></i> Effective Permissions</span>
                        <span class="vd-card__subtitle">{{ $permissions->count() }} total</span>
                    </div>
                    <div class="vd-card__body" style="padding-bottom:0.5rem;">
                        @php $grouped = $permissions->groupBy('module'); @endphp
                        @foreach ($grouped as $module => $perms)
                            <div style="margin-bottom:0.85rem;">
                                <div
                                    style="font-size:0.68rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:0.07em;color:var(--tx-muted);margin-bottom:0.35rem;">
                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($perms as $perm)
                                        <span class="vd-badge vd-badge--active" style="font-size:0.63rem;">
                                            {{ $perm->action }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Danger zone --}}
            <div class="vd-card" style="border-color:var(--red-100);">
                <div class="vd-card__header" style="background:var(--red-50);">
                    <span class="vd-card__title" style="color:var(--red-600);">
                        <i class="bi bi-exclamation-triangle"></i> Danger Zone
                    </span>
                </div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.employees.destroy', $employee) }}" id="deleteForm">
                        @csrf @method('DELETE')
                        <button type="button" class="vd-btn vd-btn--danger vd-btn--full" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> Remove Employee
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function togglePw(inputId, btnId) {
            const i = document.getElementById(inputId);
            const b = document.getElementById(btnId).querySelector('i');
            i.type = i.type === 'password' ? 'text' : 'password';
            b.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.getElementById('profileForm').addEventListener('submit', () => {
            const btn = document.getElementById('saveProfileBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
        });

        function confirmDelete() {
            Swal.fire({
                title: 'Remove {{ addslashes($employee->full_name) }}?',
                text: 'Their account will be deactivated. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (r.isConfirmed) document.getElementById('deleteForm').submit();
            });
        }
    </script>
@endpush
