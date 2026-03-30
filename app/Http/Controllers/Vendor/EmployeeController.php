<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeController extends Controller
{
    private function vendor()
    {
        return auth()->user()->vendor;
    }

    private function requireOwner(): void
    {
        if (! auth()->user()->isVendor()) {
            abort(403, 'Only the vendor owner can manage employees.');
        }
    }

    private function authorizeEmployee(Employee $employee): void
    {
        if ($employee->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function index()
    {
        $vendor = $this->vendor();

        $employees = Employee::with(['user', 'roles'])
            ->forVendor($vendor->id)
            ->latest()
            ->paginate(20);

        $stats = [
            'total'    => Employee::forVendor($vendor->id)->count(),
            'active'   => Employee::forVendor($vendor->id)->active()->count(),
            'inactive' => Employee::forVendor($vendor->id)->where('is_active', false)->count(),
        ];

        return view('vendor.employees.index', compact('employees', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        $this->requireOwner();

        $roles = Role::forVendor($this->vendor()->id)->orderBy('name')->get();

        return view('vendor.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->requireOwner();

        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'hired_at'      => ['nullable', 'date'],
            'roles'         => ['nullable', 'array'],
            'roles.*'       => ['exists:roles,id'],
        ]);

        $vendor = $this->vendor();

        DB::transaction(function () use ($data, $vendor) {
            $user = User::create([
                'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'user_type' => 'employee',
                'is_active' => true,
            ]);

            $employee = Employee::create([
                'user_id'       => $user->id,
                'vendor_id'     => $vendor->id,
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'phone'         => $data['phone'] ?? null,
                'employee_code' => $data['employee_code'] ?? null,
                'hired_at'      => $data['hired_at'] ?? now()->toDateString(),
                'is_active'     => true,
                'created_by'    => auth()->id(),
            ]);

            if (! empty($data['roles'])) {
                $pivots = collect($data['roles'])->mapWithKeys(fn ($id) => [
                    $id => ['assigned_by' => auth()->id(), 'assigned_at' => now()],
                ])->toArray();
                $employee->roles()->sync($pivots);
            }
        });

        return redirect()
            ->route('vendor.employees.index')
            ->with('success', 'Employee account created successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Employee $employee)
    {
        $this->requireOwner();
        $this->authorizeEmployee($employee);

        $employee->load(['user', 'roles']);
        $roles           = Role::forVendor($this->vendor()->id)->orderBy('name')->get();
        $assignedRoleIds = $employee->roles->pluck('id')->toArray();

        return view('vendor.employees.edit', compact('employee', 'roles', 'assignedRoleIds'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->requireOwner();
        $this->authorizeEmployee($employee);

        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'hired_at'      => ['nullable', 'date'],
            'roles'         => ['nullable', 'array'],
            'roles.*'       => ['exists:roles,id'],
        ]);

        DB::transaction(function () use ($data, $request, $employee) {
            $isActive = $request->boolean('is_active', $employee->is_active);

            $employee->update([
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'phone'         => $data['phone'] ?? null,
                'employee_code' => $data['employee_code'] ?? null,
                'hired_at'      => $data['hired_at'] ?? $employee->hired_at,
                'is_active'     => $isActive,
            ]);

            $employee->user->update([
                'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
                'is_active' => $isActive,
            ]);

            $pivots = collect($data['roles'] ?? [])->mapWithKeys(fn ($id) => [
                $id => ['assigned_by' => auth()->id(), 'assigned_at' => now()],
            ])->toArray();
            $employee->roles()->sync($pivots);
        });

        return redirect()
            ->route('vendor.employees.index')
            ->with('success', "{$employee->full_name} updated.");
    }

    // ── Reset password ────────────────────────────────────────────────────

    public function resetPassword(Request $request, Employee $employee)
    {
        $this->requireOwner();
        $this->authorizeEmployee($employee);

        $request->validate([
            'password' => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ]);

        $employee->user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password reset for {$employee->full_name}.");
    }

    // ── Toggle active ─────────────────────────────────────────────────────

    public function toggleActive(Employee $employee)
    {
        $this->requireOwner();
        $this->authorizeEmployee($employee);

        $new = ! $employee->is_active;
        $employee->update(['is_active' => $new]);
        $employee->user->update(['is_active' => $new]);

        return response()->json([
            'success'   => true,
            'is_active' => $new,
            'message'   => $new ? "{$employee->full_name} activated." : "{$employee->full_name} deactivated.",
        ]);
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function destroy(Employee $employee)
    {
        $this->requireOwner();
        $this->authorizeEmployee($employee);

        DB::transaction(function () use ($employee) {
            $employee->roles()->detach();
            $employee->user->update(['is_active' => false]);
            $employee->delete();
        });

        return redirect()
            ->route('vendor.employees.index')
            ->with('success', 'Employee removed.');
    }
}
