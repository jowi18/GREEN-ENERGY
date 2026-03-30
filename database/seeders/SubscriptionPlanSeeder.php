<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $plans = [
            [
                'name'          => 'Monthly',
                'slug'          => 'monthly',
                'description'   => 'Full access to all vendor portal features billed monthly.',
                'price'         => 29.99,
                'currency'      => 'PHP',
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'paypal_plan_id'=> null, // set after creating plan in PayPal dashboard
                'max_products'  => null, // unlimited
                'max_employees' => 10,
                'features'      => json_encode([
                    'Full vendor portal access',
                    'POS system (unlimited transactions)',
                    'Inventory management',
                    'Online storefront',
                    'Order & delivery management',
                    'Customer chat',
                    'Up to 10 employees',
                    'Basic analytics',
                ]),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Annual',
                'slug'          => 'annual',
                'description'   => 'Everything in Monthly — save 30% with annual billing.',
                'price'         => 249.99,
                'currency'      => 'PHP',
                'billing_cycle' => 'annual',
                'duration_days' => 365,
                'paypal_plan_id'=> null,
                'max_products'  => null,
                'max_employees' => null, // unlimited
                'features'      => json_encode([
                    'Everything in Monthly plan',
                    'Unlimited employees',
                    'Priority support',
                    'Advanced analytics & exports',
                    'Featured listing on platform',
                    'Save 30% vs monthly billing',
                ]),
                'is_featured'   => true,
                'is_active'     => true,
                'sort_order'    => 2,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Quarterly',
                'slug'          => 'quarterly',
                'description'   => 'Full access billed every 3 months.',
                'price'         => 79.99,
                'currency'      => 'PHP',
                'billing_cycle' => 'quarterly',
                'duration_days' => 90,
                'paypal_plan_id'=> null,
                'max_products'  => null,
                'max_employees' => 20,
                'features'      => json_encode([
                    'Full vendor portal access',
                    'POS system (unlimited transactions)',
                    'Inventory management',
                    'Online storefront',
                    'Order & delivery management',
                    'Customer chat',
                    'Up to 20 employees',
                    'Standard analytics',
                ]),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 3,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        DB::table('subscription_plans')->insertOrIgnore($plans);

        $this->command->info('✔  Subscription plans seeded (' . count($plans) . ' plans).');
    }
}
