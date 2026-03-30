<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ config('app.name') }} — The Philippines' leading multi-vendor platform for solar panels, batteries, inverters, and renewable energy services.">
    <title>{{ config('app.name') }} — Power the Future of Solar Energy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        :root {
            --ink: #040f07;
            --ink-2: #0a1f10;
            --ink-3: #0f2e18;
            --green-900: #052b10;
            --green-800: #0a4019;
            --green-700: #0f5c24;
            --green-600: #157a30;
            --green-500: #1ea040;
            --green-400: #2ecc71;
            --green-300: #5dd88e;
            --green-200: #9eeaba;
            --green-100: #d0f5e2;
            --green-50: #edfaf2;
            --yellow-600: #b08800;
            --yellow-500: #e6b800;
            --yellow-400: #f5c518;
            --yellow-300: #fad85a;
            --yellow-100: #fef3c7;
            --white: #ffffff;
            --off-white: #f6f9f6;
            --gray-200: #d9e4db;
            --gray-400: #7a9280;
            --gray-600: #4a6350;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --font-display: 'Bricolage Grotesque', system-ui, sans-serif;
            --font-body: 'DM Sans', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            font-size: 16px;
            line-height: 1.65;
            color: var(--ink);
            background: var(--white);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
        }


        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.7s cubic-bezier(.22, 1, .36, 1), transform 0.7s cubic-bezier(.22, 1, .36, 1);
        }

        .reveal.visible {
            opacity: 1;
            transform: none;
        }

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        .reveal-delay-4 {
            transition-delay: 0.4s;
        }

        .nav-outer {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            transition: background 0.3s, box-shadow 0.3s, backdrop-filter 0.3s;
        }

        .nav-outer.scrolled {
            background: rgba(4, 15, 7, 0.92);
            backdrop-filter: blur(16px);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.07);
        }

        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.03em;
        }

        .nav-logo__dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--yellow-400);
            box-shadow: 0 0 8px var(--yellow-400);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.4rem 0.75rem;
            border-radius: var(--radius-sm);
            transition: color 0.15s, background 0.15s;
        }

        .nav-links a:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-nav-ghost {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            border-radius: var(--radius-sm);
            transition: all 0.15s;
            font-family: var(--font-body);
            background: transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-nav-ghost:hover {
            border-color: rgba(255, 255, 255, 0.4);
            color: var(--white);
            background: rgba(255, 255, 255, 0.06);
        }

        .btn-nav-primary {
            background: var(--green-400);
            color: var(--ink);
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.48rem 1.1rem;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            font-family: var(--font-body);
            transition: background 0.15s, box-shadow 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 12px rgba(46, 204, 113, 0.35);
        }

        .btn-nav-primary:hover {
            background: var(--green-300);
            box-shadow: 0 4px 20px rgba(46, 204, 113, 0.5);
        }

        .nav-hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
        }


        .hero {
            background: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 120px 0 80px;
        }

        /* Radial green glow */
        .hero__glow {
            position: absolute;
            width: 900px;
            height: 900px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(46, 204, 113, 0.12) 0%, transparent 65%);
            top: -200px;
            right: -200px;
            pointer-events: none;
        }

        .hero__glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 197, 24, 0.07) 0%, transparent 65%);
            bottom: -100px;
            left: 5%;
            pointer-events: none;
        }

        /* Animated grid lines */
        .hero__grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(46, 204, 113, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(46, 204, 113, 0.05) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 0%, transparent 100%);
            pointer-events: none;
        }

        .hero__inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.25);
            border-radius: 100px;
            padding: 0.3rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--green-300);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hero__eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green-400);
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0.3
            }
        }

        .hero__headline {
            font-family: var(--font-display);
            font-size: clamp(2.8rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.04em;
            color: var(--white);
            margin-bottom: 1.5rem;
        }

        .hero__headline .accent-green {
            color: var(--green-400);
        }

        .hero__headline .accent-yellow {
            color: var(--yellow-400);
        }

        .hero__sub {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 2.5rem;
        }

        .hero__actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: var(--green-400);
            color: var(--ink);
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 700;
            padding: 0.85rem 1.75rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 24px rgba(46, 204, 113, 0.4);
            transition: all 0.2s;
            letter-spacing: -0.01em;
        }

        .btn-hero-primary:hover {
            background: var(--green-300);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(46, 204, 113, 0.5);
            color: var(--ink);
        }

        .btn-hero-secondary {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.92rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.15s;
            padding: 0.85rem 0;
        }

        .btn-hero-secondary:hover {
            color: var(--white);
        }

        .btn-hero-secondary .play-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: border-color 0.15s, background 0.15s;
        }

        .btn-hero-secondary:hover .play-icon {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.06);
        }

        /* Hero stats bar */
        .hero__stats {
            display: flex;
            gap: 2.5rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            flex-wrap: wrap;
        }

        .hero__stat-value {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .hero__stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 0.25rem;
        }

        /* Solar panel SVG illustration */
        .hero__visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            transform: perspective(800px) rotateX(8deg) rotateY(-12deg);
            filter: drop-shadow(0 40px 80px rgba(46, 204, 113, 0.25));
        }

        .panel-cell {
            width: 72px;
            height: 72px;
            border-radius: 6px;
            background: linear-gradient(135deg, #0a2e14 0%, #0f4020 50%, #0a2e14 100%);
            border: 1px solid rgba(46, 204, 113, 0.2);
            position: relative;
            overflow: hidden;
            animation: panelGlow 3s ease-in-out infinite;
            animation-delay: var(--delay, 0s);
        }

        @keyframes panelGlow {

            0%,
            100% {
                border-color: rgba(46, 204, 113, 0.15);
                box-shadow: none;
            }

            50% {
                border-color: rgba(46, 204, 113, 0.5);
                box-shadow: 0 0 12px rgba(46, 204, 113, 0.2);
            }
        }

        .panel-cell::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(46, 204, 113, 0.08) 1px, transparent 1px),
                linear-gradient(rgba(46, 204, 113, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .panel-cell::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(135deg, transparent 40%, rgba(46, 204, 113, 0.15) 50%, transparent 60%);
            animation: shimmer 4s linear infinite;
            animation-delay: var(--delay, 0s);
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%);
            }

            100% {
                transform: translateX(100%) translateY(100%);
            }
        }

        /* Energy line from panels */
        .hero__energy-line {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 60px;
            background: linear-gradient(to bottom, var(--green-400), transparent);
            animation: energyPulse 2s ease-in-out infinite;
        }

        @keyframes energyPulse {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 1;
            }
        }

        .hero__badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            backdrop-filter: blur(12px);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            white-space: nowrap;
        }

        .hero__badge--tl {
            top: 10%;
            left: -8%;
            animation: float 6s ease-in-out infinite;
        }

        .hero__badge--br {
            bottom: 10%;
            right: -8%;
            animation: float 6s ease-in-out infinite 3s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .hero__badge .badge-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }


        .section-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--green-600);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .section-eyebrow::before {
            content: '';
            width: 24px;
            height: 2px;
            background: var(--green-400);
            border-radius: 2px;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.035em;
            line-height: 1.1;
            color: var(--ink);
        }

        .section-title .hl {
            color: var(--green-600);
        }

        .section-sub {
            font-size: 1rem;
            color: var(--gray-600);
            line-height: 1.75;
            max-width: 560px;
            margin-top: 1rem;
        }

        .trust-bar {
            background: var(--ink-2);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 1.25rem 0;
            overflow: hidden;
        }

        .trust-bar__inner {
            display: flex;
            align-items: center;
            gap: 3rem;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .trust-item i {
            color: var(--green-400);
            font-size: 0.9rem;
        }


        .benefits {
            padding: 100px 0;
            background: var(--off-white);
        }

        .benefits__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 3.5rem;
        }

        .benefit-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .benefit-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .benefit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--green-400), var(--yellow-400));
            opacity: 0;
            transition: opacity 0.25s;
        }

        .benefit-card:hover::before {
            opacity: 1;
        }

        .benefit-card__num {
            font-family: var(--font-mono);
            font-size: 0.68rem;
            color: var(--green-400);
            letter-spacing: 0.08em;
            margin-bottom: 1.25rem;
        }

        .benefit-card__icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: var(--green-50);
            border: 1px solid var(--green-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: var(--green-600);
            margin-bottom: 1.25rem;
            transition: background 0.2s, transform 0.2s;
        }

        .benefit-card:hover .benefit-card__icon {
            background: var(--green-400);
            color: var(--white);
            transform: scale(1.05);
        }

        .benefit-card__title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 0.6rem;
        }

        .benefit-card__desc {
            font-size: 0.9rem;
            color: var(--gray-600);
            line-height: 1.7;
        }


        .how-it-works {
            padding: 100px 0;
            background: var(--white);
            position: relative;
            overflow: hidden;
        }

        /* Decorative diagonal stripe */
        .how-it-works::before {
            content: '';
            position: absolute;
            top: 0;
            right: -5%;
            width: 50%;
            height: 100%;
            background: var(--off-white);
            transform: skewX(-6deg);
            pointer-events: none;
        }

        .how__layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .how__steps {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .how__step {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem 0;
            position: relative;
            cursor: default;
        }

        .how__step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 22px;
            top: 56px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--green-300), var(--gray-200));
        }

        .how__step-num {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--ink);
            color: var(--white);
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .how__step:hover .how__step-num {
            background: var(--green-600);
            box-shadow: 0 0 0 6px var(--green-50);
        }

        .how__step-title {
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.4rem;
            letter-spacing: -0.02em;
        }

        .how__step-desc {
            font-size: 0.9rem;
            color: var(--gray-600);
            line-height: 1.7;
        }

        /* Visual side */
        .how__visual {
            background: var(--ink);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .how__visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 70% 30%, rgba(46, 204, 113, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .flow-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.75);
            position: relative;
            z-index: 2;
            transition: border-color 0.2s, background 0.2s;
        }

        .flow-card:hover {
            border-color: rgba(46, 204, 113, 0.4);
            background: rgba(46, 204, 113, 0.06);
        }

        .flow-card__icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .flow-card__tag {
            margin-left: auto;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 100px;
            letter-spacing: 0.04em;
        }

        .featured {
            padding: 100px 0;
            background: var(--off-white);
        }

        .featured__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-top: 3rem;
        }

        .vendor-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .vendor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.08);
        }

        .vendor-card__header {
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .vendor-card__header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.03) 0px, rgba(255, 255, 255, 0.03) 1px, transparent 1px, transparent 12px);
        }

        .vendor-card__body {
            padding: 1.25rem;
        }

        .vendor-card__name {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 0.2rem;
        }

        .vendor-card__location {
            font-size: 0.8rem;
            color: var(--gray-400);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .vendor-card__stars {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            color: var(--yellow-500);
            margin: 0.75rem 0;
        }

        .vendor-card__stars span {
            color: var(--gray-600);
            margin-left: 0.2rem;
        }

        .vendor-tag {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 100px;
            background: var(--green-50);
            color: var(--green-700);
            border: 1px solid var(--green-100);
            margin: 0.15rem 0.15rem 0.15rem 0;
        }

        .featured__see-all {
            text-align: center;
            margin-top: 2.5rem;
        }


        .pricing {
            padding: 100px 0;
            background: var(--ink);
            position: relative;
            overflow: hidden;
        }

        .pricing::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 60% at 20% 50%, rgba(46, 204, 113, 0.08) 0%, transparent 70%),
                radial-gradient(ellipse 40% 40% at 80% 20%, rgba(245, 197, 24, 0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .pricing .section-eyebrow {
            color: var(--green-300);
        }

        .pricing .section-title {
            color: var(--white);
        }

        .pricing .section-sub {
            color: rgba(255, 255, 255, 0.5);
        }

        .pricing__intro {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
            margin-bottom: 4rem;
        }

        .pricing__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            position: relative;
            z-index: 2;
        }

        .plan-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            position: relative;
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
        }

        .plan-card:hover {
            border-color: rgba(46, 204, 113, 0.4);
            background: rgba(46, 204, 113, 0.04);
            transform: translateY(-4px);
        }

        .plan-card--featured {
            border-color: var(--green-400);
            background: rgba(46, 204, 113, 0.07);
        }

        .plan-card--featured::before {
            content: 'Most Popular';
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--green-400);
            color: var(--ink);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.25rem 0.9rem;
            border-radius: 100px;
            white-space: nowrap;
        }

        .plan-card__name {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
        }

        .plan-card__price {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: var(--white);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .plan-card__price span {
            font-size: 1rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0;
        }

        .plan-card__cycle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 1.5rem;
        }

        .plan-card__divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin: 1.25rem 0;
        }

        .plan-feature {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.65);
            margin-bottom: 0.65rem;
        }

        .plan-feature i {
            color: var(--green-400);
            font-size: 0.85rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .btn-plan {
            width: 100%;
            margin-top: 1.75rem;
            padding: 0.7rem;
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .btn-plan--default {
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .btn-plan--default:hover {
            background: rgba(255, 255, 255, 0.12);
            color: var(--white);
        }

        .btn-plan--featured {
            background: var(--green-400);
            color: var(--ink);
            box-shadow: 0 4px 16px rgba(46, 204, 113, 0.35);
        }

        .btn-plan--featured:hover {
            background: var(--green-300);
            box-shadow: 0 8px 24px rgba(46, 204, 113, 0.5);
        }


        .services {
            padding: 100px 0;
            background: var(--white);
        }

        .services__grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 3.5rem;
        }

        .service-card {
            border-radius: var(--radius-lg);
            padding: 2.25rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.1);
        }

        .service-card--install {
            background: var(--ink);
        }

        .service-card--maintenance {
            background: linear-gradient(135deg, #0a4019, #0f5c24);
        }

        .service-card--repair {
            background: linear-gradient(135deg, #1a2e08, #2a4010);
        }

        .service-card--consultation {
            background: linear-gradient(135deg, #0f2030, #1a3548);
        }

        .service-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.02) 0px, rgba(255, 255, 255, 0.02) 1px, transparent 1px, transparent 16px);
            pointer-events: none;
        }

        .service-card__icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: var(--white);
            margin-bottom: 1.25rem;
            position: relative;
            z-index: 1;
        }

        .service-card__title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.03em;
            margin-bottom: 0.65rem;
            position: relative;
            z-index: 1;
        }

        .service-card__desc {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.7;
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
        }

        .service-card__link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--green-300);
            position: relative;
            z-index: 1;
            transition: gap 0.15s;
        }

        .service-card__link:hover {
            gap: 0.65rem;
            color: var(--green-200);
        }


        .dual-cta {
            padding: 100px 0;
            background: var(--off-white);
        }

        .dual-cta__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 0;
        }

        .cta-block {
            border-radius: var(--radius-xl);
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .cta-block--vendor {
            background: var(--ink);
        }

        .cta-block--vendor::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 80% at 30% 30%, rgba(46, 204, 113, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .cta-block--customer {
            background: linear-gradient(135deg, var(--green-600), var(--green-700));
        }

        .cta-block--customer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 70% at 70% 70%, rgba(245, 197, 24, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .cta-block__eyebrow {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--green-300);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .cta-block--customer .cta-block__eyebrow {
            color: var(--yellow-300);
        }

        .cta-block__title {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1.1;
            margin-bottom: 0.85rem;
            position: relative;
            z-index: 1;
        }

        .cta-block__sub {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.7;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .btn-cta-vendor {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--green-400);
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(46, 204, 113, 0.35);
            transition: all 0.2s;
            position: relative;
            z-index: 1;
        }

        .btn-cta-vendor:hover {
            background: var(--green-300);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(46, 204, 113, 0.5);
            color: var(--ink);
        }

        .btn-cta-customer {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--yellow-400);
            color: var(--ink-2);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(245, 197, 24, 0.35);
            transition: all 0.2s;
            position: relative;
            z-index: 1;
        }

        .btn-cta-customer:hover {
            background: var(--yellow-300);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(245, 197, 24, 0.5);
            color: var(--ink-2);
        }

        .cta-block__list {
            list-style: none;
            margin: 0 0 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .cta-block__list li {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.65);
        }

        .cta-block__list li i {
            color: var(--green-400);
            font-size: 0.85rem;
        }

        .cta-block--customer .cta-block__list li i {
            color: var(--yellow-300);
        }


        .footer {
            background: var(--ink);
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            padding: 60px 0 32px;
        }

        .footer__grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer__brand p {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.4);
            line-height: 1.7;
            max-width: 280px;
            margin: 0.85rem 0 1.5rem;
        }

        .footer__socials {
            display: flex;
            gap: 0.5rem;
        }

        .footer__social-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
            transition: all 0.15s;
        }

        .footer__social-btn:hover {
            border-color: var(--green-400);
            color: var(--green-400);
            background: rgba(46, 204, 113, 0.08);
        }

        .footer__col-title {
            font-family: var(--font-display);
            font-size: 0.8rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .footer__links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .footer__links a {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.4);
            transition: color 0.15s;
        }

        .footer__links a:hover {
            color: var(--white);
        }

        .footer__bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            padding-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer__copy {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.25);
        }

        .footer__legal {
            display: flex;
            gap: 1.5rem;
        }

        .footer__legal a {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.25);
            transition: color 0.15s;
        }

        .footer__legal a:hover {
            color: rgba(255, 255, 255, 0.5);
        }


        .btn-outline-dark {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            border-radius: var(--radius-md);
            border: 2px solid var(--gray-200);
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-outline-dark:hover {
            border-color: var(--ink);
            background: var(--ink);
            color: var(--white);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--green-600);
            color: var(--white);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.72rem 1.6rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 3px 12px rgba(21, 122, 48, 0.3);
        }

        .btn-primary:hover {
            background: var(--green-700);
            color: var(--white);
            box-shadow: 0 6px 20px rgba(21, 122, 48, 0.45);
        }


        @media (max-width: 1024px) {
            .hero__inner {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }

            .hero__sub {
                margin: 0 auto 2.5rem;
            }

            .hero__actions {
                justify-content: center;
            }

            .hero__stats {
                justify-content: center;
            }

            .hero__visual {
                display: none;
            }

            .how__layout {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .how-it-works::before {
                display: none;
            }

            .pricing__intro {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .benefits__grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer__grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 100px 0 60px;
            }

            .benefits__grid {
                grid-template-columns: 1fr;
            }

            .featured__grid {
                grid-template-columns: 1fr;
            }

            .pricing__grid {
                grid-template-columns: 1fr;
            }

            .services__grid {
                grid-template-columns: 1fr;
            }

            .dual-cta__grid {
                grid-template-columns: 1fr;
            }

            .footer__grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .nav-links {
                display: none;
            }

            .nav-hamburger {
                display: block;
            }

            .nav-actions .btn-nav-ghost {
                display: none;
            }

            .cta-block {
                padding: 2rem;
            }

            .how__visual {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero__headline {
                font-size: 2.4rem;
            }

            .section-title {
                font-size: 1.9rem;
            }

            .section-wrap {
                padding: 0 1.25rem;
            }

            .plan-card {
                padding: 1.5rem;
            }
        }

        /* ── Section shell ── */
        .find-section {
            background: #0b1f0f;
            padding: 5rem 0 0;
            position: relative;
            overflow: hidden;
        }

        .find-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 30%, rgba(21, 128, 61, .18) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 80% 70%, rgba(22, 101, 52, .12) 0%, transparent 70%);
            pointer-events: none;
        }

        .find-section .section-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* ── Header ── */
        .find-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .find-eyebrow {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #4ade80;
            margin-bottom: .5rem;
        }

        .find-title {
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            font-weight: 900;
            line-height: 1.1;
            color: #fff;
            font-family: 'Nunito', sans-serif;
        }

        .find-title .hl {
            color: #4ade80;
            position: relative;
        }

        .find-sub {
            font-size: .9rem;
            color: #86efac;
            margin-top: .6rem;
            line-height: 1.6;
        }

        /* ── Search + filter bar ── */
        .find-controls {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .find-search-wrap {
            flex: 1;
            min-width: 220px;
            position: relative;
        }

        .find-search-wrap i {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: .9rem;
            pointer-events: none;
        }

        .find-search {
            width: 100%;
            background: rgba(255, 255, 255, .07);
            border: 1.5px solid rgba(255, 255, 255, .12);
            border-radius: 10px;
            padding: .7rem 1rem .7rem 2.4rem;
            font-size: .875rem;
            color: #fff;
            outline: none;
            transition: border-color .15s, background .15s;
        }

        .find-search::placeholder {
            color: #6b7280;
        }

        .find-search:focus {
            border-color: #4ade80;
            background: rgba(255, 255, 255, .1);
        }

        .find-filter {
            background: rgba(255, 255, 255, .07);
            border: 1.5px solid rgba(255, 255, 255, .12);
            border-radius: 10px;
            padding: .7rem 1rem;
            font-size: .82rem;
            color: #d1fae5;
            outline: none;
            cursor: pointer;
            transition: border-color .15s;
            appearance: none;
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%234ade80'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .6rem center;
        }

        .find-filter:focus {
            border-color: #4ade80;
        }

        .find-filter option {
            background: #1a2e1f;
            color: #fff;
        }

        .find-locate-btn {
            display: flex;
            align-items: center;
            gap: .4rem;
            background: rgba(74, 222, 128, .12);
            border: 1.5px solid rgba(74, 222, 128, .3);
            border-radius: 10px;
            padding: .7rem 1rem;
            font-size: .82rem;
            font-weight: 700;
            color: #4ade80;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s;
        }

        .find-locate-btn:hover {
            background: rgba(74, 222, 128, .2);
            border-color: #4ade80;
        }

        /* ── Map + sidebar layout ── */
        .find-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 0;
            height: 560px;
            border-radius: 16px 16px 0 0;
            overflow: hidden;
            border: 1.5px solid rgba(255, 255, 255, .1);
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .find-layout {
                grid-template-columns: 1fr;
                height: auto;
                border-radius: 12px 12px 0 0;
            }
        }

        /* ── Sidebar ── */
        .find-sidebar {
            background: #111f14;
            border-right: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .find-sidebar__count {
            padding: .85rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
            flex-shrink: 0;
        }

        .find-sidebar__count span {
            color: #4ade80;
            font-size: .875rem;
        }

        .find-list {
            overflow-y: auto;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: rgba(74, 222, 128, .2) transparent;
        }

        .find-list::-webkit-scrollbar {
            width: 4px;
        }

        .find-list::-webkit-scrollbar-thumb {
            background: rgba(74, 222, 128, .2);
            border-radius: 2px;
        }

        .vendor-list-item {
            display: flex;
            gap: .75rem;
            padding: .9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .05);
            cursor: pointer;
            transition: background .12s;
            align-items: flex-start;
        }

        .vendor-list-item:hover,
        .vendor-list-item.active {
            background: rgba(74, 222, 128, .08);
        }

        .vendor-list-item.active {
            border-left: 3px solid #4ade80;
        }

        .vli-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #0a2e14;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 800;
            color: #4ade80;
            overflow: hidden;
        }

        .vli-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vli-info {
            flex: 1;
            min-width: 0;
        }

        .vli-name {
            font-size: .83rem;
            font-weight: 700;
            color: #f0fdf4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vli-city {
            font-size: .72rem;
            color: #6b7280;
            margin-top: .1rem;
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .vli-stars {
            font-size: .68rem;
            color: #fbbf24;
            margin-top: .25rem;
            display: flex;
            align-items: center;
            gap: .2rem;
        }

        .vli-stars span {
            color: #6b7280;
        }

        .vli-empty {
            padding: 2rem 1rem;
            text-align: center;
            color: #6b7280;
            font-size: .83rem;
        }

        /* ── Map pane ── */
        #findVendorMap {
            width: 100%;
            height: 100%;
            min-height: 400px;
            background: #1a2e1f;
        }

        @media (max-width: 768px) {
            #findVendorMap {
                height: 380px;
            }

            .find-sidebar {
                max-height: 300px;
            }
        }

        /* ── Leaflet popup override ── */
        .leaflet-popup-content-wrapper {
            background: #111f14 !important;
            border: 1.5px solid rgba(74, 222, 128, .3) !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .4) !important;
            color: #f0fdf4 !important;
            padding: 0 !important;
        }

        .leaflet-popup-content {
            margin: 0 !important;
        }

        .leaflet-popup-tip {
            background: #111f14 !important;
        }

        .leaflet-popup-close-button {
            color: #4ade80 !important;
            font-size: 1rem !important;
            top: 6px !important;
            right: 8px !important;
        }

        .map-popup {
            padding: .85rem 1rem;
            min-width: 200px;
        }

        .map-popup__logo {
            width: 36px;
            height: 36px;
            border-radius: 7px;
            background: #0a2e14;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 800;
            color: #4ade80;
            overflow: hidden;
            margin-bottom: .6rem;
            flex-shrink: 0;
        }

        .map-popup__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .map-popup__name {
            font-size: .875rem;
            font-weight: 800;
            color: #f0fdf4;
            margin-bottom: .2rem;
        }

        .map-popup__city {
            font-size: .72rem;
            color: #6b7280;
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .2rem;
        }

        .map-popup__stars {
            font-size: .68rem;
            color: #fbbf24;
            margin-bottom: .55rem;
        }

        .map-popup__tagline {
            font-size: .72rem;
            color: #86efac;
            margin-bottom: .65rem;
            line-height: 1.4;
        }

        .map-popup__btn {
            display: block;
            text-align: center;
            background: #15803d;
            color: #fff;
            border-radius: 6px;
            padding: .4rem .75rem;
            font-size: .75rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .12s;
        }

        .map-popup__btn:hover {
            background: #16a34a;
            color: #fff;
        }

        /* ── No results ── */
        .find-no-results {
            display: none;
            padding: 1.5rem 1rem;
            text-align: center;
            color: #6b7280;
            font-size: .83rem;
        }
    </style>
