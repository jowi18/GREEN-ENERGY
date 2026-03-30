{{-- resources/views/customer/solar/index.blade.php --}}
@extends('layouts.customer')
@section('title', 'My Solar Projects')

@push('styles')
    <style>
        .sp-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            transition: border-color .15s, box-shadow .15s;
            text-decoration: none;
            display: block;
            color: inherit;
            margin-bottom: 1rem;
        }

        .sp-card:hover {
            border-color: var(--cg-300);
            box-shadow: 0 4px 16px rgba(46, 204, 113, .1);
            color: inherit;
        }

        .sp-card__header {
            padding: 1rem 1.25rem .75rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
        }

        .sp-card__body {
            padding: 0 1.25rem 1rem;
        }

        .sp-card__footer {
            padding: .65rem 1.25rem;
            background: var(--n-50);
            border-top: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .sp-mini-pipeline {
            display: flex;
            align-items: center;
            gap: 0;
            overflow: hidden;
            flex: 1;
        }

        .sp-mini-step {
            height: 4px;
            flex: 1;
            background: var(--n-200);
            border-radius: 2px;
            margin-right: 2px;
            transition: background .3s;
        }

        .sp-mini-step.done {
            background: var(--cg-500);
        }

        .sp-mini-step.current {
            background: var(--cg-400);
        }

        .sp-property-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            font-weight: 600;
            padding: .2rem .6rem;
            border-radius: var(--r-full);
            background: var(--n-100);
            color: var(--tx-muted);
        }

        .sp-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--tx-muted);
        }

        .sp-empty__icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: .3;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:760px;">

        {{-- Header --}}
        <div
            style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;
        gap:.75rem;margin-bottom:1.5rem;">
            <div>
                <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">
                    ☀️ My Solar Projects
                </h5>
                <div class="text-muted" style="font-size:.82rem;margin-top:.2rem;">
                    Track your solar installation inquiries and project progress.
                </div>
            </div>
            <a href="{{ route('customer.solar.create') }}" class="cu-btn cu-btn--primary">
                <i class="bi bi-plus-lg"></i> New Inquiry
            </a>
        </div>

        @if (session('success'))
            <div class="cu-alert cu-alert--success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($projects->count())

            @foreach ($projects as $project)
                @php
                    $pipeline = \App\Models\SolarProject::STATUS_PIPELINE;
                    $curIdx = array_search($project->status, $pipeline) ?: 0;
                    $total = count($pipeline);
                @endphp

                <a href="{{ route('customer.solar.show', $project) }}" class="sp-card">

                    <div class="sp-card__header">
                        <div>
                            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;">
                                <span class="mono fw-700" style="font-size:.9rem;color:var(--cg-700);">
                                    {{ $project->project_number }}
                                </span>
                                <span class="cu-badge {{ $project->status_badge_class }}" style="font-size:.65rem;">
                                    {{ $project->status_label }}
                                </span>
                            </div>
                            <div class="fw-700" style="font-size:.9rem;">
                                {{ $project->vendor->business_name }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem;margin-top:.15rem;">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $project->installation_address }}
                            </div>
                        </div>

                        {{-- Property type badge --}}
                        <div class="sp-property-badge">
                            @php $propIcons = ['residential'=>'🏠','commercial'=>'🏢','industrial'=>'🏭','agricultural'=>'🌾']; @endphp
                            {{ $propIcons[$project->property_type] ?? '🏠' }}
                            {{ ucfirst($project->property_type) }}
                        </div>
                    </div>

                    <div class="sp-card__body">
                        {{-- Mini pipeline bar --}}
                        <div style="margin-bottom:.4rem;">
                            <div class="sp-mini-pipeline">
                                @foreach ($pipeline as $i => $step)
                                    <div
                                        class="sp-mini-step
                                    {{ $i < $curIdx ? 'done' : ($i === $curIdx ? 'current' : '') }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div style="font-size:.72rem;color:var(--tx-muted);">
                            Stage {{ $curIdx + 1 }} of {{ $total }}
                            @if ($project->status !== 'cancelled' && $project->status !== 'warranty_support_active')
                                &nbsp;·&nbsp; {{ round((($curIdx + 1) / $total) * 100) }}% complete
                            @endif
                        </div>
                    </div>

                    <div class="sp-card__footer">
                        <div class="text-muted" style="font-size:.75rem;">
                            <i class="bi bi-clock me-1"></i>
                            Submitted {{ $project->created_at->diffForHumans() }}
                        </div>

                        <div style="display:flex;align-items:center;gap:.65rem;">
                            @if ($project->activeQuotation && $project->status === 'quotation_generated')
                                <span style="font-size:.75rem;font-weight:700;color:#d97706;">
                                    <i class="bi bi-exclamation-circle me-1"></i>Quotation awaiting your response
                                </span>
                            @endif
                            <span style="font-size:.75rem;color:var(--cg-600);font-weight:600;">
                                View details <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </div>

                </a>
            @endforeach

            <div>{{ $projects->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="cu-card">
                <div class="sp-empty">
                    <div class="sp-empty__icon">☀️</div>
                    <h6 class="fw-700" style="font-family:'Nunito',sans-serif;">No Solar Projects Yet</h6>
                    <p style="font-size:.875rem;max-width:320px;margin:.5rem auto 1.25rem;">
                        Submit your first solar installation inquiry and start your journey to renewable energy.
                    </p>
                    <a href="{{ route('customer.solar.create') }}" class="cu-btn cu-btn--primary">
                        <i class="bi bi-plus-lg"></i> Start a Solar Inquiry
                    </a>
                </div>
            </div>
        @endif

    </div>
@endsection
