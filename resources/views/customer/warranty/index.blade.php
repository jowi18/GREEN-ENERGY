{{-- ================================================================
     resources/views/customer/warranty/index.blade.php
================================================================ --}}
@extends('layouts.customer')
@section('title', 'My Warranty Claims')

@section('content')
    <div class="cu-page">
        <div class="cu-section-head mb-4">
            <div class="cu-section-title">🛡️ My Warranty Claims</div>
            <a href="{{ route('customer.warranty.create') }}" class="cu-btn cu-btn--primary cu-btn--sm">
                <i class="bi bi-plus-lg"></i> File New Claim
            </a>
        </div>

        {{-- Status filter tabs --}}
        <div style="display:flex;gap:.4rem;overflow-x:auto;padding-bottom:.35rem;margin-bottom:1.1rem;scrollbar-width:none;">
            @foreach (['' => 'All', 'submitted' => 'Submitted', 'under_review' => 'Under Review', 'approved' => 'Approved', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $val => $label)
                <a href="{{ route('customer.warranty.index', $val ? ['status' => $val] : []) }}"
                    class="cu-btn cu-btn--sm {{ request('status', '') === $val ? 'cu-btn--primary' : 'cu-btn--ghost' }}"
                    style="flex-shrink:0;white-space:nowrap;">{{ $label }}</a>
            @endforeach
        </div>

        @if ($claims->count())
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach ($claims as $claim)
                    <a href="{{ route('customer.warranty.show', $claim) }}"
                        style="display:block;text-decoration:none;color:inherit;">
                        <div class="cu-card" style="transition:box-shadow .2s;">
                            <div class="cu-card__body">
                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                    <div
                                        style="width:46px;height:46px;border-radius:var(--r-md);
                                    background:var(--cg-50);color:var(--cg-600);
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:1.2rem;flex-shrink:0;">
                                        🛡️
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-weight:800;font-size:.9rem;">
                                            {{ $claim->product?->name ?? $claim->orderItem->product_name }}
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem;">
                                            {{ $claim->request_number }}
                                            &middot; {{ $claim->request_type_label }}
                                            &middot; {{ $claim->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem;">
                                            {{ $claim->vendor->business_name }}
                                        </div>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <span class="cu-badge cu-badge--{{ $claim->status_color }}"
                                            style="font-size:.68rem;">
                                            {{ $claim->status_label }}
                                        </span>
                                        <div class="text-muted" style="font-size:.72rem;margin-top:.3rem;">
                                            {{ $claim->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $claims->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="cu-empty">
                <i class="bi bi-shield"></i>
                <h6>No warranty claims</h6>
                <p>File a claim for any product with an active warranty from your completed orders.</p>
                <a href="{{ route('customer.warranty.create') }}" class="cu-btn cu-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-shield-check"></i> File a Claim
                </a>
            </div>
        @endif
    </div>
@endsection