</head>

<body>

    {{-- ════════════════════════════════════════════════════════
     NAVIGATION
════════════════════════════════════════════════════════ --}}
    <nav class="nav-outer" id="mainNav">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-logo">
                <span class="nav-logo__dot"></span>
                {{ config('app.name', 'SolarHub') }}
            </a>

            <ul class="nav-links">
                <li><a href="#benefits">Benefits</a></li>
                <li><a href="#how-it-works">How it Works</a></li>
                <li><a href="#vendors">Vendors</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#services">Services</a></li>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('admin.login') }}" class="btn-nav-ghost">
                    <i class="bi bi-shield-lock"></i> Admin Login
                </a>
                <a href="{{ route('vendor.login') }}" class="btn-nav-ghost">
                    <i class="bi bi-shop"></i> Vendor Login
                </a>
                <a href="{{ route('customer.register') }}" class="btn-nav-primary">
                    Get Started
                </a>
            </div>

            <button class="nav-hamburger" id="hamburger" aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </nav>

    {{-- ════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════ --}}
    <section class="hero" id="hero">
        <div class="hero__glow"></div>
        <div class="hero__glow-2"></div>
        <div class="hero__grid"></div>

        <div class="hero__inner">
            {{-- Left: copy --}}
            <div>
                <div class="hero__eyebrow">
                    Philippines #1 Solar Marketplace
                </div>

                <h1 class="hero__headline">
                    Power your home <br>
                    with <span class="accent-green">clean energy KENNETH BADING</span><br>
                    from the <span class="accent-yellow">sun. KENNETH BADING</span>
                </h1>

                <p class="hero__sub">
                    Discover verified solar vendors near you. Order panels,
                    batteries and inverters online — or request professional
                    installation and maintenance at your doorstep.
                </p>

                <div class="hero__actions">
                    <a href="{{ route('customer.register') }}" class="btn-hero-primary">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Find Vendors Near Me
                    </a>
                    <a href="#how-it-works" class="btn-hero-secondary">
                        <span class="play-icon"><i class="bi bi-play-fill"></i></span>
                        See how it works
                    </a>
                </div>

                <div class="hero__stats">
                    <div>
                        <div class="hero__stat-value" data-counter="{{ $featuredVendors->count() ?: 120 }}+">
                            0
                        </div>
                        <div class="hero__stat-label">Active vendors</div>
                    </div>
                    <div>
                        <div class="hero__stat-value" data-counter="8500">0</div>
                        <div class="hero__stat-label">Products listed</div>
                    </div>
                    <div>
                        <div class="hero__stat-value" data-counter="12000">0</div>
                        <div class="hero__stat-label">Customers served</div>
                    </div>
                </div>
            </div>

            {{-- Right: solar panel illustration --}}
            <div class="hero__visual">
                <div class="hero__badge hero__badge--tl">
                    <div class="badge-icon" style="background:rgba(46,204,113,0.15);color:#2ecc71;">
                        <i class="bi bi-sun-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:0.82rem;color:#fff;">Solar Output</div>
                        <div style="color:rgba(255,255,255,0.45);font-size:0.72rem;">↑ 18% this month</div>
                    </div>
                </div>

                <div class="panel-grid">
                    @for ($row = 0; $row < 4; $row++)
                        @for ($col = 0; $col < 4; $col++)
                            @php $d = ($row + $col) * 0.3; @endphp
                            <div class="panel-cell" style="--delay:{{ $d }}s;"></div>
                        @endfor
                    @endfor
                </div>

                <div class="hero__energy-line"></div>

                <div class="hero__badge hero__badge--br">
                    <div class="badge-icon" style="background:rgba(245,197,24,0.12);color:#f5c518;">
                        <i class="bi bi-shield-check-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:0.82rem;color:#fff;">Verified Vendors</div>
                        <div style="color:rgba(255,255,255,0.45);font-size:0.72rem;">DTI & SEC checked</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     TRUST BAR
