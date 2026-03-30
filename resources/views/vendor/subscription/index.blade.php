@extends('layouts.vendor')

@section('title', 'Subscription')
@section('page-title', 'Subscription')

@section('content')

    {{-- ── Current subscription banner ── --}}
    @if ($currentSubscription)
        <div
            class="sub-banner sub-banner--{{ $currentSubscription->daysRemaining() <= 7 ? 'expiring' : 'active' }} gap-section">
            <div class="sub-banner__icon">
                <i
                    class="bi bi-{{ $currentSubscription->daysRemaining() <= 7 ? 'exclamation-triangle-fill' : 'shield-check' }}"></i>
            </div>
            <div class="sub-banner__text">
                <div class="sub-banner__title">
                    {{ $currentSubscription->plan->name }} Plan — Active
                </div>
                <div class="sub-banner__desc">
                    Your subscription is active until
                    <strong>{{ $currentSubscription->expires_at->format('F d, Y') }}</strong>
                    ({{ $currentSubscription->daysRemaining() }} days remaining).
                </div>
            </div>
            <span class="vd-badge vd-badge--active" style="font-size:0.72rem;padding:0.3rem 0.75rem;">
                {{ $currentSubscription->daysRemaining() }}d left
            </span>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Left: plan selection ── --}}
        <div class="col-12 col-xl-8">

            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-lightning-charge"></i>
                        {{ $currentSubscription ? 'Change or Renew Plan' : 'Choose a Plan' }}
                    </span>
                    <span class="vd-card__subtitle">All plans include full vendor portal access</span>
                </div>
                <div class="vd-card__body">

                    <form method="POST" action="{{ route('vendor.subscription.checkout') }}" id="subForm">
                        @csrf

                        <div class="row g-3 mb-4" id="planGrid">
                            @forelse($plans as $plan)
                                <div class="col-12 col-md-4">
                                    <div class="plan-card {{ $plan->is_featured ? 'plan-card--featured' : '' }} {{ $currentSubscription?->plan_id == $plan->id ? 'selected' : '' }}"
                                        data-plan="{{ $plan->id }}" onclick="selectPlan({{ $plan->id }})">

                                        @if ($plan->is_featured)
                                            <div class="plan-card__ribbon">Most Popular</div>
                                        @endif

                                        <div class="plan-card__check"><i class="bi bi-check2"></i></div>

                                        <div class="plan-card__name">{{ $plan->name }}</div>

                                        <div class="plan-card__price">
                                            <sup>P</sup>{{ number_format($plan->price, 0) }}<span>/{{ $plan->billing_cycle === 'monthly' ? 'mo' : ($plan->billing_cycle === 'annual' ? 'yr' : 'qtr') }}</span>
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

                                        @if ($plan->max_employees !== null)
                                            <div class="plan-feature">
                                                <i class="bi bi-people-fill"></i>
                                                Up to {{ $plan->max_employees }} employees
                                            </div>
                                        @else
                                            <div class="plan-feature">
                                                <i class="bi bi-people-fill"></i>
                                                Unlimited employees
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                {{-- Static fallback if no plans in DB yet --}}
                                @foreach ([['monthly', '29', 'mo', 'Monthly', false, ['Full POS system', 'Inventory management', 'Online storefront', 'Order & delivery tools', 'Up to 10 employees', 'Basic analytics']], ['quarterly', '79', 'qtr', 'Quarterly', false, ['Everything in Monthly', 'Up to 20 employees', 'Standard analytics', 'Priority email support']], ['annual', '249', 'yr', 'Annual', true, ['Everything in Quarterly', 'Unlimited employees', 'Advanced analytics & exports', 'Featured shop listing', 'Priority support']]] as [$slug, $price, $per, $name, $featured, $features])
                                    <div class="col-12 col-md-4">
                                        <div class="plan-card {{ $featured ? 'plan-card--featured' : '' }}"
                                            style="opacity:0.65;pointer-events:none;">
                                            @if ($featured)
                                                <div class="plan-card__ribbon">Most Popular</div>
                                            @endif
                                            <div class="plan-card__name">{{ $name }}</div>
                                            <div class="plan-card__price">
                                                <sup>P</sup>{{ $price }}<span>/{{ $per }}</span></div>
                                            <div class="plan-card__cycle">Billed {{ $slug }}</div>
                                            <hr class="plan-card__divider">
                                            @foreach ($features as $f)
                                                <div class="plan-feature"><i
                                                        class="bi bi-check-circle-fill"></i>{{ $f }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>

                        {{-- Hidden plan ID --}}
                        <input type="hidden" name="plan_id" id="selectedPlanId"
                            value="{{ $currentSubscription?->plan_id ?? ($plans->firstWhere('is_featured', true)?->id ?? $plans->first()?->id) }}">

                        {{-- Order summary --}}
                        <div id="orderSummary"
                            style="background:var(--n-50);border:1px solid var(--card-border);
                                border-radius:var(--r-md);padding:1.25rem;margin-bottom:1.25rem;">
                            <div
                                style="font-family:'Outfit',sans-serif;font-size:0.8rem;font-weight:700;
                                    text-transform:uppercase;letter-spacing:0.06em;color:var(--tx-muted);
                                    margin-bottom:0.85rem;">
                                Order Summary
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.5rem;">
                                <span class="text-secondary">Plan</span>
                                <span class="fw-600" id="summaryPlan">Select a plan</span>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.5rem;">
                                <span class="text-secondary">Billing cycle</span>
                                <span class="fw-600" id="summaryCycle">—</span>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.5rem;">
                                <span class="text-secondary">Access duration</span>
                                <span class="fw-600" id="summaryDuration">—</span>
                            </div>
                            <hr class="divider" style="margin:0.85rem 0;">
                            <div style="display:flex;justify-content:space-between;font-size:1rem;">
                                <span class="fw-700">Total due today</span>
                                <span class="fw-700 mono text-green" id="summaryTotal">$0.00</span>
                            </div>
                        </div>

                        {{-- PayPal checkout button --}}
                        <button type="submit" class="vd-btn vd-btn--primary vd-btn--full vd-btn--lg" id="checkoutBtn">
                            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png"
                                alt="PayPal" style="height:18px;filter:brightness(10);">
                            Pay with PayPal
                        </button>

                        <p class="text-muted text-center mt-2" style="font-size:0.75rem;">
                            <i class="bi bi-lock-fill me-1"></i>
                            Secured by PayPal. You'll be redirected to complete payment.
                        </p>
                    </form>

                </div>
            </div>
        </div>

        {{-- ── Right: details + FAQ ── --}}
        <div class="col-12 col-xl-4">

            @if ($currentSubscription)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-receipt"></i>Current Subscription</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item__label">Plan</div>
                                <div class="detail-item__value">{{ $currentSubscription->plan->name }}</div>
                            </div>
                            <div>
                                <div class="detail-item__label">Amount</div>
                                <div class="detail-item__value mono">
                                    P{{ number_format($currentSubscription->amount_paid, 2) }}</div>
                            </div>
                            <div>
                                <div class="detail-item__label">Started</div>
                                <div class="detail-item__value" style="font-size:0.82rem;">
                                    {{ $currentSubscription->starts_at?->format('M d, Y') ?? '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="detail-item__label">Expires</div>
                                <div class="detail-item__value" style="font-size:0.82rem;">
                                    {{ $currentSubscription->expires_at?->format('M d, Y') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-check2-all"></i>What's Included</span>
                </div>
                <div class="vd-card__body">
                    @php
                        $included = [
                            ['bi-shop', 'Full POS System', 'Serve walk-in customers with barcode scanning and PayPal.'],
                            [
                                'bi-archive',
                                'Inventory Management',
                                'Track stock, set reorder alerts, and log every movement.',
                            ],
                            ['bi-shop', 'Online Storefront', 'Your own shop page with product listings and reviews.'],
                            [
                                'bi-truck',
                                'Order & Delivery Tools',
                                'Manage online orders and assign delivery personnel.',
                            ],
                            ['bi-tools', 'Service Management', 'Schedule and track installation and maintenance jobs.'],
                            ['bi-people', 'Employee Management', 'Create employees with role-based access control.'],
                            [
                                'bi-chat-dots',
                                'Customer Chat',
                                'Real-time messaging with customers about orders and services.',
                            ],
                            [
                                'bi-shield-check',
                                'Warranty Handling',
                                'Manage warranty claims and technician assignments.',
                            ],
                        ];
                    @endphp
                    @foreach ($included as [$icon, $title, $desc])
                        <div style="display:flex;align-items:flex-start;gap:0.65rem;margin-bottom:0.85rem;">
                            <div
                                style="width:30px;height:30px;border-radius:var(--r-sm);background:var(--g-50);
                                display:flex;align-items:center;justify-content:center;
                                color:var(--g-600);font-size:0.82rem;flex-shrink:0;margin-top:1px;">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:0.845rem;">{{ $title }}</div>
                                <div class="text-muted" style="font-size:0.78rem;line-height:1.5;margin-top:1px;">
                                    {{ $desc }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-question-circle"></i>Common Questions</span>
                </div>
                <div class="vd-card__body">
                    @php
                        $faqs = [
                            [
                                'Can I cancel anytime?',
                                'Yes. Cancel before your renewal date and your plan stays active until expiry — no charges after that.',
                            ],
                            [
                                'Can I switch plans?',
                                'Yes. Subscribing to a new plan starts a new billing cycle from today.',
                            ],
                            [
                                'Is payment secure?',
                                'All payments are processed by PayPal. We never store your card details.',
                            ],
                            [
                                'What happens when it expires?',
                                'Your portal is locked. You can renew at any time to restore full access. Your data is preserved.',
                            ],
                        ];
                    @endphp
                    @foreach ($faqs as [$q, $a])
                        <div style="border-bottom:1px solid var(--n-100);{{ $loop->last ? 'border-bottom:none;' : '' }}">
                            <button type="button" onclick="toggleFaq(this)"
                                style="width:100%;background:none;border:none;padding:0.75rem 0;
                                   font-family:'DM Sans',sans-serif;font-size:0.845rem;font-weight:600;
                                   color:var(--tx-primary);text-align:left;display:flex;
                                   justify-content:space-between;align-items:center;gap:0.5rem;cursor:pointer;">
                                {{ $q }}
                                <i class="bi bi-chevron-down"
                                    style="flex-shrink:0;font-size:0.75rem;transition:transform 0.2s;color:var(--tx-muted);"></i>
                            </button>
                            <div
                                style="display:none;font-size:0.82rem;color:var(--tx-secondary);
                                line-height:1.7;padding-bottom:0.75rem;">
                                {{ $a }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

@endsection

@php
    $planDataJson = $plans
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'cycle' => $p->billing_label,
                'duration' => $p->duration_days . ' days',
                'currency' => $p->currency,
            ];
        })
        ->keyBy('id');

    $defaultPlanId =
        $currentSubscription?->plan_id ??
        ($plans->firstWhere('is_featured', true)?->id ?? ($plans->first()?->id ?? null));
@endphp

@push('scripts')
    <script>

        const planData = @json($planDataJson);

        let selectedId = {{ $defaultPlanId ?? 'null' }};

        function selectPlan(id) {
            selectedId = id;
            document.getElementById('selectedPlanId').value = id;

            document.querySelectorAll('.plan-card').forEach(c => {
                c.classList.toggle('selected', parseInt(c.dataset.plan) === id);
            });

            const p = planData[id];
            if (p) {
                document.getElementById('summaryPlan').textContent = p.name + ' Plan';
                document.getElementById('summaryCycle').textContent = p.cycle;
                document.getElementById('summaryDuration').textContent = p.duration;
                document.getElementById('summaryTotal').textContent = p.currency + ' P' + parseFloat(p.price).toFixed(2);
            }
        }

        // Initialise summary on load
        if (selectedId) selectPlan(selectedId);

        // Form submit — show loading state
        document.getElementById('subForm').addEventListener('submit', function(e) {
            if (!selectedId) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Select a plan',
                    text: 'Please choose a subscription plan before proceeding.',
                    confirmButtonColor: '#157a30'
                });
                return;
            }
            const btn = document.getElementById('checkoutBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting to PayPal…';
        });

        // FAQ accordion
        function toggleFaq(btn) {
            const body = btn.nextElementSibling;
            const icon = btn.querySelector('i');
            const open = body.style.display === 'block';
            body.style.display = open ? 'none' : 'block';
            icon.style.transform = open ? '' : 'rotate(180deg)';
        }
    </script>
@endpush
