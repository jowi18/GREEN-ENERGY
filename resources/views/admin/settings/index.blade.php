@extends('layouts.admin')
@section('title', 'Platform Settings')
@section('page-title', 'Platform Settings')

@section('content')
    <div class="row g-3">

        {{-- ── Left: Settings form ── --}}
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf @method('PUT')

                {{-- App Identity --}}
                <div class="panel mb-3">
                    <div
                        style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;
                            font-weight:800;font-size:.875rem;color:#1a2e1c;">
                        <i class="bi bi-layers me-2" style="color:#157a30;"></i>App Identity
                    </div>
                    <div style="padding:1.25rem;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">App Name <span class="text-danger">*</span></label>
                                <input type="text" name="app_name"
                                    value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}"
                                    class="form-control" required maxlength="80">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Tagline</label>
                                <input type="text" name="app_tagline"
                                    value="{{ old('app_tagline', $settings['app_tagline'] ?? '') }}" class="form-control"
                                    maxlength="160" placeholder="Your trusted solar marketplace">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Support Email</label>
                                <input type="email" name="support_email"
                                    value="{{ old('support_email', $settings['support_email'] ?? '') }}"
                                    class="form-control" placeholder="support@solarapp.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Support Phone</label>
                                <input type="text" name="support_phone"
                                    value="{{ old('support_phone', $settings['support_phone'] ?? '') }}"
                                    class="form-control" placeholder="+63 9XX XXX XXXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-600">Default Currency</label>
                                <input type="text" name="default_currency"
                                    value="{{ old('default_currency', $settings['default_currency'] ?? 'PHP') }}"
                                    class="form-control" maxlength="10" placeholder="PHP">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-600">Commission Rate (%)</label>
                                <input type="number" name="commission_rate"
                                    value="{{ old('commission_rate', $settings['commission_rate'] ?? 0) }}"
                                    class="form-control" min="0" max="100" step="0.01" placeholder="0">
                                <div class="form-text">Platform commission on vendor sales (future billing).</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Access Controls --}}
                <div class="panel mb-3">
                    <div
                        style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;
                            font-weight:800;font-size:.875rem;color:#1a2e1c;">
                        <i class="bi bi-shield-lock me-2" style="color:#157a30;"></i>Access Controls
                    </div>
                    <div style="padding:.25rem 0;">
                        @foreach ([['maintenance_mode', 'Maintenance Mode', 'Put the platform in maintenance mode. Only admins can access.', '#ef4444'], ['allow_new_registrations', 'Allow New Registrations', 'Allow new vendors and customers to register on the platform.', '#157a30'], ['require_email_verification', 'Require Email Verification', 'New users must verify their email before accessing the platform.', '#3b82f6'], ['paypal_sandbox', 'PayPal Sandbox Mode', 'Use PayPal sandbox for testing. Disable in production.', '#f59e0b']] as [$field, $label, $desc, $activeColor])
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;
                                padding:.9rem 1.25rem;border-bottom:1px solid #f8faf8;">
                                <div style="flex:1;padding-right:1rem;">
                                    <div class="fw-700" style="font-size:.875rem;">{{ $label }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">{{ $desc }}</div>
                                </div>
                                <label style="position:relative;width:42px;height:22px;flex-shrink:0;cursor:pointer;">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        {{ $settings[$field] ?? false ? 'checked' : '' }}
                                        style="opacity:0;width:0;height:0;"
                                        onchange="this.closest('label').querySelector('span').style.background = this.checked ? '{{ $activeColor }}' : '#d1d5db'">
                                    <span
                                        style="position:absolute;inset:0;border-radius:999px;cursor:pointer;transition:background .2s;
                                         background:{{ $settings[$field] ?? false ? $activeColor : '#d1d5db' }};"></span>
                                    <span
                                        style="position:absolute;top:2px;left:2px;width:18px;height:18px;
                                         border-radius:50%;background:#fff;transition:transform .2s;
                                         {{ $settings[$field] ?? false ? 'transform:translateX(20px);' : '' }}"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Maintenance Message --}}
                <div class="panel mb-4">
                    <div
                        style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;
                            font-weight:800;font-size:.875rem;color:#1a2e1c;">
                        <i class="bi bi-cone-striped me-2" style="color:#f59e0b;"></i>Maintenance Message
                    </div>
                    <div style="padding:1.25rem;">
                        <textarea name="maintenance_message" rows="3" class="form-control" maxlength="500"
                            placeholder="We're currently performing scheduled maintenance. We'll be back shortly!">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                        <div class="form-text">Shown to users when maintenance mode is active.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg px-4">
                    <i class="bi bi-floppy me-1"></i> Save Settings
                </button>
            </form>
        </div>

        {{-- ── Right: Platform stats ── --}}
        <div class="col-12 col-lg-4">
            <div class="panel" style="position:sticky;top:calc(64px + 1rem);">
                <div
                    style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f1;
                        font-weight:800;font-size:.875rem;color:#1a2e1c;">
                    <i class="bi bi-speedometer2 me-2" style="color:#157a30;"></i>Platform Overview
                </div>
                @foreach ([['Total Vendors', $platformStats['total_vendors'], '#157a30', 'bi-shop'], ['Active Vendors', $platformStats['active_vendors'], '#22c55e', 'bi-check-circle'], ['Total Customers', $platformStats['total_customers'], '#3b82f6', 'bi-people'], ['Total Orders', $platformStats['total_orders'], '#8b5cf6', 'bi-bag-check'], ['Subscription Rev.', '₱' . number_format($platformStats['total_revenue'], 0), '#f59e0b', 'bi-currency-exchange'], ['Total Reviews', $platformStats['total_reviews'], '#ef4444', 'bi-star']] as [$l, $v, $c, $i])
                    <div
                        style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;
                        border-bottom:1px solid #f8faf8;font-size:.875rem;">
                        <div
                            style="width:32px;height:32px;border-radius:8px;background:{{ $c }}1a;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $i }}" style="color:{{ $c }};font-size:.9rem;"></i>
                        </div>
                        <div style="flex:1;color:#4b5563;">{{ $l }}</div>
                        <div style="font-weight:800;color:#1a2e1c;font-family:monospace;">{{ $v }}</div>
                    </div>
                @endforeach

                <div style="padding:1rem 1.25rem;">
                    <div class="text-muted" style="font-size:.72rem;">
                        <i class="bi bi-clock me-1"></i>
                        Data as of {{ now()->format('M d, Y g:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
