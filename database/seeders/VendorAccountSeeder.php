<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class VendorAccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. User account ───────────────────────────────────────────────
        if (DB::table('users')->where('email', 'vendor@solarhub.test')->exists()) {
            $this->command->warn('  Skipped — vendor@solarhub.test already exists.');
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'name'       => 'Jullian Santos',
            'email'      => 'vendor@solarsolution.test',
            'password'   => Hash::make('12345678'),
            'user_type'  => 'vendor',
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ── 2. Vendor profile (fully active) ─────────────────────────────
        $vendorId = DB::table('vendors')->insertGetId([
            'user_id'                      => $userId,
            'owner_first_name'             => 'Jullian',
            'owner_last_name'              => 'Santos',
            'owner_phone'                  => '+63 917 123 4567',
            'business_name'                => 'SunRise Solar Solutions',
            'business_type'                => 'sme',
            'business_registration_number' => 'DTI-NCR-2026-00123',
            'business_phone'               => '+63 2 8888 1234',
            'business_email'               => 'info@sunrisesolar.ph',
            'business_website'             => 'https://sunrisesolar.ph',
            'address_line1'                => '45 Greenfield Avenue, Brgy. Bagumbayan',
            'city'                         => 'Quezon City',
            'province_state'               => 'Metro Manila',
            'postal_code'                  => '1110',
            'country'                      => 'Philippines',
            'latitude'                     => 14.6760,
            'longitude'                    => 121.0437,
            'shop_description'             => 'Your trusted partner for residential and commercial solar energy solutions. We supply, install, and maintain solar panels, batteries, and inverter systems across Metro Manila.',
            'status'                       => 'active',
            'approved_at'                  => $now->copy()->subDays(10),
            'average_rating'               => 4.7,
            'total_reviews'                => 38,
            'created_at'                   => $now->copy()->subDays(15),
            'updated_at'                   => $now,
        ]);

        // ── 3. Active subscription (Monthly plan) ─────────────────────────
        $planId = DB::table('subscription_plans')->where('slug', 'monthly')->value('id')
                  ?? DB::table('subscription_plans')->value('id');

        if ($planId) {
            DB::table('subscriptions')->insert([
                'vendor_id'            => $vendorId,
                'subscription_plan_id' => $planId,
                'paypal_order_id'      => 'SEED-' . strtoupper(Str::random(10)),
                'paypal_payer_id'      => 'PAYERID-SEED-001',
                'status'               => 'active',
                'amount_paid'          => 29.99,
                'currency'             => 'PHP',
                'starts_at'            => $now->copy()->subDays(10),
                'expires_at'           => $now->copy()->addDays(20),
                'auto_renew'           => true,
                'last_renewed_at'      => $now->copy()->subDays(10),
                'next_renewal_at'      => $now->copy()->addDays(20),
                'created_at'           => $now->copy()->subDays(10),
                'updated_at'           => $now,
            ]);
        }

        // ── 4. Vendor documents ───────────────────────────────────────────
        $docs = [
            ['business_permit',  'Business Permit 2024',     'vendor-documents/seed/business-permit.pdf'],
            ['government_id',    'PhilSys ID - Maria Santos', 'vendor-documents/seed/gov-id.jpg'],
            ['proof_of_address', 'Utility Bill - March 2024', 'vendor-documents/seed/proof-of-address.pdf'],
        ];

        foreach ($docs as [$type, $label, $path]) {
            DB::table('vendor_documents')->insert([
                'vendor_id'          => $vendorId,
                'document_type'      => $type,
                'document_label'     => $label,
                'file_path'          => $path,
                'file_original_name' => basename($path),
                'file_mime_type'     => str_ends_with($path, '.pdf') ? 'application/pdf' : 'image/jpeg',
                'file_size'          => rand(80000, 400000),
                'review_status'      => 'accepted',
                'reviewed_at'        => $now->copy()->subDays(12),
                'created_at'         => $now->copy()->subDays(15),
                'updated_at'         => $now,
            ]);
        }


        // ── 6. Products ───────────────────────────────────────────────────
        $products = [
            [
                'name'        => '400W Monocrystalline Solar Panel',
                'slug'        => '400w-monocrystalline-solar-panel',
                'sku'         => 'SP-MONO-400W',
                'barcode'     => '6901234567890',
                'price'       => 12500.00,
                'category'    => 'monocrystalline-panels',
                'type'        => 'physical',
                'warranty'    => 300, // months
                'stock'       => 45,
                'reorder'     => 10,
            ],
            [
                'name'        => '550W Half-Cut Monocrystalline Panel',
                'slug'        => '550w-half-cut-monocrystalline-panel',
                'sku'         => 'SP-MONO-550W',
                'barcode'     => '6901234567891',
                'price'       => 17800.00,
                'category'    => 'monocrystalline-panels',
                'type'        => 'physical',
                'warranty'    => 300,
                'stock'       => 28,
                'reorder'     => 8,
            ],
            [
                'name'        => '200Ah LiFePO4 Lithium Battery',
                'slug'        => '200ah-lifepo4-lithium-battery',
                'sku'         => 'BAT-LFP-200AH',
                'barcode'     => '6901234567892',
                'price'       => 32000.00,
                'category'    => 'lithium-ion-batteries',
                'type'        => 'physical',
                'warranty'    => 120,
                'stock'       => 12,
                'reorder'     => 5,
            ],
            [
                'name'        => '5kW Hybrid Solar Inverter',
                'slug'        => '5kw-hybrid-solar-inverter',
                'sku'         => 'INV-HYB-5KW',
                'barcode'     => '6901234567893',
                'price'       => 28000.00,
                'category'    => 'hybrid-inverters',
                'type'        => 'physical',
                'warranty'    => 60,
                'stock'       => 7,
                'reorder'     => 3,
            ],
            [
                'name'        => '30A MPPT Charge Controller',
                'slug'        => '30a-mppt-charge-controller',
                'sku'         => 'CC-MPPT-30A',
                'barcode'     => '6901234567894',
                'price'       => 3200.00,
                'category'    => 'charge-controllers',
                'type'        => 'physical',
                'warranty'    => 24,
                'stock'       => 3,   // low stock — will trigger alert
                'reorder'     => 5,
            ],
            [
                'name'        => 'Aluminum Roof Mounting Kit (8 panels)',
                'slug'        => 'aluminum-roof-mounting-kit-8-panels',
                'sku'         => 'MNT-ROOF-8P',
                'barcode'     => '6901234567895',
                'price'       => 6800.00,
                'category'    => 'mounting-racking',
                'type'        => 'physical',
                'warranty'    => 120,
                'stock'       => 0,   // out of stock
                'reorder'     => 5,
            ],
            [
                'name'        => 'Residential Solar Installation',
                'slug'        => 'residential-solar-installation',
                'sku'         => 'SVC-INSTALL-RES',
                'barcode'     => null,
                'price'       => 15000.00,
                'category'    => 'residential-installation',
                'type'        => 'service',
                'warranty'    => 12,
                'stock'       => 999, // services don't deplete
                'reorder'     => 0,
            ],
            [
                'name'        => 'Annual Preventive Maintenance',
                'slug'        => 'annual-preventive-maintenance',
                'sku'         => 'SVC-MAINT-ANN',
                'barcode'     => null,
                'price'       => 3500.00,
                'category'    => 'preventive-maintenance',
                'type'        => 'service',
                'warranty'    => null,
                'stock'       => 999,
                'reorder'     => 0,
            ],
        ];

        foreach ($products as $p) {
            $categoryId = DB::table('product_categories')->where('slug', $p['category'])->value('id')
                          ?? DB::table('product_categories')->value('id');

            $productId = DB::table('products')->insertGetId([
                'vendor_id'         => $vendorId,
                'category_id'       => $categoryId,
                'name'              => $p['name'],
                'slug'              => $p['slug'],
                'sku'               => $p['sku'],
                'barcode'           => $p['barcode'],
                'short_description' => 'High-quality ' . strtolower($p['name']) . ' for residential and commercial solar systems.',
                'description'       => 'Professional-grade ' . strtolower($p['name']) . ' supplied and supported by SunRise Solar Solutions. All products come with full technical documentation and after-sales support.',
                'price'             => $p['price'],
                'cost_price'        => $p['price'] * 0.65,
                'currency'          => 'PHP',
                'product_type'      => $p['type'],
                'warranty_months'   => $p['warranty'],
                'status'            => 'active',
                'is_featured'       => in_array($p['sku'], ['SP-MONO-400W', 'BAT-LFP-200AH']),
                'average_rating'    => round(rand(42, 50) / 10, 1),
                'total_reviews'     => rand(5, 40),
                'total_sold'        => rand(10, 200),
                'created_at'        => $now->copy()->subDays(rand(1, 14)),
                'updated_at'        => $now,
            ]);

            // Inventory record
            $inventoryId = DB::table('inventories')->insertGetId([
                'product_id'        => $productId,
                'vendor_id'         => $vendorId,
                'quantity_on_hand'  => $p['stock'],
                'quantity_reserved' => 0,
                'reorder_point'     => $p['reorder'],
                'reorder_quantity'  => $p['reorder'] * 2,
                'unit_of_measure'   => $p['type'] === 'service' ? 'service' : 'piece',
                'last_stock_update' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Opening stock movement
            if ($p['stock'] > 0 && $p['type'] !== 'service') {
                DB::table('stock_movements')->insert([
                    'inventory_id'   => $inventoryId,
                    'product_id'     => $productId,
                    'vendor_id'      => $vendorId,
                    'movement_type'  => 'opening_stock',
                    'quantity_change'=> $p['stock'],
                    'quantity_before'=> 0,
                    'quantity_after' => $p['stock'],
                    'notes'          => 'Opening stock — seeded',
                    'performed_by'   => $userId,
                    'created_at'     => $now->copy()->subDays(14),
                    'updated_at'     => $now->copy()->subDays(14),
                ]);
            }
        }



        // ── 9. Summary ────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✔  Vendor account seeded successfully.');
        $this->command->info('');
        $this->command->info('  ┌─────────────────────────────────────────────────┐');
        $this->command->info('  │  VENDOR LOGIN                                   │');
        $this->command->info('  │  Email:    vendor@solarhub.test                 │');
        $this->command->info('  │  Password: Vendor@12345!                        │');
        $this->command->info('  ├─────────────────────────────────────────────────┤');
        $this->command->info('  │  EMPLOYEE LOGINS (Password: Employee@12345!)    │');
        $this->command->info('  │  cashier@solarhub.test     → Cashier            │');
        $this->command->info('  │  technician@solarhub.test  → Technician         │');
        $this->command->info('  │  cro@solarhub.test         → CRO                │');
        $this->command->info('  ├─────────────────────────────────────────────────┤');
        $this->command->info('  │  CUSTOMER LOGIN                                 │');
        $this->command->info('  │  Email:    customer@solarhub.test               │');
        $this->command->info('  │  Password: Customer@12345!                      │');
        $this->command->info('  └─────────────────────────────────────────────────┘');
        $this->command->info('');
        $this->command->warn('  ⚠  2 products have low/zero stock to test alerts.');
        $this->command->info('');
    }
}
