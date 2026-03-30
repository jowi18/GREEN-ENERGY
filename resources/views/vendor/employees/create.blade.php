@extends('layouts.vendor')

@section('title', 'Add Employee')
@section('page-title', 'Add Employee')
@section('breadcrumb')
    <a href="{{ route('vendor.employees.index') }}" class="text-secondary">Employees</a>
    <span class="sep">›</span><span class="current">New Employee</span>
@endsection

@section('content')

    <form method="POST" action="{{ route('vendor.employees.store') }}" id="empForm">
        @csrf

        <div class="row g-3">

            {{-- ── Left: personal + account ── --}}
            <div class="col-12 col-xl-8">

                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-person"></i> Personal Information</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="vd-label">First name <span class="req">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}"
                                    class="vd-input @error('first_name') is-invalid @enderror" placeholder="Juan" required
                                    autofocus>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Last name <span class="req">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                    class="vd-input @error('last_name') is-invalid @enderror" placeholder="Dela Cruz"
                                    required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Mobile number</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="vd-input"
                                    placeholder="+63 9XX XXX XXXX">
                            </div>
                            <div class="col-md-3">
                                <label class="vd-label">Employee code</label>
                                <input readonly type="text" name="employee_code" value="{{ old('employee_code') }}"
                                    class="vd-input" placeholder="EMP-001">
                                <div class="form-hint">Internal reference ID.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="vd-label">Hire date</label>
                                <input type="date" name="hired_at" value="{{ old('hired_at', date('Y-m-d')) }}"
                                    class="vd-input">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-lock"></i> Login Credentials</span>
                        <span class="vd-card__subtitle">The employee will use these to sign in</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="vd-label">Email address <span class="req">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="vd-input @error('email') is-invalid @enderror"
                                    placeholder="employee@yourbusiness.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Password <span class="req">*</span></label>
                                <div style="position:relative;">
                                    <input type="password" name="password" id="pw"
                                        class="vd-input @error('password') is-invalid @enderror"
                                        placeholder="Min 8 chars, mixed case + number" required>
                                    <button type="button" onclick="togglePw('pw','pwBtn')" id="pwBtn"
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
                                <label class="vd-label">Confirm password <span class="req">*</span></label>
                                <input type="password" name="password_confirmation" class="vd-input"
                                    placeholder="Repeat password" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Right: roles + submit ── --}}
            <div class="col-12 col-xl-4">

                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-person-lock"></i> Assign Roles</span>
                        <a href="{{ route('vendor.roles.index') }}" class="vd-btn vd-btn--ghost vd-btn--sm">
                            Manage roles
                        </a>
                    </div>
                    <div class="vd-card__body">
                        @if ($roles->count())
                            @foreach ($roles as $role)
                                <label
                                    style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.55rem 0;
                                  border-bottom:1px solid var(--n-100);cursor:pointer;">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                        {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                        style="accent-color:var(--g-500);width:15px;height:15px;margin-top:2px;flex-shrink:0;">
                                    <div>
                                        <div class="fw-600" style="font-size:0.845rem;">{{ $role->name }}</div>
                                        @if ($role->description)
                                            <div class="text-muted" style="font-size:0.75rem;margin-top:1px;">
                                                {{ $role->description }}</div>
                                        @endif
                                        {{-- Permission count badge --}}
                                        <div style="margin-top:0.3rem;">
                                            <span class="vd-badge vd-badge--neutral" style="font-size:0.65rem;">
                                                {{ $role->permissions->count() }}
                                                permission{{ $role->permissions->count() !== 1 ? 's' : '' }}
                                            </span>
                                            @if ($role->is_system)
                                                <span class="vd-badge vd-badge--info ms-1"
                                                    style="font-size:0.65rem;">System</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <div class="empty-state" style="padding:1.5rem 0;">
                                <i class="bi bi-person-lock"></i>
                                <p>No roles yet. <a href="{{ route('vendor.roles.index') }}">Create roles</a> first.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="vd-card">
                    <div class="vd-card__body">
                        <button type="submit" class="vd-btn vd-btn--primary vd-btn--full" id="saveBtn">
                            <i class="bi bi-person-plus"></i> Create Employee
                        </button>
                        <a href="{{ route('vendor.employees.index') }}"
                            class="vd-btn vd-btn--ghost vd-btn--full mt-2">Cancel</a>
                    </div>
                </div>

            </div>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function generateEmployeeCode() {
                const year = new Date().getFullYear();
                const randomNumber = Math.floor(100000 + Math.random() * 900000);
                return `EMP${year}-${randomNumber}`;
            }

            $('input[name="employee_code"]').val(generateEmployeeCode());

        });

        function togglePw(inputId, btnId) {
            const i = document.getElementById(inputId);
            const b = document.getElementById(btnId).querySelector('i');
            i.type = i.type === 'password' ? 'text' : 'password';
            b.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.getElementById('empForm').addEventListener('submit', () => {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating…';
        });
    </script>
@endpush
