{{-- ════════════════════════════════════════════════════
     resources/views/customer/services/index.blade.php
════════════════════════════════════════════════════ --}}
@extends('layouts.customer')
@section('title', 'My Service Bookings')

@section('content')
    <div class="cu-page">
        <div class="cu-section-head" style="margin-bottom:1.25rem;">
            <div class="cu-section-title">🔧 My Service Bookings</div>
            <a href="{{ route('customer.services.create') }}" class="cu-btn cu-btn--primary cu-btn--sm">
                <i class="bi bi-plus-lg"></i> Book a Service
            </a>
        </div>

        {{-- Status filter --}}
        <div
            style="display:flex;gap:0.4rem;overflow-x:auto;padding-bottom:0.35rem;margin-bottom:1.1rem;scrollbar-width:none;">
            @foreach (['' => 'All', 'submitted' => 'Submitted', 'reviewing' => 'Under Review', 'quoted' => 'Quoted', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                <a href="{{ route('customer.services.index', $val ? ['status' => $val] : []) }}"
                    class="cu-btn cu-btn--sm {{ request('status', '') === $val ? 'cu-btn--primary' : 'cu-btn--ghost' }}"
                    style="flex-shrink:0;white-space:nowrap;">{{ $label }}</a>
            @endforeach
        </div>

        @if ($bookings->count())
            @foreach ($bookings as $sr)
                <a href="{{ route('customer.services.show', $sr) }}"
                    style="display:block;text-decoration:none;color:inherit;margin-bottom:0.75rem;">
                    <div class="cu-card" style="transition:box-shadow 0.2s;">
                        <div class="cu-card__body">
                            <div class="d-flex align-items-start gap-3 flex-wrap">
                                <div
                                    style="width:44px;height:44px;border-radius:var(--r-md);
                                background:var(--cg-50);color:var(--cg-600);
                                display:flex;align-items:center;justify-content:center;
                                font-size:1.1rem;flex-shrink:0;">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:800;font-size:0.9rem;">{{ $sr->service_title }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">
                                        {{ $sr->vendor->business_name }} &middot;
                                        {{ ucwords(str_replace('_', ' ', $sr->service_type)) }} &middot;
                                        {{ $sr->request_number }}
                                    </div>
                                    @if ($sr->scheduled_date)
                                        <div
                                            style="font-size:0.78rem;color:var(--cg-600);margin-top:0.2rem;font-weight:700;">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            Scheduled: {{ $sr->scheduled_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                    @if ($sr->quoted_amount && $sr->status === 'quoted')
                                        <div
                                            style="font-size:0.78rem;color:var(--cy-600);margin-top:0.2rem;font-weight:700;">
                                            <i class="bi bi-tag me-1"></i>
                                            Quote received: ₱{{ number_format($sr->quoted_amount, 2) }} — Action required!
                                        </div>
                                    @endif
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <span class="cu-badge cu-badge--{{ $sr->status_color }}" style="font-size:0.68rem;">
                                        {{ $sr->status_label }}
                                    </span>
                                    @if ($sr->proofs->count())
                                        <div style="font-size:0.72rem;color:var(--cg-600);margin-top:0.35rem;">
                                            <i class="bi bi-images me-1"></i>{{ $sr->proofs->count() }} proof(s)
                                        </div>
                                    @endif
                                    <div class="text-muted" style="font-size:0.72rem;margin-top:0.25rem;">
                                        {{ $sr->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
            <div class="d-flex justify-content-center mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="cu-empty">
                <i class="bi bi-tools"></i>
                <h6>No service bookings yet</h6>
                <p>Book a solar installation, maintenance, or repair from one of our vendors.</p>
                <a href="{{ route('customer.services.create') }}" class="cu-btn cu-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-plus-lg"></i> Book a Service
                </a>
            </div>
        @endif
    </div>
@endsection
