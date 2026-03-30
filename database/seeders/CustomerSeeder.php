<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $customers = [
            // ── 1. Primary test customer (near the demo vendor) ────────────
            [
                'name'       => 'Jose Dela Cruz',
                'email'      => 'customer@solarhub.test',
                'first_name' => 'Jose',
                'last_name'  => 'Dela Cruz',
                'phone'      => '+63 918 765 4321',
                'address'    => '12 Rizal Street',
                'city'       => 'Makati',
                'province'   => 'Metro Manila',
                'postal'     => '1200',
                'lat'        => 14.5547,
                'lng'        => 121.0244,
                'gov_id'     => 'PhilSys ID',
                'verified'   => true,
                'orders'     => 3,
                'note'       => 'Primary test customer — near demo vendor',
            ],
            // ── 2. Customer in Quezon City ─────────────────────────────────
            [
                'name'       => 'Maria Santos',
                'email'      => 'maria.santos@example.com',
                'first_name' => 'Maria',
                'last_name'  => 'Santos',
                'phone'      => '+63 917 234 5678',
                'address'    => '45 Commonwealth Avenue, Brgy. Holy Spirit',
                'city'       => 'Quezon City',
                'province'   => 'Metro Manila',
                'postal'     => '1127',
                'lat'        => 14.7011,
                'lng'        => 121.0700,
                'gov_id'     => "Driver's License",
                'verified'   => true,
                'orders'     => 1,
                'note'       => 'Quezon City customer',
            ],
            // ── 3. Customer in Pasig ───────────────────────────────────────
            [
                'name'       => 'Roberto Villanueva',
                'email'      => 'roberto.v@example.com',
                'first_name' => 'Roberto',
                'last_name'  => 'Villanueva',
                'phone'      => '+63 919 345 6789',
                'address'    => '88 Shaw Boulevard, Brgy. Oranbo',
                'city'       => 'Pasig',
                'province'   => 'Metro Manila',
                'postal'     => '1605',
                'lat'        => 14.5607,
                'lng'        => 121.0862,
                'gov_id'     => 'Passport',
                'verified'   => true,
                'orders'     => 2,
                'note'       => 'Pasig customer with multiple orders',
            ],
            // ── 4. Unverified customer (for testing verification flow) ─────
            [
                'name'       => 'Ana Reyes',
                'email'      => 'ana.reyes@example.com',
                'first_name' => 'Ana',
                'last_name'  => 'Reyes',
                'phone'      => '+63 916 456 7890',
                'address'    => '22 Gen. Luna Street',
                'city'       => 'Parañaque',
                'province'   => 'Metro Manila',
                'postal'     => '1700',
                'lat'        => 14.4793,
                'lng'        => 121.0198,
                'gov_id'     => 'SSS ID',
                'verified'   => false,
                'orders'     => 0,
                'note'       => 'Unverified — for testing admin verification flow',
            ],
            // ── 5. Customer in Cebu (no nearby vendor — for testing map) ──
            [
                'name'       => 'Carlo Mendoza',
                'email'      => 'carlo.mendoza@example.com',
                'first_name' => 'Carlo',
                'last_name'  => 'Mendoza',
                'phone'      => '+63 915 567 8901',
                'address'    => '15 Colon Street, Brgy. Parian',
                'city'       => 'Cebu City',
                'province'   => 'Cebu',
                'postal'     => '6000',
                'lat'        => 10.3157,
                'lng'        => 123.8854,
                'gov_id'     => 'UMID',
                'verified'   => true,
                'orders'     => 0,
                'note'       => 'Cebu — no nearby vendor, tests map empty state',
            ],
        ];

        $seededCount = 0;

        foreach ($customers as $c) {
            if (DB::table('users')->where('email', $c['email'])->exists()) {
                $this->command->warn("  Skipped: {$c['email']} already exists.");
                continue;
            }

            // Create user account
            $userId = DB::table('users')->insertGetId([
                'name'       => $c['name'],
                'email'      => $c['email'],
                'password'   => Hash::make('12345678'),
                'user_type'  => 'customer',
                'is_active'  => true,
                'created_at' => $now->copy()->subDays(mt_rand(5, 60)),
                'updated_at' => $now,
            ]);

            // Create customer profile
            $customerId = DB::table('customers')->insertGetId([
                'user_id'             => $userId,
                'first_name'          => $c['first_name'],
                'last_name'           => $c['last_name'],
                'phone'               => $c['phone'],
                'address_line1'       => $c['address'],
                'city'                => $c['city'],
                'province_state'      => $c['province'],
                'postal_code'         => $c['postal'],
                'country'             => 'Philippines',
                'latitude'            => $c['lat'],
                'longitude'           => $c['lng'],
                'government_id_type'  => $c['gov_id'],
                'government_id_path'  => 'customer-ids/seed/placeholder.jpg',
                'verification_status' => $c['verified'] ? 'verified' : 'unverified',
                'verified_at'         => $c['verified'] ? $now->copy()->subDays(mt_rand(1, 30)) : null,
                'created_at'          => $now->copy()->subDays(mt_rand(5, 60)),
                'updated_at'          => $now,
            ]);

            // Seed sample orders for customers with orders > 0
            if ($c['orders'] > 0) {
                $this->seedOrders($customerId, $c['orders'], $now);
            }

            $seededCount++;
        }

        // ── Summary ───────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info("  ✔  CustomerSeeder: {$seededCount} customer(s) seeded.");
        $this->command->info('');
        $this->command->info('  ┌────────────────────────────────────────────────────────┐');
        $this->command->info('  │  ALL CUSTOMER PASSWORDS: Customer@12345!               │');
        $this->command->info('  ├────────────────────────────────────────────────────────┤');
        $this->command->info('  │  customer@solarhub.test     Jose Dela Cruz (verified)  │');
        $this->command->info('  │  maria.santos@example.com   Maria Santos    (verified) │');
        $this->command->info('  │  roberto.v@example.com      Roberto V.      (verified) │');
        $this->command->info('  │  ana.reyes@example.com      Ana Reyes    (unverified)  │');
        $this->command->info('  │  carlo.mendoza@example.com  Carlo Mendoza   (verified) │');
        $this->command->info('  └────────────────────────────────────────────────────────┘');
        $this->command->info('');
    }

    // ── Helper: create realistic orders for a customer ────────────────────

    private function seedOrders(int $customerId, int $count, Carbon $now): void
    {
        $vendorId = DB::table('vendors')
            ->where('business_email', 'info@sunrisesolar.ph')
            ->value('id');

        if (! $vendorId) return;

        $statuses = ['completed', 'paid', 'pending'];

        $productRows = DB::table('products')
            ->where('vendor_id', $vendorId)
            ->where('product_type', 'physical')
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit($count * 2)
            ->get(['id', 'name', 'price', 'warranty_months']);

        for ($i = 0; $i < $count && $productRows->count() > 0; $i++) {
            $product = $productRows[$i % $productRows->count()];
            $qty     = mt_rand(1, 3);
            $total   = $product->price * $qty;
            $status  = $statuses[$i % count($statuses)];
            $daysAgo = mt_rand(1, 60);

            $orderId = DB::table('orders')->insertGetId([
                'order_number'           => 'ORD-' . $now->format('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'customer_id'            => $customerId,
                'vendor_id'              => $vendorId,
                'status'                 => $status,
                'subtotal'               => $total,
                'discount_amount'        => 0,
                'shipping_fee'           => 0,
                'tax_amount'             => 0,
                'total_amount'           => $total,
                'currency'               => 'PHP',
                'payment_method'         => 'cash_on_delivery',
                'payment_status'         => $status === 'pending' ? 'unpaid' : 'paid',
                'shipping_address_line1' => '123 Sample Street',
                'shipping_city'          => 'Makati',
                'shipping_province'      => 'Metro Manila',
                'shipping_postal_code'   => '1200',
                'shipping_country'       => 'Philippines',
                'paid_at'                => $status !== 'pending' ? $now->copy()->subDays($daysAgo) : null,
                'created_at'             => $now->copy()->subDays($daysAgo),
                'updated_at'             => $now->copy()->subDays($daysAgo),
            ]);

            DB::table('order_items')->insert([
                'order_id'        => $orderId,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'quantity'        => $qty,
                'unit_price'      => $product->price,
                'discount_amount' => 0,
                'total_price'     => $total,
                'warranty_months' => $product->warranty_months,
                'warranty_starts_at'  => $status !== 'pending' ? $now->copy()->subDays($daysAgo) : null,
                'warranty_expires_at' => $this->safeWarrantyExpiry(
                    $status !== 'pending',
                    $product->warranty_months,
                    $now->copy()->subDays($daysAgo)
                ),
                'created_at'      => $now->copy()->subDays($daysAgo),
                'updated_at'      => $now->copy()->subDays($daysAgo),
            ]);
        }
    }

    /**
     * Return a warranty expiry date string safe for MySQL TIMESTAMP columns.
     * MySQL TIMESTAMP max is 2038-01-19. Warranties longer than that
     * (e.g. 300-month panel warranties) would throw "Incorrect datetime value".
     * We return null for those — the actual expiry can be stored as a DATE
     * column or calculated on the fly from warranty_months + start date.
     */
    private function safeWarrantyExpiry(bool $isPaid, ?int $months, Carbon $startDate): ?string
    {
        if (! $isPaid || ! $months) {
            return null;
        }

        $expiry = $startDate->copy()->addMonths($months);

        // MySQL TIMESTAMP max: 2038-01-19
        if ($expiry->year >= 2038) {
            return null;
        }

        return $expiry->toDateTimeString();
    }
}
