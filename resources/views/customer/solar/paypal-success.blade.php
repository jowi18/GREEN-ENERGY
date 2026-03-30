{{-- resources/views/customer/solar/paypal-success.blade.php --}}
@extends('layouts.customer')
@section('title', 'Payment Confirmed')

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
            background: linear-gradient(135deg, #16a34a 0%, #15803d 60%, #166534 100%);
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

        .checkmark-circle {
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
            padding: 1.75rem 1.5rem;
            animation: slideUp .4s .35s ease both;
        }

        /* ── Amount chip ── */
        .amount-chip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .amount-chip__label {
            font-size: .78rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .amount-chip__value {
            font-family: 'Nunito', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #15803d;
            font-variant-numeric: tabular-nums;
        }

        /* ── Detail rows ── */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: .65rem 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: .83rem;
            gap: 1rem;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row__label {
            color: #9ca3af;
            font-weight: 600;
            flex-shrink: 0;
        }

        .detail-row__value {
            color: #374151;
            font-weight: 700;
            text-align: right;
            word-break: break-all;
        }

        .detail-row__value.mono {
            font-family: monospace;
            font-size: .78rem;
        }

        /* ── What's next timeline ── */
        .next-steps {
            margin: 1.25rem 0;
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
        }

        .next-steps__title {
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            margin-bottom: .9rem;
        }

        .timeline {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .timeline-item {
            display: flex;
            gap: .85rem;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 14px;
            top: 28px;
            width: 2px;
            height: calc(100% + 0px);
            background: #e5e7eb;
        }

        .timeline-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 800;
            position: relative;
            z-index: 1;
            margin-top: 2px;
        }

        .timeline-dot--done {
            background: #16a34a;
            color: #fff;
        }

        .timeline-dot--pending {
            background: #e5e7eb;
            color: #9ca3af;
        }

        .timeline-content {
            padding-bottom: 1rem;
        }

        .timeline-content__title {
            font-size: .83rem;
            font-weight: 700;
            color: #374151;
        }

        .timeline-content__sub {
            font-size: .74rem;
            color: #9ca3af;
            margin-top: .15rem;
            line-height: 1.45;
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
            border-color: #15803d;
            color: #15803d;
        }

        /* ── PayPal badge ── */
        .paypal-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            margin-top: .25rem;
            font-size: .72rem;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .result-card {
                border-radius: 16px;
            }

            .result-card__hero {
                padding: 2rem 1.5rem 1.75rem;
            }

            .result-card__body {
                padding: 1.5rem 1.25rem;
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
                <div class="checkmark-circle">✓</div>
                <h1>Payment Confirmed!</h1>
                <p>Your solar inquiry is now active</p>
            </div>

            {{-- Body --}}
            <div class="result-card__body">

                {{-- Amount --}}
                <div class="amount-chip">
                    <div>
                        <div class="amount-chip__label">Site Visit Fee</div>
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:.15rem;">
                            Paid via PayPal
                        </div>
                    </div>
                    <div class="amount-chip__value">₱1,500.00</div>
                </div>

                {{-- Details --}}
                <div class="detail-row">
                    <span class="detail-row__label">Project #</span>
                    <span class="detail-row__value mono">{{ $project->project_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row__label">Service</span>
                    <span class="detail-row__value">Site Visit & Assessment</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row__label">Property</span>
                    <span class="detail-row__value">
                        {{ ucfirst($project->property_type) }} ·
                        {{ $project->installation_city }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-row__label">Date Paid</span>
                    <span class="detail-row__value">
                        {{ $project->site_visit_paid_at?->format('M d, Y · g:i A') ?? now()->format('M d, Y · g:i A') }}
                    </span>
                </div>
                @if ($project->site_visit_txn_id)
                    <div class="detail-row">
                        <span class="detail-row__label">Transaction ID</span>
                        <span class="detail-row__value mono">{{ $project->site_visit_txn_id }}</span>
                    </div>
                @endif

                {{-- What's next timeline --}}
                <div class="next-steps">
                    <div class="next-steps__title">What happens next</div>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot timeline-dot--done">✓</div>
                            <div class="timeline-content">
                                <div class="timeline-content__title">Inquiry Submitted & Paid</div>
                                <div class="timeline-content__sub">Your inquiry is now active and visible to the vendor.
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot timeline-dot--pending">2</div>
                            <div class="timeline-content">
                                <div class="timeline-content__title">Engineer Assigned</div>
                                <div class="timeline-content__sub">A licensed solar engineer will be assigned within 1–2
                                    business days.</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot timeline-dot--pending">3</div>
                            <div class="timeline-content">
                                <div class="timeline-content__title">Site Visit Scheduled</div>
                                <div class="timeline-content__sub">The engineer will contact you to set a visit date and
                                    time.</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot timeline-dot--pending">4</div>
                            <div class="timeline-content" style="padding-bottom:0;">
                                <div class="timeline-content__title">Quotation & Installation</div>
                                <div class="timeline-content__sub">After the site visit, you'll receive a formal quotation.
                                    The ₱1,500 fee will be credited toward installation.</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div class="result-card__actions">
                <a href="{{ route('customer.solar.invoice', $project) }}" class="btn-primary-green">
                    <i class="bi bi-receipt"></i> View & Print Invoice
                </a>
                <a href="{{ route('customer.solar.show', $project) }}" class="btn-outline">
                    <i class="bi bi-folder2-open"></i> Go to My Project
                </a>
                <div class="paypal-badge">
                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" style="height:16px;"
                        alt="PayPal">
                    Secured by PayPal
                </div>
            </div>

        </div>
    </div>
@endsection
