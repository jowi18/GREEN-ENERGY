<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    private array $permissions = [

        // ── HR: Employee Records ──────────────────────────────────────────────
        ['module' => 'employees',  'slug' => 'hr.employees.view',             'name' => 'View HR Employee Records'],
        ['module' => 'employees',  'slug' => 'hr.employees.create',           'name' => 'Create HR Employee Records'],
        ['module' => 'employees',  'slug' => 'hr.employees.edit',             'name' => 'Edit HR Employee Records'],
        ['module' => 'employees',  'slug' => 'hr.employees.archive',          'name' => 'Archive HR Employees'],

        // ── HR: Payroll ───────────────────────────────────────────────────────
        ['module' => 'employees',    'slug' => 'hr.payroll.view',               'name' => 'View Payroll Periods'],
        ['module' => 'employees',    'slug' => 'hr.payroll.create',             'name' => 'Create Payroll Periods'],
        ['module' => 'employees',    'slug' => 'hr.payroll.compute',            'name' => 'Compute Payroll'],
        ['module' => 'employees',    'slug' => 'hr.payroll.submit',             'name' => 'Submit Payroll for Approval'],
        ['module' => 'employees',    'slug' => 'hr.payroll.approve',            'name' => 'Approve Payroll'],
        ['module' => 'employees',    'slug' => 'hr.payroll.export',             'name' => 'Export Payroll'],

        // ── HR: Attendance ────────────────────────────────────────────────────
        ['module' => 'employees', 'slug' => 'hr.attendance.view',            'name' => 'View Attendance Records'],
        ['module' => 'employees', 'slug' => 'hr.attendance.create',          'name' => 'Log Attendance'],
        ['module' => 'employees', 'slug' => 'hr.attendance.report',          'name' => 'View Attendance Reports'],
        ['module' => 'employees', 'slug' => 'hr.attendance.settings',        'name' => 'Manage Attendance Settings'],

        // ── HR: Leave & Overtime Approvals ────────────────────────────────────
        ['module' => 'employees',     'slug' => 'hr.leaves.view',                'name' => 'View Leave Requests'],
        ['module' => 'employees',     'slug' => 'hr.leaves.approve',             'name' => 'Approve Leave Requests'],
        ['module' => 'employees',     'slug' => 'hr.leaves.overtime.view',       'name' => 'View Overtime Requests'],
        ['module' => 'employees',     'slug' => 'hr.leaves.overtime.approve',    'name' => 'Approve Overtime Requests'],

        // ── HR: Self-Service ──────────────────────────────────────────────────
        ['module' => 'employees',       'slug' => 'hr.self.access',                'name' => 'Access HR Self-Service'],
        ['module' => 'employees',       'slug' => 'hr.self.time_in',               'name' => 'Clock In'],
        ['module' => 'employees',       'slug' => 'hr.self.time_out',              'name' => 'Clock Out'],
        ['module' => 'employees',       'slug' => 'hr.self.attendance.view',       'name' => 'View Own Attendance'],
        ['module' => 'employees',       'slug' => 'hr.self.leaves.view',           'name' => 'View Own Leaves'],
        ['module' => 'employees',       'slug' => 'hr.self.leaves.store',          'name' => 'File Leave Request'],
        ['module' => 'employees',       'slug' => 'hr.self.leaves.cancel',         'name' => 'Cancel Own Leave Request'],
        ['module' => 'employees',       'slug' => 'hr.self.overtime.view',         'name' => 'View Own Overtime'],
        ['module' => 'employees',       'slug' => 'hr.self.overtime.store',        'name' => 'File Overtime Request'],
        ['module' => 'employees',       'slug' => 'hr.self.payslips.view',         'name' => 'View Own Payslips'],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name'   => $permission['name'],
                    'module' => $permission['module'],
                ]
            );
        }

        $this->command->info('✅ ' . count($this->permissions) . ' permissions seeded.');
    }
}
