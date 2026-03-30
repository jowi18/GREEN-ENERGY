@extends('layouts.auth')

@section('title', 'Application Status')

@section('panel-subtitle')
    We appreciate your patience while we verify your business details.
@endsection

@section('panel-features')
    <li><i class="bi bi-shield-check fi"></i> Secure document verification</li>
    <li><i class="bi bi-clock-history fi"></i> Review within 3–5 business days</li>
    <li><i class="bi bi-envelope-check fi"></i> Email notification on approval</li>
    <li><i class="bi bi-lightning-charge fi"></i> Instant portal access after subscribing</li>
@endsection

@section('form-content')

    <div style="max-width:440px;">

        {{-- Status icon & heading --}}
        @php
            $statusConfig = match ($vendor->status) {
                'pending' => [
                    'icon' => 'bi-hourglass-split',
                    'wrap' => 'status-icon-pending',
                    'title' => 'Application Submitted',
                    'sub' => 'Your application is in our queue and will be reviewed shortly.',
                ],
                'under_review' => [
                    'icon' => 'bi-search',
                    'wrap' => 'status-icon-review',
                    'title' => 'Under Review',
                    'sub' => 'Our team is currently reviewing your business documents.',
                ],
                'approved', 'subscription_required' => [
                    'icon' => 'bi-check-circle',
                    'wrap' => 'status-icon-approved',
                    'title' => 'Application Approved!',
                    'sub' => 'Congratulations! Complete your subscription to activate your portal.',
                ],
                'rejected' => [
                    'icon' => 'bi-x-circle',
                    'wrap' => 'status-icon-rejected',
                    'title' => 'Application Not Approved',
                    'sub' => 'Unfortunately we could not approve your application at this time.',
                ],
                default => [
                    'icon' => 'bi-hourglass',
                    'wrap' => 'status-icon-pending',
                    'title' => 'Processing',
                    'sub' => 'Your application is being processed.',
                ],
            };
        @endphp

        <div class="status-card" style="margin-bottom:1.5rem;">
            <div class="text-center" style="padding-bottom:1.5rem;border-bottom:1px solid var(--border);">
                <div class="status-icon-wrap {{ $statusConfig['wrap'] }}">
                    <i class="bi {{ $statusConfig['icon'] }}"></i>
                </div>
                <h5
                    style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.25rem;
                       letter-spacing:-0.02em;margin-bottom:0.35rem;">
                    {{ $statusConfig['title'] }}
                </h5>
                <p style="font-size:0.875rem;color:#5a7060;line-height:1.6;max-width:320px;margin:0 auto;">
                    {{ $statusConfig['sub'] }}
                </p>
            </div>

            {{-- Pipeline tracker --}}
            <div style="padding:1.5rem 0.5rem 0.5rem;">
                @php
                    $steps = [
                        ['pending', 'bi-send', 'Submitted', 'Application received'],
                        ['under_review', 'bi-search', 'Under Review', 'Documents being verified'],
                        ['subscription_required', 'bi-check-circle', 'Approved', 'Ready to subscribe'],
                        ['active', 'bi-lightning-charge', 'Active', 'Portal fully accessible'],
                    ];

                    $statusOrder = [
                        'pending' => 0,
                        'under_review' => 1,
                        'subscription_required' => 2,
                        'approved' => 2,
                        'active' => 3,
                        'rejected' => -1,
                    ];
                    $currentIdx = $statusOrder[$vendor->status] ?? 0;
                @endphp

                @if ($vendor->isRejected())
                    {{-- Rejection message --}}
                    <div
                        style="background:#fff5f5;border:1px solid #fecaca;border-radius:10px;
                        padding:1rem;font-size:0.85rem;color:#991b1b;">
                        <div style="font-weight:600;margin-bottom:0.4rem;">
                            <i class="bi bi-info-circle me-1"></i>Reason for rejection:
                        </div>
                        {{ $vendor->rejection_reason ?? 'No reason provided. Please contact support.' }}
                    </div>
                @else
                    {{-- Step timeline --}}
                    @foreach ($steps as $i => [$key, $icon, $label, $desc])
                        @php
                            $done = $i < $currentIdx;
                            $active = $i === $currentIdx;
                            $pending = $i > $currentIdx;
                        @endphp
                        <div
                            style="display:flex;align-items:flex-start;gap:0.85rem;
                            {{ !$loop->last ? 'margin-bottom:0;padding-bottom:0;' : '' }}
                            position:relative;">

                            {{-- Connector line --}}
                            @if (!$loop->last)
                                <div
                                    style="position:absolute;left:15px;top:30px;bottom:-4px;width:2px;
                                background:{{ $done ? 'var(--green-400)' : 'var(--border)' }};
                                z-index:0;">
                                </div>
                            @endif

                            {{-- Step circle --}}
                            <div
                                style="width:30px;height:30px;border-radius:50%;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;
                                font-size:0.8rem;position:relative;z-index:1;
                                {{ $done
                                    ? 'background:var(--green-400);color:#fff;border:none;'
                                    : ($active
                                        ? 'background:var(--green-900);color:#fff;border:2px solid var(--green-400);box-shadow:0 0 0 4px var(--green-50);'
                                        : 'background:#fff;color:var(--text-muted,#9aada0);border:2px solid var(--border);') }}">
                                <i class="bi {{ $done ? 'bi-check2' : $icon }}"></i>
                            </div>

                            {{-- Step text --}}
                            <div style="padding-bottom:1.25rem;flex:1;">
                                <div
                                    style="font-size:0.875rem;font-weight:{{ $active ? '700' : '500' }};
                                    color:{{ $done || $active ? 'var(--green-900)' : 'var(--text-muted,#9aada0)' }};">
                                    {{ $label }}
                                    @if ($active)
                                        <span
                                            style="font-size:0.65rem;background:var(--yellow-100);color:#8a6500;
                                             border-radius:20px;padding:0.1rem 0.5rem;margin-left:0.4rem;
                                             font-weight:700;letter-spacing:0.04em;text-transform:uppercase;">
                                            Current
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:0.78rem;color:var(--text-muted,#9aada0);margin-top:0.15rem;">
                                    {{ $desc }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- CTA based on status --}}
        @if ($vendor->status === 'subscription_required' || $vendor->status === 'approved')
            <a href="{{ route('vendor.subscription.index') }}" class="btn-primary-green w-100"
                style="justify-content:center;padding:0.75rem;font-size:0.9rem;">
                <i class="bi bi-lightning-charge"></i> Subscribe & Activate Portal
            </a>
            <p style="text-align:center;font-size:0.78rem;color:#8fa695;margin-top:0.85rem;">
                Plans start from $29.99/month. Cancel anytime.
            </p>
        @elseif($vendor->isRejected())
            <div style="display:flex;gap:0.75rem;">
                <a href="#" class="btn-outline-green" style="flex:1;justify-content:center;">
                    <i class="bi bi-headset"></i> Contact Support
                </a>
                <a href="{{ route('vendor.register') }}" class="btn-primary-green" style="flex:1;justify-content:center;">
                    <i class="bi bi-arrow-repeat"></i> Re-apply
                </a>
            </div>
        @else
            {{-- Pending / under review --}}
            <div
                style="background:var(--green-50);border:1px solid var(--green-100);border-radius:10px;
                padding:1rem;font-size:0.83rem;color:var(--green-800);">
                <div style="display:flex;align-items:center;gap:0.5rem;font-weight:600;margin-bottom:0.3rem;">
                    <i class="bi bi-clock"></i> What happens next?
                </div>
                <ul style="margin:0;padding-left:1.1rem;line-height:1.8;color:#3a6645;">
                    <li>Our team reviews your documents (3–5 business days)</li>
                    <li>You'll receive an email with the decision</li>
                    <li>If approved, subscribe to activate your full portal</li>
                </ul>
            </div>

            <div style="margin-top:1rem;text-align:center;">
                <button onclick="window.location.reload()"
                    style="background:none;border:none;color:var(--green-600);font-size:0.83rem;
                       cursor:pointer;font-weight:500;font-family:inherit;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh status
                </button>
            </div>
        @endif

        {{-- Business info summary --}}
        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);">
            <p
                style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;
                  color:var(--text-muted,#9aada0);margin-bottom:0.65rem;">
                Your Application
            </p>
            <div style="font-size:0.85rem;display:flex;flex-direction:column;gap:0.4rem;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#5a7060;">Business</span>
                    <span style="font-weight:600;">{{ $vendor->business_name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#5a7060;">Submitted</span>
                    <span style="font-weight:500;">{{ $vendor->created_at->format('M d, Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#5a7060;">Documents</span>
                    <span style="font-weight:500;">{{ $vendor->documents()->count() }} uploaded</span>
                </div>
            </div>
        </div>

        {{-- Logout --}}
        <div style="margin-top:1.25rem;text-align:center;">
            <form method="POST" action="{{ route('vendor.logout') }}" style="display:inline;">
                @csrf
                <button type="submit"
                    style="background:none;border:none;color:#8fa695;font-size:0.8rem;
                           cursor:pointer;font-family:inherit;font-weight:500;">
                    <i class="bi bi-box-arrow-left me-1"></i>Sign out
                </button>
            </form>
        </div>

    </div>

@endsection
