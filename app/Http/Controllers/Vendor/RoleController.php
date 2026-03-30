<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function vendor()
    {
        return auth()->user()->vendor;
    }

    private function requireOwner(): void
    {
        if (! auth()->user()->isVendor()) {
            abort(403, 'Only the vendor owner can manage roles.');
        }
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function index()
    {
        $vendor = $this->vendor();

        // Vendor's custom roles + system roles (null vendor_id)
        $customRoles = Role::forVendor($vendor->id)
            ->withCount('employees')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $systemRoles = Role::whereNull('vendor_id')
            ->withCount('employees')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        // Permissions grouped by module for the create/edit form
        $permissionGroups = Permission::groupedByModule();

        return view('vendor.roles.index', compact('customRoles', 'systemRoles', 'permissionGroups'));
    }

    // ── Store (inline create from modal) ─────────────────────────────────

    public function store(Request $request)
    {
        $this->requireOwner();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $vendor = $this->vendor();

        // Prevent duplicate name within this vendor
        if (Role::forVendor($vendor->id)->where('name', $data['name'])->exists()) {
            return back()
                ->withErrors(['name' => 'A role with this name already exists.'])
                ->withInput();
        }

        $role = Role::create([
            'vendor_id'   => $vendor->id,
            'name'        => $data['name'],
            'slug'        => \Illuminate\Support\Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_system'   => false,
        ]);

        if (! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return redirect()
            ->route('vendor.roles.index')
            ->with('success', "Role \"{$role->name}\" created.");
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Role $role)
    {
        $this->requireOwner();
        $this->authorizeRole($role);

        // System roles: only permissions can be changed — name and slug are locked
        if ($role->is_system) {
            $data = $request->validate([
                'permissions'   => ['nullable', 'array'],
                'permissions.*' => ['exists:permissions,id'],
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return redirect()
                ->route('vendor.roles.index')
                ->with('success', "Permissions updated for system role \"{$role->name}\".");
        }

        // Custom roles — name, description, and permissions are all editable
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('vendor.roles.index')
            ->with('success', "Role \"{$role->name}\" updated.");
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function destroy(Role $role)
    {
        $this->requireOwner();
        $this->authorizeRole($role);

        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->employees()->count() > 0) {
            return back()->with('error', "Cannot delete \"{$role->name}\" — it is assigned to {$role->employees()->count()} employee(s). Remove the role from all employees first.");
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('vendor.roles.index')
            ->with('success', "Role \"{$role->name}\" deleted.");
    }

    // ── AJAX: permissions for a role (used in edit modal) ─────────────────

    public function permissions(Role $role)
    {
        $this->authorizeRole($role);

        return response()->json([
            'role'        => $role->only('id', 'name', 'description', 'is_system'),
            'permissions' => $role->permissions->pluck('id'),
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function authorizeRole(Role $role): void
    {
        // Allow system roles to be viewed but not modified
        if ($role->vendor_id !== null && $role->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }
}