════════════════════════════════════════════════════════ --}}
    <div class="trust-bar">
        <div class="trust-bar__inner">
            <div class="trust-item"><i class="bi bi-patch-check-fill"></i> Admin-verified vendors</div>
            <div class="trust-item"><i class="bi bi-shield-lock-fill"></i> Secure PayPal checkout</div>
            <div class="trust-item"><i class="bi bi-geo-alt-fill"></i> Location-based discovery</div>
            <div class="trust-item"><i class="bi bi-headset"></i> Dedicated technician network</div>
            <div class="trust-item"><i class="bi bi-award-fill"></i> Warranty management included</div>
            <div class="trust-item"><i class="bi bi-chat-dots-fill"></i> Real-time vendor chat</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
     BENEFITS
════════════════════════════════════════════════════════ --}}
    <section class="benefits" id="benefits">
        <div class="section-wrap">
            <div class="text-center reveal">
                <div class="section-eyebrow" style="justify-content:center;">Platform Benefits</div>
                <h2 class="section-title">Everything you need,<br><span class="hl">in one platform</span></h2>
                <p class="section-sub" style="margin:1rem auto 0;">
                    From browsing to installation — a complete ecosystem for buying,
                    selling, and maintaining renewable energy systems.
                </p>
            </div>

            <div class="benefits__grid">
                @php
                    $benefits = [
                        [
                            'bi-geo-alt',
                            '01',
                            'Nearby Vendor Discovery',
                            'Use GPS to find verified solar vendors and installers within your radius. Filter by rating, distance, and product availability.',
                        ],
                        [
                            'bi-shop',
                            '02',
                            'Multi-Vendor Marketplace',
                            'Browse hundreds of shops selling solar panels, batteries, inverters, and accessories — all in one place.',
                        ],
                        [
                            'bi-receipt',
                            '03',
                            'Integrated POS System',
                            'Vendors serve walk-in customers with a full point-of-sale system — barcode scanning, cart, PayPal, and receipt printing.',
                        ],
                        [
                            'bi-box-seam',
                            '04',
                            'Real-Time Inventory',
                            'Live stock tracking with low-stock alerts, stock movement audit log, and automatic deduction on every sale.',
                        ],
                        [
                            'bi-tools',
                            '05',
                            'Installation & Maintenance',
                            'Book certified technicians for residential or commercial solar installation, preventive maintenance, and repairs.',
                        ],
                        [
                            'bi-shield-check',
                            '06',
                            'Warranty Management',
                            'Track warranties per product, submit claims directly to vendors, and get scheduled for on-site resolution.',
                        ],
                    ];
                @endphp

                @foreach ($benefits as $i => [$icon, $num, $title, $desc])
                    <div class="benefit-card reveal reveal-delay-{{ ($i % 3) + 1 }}">
                        <div class="benefit-card__num">// {{ $num }}</div>
                        <div class="benefit-card__icon">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <h3 class="benefit-card__title">{{ $title }}</h3>
                        <p class="benefit-card__desc">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════════════════════════ --}}
    <section class="how-it-works" id="how-it-works">
        <div class="section-wrap">
            <div class="how__layout">
                {{-- Steps --}}
                <div>
                    <div class="section-eyebrow reveal">How it Works</div>
                    <h2 class="section-title reveal" style="margin-bottom:2.5rem;">
                        Up and running<br><span class="hl">in minutes</span>
                    </h2>

                    <div class="how__steps">
                        @php
                            $steps = [
                                [
                                    'Register your account',
                                    'Sign up as a customer or vendor in under 2 minutes. Vendors submit business documents for admin verification.',
                                ],
                                [
                                    'Discover or list products',
                                    'Customers browse nearby vendors by location, category, and rating. Vendors list products with photos, specs, and pricing.',
                                ],
                                [
                                    'Order or subscribe',
                                    'Customers place orders or request services online. Vendors activate their full portal by subscribing via PayPal.',
                                ],
                                [
                                    'Deliver and get support',
                                    'Track deliveries in real time, submit warranty claims, and chat directly with your vendor for ongoing support.',
                                ],
                            ];
                        @endphp
                        @foreach ($steps as $i => [$title, $desc])
                            <div class="how__step reveal reveal-delay-{{ $i + 1 }}">
                                <div class="how__step-num">{{ $i + 1 }}</div>
                                <div>
                                    <div class="how__step-title">{{ $title }}</div>
                                    <p class="how__step-desc">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Visual panel --}}
                <div class="how__visual reveal reveal-delay-2">
                    @php
                        $flowItems = [
                            [
                                'bi-person-check',
                                'rgba(46,204,113,0.12)',
                                '#2ecc71',
                                'Customer registered',
                                'Active',
                                'rgba(46,204,113,0.15)',
                                '#2ecc71',
                            ],
                            [
                                'bi-shop',
                                'rgba(245,197,24,0.12)',
                                '#f5c518',
                                'Vendor approved',
                                'Verified',
                                'rgba(245,197,24,0.15)',
                                '#f5c518',
                            ],
                            [
                                'bi-credit-card',
                                'rgba(52,152,219,0.12)',
                                '#3498db',
                                'Subscription activated',
                                'Paid',
                                'rgba(52,152,219,0.15)',
                                '#3498db',
                            ],
                            [
                                'bi-cart-check',
                                'rgba(46,204,113,0.12)',
                                '#2ecc71',
                                'Order placed & paid',
                                'Shipped',
                                'rgba(46,204,113,0.15)',
                                '#2ecc71',
                            ],
                            [
                                'bi-tools',
                                'rgba(231,76,60,0.12)',
                                '#e74c3c',
                                'Installation scheduled',
                                'Booked',
                                'rgba(231,76,60,0.15)',
                                '#e74c3c',
                            ],
                            [
                                'bi-chat-dots',
                                'rgba(155,89,182,0.12)',
                                '#9b59b6',
                                'Support chat opened',
                                'Live',
                                'rgba(155,89,182,0.15)',
                                '#9b59b6',
                            ],
                        ];
                    @endphp

                    @foreach ($flowItems as [$icon, $iconBg, $iconColor, $label, $tag, $tagBg, $tagColor])
                        <div class="flow-card">
                            <div class="flow-card__icon"
                                style="background:{{ $iconBg }};color:{{ $iconColor }};">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <span>{{ $label }}</span>
                            <span class="flow-card__tag"
                                style="background:{{ $tagBg }};color:{{ $tagColor }};">
                                {{ $tag }}
                            </span>
                        </div>
                    @endforeach

                    <div
                        style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,0.08);
                            display:flex;align-items:center;gap:0.65rem;font-size:0.82rem;
                            color:rgba(255,255,255,0.35);position:relative;z-index:2;">
                        <i class="bi bi-lightning-charge-fill" style="color:var(--yellow-400);"></i>
                        Platform events update in real-time
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     FEATURED VENDORS / PRODUCTS
════════════════════════════════════════════════════════ --}}
    <section class="featured" id="vendors">
        <div class="section-wrap">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 reveal">
                <div>
                    <div class="section-eyebrow">Featured on Platform</div>
                    <h2 class="section-title">Top-rated <span class="hl">solar vendors</span></h2>
                    <p class="section-sub">Verified businesses, real customer reviews, and live inventory.</p>
                </div>
                <a href="#find-vendor" class="btn-primary align-self-end"
                    style="white-space:nowrap;">
                    <i class="bi bi-geo-alt"></i> Find Near Me
                </a>
            </div>

            <div class="featured__grid">
                @forelse($featuredVendors as $vendor)
                    <div class="vendor-card reveal reveal-delay-{{ $loop->iteration }}">
                        @php
                            $bgColors = ['#0a2e14', '#1a3d08', '#0a1f2e', '#2e1a08', '#1a0a2e'];
                            $bg = $bgColors[$loop->index % count($bgColors)];
                        @endphp
                        <div class="vendor-card__header" style="background:{{ $bg }};">
                            {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                        </div>
                        <div class="vendor-card__body">
                            <div class="vendor-card__name">{{ $vendor->business_name }}</div>
                            <div class="vendor-card__location">
                                <i class="bi bi-geo-alt-fill" style="color:var(--green-500);font-size:0.75rem;"></i>
                                {{ $vendor->city }}, {{ $vendor->province_state }}
                            </div>
                            <div class="vendor-card__stars">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i
                                        class="bi {{ $s <= round($vendor->average_rating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                                <span>{{ number_format($vendor->average_rating, 1) }}
                                    ({{ $vendor->total_reviews }})
                                </span>
                            </div>
                            <div>
                                <span class="vendor-tag"><i class="bi bi-sun me-1"></i>Solar Panels</span>
                                <span class="vendor-tag"><i class="bi bi-battery-full me-1"></i>Batteries</span>
                                @if ($vendor->shop_description)
                                    <span class="vendor-tag">+ More</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Placeholder cards if no vendors yet --}}
                    @php
                        $placeholders = [
                            ['SB', 'Sunshine Bright Solar', 'Quezon City', 'Metro Manila', 4.9, 142],
                            ['ES', 'EcoSun Solutions', 'Cebu City', 'Cebu', 4.8, 98],
                            ['GE', 'GreenEdge Energy', 'Makati', 'Metro Manila', 4.7, 67],
                        ];
                    @endphp
                    @foreach ($placeholders as [$init, $name, $city, $province, $rating, $reviews])
                        <div class="vendor-card reveal reveal-delay-{{ $loop->iteration }}">
                            @php $bg = ['#0a2e14','#1a3d08','#0a1f2e'][$loop->index]; @endphp
                            <div class="vendor-card__header" style="background:{{ $bg }};">
                                {{ $init }}</div>
                            <div class="vendor-card__body">
                                <div class="vendor-card__name">{{ $name }}</div>
                                <div class="vendor-card__location">
                                    <i class="bi bi-geo-alt-fill"
                                        style="color:var(--green-500);font-size:0.75rem;"></i>
                                    {{ $city }}, {{ $province }}
                                </div>
                                <div class="vendor-card__stars">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <i class="bi {{ $s <= round($rating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                    <span>{{ $rating }} ({{ $reviews }})</span>
                                </div>
                                <div>
                                    <span class="vendor-tag"><i class="bi bi-sun me-1"></i>Solar Panels</span>
                                    <span class="vendor-tag"><i class="bi bi-battery-full me-1"></i>Batteries</span>
                                    <span class="vendor-tag"><i
                                            class="bi bi-lightning-charge me-1"></i>Inverters</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>

            <div class="featured__see-all reveal">
                <a href="{{ route('customer.register') }}" class="btn-outline-dark">
                    View all vendors <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     PRICING / SUBSCRIPTION
