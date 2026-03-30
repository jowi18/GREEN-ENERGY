<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $admins = [
            [
                'email'          => 'superadmin@example.com',
                'password'       => Hash::make('12345678'),
                'first_name'     => 'Super',
                'last_name'      => 'Admin',
                'phone'          => '+63 900 000 0001',
                'is_super_admin' => true,
            ],
            [
                'email'          => 'admin@example.com',
                'password'       => Hash::make('12345678'),
                'first_name'     => 'Platform',
                'last_name'      => 'Admin',
                'phone'          => '+63 900 000 0002',
                'is_super_admin' => false,
            ],
        ];

        foreach ($admins as $data) {
            // Avoid duplicate seeding
            if (DB::table('users')->where('email', $data['email'])->exists()) {
                $this->command->warn("  Skipped {$data['email']} — already exists.");
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => $data['first_name'] . ' ' . $data['last_name'],
                'email'      => $data['email'],
                'password'   => $data['password'],
                'user_type'  => 'admin',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('admins')->insert([
                'user_id'        => $userId,
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'phone'          => $data['phone'],
                'is_super_admin' => $data['is_super_admin'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $this->command->info("✔  Admin created: {$data['email']}");
        }
    }
}
