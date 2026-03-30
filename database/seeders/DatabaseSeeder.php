<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Order matters — each seeder may depend on the tables
     * populated by the ones before it.
     *
     * Run with:
     *   php artisan db:seed
     *
     * Or reset + reseed from scratch:
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  Renewable Energy Platform — Database Seeder');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');

        $this->call([
            // 1. Roles & permissions — no FK dependencies
            RolePermissionSeeder::class,

            // 2. Subscription plans — no FK dependencies
            SubscriptionPlanSeeder::class,

            // 3. Product categories — self-referencing, handled internally
            ProductCategorySeeder::class,

            // 4. Admin users — depends on users table only
            AdminSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  All seeders completed successfully.');
        $this->command->info('');
        $this->command->info('  Default admin credentials:');
        $this->command->info('  ┌─────────────────────────────────────────────┐');
        $this->command->info('  │  superadmin@renewableplatform.com           │');
        $this->command->info('  │  admin@renewableplatform.com                │');
        $this->command->info('  │  Password: Admin@12345!                     │');
        $this->command->info('  └─────────────────────────────────────────────┘');
        $this->command->warn('  ⚠  Change all passwords before deploying to production.');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
    }
}
