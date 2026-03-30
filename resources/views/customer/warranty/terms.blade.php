{{-- ================================================================
     resources/views/customer/warranty/terms.blade.php
================================================================ --}}
@extends('layouts.customer')
@section('title', 'Warranty Terms & Conditions')

@push('styles')
    <style>
        .wt-hero {
            background: linear-gradient(135deg, var(--cg-700) 0%, var(--cg-500) 100%);
            border-radius: var(--r-xl);
            padding: 2.5rem 2rem;
            color: #fff;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .wt-hero::before {
            content: '🛡️';
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 5rem;
            opacity: .15;
            pointer-events: none;
        }

        .wt-hero__title {
            font-family: 'Nunito', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: .4rem;
            letter-spacing: -.02em;
        }

        .wt-hero__sub {
            font-size: .875rem;
            opacity: .85;
            max-width: 520px;
            line-height: 1.6;
        }

        .wt-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: var(--r-full);
            padding: .25rem .75rem;
            font-size: .75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            backdrop-filter: blur(4px);
        }

        /* ── Section cards ── */
        .wt-section {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .wt-section__header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--card-border);
            cursor: pointer;
            user-select: none;
            transition: background .15s;
        }

        .wt-section__header:hover {
            background: var(--n-50);
        }

        .wt-section__icon {
            width: 38px;
            height: 38px;
            border-radius: var(--r-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .wt-section__icon--green {
            background: var(--cg-50);
            color: var(--cg-600);
        }

        .wt-section__icon--red {
            background: #fef2f2;
            color: #dc2626;
        }

        .wt-section__icon--yellow {
            background: #fffbeb;
            color: #d97706;
        }

        .wt-section__icon--blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .wt-section__icon--purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .wt-section__title {
            font-family: 'Nunito', sans-serif;
            font-size: .95rem;
            font-weight: 800;
            color: var(--tx-primary);
            flex: 1;
        }

        .wt-section__chevron {
            color: var(--tx-muted);
            font-size: .8rem;
            transition: transform .2s;
        }

        .wt-section__body {
            padding: 1.25rem;
        }

        /* ── Coverage items ── */
        .coverage-item {
            display: flex;
            gap: .85rem;
            padding: .85rem;
            border-radius: var(--r-md);
            margin-bottom: .6rem;
            border: 1.5px solid transparent;
            transition: border-color .15s, background .15s;
        }

        .coverage-item:last-child {
            margin-bottom: 0;
        }

        .coverage-item--covered {
            background: var(--cg-50);
            border-color: var(--cg-100);
        }

        .coverage-item--excluded {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .coverage-item--neutral {
            background: var(--n-50);
            border-color: var(--card-border);
        }

        .coverage-item__icon {
            font-size: 1.2rem;
            flex-shrink: 0;
            line-height: 1.4;
        }

        .coverage-item__title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
            margin-bottom: .2rem;
        }

        .coverage-item__desc {
            font-size: .8rem;
            color: var(--tx-secondary);
            line-height: 1.6;
        }

        .coverage-item__example {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-top: .35rem;
            font-size: .75rem;
            color: var(--tx-muted);
            background: rgba(0, 0, 0, .04);
            padding: .2rem .6rem;
            border-radius: var(--r-full);
        }

        /* ── Product-specific table ── */
        .product-warranty-grid {
            display: grid;
            gap: .75rem;
        }

        .pw-card {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
        }

        .pw-card__head {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1rem;
            background: var(--n-50);
            border-bottom: 1px solid var(--card-border);
        }

        .pw-card__emoji {
            font-size: 1.2rem;
        }

        .pw-card__name {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
        }

        .pw-card__body {
            padding: .75rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .pw-item {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            font-size: .8rem;
            color: var(--tx-secondary);
            line-height: 1.5;
        }

        .pw-item i {
            color: var(--cg-500);
            font-size: .75rem;
            margin-top: .15rem;
            flex-shrink: 0;
        }

        /* ── Duration table ── */
        .duration-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }

        .duration-table th {
            background: var(--n-50);
            padding: .6rem .85rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tx-muted);
            text-align: left;
            border-bottom: 1.5px solid var(--card-border);
        }

        .duration-table td {
            padding: .65rem .85rem;
            border-bottom: 1px solid var(--n-100);
            color: var(--tx-secondary);
            vertical-align: top;
        }

        .duration-table tr:last-child td {
            border-bottom: none;
        }

        .duration-table td:first-child {
            font-weight: 600;
            color: var(--tx-primary);
        }

        .duration-badge {
            display: inline-block;
            padding: .15rem .55rem;
            border-radius: var(--r-full);
            font-size: .72rem;
            font-weight: 700;
            background: var(--cg-50);
            color: var(--cg-700);
            border: 1px solid var(--cg-100);
            white-space: nowrap;
        }

        /* ── How to claim steps ── */
        .claim-steps {
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
        }

        .claim-steps::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 24px;
            bottom: 24px;
            width: 2px;
            background: var(--card-border);
            z-index: 0;
        }

        .claim-step {
            display: flex;
            gap: 1rem;
            position: relative;
            z-index: 1;
            padding-bottom: 1.25rem;
        }

        .claim-step:last-child {
            padding-bottom: 0;
        }

        .claim-step__num {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--cg-600);
            color: #fff;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: .875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 3px solid var(--card-bg);
            box-shadow: 0 0 0 2px var(--cg-200);
        }

        .claim-step__content {
            padding-top: .4rem;
        }

        .claim-step__title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
            margin-bottom: .2rem;
        }

        .claim-step__desc {
            font-size: .8rem;
            color: var(--tx-secondary);
            line-height: 1.6;
        }

        /* ── Info callout ── */
        .wt-callout {
            display: flex;
            gap: .75rem;
            padding: .85rem 1rem;
            border-radius: var(--r-md);
            margin-top: 1rem;
            font-size: .82rem;
            line-height: 1.6;
        }

        .wt-callout--info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .wt-callout--warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .wt-callout--green {
            background: var(--cg-50);
            border: 1px solid var(--cg-200);
            color: var(--cg-800);
        }

        .wt-callout i {
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: .1rem;
        }

        /* ── Quick summary chips ── */
        .summary-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1rem;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .85rem;
            border-radius: var(--r-full);
            font-size: .78rem;
            font-weight: 600;
            border: 1.5px solid;
        }

        .summary-chip--covered {
            background: var(--cg-50);
            color: var(--cg-700);
            border-color: var(--cg-200);
        }

        .summary-chip--excluded {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* ── CTA ── */
        .wt-cta {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px solid var(--cg-200);
            border-radius: var(--r-xl);
            padding: 1.75rem;
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:760px;">

        {{-- ── Hero ── --}}
        <div class="wt-hero">

            {{-- Shop info bar --}}
            <div
                style="display:flex;align-items:center;gap:.85rem;
        background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);
        border-radius:var(--r-md);padding:.75rem 1rem;margin-bottom:1.25rem;
        backdrop-filter:blur(6px);">

                {{-- Logo --}}
                @if ($vendor->shop_logo)
                    <img src="{{ Storage::url($vendor->shop_logo) }}" alt="{{ $vendor->business_name }}"
                        style="width:44px;height:44px;border-radius:var(--r-md);
                        object-fit:cover;border:2px solid rgba(255,255,255,.3);flex-shrink:0;">
                @else
                    <div
                        style="width:44px;height:44px;border-radius:var(--r-md);
                background:rgba(255,255,255,.2);color:#fff;font-weight:800;
                font-size:.9rem;display:flex;align-items:center;justify-content:center;
                flex-shrink:0;border:2px solid rgba(255,255,255,.3);">
                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                    </div>
                @endif

                {{-- Info --}}
                <div style="flex:1;min-width:0;">
                    <div
                        style="font-weight:800;font-size:.95rem;color:#fff;
                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $vendor->business_name }}
                    </div>
                    <div
                        style="font-size:.72rem;color:rgba(255,255,255,.8);margin-top:.1rem;
                display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                        @if ($vendor->city)
                            <span><i
                                    class="bi bi-geo-alt me-1"></i>{{ $vendor->city }}{{ $vendor->province_state ? ', ' . $vendor->province_state : '' }}</span>
                        @endif
                        @if ($vendor->business_phone)
                            <span>·</span>
                            <span><i class="bi bi-telephone me-1"></i>{{ $vendor->business_phone }}</span>
                        @endif
                        @if ($vendor->business_email)
                            <span>·</span>
                            <span><i class="bi bi-envelope me-1"></i>{{ $vendor->business_email }}</span>
                        @endif
                    </div>
                </div>

                {{-- Rating --}}
                @if ($vendor->average_rating)
                    <div style="text-align:center;flex-shrink:0;">
                        <div style="font-weight:800;font-size:1.1rem;color:#fff;line-height:1;">
                            {{ number_format($vendor->average_rating, 1) }}
                        </div>
                        <div style="font-size:.65rem;color:rgba(255,255,255,.75);">
                            ⭐ {{ $vendor->total_reviews }} reviews
                        </div>
                    </div>
                @endif

            </div>

            <div class="wt-hero__badge">
                <i class="bi bi-shield-fill-check"></i> Official Warranty Policy
            </div>
            <div class="wt-hero__title">Warranty Terms & Conditions</div>
            <div class="wt-hero__sub">
                Understand what your product warranty covers, what is excluded,
                and how to file a claim. This policy applies to all products
                purchased from <strong>{{ $vendor->business_name }}</strong>.
            </div>
        </div>

        {{-- ── Quick summary ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--blue">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="wt-section__title">Quick Summary</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">
                <p style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;margin-bottom:1rem;">
                    A warranty protects you against <strong>manufacturing defects</strong> and
                    <strong>normal-use failures</strong> within a specified period. It does <strong>not</strong>
                    cover damage caused by misuse, accidents, or unauthorized modifications.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <div>
                        <div
                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.06em;color:var(--cg-600);margin-bottom:.5rem;">
                            ✅ Generally Covered
                        </div>
                        <div class="summary-chips" style="margin-top:0;">
                            @foreach (['Factory defects', 'Hardware failure', 'Faulty components', 'Early failure', 'Performance drop', 'Free repair parts'] as $chip)
                                <span class="summary-chip summary-chip--covered">
                                    <i class="bi bi-check2"></i> {{ $chip }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div
                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.06em;color:#dc2626;margin-bottom:.5rem;">
                            ❌ Generally Excluded
                        </div>
                        <div class="summary-chips" style="margin-top:0;">
                            @foreach (['Physical damage', 'Water/fire damage', 'Wrong installation', 'Power surge', 'Wear & tear', 'Unauthorized repair'] as $chip)
                                <span class="summary-chip summary-chip--excluded">
                                    <i class="bi bi-x"></i> {{ $chip }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── What IS covered ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--green">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div class="wt-section__title">✅ What Is Covered</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">

                @php
                    $covered = [
                        [
                            '🏭',
                            'Factory / Manufacturing Defects',
                            'Problems that exist from the time of manufacturing, before the customer even uses the product.',
                            'Device does not power on despite correct setup and power supply.',
                        ],
                        [
                            '⚙️',
                            'Hardware Malfunction',
                            'Internal components that stop working unexpectedly during normal use within the warranty period.',
                            'Inverter stops converting power; fan motor fails; display stops working.',
                        ],
                        [
                            '📉',
                            'Early Failure Under Normal Usage',
                            'Product breaks significantly earlier than its rated or expected lifespan without any misuse or negligence.',
                            'A battery rated for 5 years failing completely after 6 months of normal use.',
                        ],
                        [
                            '🔩',
                            'Faulty Components',
                            'Defective internal parts including batteries, wiring, circuit boards, sensors, or electrical connectors.',
                            'A faulty control board causing the unit to overheat or shut down unexpectedly.',
                        ],
                        [
                            '📊',
                            'Guaranteed Performance Issues',
                            'For products with a stated performance guarantee, significant drops below the rated output are covered.',
                            'Solar panel output falls below 80% of the rated wattage within the performance warranty period.',
                        ],
                        [
                            '🔧',
                            'Free Repair Parts During Warranty',
                            'Replacement parts required to fix a covered defect are provided at no cost. Labor coverage depends on the specific product policy.',
                            'A defective charge controller is replaced free of charge within a 2-year warranty.',
                        ],
                    ];
                @endphp

                @foreach ($covered as [$emoji, $title, $desc, $example])
                    <div class="coverage-item coverage-item--covered">
                        <div class="coverage-item__icon">{{ $emoji }}</div>
                        <div>
                            <div class="coverage-item__title">{{ $title }}</div>
                            <div class="coverage-item__desc">{{ $desc }}</div>
                            <div class="coverage-item__example">
                                <i class="bi bi-info-circle"></i>
                                <strong>Example:</strong> {{ $example }}
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- ── What is NOT covered ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--red">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="wt-section__title">❌ What Is NOT Covered</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">

                @php
                    $excluded = [
                        [
                            '💥',
                            'Physical Damage',
                            'Damage from drops, impacts, cracks, dents, or any external force is not covered, regardless of when it occurred.',
                            'Cracked solar panel glass from falling debris or accidental drop during installation.',
                        ],
                        [
                            '💧',
                            'Water or Fire Damage',
                            'Damage caused by flooding, submersion, rain exposure, or fire — unless the product is explicitly rated as waterproof or fire-resistant.',
                            'An inverter damaged by flooding in a non-waterproof installation area.',
                        ],
                        [
                            '🔌',
                            'Improper Installation',
                            'Damage or failure resulting from incorrect setup, wrong wiring, or failure to follow the installation manual. This is especially critical for solar panels, inverters, and battery systems.',
                            'An inverter burned out because of reversed polarity during DIY installation.',
                        ],
                        [
                            '🛠️',
                            'Unauthorized Repair or Modification',
                            'Any repair, modification, or tampering done by a non-authorized technician automatically voids the warranty for the affected component.',
                            'Opening the inverter casing and replacing internal parts without vendor authorization.',
                        ],
                        [
                            '⚡',
                            'Power Surge or Lightning Damage',
                            'Damage from electrical surges, overvoltage, or lightning strikes is considered an external event and is not covered under standard warranty.',
                            'A charge controller damaged by a lightning strike during a storm.',
                        ],
                        [
                            '🌀',
                            'Normal Wear and Tear',
                            'Gradual deterioration from regular use over time — including cosmetic changes, consumable parts, and items with a natural lifespan — is expected and not a defect.',
                            'Fading panel color, minor surface scratches, or a filter/bulb that has reached its rated lifespan.',
                        ],
                        [
                            '🚫',
                            'Misuse or Neglect',
                            'Using the product outside of its rated specifications, environment, or intended purpose.',
                            'Using a 12V battery in a 24V system, or leaving equipment exposed to extreme temperatures.',
                        ],
                        [
                            '⏰',
                            'Claims Filed After Warranty Expiry',
                            'Warranty claims submitted after the official warranty end date will not be honored, regardless of when the issue began.',
                            null,
                        ],
                    ];
                @endphp

                @foreach ($excluded as [$emoji, $title, $desc, $example])
                    <div class="coverage-item coverage-item--excluded">
                        <div class="coverage-item__icon">{{ $emoji }}</div>
                        <div>
                            <div class="coverage-item__title">{{ $title }}</div>
                            <div class="coverage-item__desc">{{ $desc }}</div>
                            @if ($example)
                                <div class="coverage-item__example">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Example:</strong> {{ $example }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="wt-callout wt-callout--warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Important:</strong> Any of the above exclusions found during inspection
                        will result in the warranty claim being rejected. A detailed inspection report
                        will be provided in such cases.
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Solar product coverage ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--yellow">
                    <i class="bi bi-sun-fill"></i>
                </div>
                <div class="wt-section__title">🔋 Solar & Renewable Energy Product Coverage</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">

                <p style="font-size:.82rem;color:var(--tx-secondary);line-height:1.6;margin-bottom:1rem;">
                    Solar and energy products have specific warranty structures. Below are the typical
                    coverage terms per product category sold on this platform.
                </p>

                <div class="product-warranty-grid">

                    @php
                        $solarProducts = [
                            [
                                '☀️',
                                'Solar Panels',
                                [
                                    'Manufacturing defects in materials and workmanship',
                                    'Performance guarantee: ≥ 80–90% rated output after 20–25 years (linear degradation)',
                                    'Frame, junction box, and cable defects',
                                ],
                            ],
                            [
                                '🔄',
                                'Solar Inverters',
                                [
                                    'Internal electronics and control board failure',
                                    'Communication module defects',
                                    'Display and user interface malfunctions',
                                ],
                            ],
                            [
                                '🔋',
                                'Solar Batteries',
                                [
                                    'Charging and discharging defects',
                                    'Abnormal capacity loss beyond rated cycle degradation',
                                    'BMS (Battery Management System) failure',
                                ],
                            ],
                            [
                                '🏗️',
                                'Mounting Structures',
                                [
                                    'Corrosion defects from manufacturing material quality',
                                    'Structural failure due to manufacturing issues (not weather events)',
                                    'Incorrect dimension or specification from factory',
                                ],
                            ],
                            [
                                '🔌',
                                'Charge Controllers & Accessories',
                                [
                                    'MPPT/PWM controller failure under normal load',
                                    'Defective voltage regulation',
                                    'Factory wiring and connector defects',
                                ],
                            ],
                        ];
                    @endphp

                    @foreach ($solarProducts as [$emoji, $name, $items])
                        <div class="pw-card">
                            <div class="pw-card__head">
                                <span class="pw-card__emoji">{{ $emoji }}</span>
                                <span class="pw-card__name">{{ $name }}</span>
                            </div>
                            <div class="pw-card__body">
                                @foreach ($items as $item)
                                    <div class="pw-item">
                                        <i class="bi bi-check-circle-fill"></i>
                                        {{ $item }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>

                <div class="wt-callout wt-callout--info" style="margin-top:1rem;">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        Performance warranties for solar panels require a certified performance test
                        report. Please keep your installation records and a baseline output reading
                        taken at the time of installation.
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Warranty duration ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--purple">
                    <i class="bi bi-calendar-range-fill"></i>
                </div>
                <div class="wt-section__title">📅 Typical Warranty Durations</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body" style="padding:0;">
                <div class="table-responsive">
                    <table class="duration-table">
                        <thead>
                            <tr>
                                <th>Product Category</th>
                                <th>Workmanship Warranty</th>
                                <th>Performance Warranty</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $durations = [
                                    [
                                        'Solar Panels',
                                        '10–12 years',
                                        '25 years',
                                        'Linear degradation guarantee (≥80% output)',
                                    ],
                                    [
                                        'Solar Inverters',
                                        '2–5 years',
                                        '—',
                                        'Extended warranty available from some brands',
                                    ],
                                    [
                                        'Solar Batteries (LiFePO4)',
                                        '3–10 years',
                                        '—',
                                        'Cycle count and capacity conditions apply',
                                    ],
                                    [
                                        'Lead-Acid Batteries',
                                        '6–18 months',
                                        '—',
                                        'Based on proper charging and maintenance',
                                    ],
                                    ['Charge Controllers', '1–2 years', '—', 'Brand-dependent'],
                                    [
                                        'Mounting Structures',
                                        '5–10 years',
                                        '—',
                                        'Against manufacturing corrosion defects',
                                    ],
                                    ['General Electronics', '6–12 months', '—', 'Verify per product listing'],
                                    ['Home Appliances', '1–2 years', '—', 'As stated on product label or manual'],
                                ];
                            @endphp
                            @foreach ($durations as [$product, $workmanship, $performance, $note])
                                <tr>
                                    <td>{{ $product }}</td>
                                    <td><span class="duration-badge">{{ $workmanship }}</span></td>
                                    <td>
                                        @if ($performance !== '—')
                                            <span class="duration-badge"
                                                style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                                                {{ $performance }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-size:.78rem;">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.75rem;color:var(--tx-muted);">{{ $note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:.85rem 1.25rem;">
                    <div class="wt-callout wt-callout--warning" style="margin-top:0;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            The exact warranty duration for your product is stated on the product listing page
                            and your purchase receipt. The table above reflects <strong>typical industry ranges</strong>
                            and may vary per brand and vendor.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── How to file a claim ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--green">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <div class="wt-section__title">📋 How to File a Warranty Claim</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">

                <div class="claim-steps">
                    @php
                        $steps = [
                            [
                                'Verify Your Warranty',
                                'Check your product listing or purchase receipt to confirm the warranty period is still active. Claims filed after expiry cannot be processed.',
                            ],
                            [
                                'Prepare Your Documents',
                                'Gather your proof of purchase (receipt or order confirmation), photos of the defect or issue, the product\'s serial number, and any relevant installation records.',
                            ],
                            [
                                'Submit a Warranty Claim',
                                'Go to My Orders → select the order → click "File Warranty Claim." Fill in the claim type, describe the issue in detail, and upload your photos and documents.',
                            ],
                            [
                                'Await Review',
                                'The vendor will review your claim within 1–5 business days. You will receive a notification when the claim status changes.',
                            ],
                            [
                                'Inspection or Service Scheduling',
                                'If approved, a technician may be assigned to inspect or service your product. You will be notified of the scheduled date and time slot.',
                            ],
                            [
                                'Resolution',
                                'Once the inspection is complete, the vendor will either repair, replace, or refund as appropriate. You will receive a completion report with photos.',
                            ],
                        ];
                    @endphp

                    @foreach ($steps as $i => [$title, $desc])
                        <div class="claim-step">
                            <div class="claim-step__num">{{ $i + 1 }}</div>
                            <div class="claim-step__content">
                                <div class="claim-step__title">{{ $title }}</div>
                                <div class="claim-step__desc">{{ $desc }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="wt-callout wt-callout--green" style="margin-top:1.25rem;">
                    <i class="bi bi-lightbulb-fill"></i>
                    <div>
                        <strong>Tip:</strong> Take a short video of the issue before filing your claim.
                        A clear video showing the defect or malfunction significantly speeds up the
                        review process.
                    </div>
                </div>

            </div>
        </div>

        {{-- ── What determines coverage ── --}}
        <div class="wt-section">
            <div class="wt-section__header" onclick="toggleSection(this)">
                <div class="wt-section__icon wt-section__icon--blue">
                    <i class="bi bi-sliders"></i>
                </div>
                <div class="wt-section__title">📄 What Determines Your Exact Coverage</div>
                <i class="bi bi-chevron-down wt-section__chevron"></i>
            </div>
            <div class="wt-section__body">

                <p style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;margin-bottom:1rem;">
                    Warranty coverage is not one-size-fits-all. The following factors determine
                    the exact scope of your warranty:
                </p>

                @php
                    $factors = [
                        [
                            'bi-tag-fill',
                            'Warranty Type',
                            'Store warranty, manufacturer warranty, replacement warranty, or service warranty — each has different coverage scope and claim procedures.',
                        ],
                        [
                            'bi-calendar-check',
                            'Warranty Duration',
                            'Coverage only applies within the stated warranty period. The start date is typically the purchase date or delivery date.',
                        ],
                        [
                            'bi-box-seam-fill',
                            'Product Category',
                            'Different product types have different warranty norms. A solar panel has a longer warranty than a consumer electronic accessory.',
                        ],
                        [
                            'bi-tools',
                            'Installation Requirements',
                            'Many warranties require certified or professional installation. DIY installation may void the warranty for certain products.',
                        ],
                        [
                            'bi-file-earmark-ruled',
                            'Usage Conditions',
                            'Operating the product within its rated specifications (voltage, temperature, load) is required to maintain warranty validity.',
                        ],
                        [
                            'bi-shop',
                            'Vendor Policy',
                            'Each vendor on this platform may have additional terms. Always check the specific warranty policy on the product listing page.',
                        ],
                    ];
                @endphp

                @foreach ($factors as [$icon, $title, $desc])
                    <div class="coverage-item coverage-item--neutral" style="margin-bottom:.6rem;">
                        <div class="coverage-item__icon">
                            <i class="bi {{ $icon }}" style="color:var(--cg-600);font-size:1rem;"></i>
                        </div>
                        <div>
                            <div class="coverage-item__title">{{ $title }}</div>
                            <div class="coverage-item__desc">{{ $desc }}</div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- ── CTA ── --}}
        <div class="wt-cta">
            <div style="font-size:2rem;margin-bottom:.5rem;">🛡️</div>
            <div class="fw-800" style="font-family:'Nunito',sans-serif;font-size:1.05rem;margin-bottom:.4rem;">
                Ready to file a warranty claim?
            </div>
            <div class="text-muted" style="font-size:.82rem;margin-bottom:1.25rem;">
                Go to your orders and select the product you want to claim warranty for.
            </div>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('customer.orders.index') }}" class="cu-btn cu-btn--primary">
                    <i class="bi bi-bag-check"></i> View My Orders
                </a>
                <a href="{{ route('customer.vendors.show', $vendor) }}" class="cu-btn cu-btn--ghost">
                    <i class="bi bi-shop"></i> Back to {{ $vendor->business_name }}
                </a>
            </div>
        </div>

        {{-- Last updated notice --}}
        <div class="text-center text-muted mt-3" style="font-size:.72rem;padding-bottom:1rem;">
            <i class="bi bi-clock me-1"></i>
            Last updated: {{ \Carbon\Carbon::now()->format('F Y') }}
            &nbsp;·&nbsp;
            This document is for informational purposes. Refer to the specific product listing for exact warranty terms.
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function toggleSection(header) {
            const body = header.nextElementSibling;
            const chevron = header.querySelector('.wt-section__chevron');
            const open = body.style.display !== 'none' && body.style.display !== '';

            body.style.display = open ? 'none' : 'block';
            chevron.style.transform = open ? '' : 'rotate(180deg)';
        }

        // All sections open by default — collapse all except first
        document.querySelectorAll('.wt-section__body').forEach((body, i) => {
            if (i > 0) {
                body.style.display = 'none';
                body.previousElementSibling
                    .querySelector('.wt-section__chevron').style.transform = '';
            }
        });
    </script>
@endpush
