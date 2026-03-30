<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * Category tree:
         *
         * Solar Panels
         *   ├── Monocrystalline
         *   ├── Polycrystalline
         *   └── Thin Film
         * Batteries & Storage
         *   ├── Lithium-Ion
         *   ├── Lead-Acid
         *   └── Flow Batteries
         * Inverters & Converters
         *   ├── String Inverters
         *   ├── Microinverters
         *   └── Hybrid Inverters
         * Mounting & Racking
         * Cables & Accessories
         * Monitoring Systems
         * Installation Services
         *   ├── Residential Installation
         *   ├── Commercial Installation
         *   └── Off-Grid Installation
         * Maintenance Services
         *   ├── Preventive Maintenance
         *   ├── Corrective Maintenance
         *   └── System Inspection
         * Repair Services
         */

        $parents = [
            [
                'name'        => 'Solar Panels',
                'slug'        => 'solar-panels',
                'description' => 'Photovoltaic panels for residential and commercial use.',
                'icon'        => 'bi-sun',
                'children'    => [
                    ['name' => 'Monocrystalline',  'slug' => 'monocrystalline-panels'],
                    ['name' => 'Polycrystalline',  'slug' => 'polycrystalline-panels'],
                    ['name' => 'Thin Film',        'slug' => 'thin-film-panels'],
                ],
            ],
            [
                'name'        => 'Batteries & Storage',
                'slug'        => 'batteries-storage',
                'description' => 'Energy storage solutions for solar systems.',
                'icon'        => 'bi-battery-full',
                'children'    => [
                    ['name' => 'Lithium-Ion',      'slug' => 'lithium-ion-batteries'],
                    ['name' => 'Lead-Acid',        'slug' => 'lead-acid-batteries'],
                    ['name' => 'Flow Batteries',   'slug' => 'flow-batteries'],
                ],
            ],
            [
                'name'        => 'Inverters & Converters',
                'slug'        => 'inverters-converters',
                'description' => 'Convert DC power from panels to AC power for home use.',
                'icon'        => 'bi-lightning-charge',
                'children'    => [
                    ['name' => 'String Inverters',   'slug' => 'string-inverters'],
                    ['name' => 'Microinverters',     'slug' => 'microinverters'],
                    ['name' => 'Hybrid Inverters',   'slug' => 'hybrid-inverters'],
                    ['name' => 'Charge Controllers', 'slug' => 'charge-controllers'],
                ],
            ],
            [
                'name'        => 'Mounting & Racking',
                'slug'        => 'mounting-racking',
                'description' => 'Roof and ground mounting systems for solar panels.',
                'icon'        => 'bi-tools',
                'children'    => [],
            ],
            [
                'name'        => 'Cables & Accessories',
                'slug'        => 'cables-accessories',
                'description' => 'MC4 connectors, solar cables, junction boxes, and more.',
                'icon'        => 'bi-plugin',
                'children'    => [],
            ],
            [
                'name'        => 'Monitoring Systems',
                'slug'        => 'monitoring-systems',
                'description' => 'Smart meters, data loggers, and monitoring devices.',
                'icon'        => 'bi-speedometer2',
                'children'    => [],
            ],
            [
                'name'        => 'Installation Services',
                'slug'        => 'installation-services',
                'description' => 'Professional solar system installation services.',
                'icon'        => 'bi-person-gear',
                'children'    => [
                    ['name' => 'Residential Installation',  'slug' => 'residential-installation'],
                    ['name' => 'Commercial Installation',   'slug' => 'commercial-installation'],
                    ['name' => 'Off-Grid Installation',     'slug' => 'off-grid-installation'],
                ],
            ],
            [
                'name'        => 'Maintenance Services',
                'slug'        => 'maintenance-services',
                'description' => 'Scheduled and corrective maintenance services.',
                'icon'        => 'bi-wrench-adjustable',
                'children'    => [
                    ['name' => 'Preventive Maintenance', 'slug' => 'preventive-maintenance'],
                    ['name' => 'Corrective Maintenance', 'slug' => 'corrective-maintenance'],
                    ['name' => 'System Inspection',      'slug' => 'system-inspection'],
                ],
            ],
            [
                'name'        => 'Repair Services',
                'slug'        => 'repair-services',
                'description' => 'Diagnosis and repair of faulty solar components.',
                'icon'        => 'bi-hammer',
                'children'    => [],
            ],
        ];

        $order = 1;

        foreach ($parents as $parentDef) {
            $parentId = DB::table('product_categories')->insertGetId([
                'parent_id'   => null,
                'name'        => $parentDef['name'],
                'slug'        => $parentDef['slug'],
                'description' => $parentDef['description'] ?? null,
                'icon'        => $parentDef['icon'] ?? null,
                'is_active'   => true,
                'sort_order'  => $order++,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $childOrder = 1;
            foreach ($parentDef['children'] as $child) {
                DB::table('product_categories')->insert([
                    'parent_id'  => $parentId,
                    'name'       => $child['name'],
                    'slug'       => $child['slug'],
                    'is_active'  => true,
                    'sort_order' => $childOrder++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $total = DB::table('product_categories')->count();
        $this->command->info("✔  Product categories seeded ({$total} categories).");
    }
}
