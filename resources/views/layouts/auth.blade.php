<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') — {{ config('app.name') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts: DM Sans + Syne --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --green-900: #0d3d1a;
            --green-800: #145228;
            --green-700: #1a6b34;
            --green-600: #218544;
            --green-500: #28a055;
            --green-400: #4cbe74;
            --green-100: #d4f0de;
            --green-50: #edfaf2;
            --yellow-500: #f5c518;
            --yellow-400: #f7d14b;
            --yellow-100: #fef9e0;
            --surface: #f7f9f7;
            --border: #d8e8dc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            background: var(--surface);
            color: #1a2e1f;
            min-height: 100vh;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Left panel — branding */
        .auth-panel-left {
            background: var(--green-900);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
        }

        .auth-panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 80%, rgba(245, 197, 24, 0.12) 0%, transparent 70%),
                radial-gradient(ellipse 50% 60% at 80% 10%, rgba(40, 160, 85, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Decorative solar hex grid */
        .hex-grid {
            position: absolute;
            top: 0;
            right: -60px;
            width: 320px;
            opacity: 0.06;
        }

        .brand-logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .brand-logo .logo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--yellow-400);
        }

        .auth-panel-tagline {
            font-family: 'Syne', sans-serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -0.03em;
        }

        .auth-panel-tagline span {
            color: var(--yellow-400);
        }

        .auth-panel-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            margin-top: 1rem;
            max-width: 320px;
            line-height: 1.7;
        }

        .auth-feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0 0;
        }

        .auth-feature-list li {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
        }

        .auth-feature-list li .fi {
            color: var(--yellow-400);
            font-size: 1rem;
        }

        .auth-panel-footer {
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.8rem;
        }

        /* Right panel — form */
        .auth-panel-right {
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 4rem;
        }

        .auth-form-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--green-900);
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }

        .auth-form-header p {
            color: #5a7060;
            font-size: 0.9rem;
        }

        /* Step indicator */
        .step-indicator {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step-dot {
            height: 4px;
            border-radius: 2px;
            flex: 1;
            background: var(--border);
            transition: background 0.3s;
        }

        .step-dot.active {
            background: var(--green-500);
        }

        .step-dot.done {
            background: var(--green-300);
        }

        /* Form controls */
        .form-label {
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--green-900);
            margin-bottom: 0.4rem;
        }

        .form-control,
        .form-select {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green-500);
            box-shadow: 0 0 0 3px rgba(40, 160, 85, 0.12);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .input-group-text {
            background: var(--green-50);
            border: 1.5px solid var(--border);
            border-radius: 10px 0 0 10px;
            color: var(--green-700);
        }

        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }

        /* Buttons */
        .btn-primary-green {
            background: var(--green-600);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-green:hover {
            background: var(--green-700);
        }

        .btn-primary-green:active {
            transform: scale(0.98);
        }

        .btn-outline-green {
            background: transparent;
            color: var(--green-600);
            border: 1.5px solid var(--green-400);
            border-radius: 10px;
            padding: 0.65rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-outline-green:hover {
            background: var(--green-50);
            border-color: var(--green-600);
        }

        /* Document upload card */
        .doc-upload-card {
            border: 1.5px dashed var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            background: #fff;
            margin-bottom: 0.75rem;
            transition: border-color 0.2s;
        }

        .doc-upload-card:hover {
            border-color: var(--green-400);
        }

        .doc-upload-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--green-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green-600);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Alerts */
        .alert-success-custom {
            background: var(--green-50);
            border: 1.5px solid var(--green-100);
            border-radius: 10px;
            color: var(--green-800);
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        .alert-error-custom {
            background: #fff5f5;
            border: 1.5px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
        }

        /* Leaflet map */
        #vendor-map {
            height: 300px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            z-index: 1;
        }

        .map-coords-display {
            font-size: 0.8rem;
            color: var(--green-700);
            background: var(--green-50);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
        }

        /* Divider */
        .auth-divider {
            text-align: center;
            position: relative;
            margin: 1.5rem 0;
            color: #8fa695;
            font-size: 0.8rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: var(--border);
        }

        .auth-divider::before {
            left: 0;
        }

        .auth-divider::after {
            right: 0;
        }

        /* Status page */
        .status-card {
            border-radius: 16px;
            border: 1.5px solid var(--border);
            background: #fff;
            padding: 2rem;
        }

        .status-icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1.25rem;
        }

        .status-icon-pending {
            background: #fef9e0;
            color: var(--yellow-500);
        }

        .status-icon-review {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-icon-approved {
            background: var(--green-50);
            color: var(--green-600);
        }

        .status-icon-rejected {
            background: #fff5f5;
            color: #dc2626;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-panel-left {
                display: none;
            }

            .auth-panel-right {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-shell">

        {{-- ── Left branding panel ── --}}
        <div class="auth-panel-left">
            <svg class="hex-grid" viewBox="0 0 300 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                @for ($row = 0; $row < 6; $row++)
                    @for ($col = 0; $col < 4; $col++)
                        @php
                            $cx = 40 + $col * 72 + ($row % 2 ? 36 : 0);
                            $cy = 40 + $row * 62;
                            $r = 28;
                            $pts = [];
                            for ($i = 0; $i < 6; $i++) {
                                $angle = deg2rad(60 * $i - 30);
                                $pts[] = round($cx + $r * cos($angle), 1) . ',' . round($cy + $r * sin($angle), 1);
                            }
                        @endphp
                        <polygon points="{{ implode(' ', $pts) }}" stroke="white" stroke-width="1" fill="none" />
                    @endfor
                @endfor
            </svg>

            <a href="{{ route('home') }}" class="brand-logo" style="position:relative;z-index:2;">
                <span class="logo-dot"></span>
                {{ config('app.name', 'SolarHub') }}
            </a>

            <div style="position:relative;z-index:2;">
                <p class="auth-panel-tagline">
                    Power the future.<br>
                    <span>Sell clean energy.</span>
                </p>
                <p class="auth-panel-subtitle">
                    @yield('panel-subtitle', 'Join the Philippines\' fastest-growing renewable energy marketplace.')
                </p>

                <ul class="auth-feature-list">
                    @yield(
                        'panel-features',
                        collect([['bi-shop', 'Your own solar storefront'], ['bi-pos2', 'POS system for walk-in sales'], ['bi-box-seam', 'Inventory & delivery tools'], ['bi-chat-dots', 'Real-time customer chat']])->map(fn($f) => "<li><i class=\"bi {$f[0]} fi\"></i>{$f[1]}</li>")->implode('')
                    )
                </ul>
            </div>

            <p class="auth-panel-footer" style="position:relative;z-index:2;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>

        {{-- ── Right form panel ── --}}
        <div class="auth-panel-right">
            @if (session('success'))
                <div class="alert-success-custom mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error-custom mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('form-content')
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    @stack('scripts')
</body>

</html>
