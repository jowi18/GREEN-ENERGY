@extends('layouts.vendor')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')

    <div class="row g-3">

        {{-- ── Left: role list ── --}}
        <div class="col-12 col-xl-7">

            {{-- Custom roles --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person-lock"></i> Your Custom Roles</span>
                    @if (auth()->user()->isVendor())
                        <button class="vd-btn vd-btn--primary vd-btn--sm" data-bs-toggle="modal"
                            data-bs-target="#createRoleModal">
                            <i class="bi bi-plus-lg"></i> New Role
                        </button>
                    @endif
                </div>

                @if ($customRoles->count())
                    <div class="vd-card__body" style="padding:0;">
                        @foreach ($customRoles as $role)
                            <div
                                style="padding:1rem 1.25rem;border-bottom:1px solid var(--n-100);
                            {{ $loop->last ? 'border-bottom:none;' : '' }}">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div style="flex:1;min-width:0;">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-700" style="font-family:'Outfit',sans-serif;font-size:0.92rem;">
                                                {{ $role->name }}
                                            </span>
                                            <span class="vd-badge vd-badge--neutral" style="font-size:0.65rem;">
                                                {{ $role->employees_count }}
                                                employee{{ $role->employees_count !== 1 ? 's' : '' }}
                                            </span>
                                            <span class="vd-badge vd-badge--active" style="font-size:0.65rem;">
                                                {{ $role->permissions->count() }} permissions
                                            </span>
                                        </div>
                                        @if ($role->description)
                                            <p class="text-muted mt-1 mb-0" style="font-size:0.8rem;">
                                                {{ $role->description }}</p>
                                        @endif

                                        {{-- Permission chips grouped by module --}}
                                        @if ($role->permissions->count())
                                            @php $grouped = $role->permissions->groupBy('module'); @endphp
                                            <div class="mt-2">
                                                @foreach ($grouped as $module => $perms)
                                                    <div class="d-inline-flex align-items-center gap-1 flex-wrap me-3 mb-1">
                                                        <span
                                                            style="font-size:0.67rem;font-weight:700;text-transform:uppercase;
                                                 letter-spacing:0.06em;color:var(--tx-muted);">
                                                            {{ ucwords(str_replace('_', ' ', $module)) }}:
                                                        </span>
                                                        @foreach ($perms as $perm)
                                                            <span class="vd-badge vd-badge--info"
                                                                style="font-size:0.62rem;">
                                                                {{ $perm->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    @if (auth()->user()->isVendor())
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <button class="vd-btn vd-btn--ghost vd-btn--icon" title="Edit"
                                                onclick="openEditRole({{ $role->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="vd-btn vd-btn--danger vd-btn--icon" title="Delete"
                                                onclick="confirmDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}', {{ $role->employees_count }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-person-lock"></i>
                        <h6>No custom roles yet</h6>
                        <p>Create roles to control what each employee can access.</p>
                    </div>
                @endif
            </div>

            {{-- System roles (read-only) --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-shield"></i> System Roles</span>
                    <span class="vd-card__subtitle">Built-in — name is locked, permissions are editable</span>
                </div>
                <div class="vd-card__body" style="padding:0;">
                    @foreach ($systemRoles as $role)
                        <div
                            style="padding:0.9rem 1.25rem;border-bottom:1px solid var(--n-100);
                            {{ $loop->last ? 'border-bottom:none;' : '' }}">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-600" style="font-size:0.875rem;">{{ $role->name }}</span>
                                <span class="vd-badge vd-badge--info" style="font-size:0.65rem;">System</span>
                                <span class="vd-badge vd-badge--neutral" style="font-size:0.65rem;">
                                    {{ $role->permissions->count() }}
                                    permission{{ $role->permissions->count() !== 1 ? 's' : '' }}
                                </span>
                                @if (auth()->user()->isVendor())
                                    <button class="vd-btn vd-btn--ghost vd-btn--sm ms-auto"
                                        onclick="openEditRole({{ $role->id }})" title="Edit permissions">
                                        <i class="bi bi-key"></i> Edit Permissions
                                    </button>
                                @endif
                            </div>
                            @if ($role->description)
                                <p class="text-muted mt-1 mb-0" style="font-size:0.78rem;">{{ $role->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── Right: permission reference ── --}}
        <div class="col-12 col-xl-5">
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-key"></i> All Available Permissions</span>
                    <span class="vd-card__subtitle">{{ $permissionGroups->flatten()->count() }} total</span>
                </div>
                <div class="vd-card__body" style="padding:0;">
                    @foreach ($permissionGroups as $module => $permissions)
                        <div style="padding:0.9rem 1.25rem;border-bottom:1px solid var(--n-100);">
                            <div
                                style="font-size:0.7rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:0.08em;color:var(--tx-muted);margin-bottom:0.5rem;">
                                {{ ucwords(str_replace('_', ' ', $module)) }}
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($permissions as $perm)
                                    <span class="vd-badge vd-badge--neutral" style="font-size:0.67rem;"
                                        title="{{ $perm->description ?? $perm->name }}">
                                        {{ $perm->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ════ Create role modal ════ --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.roles.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">
                            <i class="bi bi-plus-circle me-2 text-green"></i>Create New Role
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="vd-label">Role name <span class="req">*</span></label>
                                <input type="text" name="name" class="vd-input" placeholder="e.g. Cashier, Technician"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Description</label>
                                <input type="text" name="description" class="vd-input"
                                    placeholder="Brief description of this role">
                            </div>
                        </div>

                        <label class="vd-label" style="margin-bottom:0.75rem;">
                            Permissions
                            <span class="text-muted fw-400" style="font-size:0.75rem;"> — check what this role can
                                do</span>
                        </label>

                        @foreach ($permissionGroups as $module => $permissions)
                            <div style="margin-bottom:1.1rem;">
                                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.45rem;">
                                    <span
                                        style="font-size:0.7rem;font-weight:700;text-transform:uppercase;
                                         letter-spacing:0.08em;color:var(--tx-muted);">
                                        {{ ucwords(str_replace('_', ' ', $module)) }}
                                    </span>
                                    <label
                                        style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;
                                          font-size:0.72rem;color:var(--g-600);font-weight:600;">
                                        <input type="checkbox" class="module-toggle" data-module="{{ $module }}"
                                            style="accent-color:var(--g-500);width:13px;height:13px;">
                                        All
                                    </label>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($permissions as $perm)
                                        <label
                                            style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;
                                          background:var(--n-50);border:1px solid var(--card-border);
                                          border-radius:var(--r-sm);padding:0.28rem 0.6rem;
                                          font-size:0.78rem;font-weight:500;transition:border-color 0.15s;">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                class="perm-check perm-module-{{ $module }}"
                                                style="accent-color:var(--g-500);width:13px;height:13px;">
                                            {{ $perm->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-check-lg"></i> Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ Edit role modal ════ --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" id="editRoleForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-700" id="editRoleModalTitle">
                            <i class="bi bi-pencil me-2 text-green"></i>Edit Role
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- System role notice (shown/hidden by JS) --}}
                        <div id="systemRoleNotice"
                            style="display:none;background:var(--y-50);border:1px solid var(--y-200);
                                border-radius:var(--r-sm);padding:0.7rem 0.9rem;
                                font-size:0.82rem;color:var(--y-700);margin-bottom:1.1rem;">
                            <i class="bi bi-lock-fill me-1"></i>
                            <strong>System role</strong> — the name is locked and cannot be changed.
                            You can freely adjust which permissions this role grants.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="vd-label">Role name</label>
                                <input type="text" name="name" id="editRoleName" class="vd-input" required>
                                <div id="editRoleNameHint"
                                    style="display:none;font-size:0.72rem;color:var(--tx-muted);margin-top:0.25rem;">
                                    <i class="bi bi-lock me-1"></i>Cannot be changed for system roles
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="vd-label">Description</label>
                                <input type="text" name="description" id="editRoleDesc" class="vd-input">
                            </div>
                        </div>

                        <label class="vd-label" style="margin-bottom:0.75rem;">Permissions
                            <span class="text-muted fw-400" style="font-size:0.75rem;"> — check what this role can
                                do</span>
                        </label>

                        @foreach ($permissionGroups as $module => $permissions)
                            <div style="margin-bottom:1.1rem;">
                                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.45rem;">
                                    <span
                                        style="font-size:0.7rem;font-weight:700;text-transform:uppercase;
                                         letter-spacing:0.08em;color:var(--tx-muted);">
                                        {{ ucwords(str_replace('_', ' ', $module)) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($permissions as $perm)
                                        <label
                                            style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;
                                          background:var(--n-50);border:1px solid var(--card-border);
                                          border-radius:var(--r-sm);padding:0.28rem 0.6rem;
                                          font-size:0.78rem;font-weight:500;">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                class="edit-perm-check" id="edit-perm-{{ $perm->id }}"
                                                style="accent-color:var(--g-500);width:13px;height:13px;">
                                            {{ $perm->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete form (hidden) --}}
    <form method="POST" id="deleteRoleForm" style="display:none;">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
    <script>
        /* ── Module "All" toggle ──────────────────────── */
        document.querySelectorAll('.module-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const checks = document.querySelectorAll(`.perm-module-${this.dataset.module}`);
                checks.forEach(c => c.checked = this.checked);
            });
        });

        /* ── Open edit modal & load permissions ─────── */
        function openEditRole(roleId) {
            // Reset all checkboxes
            document.querySelectorAll('.edit-perm-check').forEach(c => c.checked = false);

            $.get(`/vendor/roles/${roleId}/permissions`, function(data) {
                const isSystem = data.role.is_system;

                // Modal title
                document.getElementById('editRoleModalTitle').innerHTML =
                    `<i class="bi bi-${isSystem ? 'key' : 'pencil'} me-2 text-green"></i>` +
                    (isSystem ? 'Edit Permissions — ' + data.role.name : 'Edit Role');

                // Name field — lock if system role
                const nameInput = document.getElementById('editRoleName');
                nameInput.value = data.role.name;
                nameInput.disabled = isSystem;
                nameInput.style.opacity = isSystem ? '0.55' : '';
                nameInput.style.cursor = isSystem ? 'not-allowed' : '';
                document.getElementById('editRoleNameHint').style.display = isSystem ? '' : 'none';

                // Description — also lock for system roles
                const descInput = document.getElementById('editRoleDesc');
                descInput.value = data.role.description ?? '';
                descInput.disabled = isSystem;
                descInput.style.opacity = isSystem ? '0.55' : '';

                // System notice banner
                document.getElementById('systemRoleNotice').style.display = isSystem ? '' : 'none';

                // Form action
                document.getElementById('editRoleForm').action = `/vendor/roles/${roleId}`;

                // Check assigned permissions
                data.permissions.forEach(id => {
                    const cb = document.getElementById(`edit-perm-${id}`);
                    if (cb) cb.checked = true;
                });

                new bootstrap.Modal(document.getElementById('editRoleModal')).show();
            });
        }

        /* ── Delete role ─────────────────────────────── */
        function confirmDeleteRole(id, name, empCount) {
            if (empCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot delete role',
                    text: `"${name}" is assigned to ${empCount} employee${empCount !== 1 ? 's' : ''}. Remove it from all employees first.`,
                    confirmButtonColor: '#157a30',
                });
                return;
            }

            Swal.fire({
                title: `Delete "${name}"?`,
                text: 'This role will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                const f = document.getElementById('deleteRoleForm');
                f.action = `/vendor/roles/${id}`;
                f.submit();
            });
        }
    </script>
@endpush