════════════════════════════════════════════════════════ --}}
    <section class="pricing" id="pricing">
        <div class="section-wrap">
            <div class="pricing__intro">
                <div class="reveal">
                    <div class="section-eyebrow">Vendor Subscriptions</div>
                    <h2 class="section-title">
                        One plan to<br>run your solar business
                    </h2>
                    <p class="section-sub">
                        Subscribe after admin approval and unlock your full vendor portal —
                        POS, inventory, online storefront, employee management, and more.
                    </p>
                </div>
                <div class="reveal reveal-delay-2" style="padding-top:0.5rem;">
                    <div style="display:flex;flex-direction:column;gap:0.85rem;">
                        @php
                            $whys = [
                                ['bi-check-circle-fill', 'No setup fees. Activate instantly after payment.'],
                                ['bi-check-circle-fill', 'Full portal access from day one of subscription.'],
                                ['bi-check-circle-fill', 'Cancel anytime — no lock-in contracts.'],
                                ['bi-check-circle-fill', 'Free during admin review period.'],
                            ];
                        @endphp
                        @foreach ($whys as [$icon, $text])
                            <div
                                style="display:flex;align-items:center;gap:0.65rem;font-size:0.9rem;color:rgba(255,255,255,0.6);">
                                <i class="bi {{ $icon }}"
                                    style="color:var(--green-400);font-size:0.88rem;flex-shrink:0;"></i>
                                {{ $text }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pricing__grid">
                @forelse($subscriptionPlans as $plan)
                    <div
                        class="plan-card {{ $plan->is_featured ? 'plan-card--featured' : '' }} reveal reveal-delay-{{ $loop->iteration }}">
                        <div class="plan-card__name">{{ $plan->name }}</div>
                        <div class="plan-card__price">
                            ${{ number_format($plan->price, 0) }}<span>/{{ $plan->billing_cycle === 'monthly' ? 'mo' : ($plan->billing_cycle === 'annual' ? 'yr' : 'qtr') }}</span>
                        </div>
                        <div class="plan-card__cycle">{{ $plan->billing_label }}</div>

                        <hr class="plan-card__divider">

                        @if ($plan->features)
                            @foreach ($plan->features as $feature)
                                <div class="plan-feature">
                                    <i class="bi bi-check-circle-fill"></i>
                                    {{ $feature }}
                                </div>
                            @endforeach
                        @endif

                        <a href="{{ route('vendor.register') }}"
                            class="btn-plan {{ $plan->is_featured ? 'btn-plan--featured' : 'btn-plan--default' }}">
                            <i class="bi bi-shop"></i> Start with {{ $plan->name }}
                        </a>
                    </div>
                @empty
                    {{-- Static fallback plans --}}
                    @php
                        $staticPlans = [
                            [
                                'Monthly',
                                '$29',
                                '/mo',
                                'per month',
                                false,
                                [
                                    'POS system (unlimited)',
                                    'Inventory management',
                                    'Online storefront',
                                    'Order & delivery tools',
                                    'Up to 10 employees',
                                    'Basic analytics',
                                ],
                            ],
                            [
                                'Quarterly',
                                '$79',
                                '/qtr',
                                'per 3 months',
                                false,
                                [
                                    'Everything in Monthly',
                                    'Up to 20 employees',
                                    'Standard analytics',
                                    'Priority email support',
                                ],
                            ],
                            [
                                'Annual',
                                '$249',
                                '/yr',
                                'per year — save 30%',
                                true,
                                [
                                    'Everything in Quarterly',
                                    'Unlimited employees',
                                    'Advanced analytics',
                                    'Featured listing',
                                    'Priority support',
                                    'Dedicated account manager',
                                ],
                            ],
                        ];
                    @endphp
                    @foreach ($staticPlans as [$name, $price, $per, $cycle, $featured, $features])
                        <div
                            class="plan-card {{ $featured ? 'plan-card--featured' : '' }} reveal reveal-delay-{{ $loop->iteration }}">
                            <div class="plan-card__name">{{ $name }}</div>
                            <div class="plan-card__price">{{ $price }}<span>{{ $per }}</span></div>
                            <div class="plan-card__cycle">{{ $cycle }}</div>
                            <hr class="plan-card__divider">
                            @foreach ($features as $f)
                                <div class="plan-feature"><i class="bi bi-check-circle-fill"></i>{{ $f }}
                                </div>
                            @endforeach
                            <a href="{{ route('vendor.register') }}"
                                class="btn-plan {{ $featured ? 'btn-plan--featured' : 'btn-plan--default' }}">
                                <i class="bi bi-shop"></i> Start with {{ $name }}
                            </a>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     SERVICES
════════════════════════════════════════════════════════ --}}
    <section class="services" id="services">
        <div class="section-wrap">
            <div class="text-center reveal">
                <div class="section-eyebrow" style="justify-content:center;">Customer Services</div>
                <h2 class="section-title">Beyond just <span class="hl">buying products</span></h2>
                <p class="section-sub" style="margin:1rem auto 0;">
                    Get certified technicians to your location for installation,
                    maintenance, repair, and expert consultation.
                </p>
            </div>

            <div class="services__grid">
                @php
                    $services = [
                        [
                            'install',
                            'bi-tools',
                            'Professional Installation',
                            'Residential & commercial solar system installation by certified technicians. Grid-tied, off-grid, and hybrid systems.',
                        ],
                        [
                            'maintenance',
                            'bi-wrench-adjustable',
                            'Preventive Maintenance',
                            'Keep your system running at peak efficiency with scheduled cleaning, inspection, and component testing.',
                        ],
                        [
                            'repair',
                            'bi-hammer',
                            'Repair & Diagnostics',
                            'Fault detection and component replacement. From inverter issues to panel damage — we send the right tech.',
                        ],
                        [
                            'consultation',
                            'bi-lightbulb',
                            'Energy Consultation',
                            'Site assessment and load analysis to design the optimal solar solution for your home or business.',
                        ],
                    ];
                @endphp
                @foreach ($services as [$type, $icon, $title, $desc])
                    <div
                        class="service-card service-card--{{ $type }} reveal reveal-delay-{{ $loop->iteration }}">
                        <div class="service-card__icon"><i class="bi {{ $icon }}"></i></div>
                        <h3 class="service-card__title">{{ $title }}</h3>
                        <p class="service-card__desc">{{ $desc }}</p>
                        <a href="{{ route('customer.register') }}" class="service-card__link">
                            Request service <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     DUAL CTA
