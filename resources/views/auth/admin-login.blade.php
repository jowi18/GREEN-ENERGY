<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Sign In — {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --green-900: #0b1f12;
            --green-700: #1a3d24;
            --green-500: #22a050;
            --green-400: #2ecc71;
            --green-50: #edfaf2;
            --yellow: #f5c518;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--green-900);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated background rings */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.04;
            pointer-events: none;
        }

        body::before {
            width: 600px;
            height: 600px;
            border: 80px solid var(--green-400);
            top: -200px;
            right: -200px;
            animation: pulse 8s ease-in-out infinite;
        }

        body::after {
            width: 400px;
            height: 400px;
            border: 60px solid var(--yellow);
            bottom: -150px;
            left: -100px;
            animation: pulse 10s ease-in-out infinite 2s;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.04;
            }

            50% {
                transform: scale(1.08);
                opacity: 0.07;
            }
        }

        /* Hex grid decoration */
        .bg-hex {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(60deg,
                    rgba(46, 204, 113, 0.03) 0px, rgba(46, 204, 113, 0.03) 1px,
                    transparent 1px, transparent 28px),
                repeating-linear-gradient(-60deg,
                    rgba(46, 204, 113, 0.03) 0px, rgba(46, 204, 113, 0.03) 1px,
                    transparent 1px, transparent 28px);
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.5rem 2.25rem;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(12px);
            box-shadow:
                0 0 0 1px rgba(46, 204, 113, 0.08),
                0 40px 80px rgba(0, 0, 0, 0.5);
        }

        /* Top gradient bar */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 2rem;
            right: 2rem;
            height: 2px;
            background: linear-gradient(90deg, var(--green-400), var(--yellow));
            border-radius: 0 0 2px 2px;
        }

        .login-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .login-logo__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--green-400), var(--green-500));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--green-900);
            box-shadow: 0 4px 16px rgba(46, 204, 113, 0.3);
        }

        .login-logo__text .name {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .login-logo__text .sub {
            font-size: 0.67rem;
            font-weight: 600;
            color: var(--yellow);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
            margin-bottom: 0.4rem;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.45);
            margin-bottom: 1.75rem;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 0.02em;
            margin-bottom: 0.4rem;
            display: block;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            color: #fff;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .form-control:focus {
            border-color: var(--green-400);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.18);
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.18);
        }

        .invalid-feedback {
            font-size: 0.78rem;
            color: #fc8c80;
            margin-top: 0.3rem;
        }

        /* Password wrapper */
        .pw-wrap {
            position: relative;
        }

        .pw-wrap .pw-toggle {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.35);
            font-size: 1rem;
            transition: color 0.15s;
        }

        .pw-wrap .pw-toggle:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Remember / forgot row */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.6rem 0 1.4rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.45);
            cursor: pointer;
        }

        .remember-label input {
            accent-color: var(--green-400);
        }

        .forgot-link {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.45);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }

        .forgot-link:hover {
            color: var(--green-400);
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--green-500), var(--green-400));
            color: var(--green-900);
            border: none;
            border-radius: 10px;
            padding: 0.72rem;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            box-shadow: 0 4px 20px rgba(46, 204, 113, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            opacity: 0.92;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Security badge */
        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.2);
        }

        /* Error card */
        .error-box {
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: #fc8c80;
            font-size: 0.83rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
        }

        /* Back to main link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.25);
            text-decoration: none;
            margin-top: 1.25rem;
            transition: color 0.15s;
        }

        .back-link:hover {
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body>
    <div class="bg-hex"></div>

    <div class="login-card">

        <div class="login-logo">
            <div class="login-logo__icon">S</div>
            <div class="login-logo__text">
                <div class="name">{{ config('app.name', 'SolarHub') }}</div>
                <div class="sub">Admin Portal</div>
            </div>
        </div>

        <h2 class="login-title">Admin Sign In</h2>
        <p class="login-subtitle">Restricted area. Authorised personnel only.</p>

        @if ($errors->any())
            <div class="error-box">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="error-box">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" id="adminLoginForm">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="admin@example.com" autofocus required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-1">
                <label class="form-label">Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="adminPw"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Admin password"
                        required>
                    <button type="button" class="pw-toggle" id="pwToggle"
                        onclick="const i=document.getElementById('adminPw');const ic=document.querySelector('#pwToggle i');i.type=i.type==='password'?'text':'password';ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember">
                    Stay signed in
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit" id="adminLoginBtn">
                <i class="bi bi-shield-lock"></i>
                Sign In to Admin Panel
            </button>
        </form>

        <div class="security-note">
            <i class="bi bi-lock-fill"></i>
            256-bit encrypted connection
        </div>

        <a href="{{ route('home') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to main site
        </a>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', function() {
            const btn = document.getElementById('adminLoginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Authenticating…';
        });
    </script>
</body>

</html>
