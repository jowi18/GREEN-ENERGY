@extends('layouts.admin')
@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')
@section('breadcrumb')
    <a href="{{ route('admin.subscriptions.index') }}" class="text-secondary">Subscriptions</a>
    <span class="sep">›</span><span class="current">Plans</span>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.subscriptions.plans.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Plan
        </a>
    </div>

    <div class="row g-3">
        @forelse($plans as $plan)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="panel h-100" style="border-top:3px solid {{ $plan->is_featured ? '#157a30' : '#e5ede5' }};">
                    <div style="padding:1.25rem;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-800" style="font-size:1rem;">{{ $plan->name }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $plan->slug }} ·
                                    {{ ucfirst($plan->billing_cycle) }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($plan->is_featured)
                                    <span class="badge bg-success" style="font-size:.62rem;">Featured</span>
                                @endif
                                <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}"
                                    style="font-size:.62rem;">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div style="font-size:1.75rem;font-weight:800;font-family:monospace;color:#157a30;">
                            ₱{{ number_format($plan->price, 0) }}
                            <span style="font-size:.75rem;font-weight:400;color:#6b7a70;">/
                                {{ $plan->billing_cycle }}</span>
                        </div>

                        <p class="text-muted mt-2 mb-3" style="font-size:.82rem;">{{ $plan->description }}</p>

                        @if ($plan->features)
                            <ul style="font-size:.78rem;padding-left:1.2rem;color:#4b5563;margin-bottom:1rem;">
                                @foreach ($plan->features as $f)
                                    <li>{{ $f }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div
                            style="font-size:.72rem;color:#6b7a70;border-top:1px solid #f1f5f1;padding-top:.65rem;margin-top:.65rem;">
                            <span><i class="bi bi-box-seam me-1"></i>Max
                                {{ $plan->max_products === -1 ? 'Unlimited' : $plan->max_products }} products</span>
                            &nbsp;·&nbsp;
                            <span><i
                                    class="bi bi-people me-1"></i>{{ $plan->max_employees === -1 ? 'Unlimited' : $plan->max_employees }}
                                employees</span>
                            &nbsp;·&nbsp;
                            <span><i class="bi bi-person-check me-1"></i>{{ $plan->subscriptions_count }}
                                subscribers</span>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}"
                                class="btn btn-sm btn-outline-success flex-fill">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.subscriptions.plans.toggle', $plan) }}">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm btn-{{ $plan->is_active ? 'outline-danger' : 'outline-secondary' }}">
                                    <i class="bi bi-toggle-{{ $plan->is_active ? 'on' : 'off' }}"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-layers" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                No plans yet. <a href="{{ route('admin.subscriptions.plans.create') }}">Create one →</a>
            </div>
        @endforelse
    </div>
@endsection
