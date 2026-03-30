<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HrLeaveType;
use App\Models\Vendor;

class HrLeaveTypeSeeder extends Seeder
{
    private array $leaveTypes = [
        [
            'name'         => 'Vacation Leave',
            'code'         => 'VL',
            'days_per_year' => 15,
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Sick Leave',
            'code'         => 'SL',
            'days_per_year' => 15,
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Emergency Leave',
            'code'         => 'EL',
            'days_per_year' => 3,
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Maternity Leave',
            'code'         => 'ML',
            'days_per_year' => 105, // RA 11210 — 105 days
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Paternity Leave',
            'code'         => 'PL',
            'days_per_year' => 7,   // RA 8187
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Solo Parent Leave',
            'code'         => 'SPL',
            'days_per_year' => 7,   // RA 8972
            'is_paid'      => true,
            'is_active'    => true,
        ],
        [
            'name'         => 'Unpaid Leave',
            'code'         => 'UL',
            'days_per_year' => 30,
            'is_paid'      => false,
            'is_active'    => true,
        ],
    ];

    public function run(): void
    {
        $vendors = Vendor::all();

        if ($vendors->isEmpty()) {
            $this->command->warn('⚠️  No vendors found. Skipping HrLeaveTypeSeeder.');
            return;
        }

        foreach ($vendors as $vendor) {
            foreach ($this->leaveTypes as $type) {
                HrLeaveType::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'code'      => $type['code'],
                    ],
                    array_merge($type, ['vendor_id' => $vendor->id])
                );
            }
        }

        $count = $vendors->count() * count($this->leaveTypes);
        $this->command->info("✅ {$count} leave types seeded across {$vendors->count()} vendor(s).");
    }
}
