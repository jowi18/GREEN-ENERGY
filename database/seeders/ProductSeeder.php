<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get the demo vendor seeded by VendorAccountSeeder
        $vendorId = DB::table('vendors')
            ->where('business_email', 'info@sunrisesolar.ph')
            ->value('id');

        if (! $vendorId) {
            $this->command->warn('  ProductSeeder: vendor not found. Run VendorAccountSeeder first.');
            return;
        }

        // ── Category IDs ─────────────────────────────────────────────────
        $cats = DB::table('product_categories')
            ->pluck('id', 'slug');

        // Helper: resolve category or fall back to first available
        $cat = fn (string $slug): int =>
            $cats[$slug] ?? $cats->first() ?? 1;

        // ── Products ─────────────────────────────────────────────────────
        $products = [

            // ── MONOCRYSTALLINE PANELS ────────────────────────────────────
            [
                'category' => 'monocrystalline-panels',
                'name'     => '400W Monocrystalline Solar Panel',
                'sku'      => 'SP-MONO-400W',
                'barcode'  => '6901234567890',
                'price'    => 12500.00,
                'compare'  => 14000.00,
                'cost'     => 8000.00,
                'warranty' => 300,
                'stock'    => 45,
                'reorder'  => 10,
                'featured' => true,
                'desc'     => 'High-efficiency 400W monocrystalline panel with 21.3% efficiency. Ideal for residential rooftop installations. Includes MC4 connectors and mounting holes.',
                'weight'   => 22.5,
            ],
            [
                'category' => 'monocrystalline-panels',
                'name'     => '550W Half-Cut Monocrystalline Panel',
                'sku'      => 'SP-MONO-550W-HC',
                'barcode'  => '6901234567891',
                'price'    => 17800.00,
                'compare'  => null,
                'cost'     => 11500.00,
                'warranty' => 300,
                'stock'    => 28,
                'reorder'  => 8,
                'featured' => true,
                'desc'     => 'Premium half-cut cell technology reduces energy loss from shading. 550W output with 22.1% efficiency. Anti-reflective tempered glass coating.',
                'weight'   => 28.0,
            ],
            [
                'category' => 'monocrystalline-panels',
                'name'     => '330W Monocrystalline Solar Panel',
                'sku'      => 'SP-MONO-330W',
                'barcode'  => '6901234567892',
                'price'    => 9800.00,
                'compare'  => 11200.00,
                'cost'     => 6200.00,
                'warranty' => 240,
                'stock'    => 60,
                'reorder'  => 15,
                'featured' => false,
                'desc'     => 'Entry-level mono panel with solid 330W output. Budget-friendly choice for residential systems without sacrificing reliability.',
                'weight'   => 19.5,
            ],
            [
                'category' => 'monocrystalline-panels',
                'name'     => '660W Bifacial Monocrystalline Panel',
                'sku'      => 'SP-MONO-660W-BI',
                'barcode'  => '6901234567893',
                'price'    => 24500.00,
                'compare'  => null,
                'cost'     => 16000.00,
                'warranty' => 360,
                'stock'    => 15,
                'reorder'  => 5,
                'featured' => true,
                'desc'     => 'Bifacial design harvests reflected light from rear side. Up to 25% additional energy yield. Perfect for ground-mount and commercial installations.',
                'weight'   => 32.0,
            ],

            // ── POLYCRYSTALLINE PANELS ─────────────────────────────────────
            [
                'category' => 'polycrystalline-panels',
                'name'     => '275W Polycrystalline Solar Panel',
                'sku'      => 'SP-POLY-275W',
                'barcode'  => '6901234567894',
                'price'    => 7200.00,
                'compare'  => 8500.00,
                'cost'     => 4500.00,
                'warranty' => 240,
                'stock'    => 80,
                'reorder'  => 20,
                'featured' => false,
                'desc'     => 'Cost-effective polycrystalline panel. 17.8% efficiency. Best value for large off-grid systems where space is not a constraint.',
                'weight'   => 18.2,
            ],
            [
                'category' => 'polycrystalline-panels',
                'name'     => '320W Polycrystalline Solar Panel',
                'sku'      => 'SP-POLY-320W',
                'barcode'  => '6901234567895',
                'price'    => 9200.00,
                'compare'  => null,
                'cost'     => 5800.00,
                'warranty' => 240,
                'stock'    => 55,
                'reorder'  => 12,
                'featured' => false,
                'desc'     => 'Reliable polycrystalline panel with 18.5% efficiency. Excellent performance in high-temperature Philippine climate.',
                'weight'   => 21.0,
            ],

            // ── LITHIUM BATTERIES ─────────────────────────────────────────
            [
                'category' => 'lithium-ion-batteries',
                'name'     => '100Ah LiFePO4 Lithium Battery',
                'sku'      => 'BAT-LFP-100AH',
                'barcode'  => '6901234567896',
                'price'    => 18500.00,
                'compare'  => 21000.00,
                'cost'     => 12000.00,
                'warranty' => 120,
                'stock'    => 20,
                'reorder'  => 5,
                'featured' => false,
                'desc'     => 'Lithium Iron Phosphate battery — safe, long-lasting, and maintenance-free. 4000+ charge cycles. Built-in BMS with overcharge and over-discharge protection.',
                'weight'   => 12.5,
            ],
            [
                'category' => 'lithium-ion-batteries',
                'name'     => '200Ah LiFePO4 Lithium Battery',
                'sku'      => 'BAT-LFP-200AH',
                'barcode'  => '6901234567897',
                'price'    => 32000.00,
                'compare'  => 36000.00,
                'cost'     => 21000.00,
                'warranty' => 120,
                'stock'    => 12,
                'reorder'  => 4,
                'featured' => true,
                'desc'     => 'High-capacity 200Ah LiFePO4 for whole-home backup. 2.56 kWh usable energy. Compatible with most hybrid and off-grid inverters.',
                'weight'   => 24.0,
            ],
            [
                'category' => 'lithium-ion-batteries',
                'name'     => '10kWh Lithium Battery Stack (4×100Ah)',
                'sku'      => 'BAT-LFP-STACK-10K',
                'barcode'  => '6901234567898',
                'price'    => 85000.00,
                'compare'  => null,
                'cost'     => 55000.00,
                'warranty' => 120,
                'stock'    => 5,
                'reorder'  => 2,
                'featured' => true,
                'desc'     => 'Complete 10kWh battery stack for commercial or large residential systems. Stackable design, RS485 communication port, active cell balancing.',
                'weight'   => 52.0,
            ],

            // ── LEAD ACID BATTERIES ───────────────────────────────────────
            [
                'category' => 'lead-acid-batteries',
                'name'     => '200Ah AGM Deep Cycle Battery',
                'sku'      => 'BAT-AGM-200AH',
                'barcode'  => '6901234567899',
                'price'    => 8500.00,
                'compare'  => 9800.00,
                'cost'     => 5500.00,
                'warranty' => 24,
                'stock'    => 30,
                'reorder'  => 8,
                'featured' => false,
                'desc'     => 'Sealed AGM deep cycle battery for off-grid solar. Maintenance-free, spill-proof design. 500+ cycle life at 50% DOD.',
                'weight'   => 60.0,
            ],
            [
                'category' => 'lead-acid-batteries',
                'name'     => '150Ah Tubular Flooded Battery',
                'sku'      => 'BAT-TUB-150AH',
                'barcode'  => '6901234568000',
                'price'    => 5200.00,
                'compare'  => null,
                'cost'     => 3300.00,
                'warranty' => 36,
                'stock'    => 40,
                'reorder'  => 10,
                'featured' => false,
                'desc'     => 'Premium tubular plate flooded battery with excellent deep discharge recovery. Ideal for areas with frequent power outages.',
                'weight'   => 48.0,
            ],

            // ── HYBRID INVERTERS ─────────────────────────────────────────
            [
                'category' => 'hybrid-inverters',
                'name'     => '3kW Hybrid Solar Inverter',
                'sku'      => 'INV-HYB-3KW',
                'barcode'  => '6901234568001',
                'price'    => 18500.00,
                'compare'  => 21000.00,
                'cost'     => 12000.00,
                'warranty' => 60,
                'stock'    => 10,
                'reorder'  => 3,
                'featured' => false,
                'desc'     => 'Grid-tied hybrid inverter with built-in MPPT charge controller. Wi-Fi monitoring, battery priority mode, and generator support.',
                'weight'   => 14.5,
            ],
            [
                'category' => 'hybrid-inverters',
                'name'     => '5kW Hybrid Solar Inverter',
                'sku'      => 'INV-HYB-5KW',
                'barcode'  => '6901234568002',
                'price'    => 28000.00,
                'compare'  => 32000.00,
                'cost'     => 18500.00,
                'warranty' => 60,
                'stock'    => 7,
                'reorder'  => 3,
                'featured' => true,
                'desc'     => 'Most popular 5kW hybrid inverter for residential systems. Supports lithium and lead-acid batteries. LCD display, smartphone monitoring app.',
                'weight'   => 19.0,
            ],
            [
                'category' => 'hybrid-inverters',
                'name'     => '8kW Three-Phase Hybrid Inverter',
                'sku'      => 'INV-HYB-8KW-3PH',
                'barcode'  => '6901234568003',
                'price'    => 48000.00,
                'compare'  => null,
                'cost'     => 32000.00,
                'warranty' => 60,
                'stock'    => 4,
                'reorder'  => 2,
                'featured' => false,
                'desc'     => 'Three-phase hybrid inverter for commercial and industrial solar systems. 8kW continuous output, expandable with battery stacking.',
                'weight'   => 35.0,
            ],

            // ── OFF-GRID INVERTERS ────────────────────────────────────────
            [
                'category' => 'off-grid-inverters',
                'name'     => '3kW Pure Sine Wave Inverter',
                'sku'      => 'INV-OG-3KW-PSW',
                'barcode'  => '6901234568004',
                'price'    => 12000.00,
                'compare'  => 13500.00,
                'cost'     => 7800.00,
                'warranty' => 36,
                'stock'    => 18,
                'reorder'  => 5,
                'featured' => false,
                'desc'     => 'Pure sine wave output inverter for sensitive electronics. Automatic transfer switch built-in. Compatible with all battery types.',
                'weight'   => 12.0,
            ],
            [
                'category' => 'off-grid-inverters',
                'name'     => '5kW Off-Grid Inverter/Charger',
                'sku'      => 'INV-OG-5KW-CHG',
                'barcode'  => '6901234568005',
                'price'    => 22000.00,
                'compare'  => null,
                'cost'     => 14500.00,
                'warranty' => 36,
                'stock'    => 9,
                'reorder'  => 3,
                'featured' => false,
                'desc'     => 'All-in-one inverter-charger for fully off-grid living. 50A built-in battery charger, generator input, and wide voltage tolerance.',
                'weight'   => 22.0,
            ],

            // ── MPPT CHARGE CONTROLLERS ───────────────────────────────────
            [
                'category' => 'charge-controllers',
                'name'     => '30A MPPT Charge Controller',
                'sku'      => 'CC-MPPT-30A',
                'barcode'  => '6901234568006',
                'price'    => 3200.00,
                'compare'  => 3800.00,
                'cost'     => 2000.00,
                'warranty' => 24,
                'stock'    => 3,   // low stock — triggers alert
                'reorder'  => 5,
                'featured' => false,
                'desc'     => '30A MPPT solar charge controller. Max PV input 100V. LCD display showing battery SOC, charging current, and daily energy yield.',
                'weight'   => 0.85,
            ],
            [
                'category' => 'charge-controllers',
                'name'     => '60A MPPT Charge Controller',
                'sku'      => 'CC-MPPT-60A',
                'barcode'  => '6901234568007',
                'price'    => 6800.00,
                'compare'  => null,
                'cost'     => 4400.00,
                'warranty' => 24,
                'stock'    => 14,
                'reorder'  => 5,
                'featured' => false,
                'desc'     => 'High-power 60A MPPT for larger off-grid systems. Max 150V PV input. Bluetooth connectivity for smartphone monitoring.',
                'weight'   => 1.8,
            ],
            [
                'category' => 'charge-controllers',
                'name'     => '20A PWM Charge Controller',
                'sku'      => 'CC-PWM-20A',
                'barcode'  => '6901234568008',
                'price'    => 1200.00,
                'compare'  => 1500.00,
                'cost'     => 750.00,
                'warranty' => 12,
                'stock'    => 35,
                'reorder'  => 10,
                'featured' => false,
                'desc'     => 'Budget-friendly PWM charge controller for small solar systems. LED indicators, over-charge and deep-discharge protection.',
                'weight'   => 0.45,
            ],

            // ── MOUNTING & RACKING ────────────────────────────────────────
            [
                'category' => 'mounting-racking',
                'name'     => 'Aluminum Roof Mount Kit (8 Panels)',
                'sku'      => 'MNT-ROOF-8P',
                'barcode'  => '6901234568009',
                'price'    => 6800.00,
                'compare'  => 7500.00,
                'cost'     => 4200.00,
                'warranty' => 120,
                'stock'    => 0,   // out of stock
                'reorder'  => 5,
                'featured' => false,
                'desc'     => 'Complete aluminum roof mounting system for 8 standard panels. Includes rails, end clamps, mid clamps, and lag bolts. Anodized for corrosion resistance.',
                'weight'   => 18.0,
            ],
            [
                'category' => 'mounting-racking',
                'name'     => 'Ground Mount Solar Frame (12 Panels)',
                'sku'      => 'MNT-GND-12P',
                'barcode'  => '6901234568010',
                'price'    => 12500.00,
                'compare'  => null,
                'cost'     => 8000.00,
                'warranty' => 120,
                'stock'    => 8,
                'reorder'  => 3,
                'featured' => false,
                'desc'     => 'Heavy-duty galvanized steel ground mount for 12 panels. Adjustable tilt angle (15°–45°). Includes concrete anchor template.',
                'weight'   => 55.0,
            ],
            [
                'category' => 'mounting-racking',
                'name'     => 'Flat Roof Ballast Mount Kit (4 Panels)',
                'sku'      => 'MNT-FLAT-4P',
                'barcode'  => '6901234568011',
                'price'    => 3800.00,
                'compare'  => 4200.00,
                'cost'     => 2400.00,
                'warranty' => 60,
                'stock'    => 22,
                'reorder'  => 6,
                'featured' => false,
                'desc'     => 'No-penetration flat roof ballast system. Pre-assembled for fast installation. Wind-rated to 150 km/h.',
                'weight'   => 8.5,
            ],

            // ── CABLES & CONNECTORS ───────────────────────────────────────
            [
                'category' => 'cables-connectors',
                'name'     => 'Solar Cable 6mm² Red (per meter)',
                'sku'      => 'CBL-SOL-6MM-RED',
                'barcode'  => '6901234568012',
                'price'    => 85.00,
                'compare'  => null,
                'cost'     => 55.00,
                'warranty' => 0,
                'stock'    => 500,
                'reorder'  => 100,
                'featured' => false,
                'desc'     => 'UV-resistant double-insulated solar cable. 6mm² cross-section, rated for 1500V DC. TÜV and UL certified. Sold per meter.',
                'weight'   => 0.08,
            ],
            [
                'category' => 'cables-connectors',
                'name'     => 'Solar Cable 6mm² Black (per meter)',
                'sku'      => 'CBL-SOL-6MM-BLK',
                'barcode'  => '6901234568013',
                'price'    => 85.00,
                'compare'  => null,
                'cost'     => 55.00,
                'warranty' => 0,
                'stock'    => 500,
                'reorder'  => 100,
                'featured' => false,
                'desc'     => 'UV-resistant double-insulated solar cable. 6mm² cross-section, rated for 1500V DC. TÜV and UL certified. Sold per meter.',
                'weight'   => 0.08,
            ],
            [
                'category' => 'cables-connectors',
                'name'     => 'MC4 Connector Pair (Male + Female)',
                'sku'      => 'CON-MC4-PAIR',
                'barcode'  => '6901234568014',
                'price'    => 95.00,
                'compare'  => 120.00,
                'cost'     => 60.00,
                'warranty' => 0,
                'stock'    => 200,
                'reorder'  => 50,
                'featured' => false,
                'desc'     => 'Genuine TÜV-certified MC4 connectors. IP68 waterproof, rated for 30A and 1500V. Compatible with all standard solar cables.',
                'weight'   => 0.05,
            ],
            [
                'category' => 'cables-connectors',
                'name'     => 'MC4 Branch Connector 2-to-1 (T-Branch)',
                'sku'      => 'CON-MC4-TBRANCH',
                'barcode'  => '6901234568015',
                'price'    => 380.00,
                'compare'  => null,
                'cost'     => 240.00,
                'warranty' => 0,
                'stock'    => 80,
                'reorder'  => 20,
                'featured' => false,
                'desc'     => 'MC4 T-branch connector for parallel string wiring. 2 inputs to 1 output. IP67 rated. Pre-assembled with strain relief.',
                'weight'   => 0.12,
            ],

            // ── MONITORING EQUIPMENT ──────────────────────────────────────
            [
                'category' => 'monitoring-equipment',
                'name'     => 'Wi-Fi Solar Monitor (Datalogger)',
                'sku'      => 'MON-WIFI-DL',
                'barcode'  => '6901234568016',
                'price'    => 3500.00,
                'compare'  => 4000.00,
                'cost'     => 2200.00,
                'warranty' => 24,
                'stock'    => 25,
                'reorder'  => 6,
                'featured' => false,
                'desc'     => 'Wi-Fi data logger for real-time solar monitoring via smartphone app. Compatible with Growatt, Solis, Deye inverters. Daily, monthly, lifetime yield reports.',
                'weight'   => 0.15,
            ],
            [
                'category' => 'monitoring-equipment',
                'name'     => 'Clamp Meter for Solar DC Measurement',
                'sku'      => 'MON-CLAMP-DC',
                'barcode'  => '6901234568017',
                'price'    => 2800.00,
                'compare'  => null,
                'cost'     => 1800.00,
                'warranty' => 12,
                'stock'    => 12,
                'reorder'  => 4,
                'featured' => false,
                'desc'     => 'True RMS clamp meter with DC current measurement up to 600A. Essential for solar installation and troubleshooting. CAT III 600V safety rating.',
                'weight'   => 0.38,
            ],

            // ── PROTECTION DEVICES ────────────────────────────────────────
            [
                'category' => 'protection-devices',
                'name'     => 'Solar DC Circuit Breaker 30A',
                'sku'      => 'PROT-DCB-30A',
                'barcode'  => '6901234568018',
                'price'    => 450.00,
                'compare'  => 550.00,
                'cost'     => 280.00,
                'warranty' => 12,
                'stock'    => 60,
                'reorder'  => 15,
                'featured' => false,
                'desc'     => 'DC solar circuit breaker for string protection. 30A rated, 1000V DC. Double-pole design. IP65 housing for outdoor installation.',
                'weight'   => 0.22,
            ],
            [
                'category' => 'protection-devices',
                'name'     => 'DC Surge Protection Device (SPD)',
                'sku'      => 'PROT-SPD-DC',
                'barcode'  => '6901234568019',
                'price'    => 1800.00,
                'compare'  => null,
                'cost'     => 1150.00,
                'warranty' => 24,
                'stock'    => 28,
                'reorder'  => 8,
                'featured' => false,
                'desc'     => 'Type II DC surge protection device for PV strings. Protects against lightning-induced surges. 1000V DC, 40kA impulse current.',
                'weight'   => 0.35,
            ],
            [
                'category' => 'protection-devices',
                'name'     => 'Battery Fuse with Holder 100A',
                'sku'      => 'PROT-FUSE-100A',
                'barcode'  => '6901234568020',
                'price'    => 650.00,
                'compare'  => 800.00,
                'cost'     => 420.00,
                'warranty' => 0,
                'stock'    => 45,
                'reorder'  => 12,
                'featured' => false,
                'desc'     => '100A ANL/MIDI fuse with waterproof holder. Essential battery protection for off-grid and hybrid systems. Includes mounting bracket.',
                'weight'   => 0.18,
            ],

            // ── SERVICES ─────────────────────────────────────────────────
            [
                'category' => 'residential-installation',
                'name'     => 'Residential Solar Installation (up to 5kW)',
                'sku'      => 'SVC-INSTALL-5KW',
                'barcode'  => null,
                'price'    => 15000.00,
                'compare'  => 18000.00,
                'cost'     => null,
                'warranty' => 12,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => true,
                'type'     => 'service',
                'desc'     => 'Full residential solar installation service for systems up to 5kW. Includes site survey, structural assessment, panel mounting, wiring, inverter installation, and grid connection.',
            ],
            [
                'category' => 'residential-installation',
                'name'     => 'Commercial Solar Installation (6–20kW)',
                'sku'      => 'SVC-INSTALL-20KW',
                'barcode'  => null,
                'price'    => 45000.00,
                'compare'  => null,
                'cost'     => null,
                'warranty' => 12,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => false,
                'type'     => 'service',
                'desc'     => 'Commercial installation service for 6–20kW solar systems. Includes structural engineering certification, meralco/utility coordination, and 1-year labor warranty.',
            ],
            [
                'category' => 'preventive-maintenance',
                'name'     => 'Annual Solar System Maintenance',
                'sku'      => 'SVC-MAINT-ANN',
                'barcode'  => null,
                'price'    => 3500.00,
                'compare'  => 4500.00,
                'cost'     => null,
                'warranty' => null,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => false,
                'type'     => 'service',
                'desc'     => 'Annual preventive maintenance: panel cleaning and inspection, torque check, thermographic scan, inverter firmware update, performance report.',
            ],
            [
                'category' => 'preventive-maintenance',
                'name'     => 'Solar Panel Deep Cleaning',
                'sku'      => 'SVC-CLEAN-DEEP',
                'barcode'  => null,
                'price'    => 1500.00,
                'compare'  => null,
                'cost'     => null,
                'warranty' => null,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => false,
                'type'     => 'service',
                'desc'     => 'Professional deep cleaning using purified water and soft brushes. Includes visual inspection and cleaning report. Per system up to 20 panels.',
            ],
            [
                'category' => 'preventive-maintenance',
                'name'     => 'Battery Health Check & Conditioning',
                'sku'      => 'SVC-BATT-CHECK',
                'barcode'  => null,
                'price'    => 2000.00,
                'compare'  => null,
                'cost'     => null,
                'warranty' => null,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => false,
                'type'     => 'service',
                'desc'     => 'Battery capacity test and conditioning service. Identifies weak cells, checks BMS calibration, and applies conditioning cycle. Includes health report.',
            ],
            [
                'category' => 'energy-consultation',
                'name'     => 'Home Solar Energy Consultation',
                'sku'      => 'SVC-CONSULT-HOME',
                'barcode'  => null,
                'price'    => 1200.00,
                'compare'  => 1800.00,
                'cost'     => null,
                'warranty' => null,
                'stock'    => 999,
                'reorder'  => 0,
                'featured' => false,
                'type'     => 'service',
                'desc'     => 'On-site consultation for residential solar. Includes load analysis, roof assessment, system sizing recommendation, and ROI calculation. 2-hour visit.',
            ],
        ];

        $inserted = 0;

        foreach ($products as $p) {
            // Skip if already exists for this vendor
            if (DB::table('products')
                ->where('vendor_id', $vendorId)
                ->where('sku', $p['sku'])
                ->exists()) {
                continue;
            }

            $categoryId = $cat($p['category']);
            $type       = $p['type'] ?? 'physical';
            $slug       = $this->uniqueSlug($p['name'], $vendorId);

            $productId = DB::table('products')->insertGetId([
                'vendor_id'         => $vendorId,
                'category_id'       => $categoryId,
                'name'              => $p['name'],
                'slug'              => $slug,
                'sku'               => $p['sku'],
                'barcode'           => $p['barcode'],
                'short_description' => mb_substr($p['desc'], 0, 120) . '…',
                'description'       => $p['desc'],
                'price'             => $p['price'],
                'compare_price'     => $p['compare'],
                'cost_price'        => $p['cost'],
                'currency'          => 'PHP',
                'product_type'      => $type,
                'weight_kg'         => $p['weight'] ?? null,
                'warranty_months'   => $p['warranty'] ?? null,
                'status'            => $p['stock'] === 0 ? 'out_of_stock' : 'active',
                'is_featured'       => $p['featured'],
                'average_rating'    => round(mt_rand(42, 50) / 10, 1),
                'total_reviews'     => mt_rand(2, 60),
                'total_sold'        => mt_rand(5, 300),
                'created_at'        => $now->copy()->subDays(mt_rand(1, 30)),
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
                'unit_of_measure'   => $type === 'service' ? 'service' : 'piece',
                'last_stock_update' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Opening stock movement for physical products
            if ($p['stock'] > 0 && $type !== 'service') {
                DB::table('stock_movements')->insert([
                    'inventory_id'    => $inventoryId,
                    'product_id'      => $productId,
                    'vendor_id'       => $vendorId,
                    'movement_type'   => 'opening_stock',
                    'quantity_change' => $p['stock'],
                    'quantity_before' => 0,
                    'quantity_after'  => $p['stock'],
                    'notes'           => 'Opening stock — ProductSeeder',
                    'performed_by'    => null,
                    'created_at'      => $now->copy()->subDays(30),
                    'updated_at'      => $now->copy()->subDays(30),
                ]);
            }

            $inserted++;
        }

        $this->command->info("  ✔  ProductSeeder: {$inserted} products seeded.");
    }

    private function uniqueSlug(string $name, int $vendorId): string
    {
        $base  = \Illuminate\Support\Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (DB::table('products')->where('vendor_id', $vendorId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
