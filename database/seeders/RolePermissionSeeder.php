<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RolePermissionSeeder extends Seeder
{
    /**
     * All platform permissions grouped by module.
     * slug format: module.action
     */
    private array $permissions = [

        // ── Dashboard ────────────────────────────────────────────────────────
        ['module' => 'dashboard', 'slug' => 'dashboard.view',            'name' => 'View Dashboard'],

        // ── Inventory ────────────────────────────────────────────────────────
        ['module' => 'inventory', 'slug' => 'inventory.view',            'name' => 'View Inventory'],
        ['module' => 'inventory', 'slug' => 'inventory.create',          'name' => 'Add Products to Inventory'],
        ['module' => 'inventory', 'slug' => 'inventory.edit',            'name' => 'Edit Inventory Records'],
        ['module' => 'inventory', 'slug' => 'inventory.adjust',          'name' => 'Adjust Stock Levels'],
        ['module' => 'inventory', 'slug' => 'inventory.movements.view',  'name' => 'View Stock Movements'],
        ['module' => 'inventory', 'slug' => 'inventory.delete',          'name' => 'Delete Inventory Items'],

        // ── Products ─────────────────────────────────────────────────────────
        ['module' => 'products',  'slug' => 'products.view',             'name' => 'View Products'],
        ['module' => 'products',  'slug' => 'products.create',           'name' => 'Create Products'],
        ['module' => 'products',  'slug' => 'products.edit',             'name' => 'Edit Products'],
        ['module' => 'products',  'slug' => 'products.delete',           'name' => 'Delete Products'],
        ['module' => 'products',  'slug' => 'products.publish',          'name' => 'Publish / Unpublish Products'],

        // ── POS ──────────────────────────────────────────────────────────────
        ['module' => 'pos',       'slug' => 'pos.access',                'name' => 'Access POS Terminal'],
        ['module' => 'pos',       'slug' => 'pos.process_sale',          'name' => 'Process POS Sale'],
        ['module' => 'pos',       'slug' => 'pos.apply_discount',        'name' => 'Apply Discounts on POS'],
        ['module' => 'pos',       'slug' => 'pos.void_transaction',      'name' => 'Void POS Transaction'],
        ['module' => 'pos',       'slug' => 'pos.history.view',          'name' => 'View POS History'],
        ['module' => 'pos',       'slug' => 'pos.refund',                'name' => 'Issue POS Refund'],

        // ── Orders ───────────────────────────────────────────────────────────
        ['module' => 'orders',    'slug' => 'orders.view',               'name' => 'View Orders'],
        ['module' => 'orders',    'slug' => 'orders.update_status',      'name' => 'Update Order Status'],
        ['module' => 'orders',    'slug' => 'orders.cancel',             'name' => 'Cancel Orders'],
        ['module' => 'orders',    'slug' => 'orders.refund',             'name' => 'Issue Order Refunds'],

        // ── Delivery ─────────────────────────────────────────────────────────
        ['module' => 'delivery',  'slug' => 'delivery.view',             'name' => 'View Deliveries'],
        ['module' => 'delivery',  'slug' => 'delivery.assign',           'name' => 'Assign Delivery Personnel'],
        ['module' => 'delivery',  'slug' => 'delivery.update_status',    'name' => 'Update Delivery Status'],

        // ── Warranty ─────────────────────────────────────────────────────────
        ['module' => 'warranty',  'slug' => 'warranty.view',             'name' => 'View Warranty Requests'],
        ['module' => 'warranty',  'slug' => 'warranty.process',          'name' => 'Process Warranty Requests'],
        ['module' => 'warranty',  'slug' => 'warranty.assign',           'name' => 'Assign Warranty Technician'],

        // ── Service Requests ─────────────────────────────────────────────────
        ['module' => 'services',  'slug' => 'services.view',             'name' => 'View Service Requests'],
        ['module' => 'services',  'slug' => 'services.process',          'name' => 'Process Service Requests'],
        ['module' => 'services',  'slug' => 'services.quote',            'name' => 'Send Service Quotations'],
        ['module' => 'services',  'slug' => 'services.assign',           'name' => 'Assign Service Technician'],
        ['module' => 'services',  'slug' => 'services.complete',         'name' => 'Mark Service Completed'],

        // ── Employees ────────────────────────────────────────────────────────
        ['module' => 'employees', 'slug' => 'employees.view',            'name' => 'View Employees'],
        ['module' => 'employees', 'slug' => 'employees.create',          'name' => 'Create Employees'],
        ['module' => 'employees', 'slug' => 'employees.edit',            'name' => 'Edit Employee Details'],
        ['module' => 'employees', 'slug' => 'employees.delete',          'name' => 'Remove Employees'],
        ['module' => 'employees', 'slug' => 'employees.roles.assign',    'name' => 'Assign Employee Roles'],   // owner-only

        // ── Roles & Permissions ───────────────────────────────────────────────
        ['module' => 'roles',     'slug' => 'roles.view',                'name' => 'View Roles'],
        ['module' => 'roles',     'slug' => 'roles.manage',              'name' => 'Manage Roles & Permissions'], // owner-only

        // ── Customers / CRM ──────────────────────────────────────────────────
        ['module' => 'customers', 'slug' => 'customers.view',            'name' => 'View Customer Profiles'],
        ['module' => 'customers', 'slug' => 'customers.contact',         'name' => 'Contact Customers'],

        // ── Reviews ──────────────────────────────────────────────────────────
        ['module' => 'reviews',   'slug' => 'reviews.view',              'name' => 'View Reviews'],
        ['module' => 'reviews',   'slug' => 'reviews.reply',             'name' => 'Reply to Reviews'],
        ['module' => 'reviews',   'slug' => 'reviews.flag',              'name' => 'Flag Abusive Reviews'],

        // ── Chat ─────────────────────────────────────────────────────────────
        ['module' => 'chat',      'slug' => 'chat.access',               'name' => 'Access Chat'],
        ['module' => 'chat',      'slug' => 'chat.view_all',             'name' => 'View All Chat Threads'],

        // ── Analytics / Reports ──────────────────────────────────────────────
        ['module' => 'analytics', 'slug' => 'analytics.view',            'name' => 'View Analytics & Reports'],
        ['module' => 'analytics', 'slug' => 'analytics.export',          'name' => 'Export Reports'],

        // ── Subscription ─────────────────────────────────────────────────────
        ['module' => 'subscription', 'slug' => 'subscription.view',      'name' => 'View Subscription'],
        ['module' => 'subscription', 'slug' => 'subscription.manage',    'name' => 'Manage Subscription'],     // owner-only

        // ── Storefront ───────────────────────────────────────────────────────
        ['module' => 'storefront', 'slug' => 'storefront.view',          'name' => 'View Storefront Settings'],
        ['module' => 'storefront', 'slug' => 'storefront.edit',          'name' => 'Edit Storefront Settings'],
    ];

    /**
     * System roles and their default permission slugs.
     * vendor_id = null → global/system scope.
     * The Owner role gets ALL permissions (assigned dynamically below).
     */
    private array $roles = [
        [
            'name'        => 'Owner',
            'slug'        => 'owner',
            'description' => 'Vendor owner — full access to all modules.',
            'is_system'   => true,
            'permissions' => '*', // wildcard: all permissions
        ],
        [
            'name'        => 'HR Officer',
            'slug'        => 'hr_officer',
            'description' => 'Manages employee records and onboarding.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'employees.view',
                'employees.create',
                'employees.edit',
                'employees.delete',
                'roles.view',
                'customers.view',
                'analytics.view',
            ],
        ],
        [
            'name'        => 'Finance Officer',
            'slug'        => 'finance_officer',
            'description' => 'Handles financial reporting, payments, and refunds.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'orders.view',
                'orders.refund',
                'pos.history.view',
                'pos.refund',
                'analytics.view',
                'analytics.export',
                'subscription.view',
                'customers.view',
                'reviews.view',
            ],
        ],
        [
            'name'        => 'Cashier',
            'slug'        => 'cashier',
            'description' => 'Operates the POS terminal for walk-in customers.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'pos.access',
                'pos.process_sale',
                'pos.apply_discount',
                'pos.history.view',
                'pos.void_transaction',
                'inventory.view',
                'products.view',
                'orders.view',
                'customers.view',
                'chat.access',
            ],
        ],
        [
            'name'        => 'Customer Relations Officer',
            'slug'        => 'cro',
            'description' => 'Handles customer inquiries, reviews, and service coordination.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'orders.view',
                'orders.update_status',
                'customers.view',
                'customers.contact',
                'reviews.view',
                'reviews.reply',
                'reviews.flag',
                'chat.access',
                'chat.view_all',
                'services.view',
                'services.process',
                'services.quote',
                'warranty.view',
                'warranty.process',
                'delivery.view',
            ],
        ],
        [
            'name'        => 'Technician Officer',
            'slug'        => 'technician',
            'description' => 'Handles field installation, maintenance, and warranty jobs.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'services.view',
                'services.complete',
                'warranty.view',
                'warranty.process',
                'delivery.view',
                'delivery.update_status',
                'orders.view',
                'inventory.view',
                'products.view',
                'customers.view',
                'chat.access',
            ],
        ],
        [
            'name'        => 'Staff',
            'slug'        => 'staff',
            'description' => 'General staff — read-only access to most modules.',
            'is_system'   => true,
            'permissions' => [
                'dashboard.view',
                'inventory.view',
                'products.view',
                'orders.view',
                'customers.view',
                'reviews.view',
                'chat.access',
                'delivery.view',
                'services.view',
                'warranty.view',
            ],
        ],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. Insert permissions ─────────────────────────────────────────────
        foreach ($this->permissions as &$perm) {
            $perm['created_at'] = $now;
            $perm['updated_at'] = $now;
        }
        unset($perm);

        DB::table('permissions')->insertOrIgnore($this->permissions);

        // Build slug → id map
        $permMap = DB::table('permissions')
            ->pluck('id', 'slug')
            ->toArray();

        // ── 2. Insert system roles (vendor_id = null) ─────────────────────────
        foreach ($this->roles as $roleDef) {
            $roleId = DB::table('roles')->insertGetId([
                'vendor_id'   => null,
                'name'        => $roleDef['name'],
                'slug'        => $roleDef['slug'],
                'description' => $roleDef['description'],
                'is_system'   => $roleDef['is_system'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // Resolve which permissions to attach
            $permSlugs = $roleDef['permissions'] === '*'
                ? array_keys($permMap)
                : $roleDef['permissions'];

            $pivots = [];
            foreach ($permSlugs as $slug) {
                if (isset($permMap[$slug])) {
                    $pivots[] = [
                        'role_id'       => $roleId,
                        'permission_id' => $permMap[$slug],
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }

            if (!empty($pivots)) {
                DB::table('role_permission')->insertOrIgnore($pivots);
            }
        }

        $this->command->info('✔  Roles & permissions seeded (' . count($this->permissions) . ' permissions, ' . count($this->roles) . ' roles).');
    }
}
