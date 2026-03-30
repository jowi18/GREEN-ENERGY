{{-- resources/views/customer/solar/paypal-cancel.blade.php --}}
@extends('layouts.customer')
@section('title', 'Payment Cancelled')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');

        .paypal-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        .result-card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .1), 0 4px 16px rgba(0, 0, 0, .06);
        }

        /* ── Hero band ── */
        .result-card__hero {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 60%, #991b1b 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .result-card__hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .x-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, .15);
            border: 3px solid rgba(255, 255, 255, .4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem;
            animation: popIn .5s cubic-bezier(.175, .885, .32, 1.275) both;
            position: relative;
            z-index: 1;
        }

        @keyframes popIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .result-card__hero h1 {
            font-family: 'Nunito', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: .35rem;
            position: relative;
            z-index: 1;
            animation: slideUp .4s .15s ease both;
        }

        .result-card__hero p {
            font-size: .85rem;
            color: rgba(255, 255, 255, .8);
            position: relative;
            z-index: 1;
            animation: slideUp .4s .25s ease both;
        }

        @keyframes slideUp {
            from {
                transform: translateY(12px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ── Body ── */
        .result-card__body {
            padding: 1.75rem 1.5rem 1.25rem;
            animation: slideUp .4s .35s ease both;
        }

        /* ── Info box ── */
        .info-box {
            background: #fef9c3;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            gap: .85rem;
            align-items: flex-start;
        }

        .info-box__icon {
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: .1rem;
        }

        .info-box__content {
            font-size: .83rem;
            color: #78350f;
            line-height: 1.6;
        }

        .info-box__content strong {
            color: #713f12;
        }

        /* ── What was charged ── */
        .not-charged {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .not-charged__label {
            font-size: .78rem;
            font-weight: 700;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .not-charged__sub {
            font-size: .72rem;
            color: #9ca3af;
            margin-top: .15rem;
        }

        .not-charged__amount {
            font-family: 'Nunito', sans-serif;
            font-size: 1.3rem;
            font-weight: 900;
            color: #dc2626;
            text-decoration: line-through;
            opacity: .5;
        }

        /* ── Options ── */
        .options-title {
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: .85rem;
        }

        /* ── Actions ── */
        .result-card__actions {
            padding: 0 1.5rem 1.75rem;
            display: flex;
            flex-direction: column;
            gap: .6rem;
            animation: slideUp .4s .45s ease both;
        }

        .btn-primary-green {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: #15803d;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .875rem 1.25rem;
            font-size: .9rem;
            font-weight: 800;
            font-family: 'Nunito', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, transform .1s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-primary-green:hover {
            background: #166534;
            color: #fff;
        }

        .btn-primary-green:active {
            transform: scale(.98);
        }

        .btn-outline {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: transparent;
            color: #6b7280;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: .8rem 1.25rem;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-outline:hover {
            border-color: #6b7280;
            color: #374151;
        }

        .btn-outline-red {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: transparent;
            color: #dc2626;
            border: 1.5px solid #fecaca;
            border-radius: 10px;
            padding: .8rem 1.25rem;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-outline-red:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .divider {
            text-align: center;
            font-size: .72rem;
            color: #d1d5db;
            margin: .25rem 0;
        }

        @media (max-width: 480px) {
            .result-card {
                border-radius: 16px;
            }

            .result-card__hero {
                padding: 2rem 1.5rem 1.75rem;
            }

            .result-card__body {
                padding: 1.5rem 1.25rem 1rem;
            }

            .result-card__actions {
                padding: 0 1.25rem 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="paypal-page">
        <div class="result-card">

            {{-- Hero --}}
            <div class="result-card__hero">
                <div class="x-circle">✕</div>
                <h1>Payment Cancelled</h1>
                <p>No charges were made to your account</p>
            </div>

            {{-- Body --}}
            <div class="result-card__body">

                {{-- Not charged indicator --}}
                <div class="not-charged">
                    <div>
                        <div class="not-charged__label">Not Charged</div>
                        <div class="not-charged__sub">Payment was not completed</div>
                    </div>
                    <div class="not-charged__amount">₱1,500.00</div>
                </div>

                {{-- Info --}}
                <div class="info-box">
                    <div class="info-box__icon">💡</div>
                    <div class="info-box__content">
                        <strong>Your inquiry data has been saved.</strong> You can try the payment
                        again or start a new inquiry. The ₱1,500 site visit fee is required
                        to activate your inquiry and dispatch an engineer.
                    </div>
                </div>

                <div class="options-title">What would you like to do?</div>

            </div>

            {{-- Actions --}}
            <div class="result-card__actions">

                {{-- Primary: try again --}}
                <a href="{{ route('customer.solar.create') }}" class="btn-primary-green">
                    <i class="bi bi-arrow-repeat"></i> Try Again
                </a>

                {{-- Secondary: go to solar projects --}}
                <a href="{{ route('customer.solar.index') }}" class="btn-outline">
                    <i class="bi bi-list-ul"></i> View My Projects
                </a>

                <div class="divider">or</div>

                {{-- Tertiary: go home --}}
                <a href="{{ route('customer.dashboard') }}" class="btn-outline-red">
                    <i class="bi bi-house"></i> Back to Dashboard
                </a>

            </div>

        </div>
    </div>
@endsection