════════════════════════════════════════════════════════ --}}
    <section class="dual-cta" id="cta">
        <div class="section-wrap">
            <div class="dual-cta__grid">

                {{-- Vendor CTA --}}
                <div class="cta-block cta-block--vendor reveal">
                    <div class="cta-block__eyebrow">For Solar Businesses</div>
                    <h2 class="cta-block__title">List your shop.<br>Grow your revenue.</h2>
                    <p class="cta-block__sub">
                        Join hundreds of solar vendors already managing inventory,
                        processing walk-in sales, and reaching customers online.
                    </p>
                    <ul class="cta-block__list">
                        <li><i class="bi bi-check-circle-fill"></i> Full POS system included</li>
                        <li><i class="bi bi-check-circle-fill"></i> Inventory with low-stock alerts</li>
                        <li><i class="bi bi-check-circle-fill"></i> Employee role management</li>
                        <li><i class="bi bi-check-circle-fill"></i> Subscription from $29.99/month</li>
                    </ul>
                    <a href="{{ route('vendor.register') }}" class="btn-cta-vendor">
                        <i class="bi bi-shop"></i> Register as Vendor
                    </a>
                </div>

                {{-- Customer CTA --}}
                <div class="cta-block cta-block--customer reveal reveal-delay-2">
                    <div class="cta-block__eyebrow">For Homeowners & Businesses</div>
                    <h2 class="cta-block__title">Go solar.<br>Start today.</h2>
                    <p class="cta-block__sub">
                        Find the best solar equipment and certified installers
                        in your area — all verified, rated, and ready to serve you.
                    </p>
                    <ul class="cta-block__list">
                        <li><i class="bi bi-check-circle-fill"></i> Find vendors within your radius</li>
                        <li><i class="bi bi-check-circle-fill"></i> Order online with PayPal</li>
                        <li><i class="bi bi-check-circle-fill"></i> Track installation status</li>
                        <li><i class="bi bi-check-circle-fill"></i> Free account — no credit card</li>
                    </ul>
                    <a href="{{ route('customer.register') }}" class="btn-cta-customer">
                        <i class="bi bi-person-plus-fill"></i> Create Free Account
                    </a>
                </div>

            </div>
        </div>
    </section>

    <section class="find-section" id="find-vendor">
        <div class="section-wrap">

            {{-- Header --}}
            <div class="find-header reveal">
                <div>
                    <div class="find-eyebrow">🗺️ Interactive Map</div>
                    <h2 class="find-title">Find a vendor <span class="hl">near you</span></h2>
                    <p class="find-sub">Browse verified solar vendors across the Province of Cavite.</p>
                </div>
                <a href="{{ route('customer.register') }}" class="btn-primary align-self-end"
                    style="white-space:nowrap;background:#15803d;border-color:#15803d;">
                    <i class="bi bi-person-plus"></i> Get Started
                </a>
            </div>

            {{-- Search + filter controls --}}
            <div class="find-controls reveal">
                <div class="find-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="vendorSearch" class="find-search"
                        placeholder="Search by vendor name or city…" oninput="filterVendors()">
                </div>

                <select id="cityFilter" class="find-filter" onchange="filterVendors()">
                    <option value="">All cities</option>
                    @foreach (['Alfonso', 'Amadeo', 'Bacoor', 'Carmona', 'Cavite City', 'Dasmariñas', 'General Mariano Alvarez', 'General Trias', 'Imus', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay', 'Tanza', 'Ternate', 'Trece Martires'] as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>

                <button type="button" class="find-locate-btn" onclick="locateMe()">
                    <i class="bi bi-crosshair"></i> Near Me
                </button>
            </div>

            {{-- Map + sidebar --}}
            <div class="find-layout reveal">

                {{-- Sidebar vendor list --}}
                <div class="find-sidebar">
                    <div class="find-sidebar__count">
                        <span id="vendorCount">{{ count($mapVendors) }}</span> vendors found
                    </div>
                    <div class="find-list" id="vendorList">
                        @forelse($mapVendors as $v)
                            <div class="vendor-list-item" id="vli-{{ $v['id'] }}"
                                data-id="{{ $v['id'] }}" data-name="{{ strtolower($v['name']) }}"
                                data-city="{{ $v['city'] }}" onclick="focusVendor({{ $v['id'] }})">
                                <div class="vli-logo">
                                    @if ($v['logo'])
                                        <img src="{{ $v['logo'] }}" alt="{{ $v['name'] }}">
                                    @else
                                        {{ $v['initials'] }}
                                    @endif
                                </div>
                                <div class="vli-info">
                                    <div class="vli-name">{{ $v['name'] }}</div>
                                    <div class="vli-city">
                                        <i class="bi bi-geo-alt-fill" style="color:#4ade80;font-size:.65rem;"></i>
                                        {{ $v['city'] }}, Cavite
                                    </div>
                                    <div class="vli-stars">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi {{ $s <= round($v['rating']) ? 'bi-star-fill' : 'bi-star' }}"
                                                style="font-size:.6rem;"></i>
                                        @endfor
                                        <span>{{ number_format($v['rating'], 1) }} ({{ $v['reviews'] }})</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="vli-empty">
                                <i class="bi bi-shop" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>
                                No vendors registered yet.
                            </div>
                        @endforelse
                        <div class="find-no-results" id="noResults">
                            <i class="bi bi-search" style="font-size:1.3rem;display:block;margin-bottom:.5rem;"></i>
                            No vendors match your search.
                        </div>
                    </div>
                </div>

                {{-- Leaflet map --}}
                <div id="findVendorMap"></div>
            </div>

        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════ --}}
    <footer class="footer">
        <div class="section-wrap">
            <div class="footer__grid">

                {{-- Brand --}}
                <div class="footer__brand">
                    <a href="{{ route('home') }}" class="nav-logo" style="display:inline-flex;margin-bottom:0;">
                        <span class="nav-logo__dot"></span>
                        {{ config('app.name', 'SolarHub') }}
                    </a>
                    <p>The Philippines' leading multi-vendor marketplace for solar energy products, installation
                        services, and renewable energy solutions.</p>
                    <div class="footer__socials">
                        <a href="#" class="footer__social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer__social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer__social-btn"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="footer__social-btn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                {{-- Platform --}}
                <div>
                    <div class="footer__col-title">Platform</div>
                    <ul class="footer__links">
                        <li><a href="{{ route('vendor.register') }}">Become a Vendor</a></li>
                        <li><a href="{{ route('customer.register') }}">Customer Sign Up</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#how-it-works">How it Works</a></li>
                        <li><a href="{{ route('vendor.login') }}">Vendor Portal</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div>
                    <div class="footer__col-title">Services</div>
                    <ul class="footer__links">
                        <li><a href="#services">Solar Installation</a></li>
                        <li><a href="#services">Maintenance</a></li>
                        <li><a href="#services">Repair & Diagnostics</a></li>
                        <li><a href="#services">Energy Consultation</a></li>
                        <li><a href="#services">Warranty Claims</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <div class="footer__col-title">Company</div>
                    <ul class="footer__links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="{{ route('admin.login') }}" style="color:rgba(255,255,255,0.2);">Admin</a></li>
                    </ul>
                </div>

            </div>

            <div class="footer__bottom">
                <p class="footer__copy">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    Built with renewable energy in mind. 🌱
                </p>
                <div class="footer__legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ── Navbar scroll effect ──────────────────────────── */
        const nav = document.getElementById('mainNav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }, {
            passive: true
        });

        /* ── Scroll reveal ─────────────────────────────────── */
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        /* ── Counter animation ─────────────────────────────── */
        function animateCounter(el) {
            const target = parseInt(el.dataset.counter, 10);
            const duration = 2000;
            const start = performance.now();
            const format = (n) => n >= 1000 ? (n / 1000).toFixed(n >= 10000 ? 0 : 1) + 'k' : n;

            function step(timestamp) {
                const elapsed = timestamp - start;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(ease * target);
                el.textContent = format(current) + (el.dataset.counter.includes('+') ? '+' : '');
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = format(target) + (el.dataset.counter.includes('+') ? '+' : '');
            }
            requestAnimationFrame(step);
        }

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));

        /* ── Mobile hamburger ──────────────────────────────── */
        const hamburger = document.getElementById('hamburger');
        let mobileMenuOpen = false;

        hamburger?.addEventListener('click', () => {
            mobileMenuOpen = !mobileMenuOpen;
            hamburger.innerHTML = mobileMenuOpen ?
                '<i class="bi bi-x-lg"></i>' :
                '<i class="bi bi-list"></i>';

            // Simple mobile menu toggle
            let mobileNav = document.getElementById('mobileNav');
            if (!mobileNav) {
                mobileNav = document.createElement('div');
                mobileNav.id = 'mobileNav';
                mobileNav.style.cssText = `
            position:fixed; top:72px; left:0; right:0;
            background:rgba(4,15,7,0.97); backdrop-filter:blur(16px);
            border-bottom:1px solid rgba(255,255,255,0.08);
            padding:1.5rem 2rem; z-index:99;
            display:flex; flex-direction:column; gap:0.5rem;
        `;
                mobileNav.innerHTML = `
            <a href="#benefits"     style="color:rgba(255,255,255,0.7);font-size:1rem;padding:0.5rem 0;font-weight:500;" onclick="closeMobileMenu()">Benefits</a>
            <a href="#how-it-works" style="color:rgba(255,255,255,0.7);font-size:1rem;padding:0.5rem 0;font-weight:500;" onclick="closeMobileMenu()">How it Works</a>
            <a href="#vendors"      style="color:rgba(255,255,255,0.7);font-size:1rem;padding:0.5rem 0;font-weight:500;" onclick="closeMobileMenu()">Vendors</a>
            <a href="#pricing"      style="color:rgba(255,255,255,0.7);font-size:1rem;padding:0.5rem 0;font-weight:500;" onclick="closeMobileMenu()">Pricing</a>
            <a href="#services"     style="color:rgba(255,255,255,0.7);font-size:1rem;padding:0.5rem 0;font-weight:500;" onclick="closeMobileMenu()">Services</a>
            <hr style="border-color:rgba(255,255,255,0.08); margin:0.5rem 0;">
            <a href="{{ route('vendor.login') }}"     style="color:rgba(255,255,255,0.7);font-size:0.95rem;padding:0.5rem 0;" onclick="closeMobileMenu()"><i class="bi bi-shop me-2"></i>Vendor Login</a>
            <a href="{{ route('customer.register') }}" style="color:#2ecc71;font-size:0.95rem;padding:0.5rem 0;font-weight:700;" onclick="closeMobileMenu()"><i class="bi bi-person-plus me-2"></i>Get Started — Free</a>
        `;
                document.body.appendChild(mobileNav);
            }
            mobileNav.style.display = mobileMenuOpen ? 'flex' : 'none';
        });

        function closeMobileMenu() {
            mobileMenuOpen = false;
            hamburger.innerHTML = '<i class="bi bi-list"></i>';
            const mn = document.getElementById('mobileNav');
            if (mn) mn.style.display = 'none';
        }

        /* ── Smooth anchor scroll ──────────────────────────── */
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        (function() {
            // ── Vendor data from PHP ──────────────────────────────────────────────
            const VENDORS = @json($mapVendors);

            const CAVITE_CENTER = [14.2456, 120.8789];
            const CAVITE_BOUNDS = L.latLngBounds([13.90, 120.60], [14.65, 121.20]);

            let findMap, markers = {},
                activeId = null;

            // ── Custom green pin icon ─────────────────────────────────────────────
            function makeIcon(active = false) {
                const color = active ? '#facc15' : '#16a34a';
                const size = active ? 36 : 28;
                return L.divIcon({
                    html: `<div style="width:${size}px;height:${size}px;background:${color};
                               border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                               border:3px solid #fff;
                               box-shadow:0 2px 12px rgba(0,0,0,.4);
                               transition:all .2s;"></div>`,
                    iconSize: [size, size],
                    iconAnchor: [size / 2, size],
                    className: '',
                });
            }

            // ── Build popup HTML ──────────────────────────────────────────────────
            function popupHtml(v) {
                const stars = Array.from({
                        length: 5
                    }, (_, i) =>
                    `<i class="bi ${i < Math.round(v.rating) ? 'bi-star-fill' : 'bi-star'}"
                style="font-size:.65rem;"></i>`
                ).join('');

                const logo = v.logo ?
                    `<img src="${v.logo}" style="width:100%;height:100%;object-fit:cover;">` :
                    `<span>${v.initials}</span>`;

                return `<div class="map-popup">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                        <div class="map-popup__logo">${logo}</div>
                        <div>
                            <div class="map-popup__name">${v.name}</div>
                            <div class="map-popup__city">
                                <i class="bi bi-geo-alt-fill" style="color:#4ade80;font-size:.6rem;"></i>
                                ${v.city}, Cavite
                            </div>
                        </div>
                    </div>
                    <div class="map-popup__stars">${stars}
                        <span style="color:#6b7280;font-size:.65rem;margin-left:.2rem;">
                            ${v.rating} (${v.reviews} reviews)
                        </span>
                    </div>
                    ${v.tagline ? `<div class="map-popup__tagline">${v.tagline}</div>` : ''}
                    <a href="${v.url}" class="map-popup__btn">
                        View Vendor <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>`;
            }

            // ── Initialize map ────────────────────────────────────────────────────
            function initFindMap() {
                findMap = L.map('findVendorMap', {
                    center: CAVITE_CENTER,
                    zoom: 11,
                    minZoom: 10,
                    maxZoom: 18,
                    maxBounds: CAVITE_BOUNDS,
                    maxBoundsViscosity: 0.8,
                    zoomControl: true,
                });

                // Dark-ish tile layer (CartoDB dark matter looks great on dark bg)
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '© OpenStreetMap © CARTO',
                    subdomains: 'abcd',
                }).addTo(findMap);

                // Place markers
                VENDORS.forEach(v => {
                    if (!v.lat || !v.lng) return;

                    const m = L.marker([v.lat, v.lng], {
                            icon: makeIcon()
                        })
                        .addTo(findMap)
                        .bindPopup(popupHtml(v), {
                            maxWidth: 240,
                            minWidth: 210
                        });

                    m.on('click', () => {
                        setActive(v.id);
                        scrollSidebarTo(v.id);
                    });

                    markers[v.id] = {
                        marker: m,
                        data: v
                    };
                });

                // Fit map to markers if any exist
                const pts = VENDORS.filter(v => v.lat && v.lng).map(v => [v.lat, v.lng]);
                if (pts.length > 1) findMap.fitBounds(pts, {
                    padding: [40, 40]
                });
            }

            // ── Set active vendor (highlight pin + sidebar item) ──────────────────
            function setActive(id) {
                // Reset previous
                if (activeId && markers[activeId]) {
                    markers[activeId].marker.setIcon(makeIcon(false));
                }
                document.querySelectorAll('.vendor-list-item').forEach(el => el.classList.remove('active'));

                activeId = id;

                if (markers[id]) {
                    markers[id].marker.setIcon(makeIcon(true));
                    markers[id].marker.openPopup();
                }

                const listItem = document.getElementById(`vli-${id}`);
                if (listItem) listItem.classList.add('active');
            }

            // ── Focus vendor from sidebar click ──────────────────────────────────
            window.focusVendor = function(id) {
                const entry = markers[id];
                if (!entry) return;
                setActive(id);
                findMap.setView([entry.data.lat, entry.data.lng], 15, {
                    animate: true
                });
            };

            // ── Scroll sidebar to item ────────────────────────────────────────────
            function scrollSidebarTo(id) {
                const el = document.getElementById(`vli-${id}`);
                el?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            // ── Search + city filter ──────────────────────────────────────────────
            window.filterVendors = function() {
                const query = document.getElementById('vendorSearch').value.toLowerCase().trim();
                const city = document.getElementById('cityFilter').value;
                let shown = 0;

                document.querySelectorAll('.vendor-list-item').forEach(el => {
                    const name = el.dataset.name ?? '';
                    const elCity = el.dataset.city ?? '';
                    const matchQ = !query || name.includes(query) || elCity.toLowerCase().includes(query);
                    const matchCity = !city || elCity === city;
                    const visible = matchQ && matchCity;

                    el.style.display = visible ? '' : 'none';

                    const id = parseInt(el.dataset.id);
                    if (markers[id]) {
                        if (visible) {
                            markers[id].marker.addTo(findMap);
                            shown++;
                        } else {
                            markers[id].marker.remove();
                        }
                    }
                });

                document.getElementById('vendorCount').textContent = shown;
                document.getElementById('noResults').style.display = shown === 0 ? 'block' : 'none';

                // Fit to visible markers
                const visiblePts = Object.entries(markers)
                    .filter(([id, _]) => {
                        const el = document.getElementById(`vli-${id}`);
                        return el && el.style.display !== 'none';
                    })
                    .map(([_, e]) => [e.data.lat, e.data.lng])
                    .filter(c => c[0] && c[1]);

                if (visiblePts.length > 0 && findMap) {
                    findMap.fitBounds(visiblePts, {
                        padding: [40, 40],
                        maxZoom: 14
                    });
                }
            };

            // ── Locate me ────────────────────────────────────────────────────────
            window.locateMe = function() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(pos => {
                    findMap.setView([pos.coords.latitude, pos.coords.longitude], 13, {
                        animate: true
                    });
                }, () => {});
            };

            // ── Init map when section enters viewport (lazy) ──────────────────────
            const section = document.getElementById('find-vendor');
            if ('IntersectionObserver' in window) {
                let inited = false;
                const obs = new IntersectionObserver(entries => {
                    if (entries[0].isIntersecting && !inited) {
                        inited = true;
                        initFindMap();
                        obs.disconnect();
                    }
                }, {
                    threshold: 0.1
                });
                obs.observe(section);
            } else {
                initFindMap(); // fallback: init immediately
            }

        })();
    </script>

</body>

</html>
