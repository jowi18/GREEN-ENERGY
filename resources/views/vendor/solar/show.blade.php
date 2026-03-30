{{-- resources/views/vendor/solar/show.blade.php --}}
@extends('layouts.vendor')
@section('title', 'Solar Project · ' . $solarProject->project_number)
@section('page-title', 'Solar Project')

@section('breadcrumb')
    <a href="{{ route('vendor.solar.index') }}" class="text-secondary">Solar Projects</a>
    <span class="sep">›</span>
    <span class="current">{{ $solarProject->project_number }}</span>
@endsection

@push('styles')
    <style>
        /* ── Activity log ── */
        .act-log {
            position: relative;
            padding-left: 1.5rem;
        }

        .act-log::before {
            content: '';
            position: absolute;
            left: .45rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--card-border);
        }

        .act-item {
            position: relative;
            margin-bottom: .85rem;
        }

        .act-item:last-child {
            margin-bottom: 0;
        }

        .act-dot {
            position: absolute;
            left: -1.27rem;
            top: .2rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--g-500);
            border: 2px solid var(--card-bg);
        }

        .act-time {
            font-size: .7rem;
            color: var(--tx-muted);
        }

        .act-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        .act-note {
            font-size: .78rem;
            color: var(--tx-secondary);
            margin-top: .1rem;
        }

        /* ── Doc grid ── */
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: .5rem;
        }

        .doc-tile {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s;
            display: block;
        }

        .doc-tile:hover {
            border-color: var(--g-400);
        }

        .doc-tile__img {
            width: 100%;
            height: 72px;
            object-fit: cover;
            background: var(--n-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tx-muted);
            font-size: 1.4rem;
        }

        .doc-tile__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-tile__label {
            padding: .3rem .45rem;
            font-size: .65rem;
            font-weight: 600;
            color: var(--tx-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Requirement checklist ── */
        .req-check {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            padding: .3rem 0;
        }

        .req-check i {
            font-size: .8rem;
            flex-shrink: 0;
        }

        .req-check.met i {
            color: var(--g-500);
        }

        .req-check.unmet i {
            color: #f59e0b;
        }

        .req-check.met span {
            color: var(--tx-secondary);
        }

        .req-check.unmet span {
            color: #92400e;
            font-weight: 600;
        }

        /* ── Blocked btn ── */
        .vd-btn--blocked {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
@endpush

@section('content')
    @php
        $sp = $solarProject;
        $pipeline = \App\Models\SolarProject::STATUS_PIPELINE;
        $curIdx = array_search($sp->status, $pipeline);
        $history = collect(array_reverse($sp->status_history ?? []));

        // Pre-compute transition guards for UI
        $transitionGuards = [];
        foreach ($allowedTransitions as $t) {
            $result = $sp->canTransitionTo($t);
            $transitionGuards[$t] = [
                'allowed' => $result === true,
                'reason' => $result === true ? null : $result,
            ];
        }

        // Requirements per status
        $hasEngineer = (bool) $sp->assigned_engineer_id;
        $hasSurveyDate = (bool) $sp->survey_date;
        $hasInstallDate = (bool) $sp->installation_date;
        $hasContract = $sp->activeContract !== null;
        $activeContract = $sp->activeContract;
    @endphp

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="vd-alert vd-alert--success gap-section">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="vd-alert vd-alert--danger gap-section">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Header ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="mono fw-700" style="font-size:1.05rem;color:var(--g-700);">
                            {{ $sp->project_number }}
                        </span>
                        <span class="vd-badge {{ $sp->status_badge_class }}" style="font-size:.68rem;">
                            {{ $sp->status_label }}
                        </span>
                    </div>
                    <div class="fw-700" style="font-size:.95rem;">{{ $sp->customer->full_name }}</div>
                    <div class="text-muted" style="font-size:.78rem;">
                        <i class="bi bi-geo-alt me-1"></i>{{ $sp->installation_address }}
                        · Filed {{ $sp->created_at->format('M d, Y') }}
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap align-items-center">

                    {{-- Forward transition buttons --}}
                    @foreach ($allowedTransitions as $transition)
                        @if ($transition === 'cancelled')
                            @continue
                        @endif
                        @php
                            $guard = $transitionGuards[$transition];
                            $blocked = !$guard['allowed'];
                        @endphp
                        <button class="vd-btn vd-btn--primary vd-btn--sm {{ $blocked ? 'vd-btn--blocked' : '' }}"
                            onclick="{{ $blocked ? '' : "openStatusModal('{$transition}')" }}"
                            {{ $blocked ? 'disabled' : '' }} title="{{ $blocked ? $guard['reason'] : '' }}">
                            <i class="bi bi-arrow-right-circle"></i>
                            → {{ \App\Models\SolarProject::STATUS_LABELS[$transition] }}
                            @if ($blocked)
                                <i class="bi bi-lock-fill ms-1" style="font-size:.6rem;opacity:.7;"></i>
                            @endif
                        </button>
                    @endforeach

                    {{-- Quotation CTA --}}
                    @if (in_array($sp->status, ['site_survey_completed', 'system_designed', 'proposal_rejected']))
                        <a href="{{ route('vendor.solar.quotation.create', $sp) }}"
                            class="vd-btn vd-btn--ghost vd-btn--sm">
                            <i class="bi bi-file-earmark-plus"></i>
                            {{ $sp->status === 'proposal_rejected' ? 'Revise Quotation' : 'Create Quotation' }}
                        </a>
                    @endif

                    @if(in_array($sp->status, ['site_survey_scheduled']))
                        <a href="{{ route('vendor.solar.edit', $sp) }}"
                            class="vd-btn vd-btn--ghost vd-btn--sm">
                            <i class="bi bi-file-earmark-plus"></i>
                            Site Information Update
                        </a>
                    @endif

                    {{-- Contract CTA --}}
                    @if ($sp->status === 'proposal_approved' && !$hasContract)
                        <a href="{{ route('vendor.solar.contract.create', $sp) }}" class="vd-btn vd-btn--ghost vd-btn--sm">
                            <i class="bi bi-file-earmark-text"></i> Create Contract
                        </a>
                    @elseif (
                        $hasContract &&
                            in_array($sp->status, [
                                'proposal_approved',
                                'contract_signed',
                                'permit_processing',
                                'equipment_prepared',
                                'installation_scheduled',
                                'installation_completed',
                                'testing_commissioning',
                                'utility_inspection',
                                'system_activated',
                                'project_turnover',
                            ]))
                        <a href="{{ route('vendor.solar.contract.show', [$sp, $activeContract]) }}"
                            class="vd-btn vd-btn--ghost vd-btn--sm">
                            <i class="bi bi-file-earmark-check"></i> View Contract
                        </a>
                    @endif

                    {{-- Cancel — only before contract signed --}}
                    @if ($sp->isCancellable() && in_array('cancelled', $allowedTransitions))
                        <button class="vd-btn vd-btn--danger vd-btn--sm" onclick="openStatusModal('cancelled')">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    @elseif (!$sp->isCancellable() && $sp->isActive())
                        <button class="vd-btn vd-btn--danger vd-btn--sm vd-btn--blocked" disabled
                            title="Cannot cancel after contract has been signed.">
                            <i class="bi bi-lock-fill"></i> Cancel Locked
                        </button>
                    @endif

                </div>
            </div>

            {{-- Requirement warnings --}}
            @php
                $warnings = [];
                if (!$hasEngineer) {
                    $warnings[] = ['unmet', 'bi-person-x', 'No engineer assigned — required before scheduling.'];
                }
                if (!$hasSurveyDate && in_array($sp->status, ['consultation_completed', 'site_survey_scheduled'])) {
                    $warnings[] = ['unmet', 'bi-calendar-x', 'Site survey not yet scheduled.'];
                }
                if (!$hasInstallDate && in_array($sp->status, ['equipment_prepared'])) {
                    $warnings[] = ['unmet', 'bi-calendar-x', 'Installation not yet scheduled.'];
                }
                if (!$hasContract && $sp->status === 'proposal_approved') {
                    $warnings[] = [
                        'unmet',
                        'bi-file-earmark-x',
                        'Contract not yet created — required before permit processing.',
                    ];
                }
            @endphp

            @if (count($warnings))
                <div
                    style="margin-top:1rem;background:#fffbeb;border:1px solid #fde68a;
                border-radius:var(--r-md);padding:.75rem 1rem;">
                    <div
                        style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                    letter-spacing:.06em;color:#92400e;margin-bottom:.4rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Action Required
                    </div>
                    @foreach ($warnings as [$type, $icon, $msg])
                        <div class="req-check {{ $type }}">
                            <i class="bi {{ $icon }}"></i>
                            <span>{{ $msg }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    {{-- ── Pipeline stepper ── --}}
    @if ($sp->status !== 'cancelled')
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:1rem 1.25rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;">
                    <div style="font-size:.78rem;font-weight:700;color:var(--tx-primary);">
                        {{ $sp->status_label }}
                    </div>
                    <div style="font-size:.72rem;color:var(--tx-muted);">
                        Step {{ $curIdx !== false ? $curIdx + 1 : '?' }} of {{ count($pipeline) }}
                    </div>
                </div>
                <div style="height:6px;background:var(--card-border);border-radius:3px;margin-bottom:1rem;overflow:hidden;">
                    <div
                        style="height:100%;border-radius:3px;background:var(--g-500);
                width:{{ $curIdx !== false ? round((($curIdx + 1) / count($pipeline)) * 100) : 0 }}%;
                transition:width .3s;">
                    </div>
                </div>
                @php
                    $windowStart = max(0, ($curIdx !== false ? $curIdx : 0) - 2);
                    $windowEnd = min(count($pipeline) - 1, $windowStart + 5);
                    $windowStart = max(0, $windowEnd - 5);
                @endphp
                <div
                    style="display:flex;align-items:flex-start;gap:0;overflow-x:auto;scrollbar-width:none;padding-bottom:.25rem;">
                    @if ($windowStart > 0)
                        <div
                            style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:52px;opacity:.5;">
                            <div
                                style="width:24px;height:24px;border-radius:50%;background:var(--g-500);border:2px solid var(--g-500);display:flex;align-items:center;justify-content:center;font-size:.6rem;color:#fff;">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div
                                style="font-size:.58rem;font-weight:600;color:var(--tx-muted);margin-top:.3rem;text-align:center;line-height:1.2;max-width:52px;">
                                {{ \App\Models\SolarProject::STATUS_LABELS[$pipeline[0]] }}
                            </div>
                        </div>
                        <div style="flex:1;height:2px;background:var(--g-400);margin-top:11px;min-width:8px;flex-shrink:0;">
                        </div>
                        @if ($windowStart > 1)
                            <div
                                style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:32px;">
                                <div
                                    style="width:24px;height:24px;border-radius:50%;background:var(--g-50);border:1.5px solid var(--g-300);display:flex;align-items:center;justify-content:center;font-size:.6rem;color:var(--g-600);font-weight:700;">
                                    +{{ $windowStart - 1 }}
                                </div>
                                <div style="font-size:.58rem;color:var(--tx-muted);margin-top:.3rem;">more</div>
                            </div>
                            <div
                                style="flex:1;height:2px;background:var(--g-400);margin-top:11px;min-width:8px;flex-shrink:0;">
                            </div>
                        @endif
                    @endif

                    @foreach ($pipeline as $i => $step)
                        @if ($i < $windowStart || $i > $windowEnd)
                            @continue
                        @endif
                        @php
                            $done = $curIdx !== false && $i < $curIdx;
                            $current = $curIdx !== false && $i === $curIdx;
                        @endphp
                        @if ($i > $windowStart || ($windowStart === 0 && $i > 0))
                            <div
                                style="flex:1;height:2px;margin-top:11px;min-width:12px;flex-shrink:0;background:{{ $done ? 'var(--g-400)' : 'var(--card-border)' }};">
                            </div>
                        @endif
                        <div
                            style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:{{ $current ? '80px' : '64px' }};">
                            <div
                                style="width:{{ $current ? '28px' : '24px' }};height:{{ $current ? '28px' : '24px' }};
                        border-radius:50%;position:relative;z-index:1;
                        display:flex;align-items:center;justify-content:center;
                        font-size:.65rem;font-weight:700;transition:all .2s;
                        {{ $done ? 'background:var(--g-500);border:2px solid var(--g-500);color:#fff;' : '' }}
                        {{ $current ? 'background:var(--card-bg);border:2.5px solid var(--g-500);color:var(--g-600);box-shadow:0 0 0 4px var(--g-50);' : '' }}
                        {{ !$done && !$current ? 'background:var(--card-bg);border:2px solid var(--card-border);color:var(--tx-muted);' : '' }}">
                                @if ($done)
                                    <i class="bi bi-check2" style="font-size:.7rem;"></i>
                                @elseif ($current)
                                    <i class="bi bi-circle-fill" style="font-size:.35rem;"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div
                                style="font-size:{{ $current ? '.68rem' : '.6rem' }};font-weight:{{ $current ? '700' : '600' }};
                        color:{{ $current ? 'var(--g-700)' : ($done ? 'var(--tx-secondary)' : 'var(--tx-muted)') }};
                        margin-top:.35rem;text-align:center;line-height:1.25;max-width:{{ $current ? '80px' : '64px' }};">
                                {{ \App\Models\SolarProject::STATUS_LABELS[$step] }}
                            </div>
                        </div>
                    @endforeach

                    @if ($windowEnd < count($pipeline) - 1)
                        <div
                            style="flex:1;height:2px;background:var(--card-border);margin-top:11px;min-width:8px;flex-shrink:0;">
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:32px;">
                            <div
                                style="width:24px;height:24px;border-radius:50%;background:var(--n-50);border:1.5px solid var(--card-border);display:flex;align-items:center;justify-content:center;font-size:.6rem;color:var(--tx-muted);font-weight:700;">
                                +{{ count($pipeline) - 1 - $windowEnd }}
                            </div>
                            <div style="font-size:.58rem;color:var(--tx-muted);margin-top:.3rem;">more</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Left column ── --}}
        <div class="col-12 col-xl-8">

            {{-- Customer & project info --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person"></i> Customer & Project</span>
                </div>
                <div class="vd-card__body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="detail-item__label">Customer</div>
                            <div class="detail-item__value">{{ $sp->customer->full_name }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $sp->customer->user->email ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $sp->customer->phone ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="detail-item__label">Installation Address</div>
                            <div class="detail-item__value" style="font-size:.82rem;line-height:1.5;">
                                {{ $sp->installation_address }}
                                @if ($sp->installation_city)
                                    <br>{{ $sp->installation_city }}{{ $sp->installation_province ? ', ' . $sp->installation_province : '' }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-3">
                            <div class="detail-item__label">Property Type</div>
                            <div class="detail-item__value">{{ ucfirst($sp->property_type) }}</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="detail-item__label">System Type</div>
                            <div class="detail-item__value">
                                {{ $sp->system_type ? ucwords(str_replace('_', '-', $sp->system_type)) : '—' }}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="detail-item__label">Monthly Bill</div>
                            <div class="detail-item__value">
                                {{ $sp->estimated_monthly_bill ? '₱' . number_format($sp->estimated_monthly_bill, 2) : '—' }}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="detail-item__label">System Size</div>
                            <div class="detail-item__value">
                                {{ $sp->desired_system_size_kw ? $sp->desired_system_size_kw . ' kW' : '—' }}
                            </div>
                        </div>
                    </div>
                    @if ($sp->customer_notes)
                        <div class="mt-3">
                            <div class="detail-item__label">Customer Notes</div>
                            <div
                                style="background:var(--n-50);border:1px solid var(--card-border);
                            border-radius:var(--r-sm);padding:.65rem .85rem;
                            font-size:.82rem;color:var(--tx-secondary);line-height:1.7;margin-top:.25rem;">
                                {{ $sp->customer_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quotations --}}
            @if ($sp->quotations->isNotEmpty())
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-receipt"></i> Quotations</span>
                        @if (in_array($sp->status, ['site_survey_completed', 'system_designed', 'proposal_rejected']))
                            <a href="{{ route('vendor.solar.quotation.create', $sp) }}"
                                class="vd-btn vd-btn--ghost vd-btn--sm">
                                <i class="bi bi-plus-lg"></i> New Quotation
                            </a>
                        @endif
                    </div>
                    <div class="vd-card__body">
                        @foreach ($sp->quotations as $qt)
                            <div
                                style="border:1.5px solid var(--card-border);border-radius:var(--r-md);overflow:hidden;margin-bottom:.75rem;">
                                <div
                                    style="display:flex;align-items:center;justify-content:space-between;
                        padding:.7rem 1rem;background:var(--n-50);border-bottom:1px solid var(--card-border);flex-wrap:wrap;gap:.5rem;">
                                    <div>
                                        <span class="mono fw-700"
                                            style="font-size:.82rem;">{{ $qt->quotation_number }}</span>
                                        <span class="text-muted" style="font-size:.72rem;margin-left:.5rem;">
                                            {{ $qt->created_at->format('M d, Y') }} · by {{ $qt->creator->name ?? '—' }}
                                        </span>
                                    </div>
                                    <span class="vd-badge {{ $qt->status_badge_class }}" style="font-size:.65rem;">
                                        {{ $qt->status_label }}
                                    </span>
                                </div>
                                <div style="padding:.65rem 1rem;">
                                    @foreach ($qt->items->groupBy('item_type') as $type => $typeItems)
                                        <div
                                            style="font-size:.68rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.3rem;margin-top:.5rem;">
                                            {{ ucfirst($type) }}
                                        </div>
                                        @foreach ($typeItems as $item)
                                            <div
                                                style="display:flex;justify-content:space-between;font-size:.8rem;
                                    padding:.25rem 0;border-bottom:1px solid var(--n-100);">
                                                <span>{{ $item->description }}
                                                    @if ($item->brand)
                                                        <em style="color:var(--tx-muted);">({{ $item->brand }})</em>
                                                    @endif
                                                    × {{ $item->quantity }} {{ $item->unit }}
                                                </span>
                                                <span class="mono fw-600" style="padding-left:.5rem;white-space:nowrap;">
                                                    ₱{{ number_format($item->total_price, 2) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                                @if ($qt->discount_amount > 0 || $qt->tax_amount > 0)
                                    <div style="padding:.35rem 1rem;font-size:.78rem;border-top:1px solid var(--n-100);">
                                        @if ($qt->discount_amount > 0)
                                            <div style="display:flex;justify-content:space-between;">
                                                <span class="text-muted">Discount</span>
                                                <span class="mono text-red">—
                                                    ₱{{ number_format($qt->discount_amount, 2) }}</span>
                                            </div>
                                        @endif
                                        @if ($qt->tax_amount > 0)
                                            <div style="display:flex;justify-content:space-between;">
                                                <span class="text-muted">Tax / VAT</span>
                                                <span class="mono">+ ₱{{ number_format($qt->tax_amount, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <div
                                    style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;
                        padding:.65rem 1rem;border-top:2px solid var(--card-border);background:var(--n-50);">
                                    <span>Total</span>
                                    <span class="mono"
                                        style="color:var(--g-700);">₱{{ number_format($qt->total_amount, 2) }}</span>
                                </div>
                                @if ($qt->status === 'rejected' && $qt->customer_rejection_reason)
                                    <div
                                        style="padding:.6rem 1rem;font-size:.78rem;background:#fef2f2;color:#991b1b;border-top:1px solid #fecaca;">
                                        <i class="bi bi-x-circle me-1"></i>
                                        <strong>Rejection reason:</strong> {{ $qt->customer_rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Documents --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-folder2-open"></i> Documents</span>
                </div>
                <div class="vd-card__body">
                    @php $custDocs = $sp->documents->where('uploaded_by_role','customer'); @endphp
                    @if ($custDocs->isNotEmpty())
                        <div
                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.6rem;">
                            From Customer</div>
                        <div class="doc-grid mb-3">
                            @foreach ($custDocs as $doc)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-tile">
                                    <div class="doc-tile__img">
                                        @if ($doc->isImage())
                                            <img src="{{ Storage::url($doc->file_path) }}" alt="{{ $doc->file_name }}">
                                        @else
                                            <i class="bi bi-file-earmark-pdf" style="color:#ef4444;"></i>
                                        @endif
                                    </div>
                                    <div class="doc-tile__label">{{ $doc->document_type_label }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @php $vendorDocs = $sp->documents->where('uploaded_by_role','vendor'); @endphp
                    @if ($vendorDocs->isNotEmpty())
                        <div
                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.6rem;">
                            Your Uploads</div>
                        <div class="doc-grid mb-3">
                            @foreach ($vendorDocs as $doc)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-tile">
                                    <div class="doc-tile__img">
                                        @if ($doc->isImage())
                                            <img src="{{ Storage::url($doc->file_path) }}" alt="{{ $doc->file_name }}">
                                        @else
                                            <i class="bi bi-file-earmark-pdf" style="color:#ef4444;"></i>
                                        @endif
                                    </div>
                                    <div class="doc-tile__label">{{ $doc->document_type_label }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($sp->isActive())
                        <form method="POST" action="{{ route('vendor.solar.proof', $sp) }}"
                            enctype="multipart/form-data"
                            style="background:var(--n-50);border:1px solid var(--card-border);border-radius:var(--r-md);padding:1rem;">
                            @csrf
                            <div class="fw-700" style="font-size:.82rem;margin-bottom:.75rem;"><i
                                    class="bi bi-upload me-1"></i> Upload Document</div>
                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <label class="vd-label" style="font-size:.72rem;">Type</label>
                                    <select name="document_type" class="vd-select">
                                        @foreach (['site_survey_report' => '📊 Site Survey Report', 'system_design' => '📐 System Design', 'contract' => '📝 Contract', 'permit' => '📄 Permit', 'completion_photo' => '✅ Completion Photo', 'commissioning_report' => '⚡ Commissioning Report', 'net_metering_certificate' => '🔌 Net Metering Certificate', 'other' => '📎 Other'] as $val => $lbl)
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="vd-label" style="font-size:.72rem;">Advance status to (optional)</label>
                                    <select name="advance_status" class="vd-select">
                                        <option value="">— No change —</option>
                                        @foreach (\App\Models\SolarProject::ALLOWED_TRANSITIONS[$sp->status] ?? [] as $t)
                                            @if ($t !== 'cancelled')
                                                @php $g = $sp->canTransitionTo($t); @endphp
                                                <option value="{{ $t }}" {{ $g !== true ? 'disabled' : '' }}>
                                                    {{ \App\Models\SolarProject::STATUS_LABELS[$t] }}
                                                    {{ $g !== true ? '(blocked)' : '' }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="vd-label" style="font-size:.72rem;">Files (up to 8)</label>
                                <input type="file" name="files[]" class="vd-input" multiple required
                                    accept="image/jpg,image/jpeg,image/png,image/webp,application/pdf"
                                    style="font-size:.78rem;padding:.4rem .65rem;">
                            </div>
                            <div class="mb-3">
                                <label class="vd-label" style="font-size:.72rem;">Description (optional)</label>
                                <input type="text" name="description" class="vd-input" placeholder="Brief note…"
                                    maxlength="300">
                            </div>
                            <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm"><i class="bi bi-upload"></i>
                                Upload</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Activity log --}}
            @if ($history->isNotEmpty())
                <div class="vd-card">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-clock-history"></i> Activity Log</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="act-log">
                            @foreach ($history as $entry)
                                <div class="act-item">
                                    <div class="act-dot"></div>
                                    <div class="act-time">
                                        {{ \Carbon\Carbon::parse($entry['timestamp'])->format('M d, Y · g:i A') }}
                                        · <strong>{{ $entry['user_name'] }}</strong>
                                    </div>
                                    <div class="act-label">{{ $entry['label'] ?? $entry['status'] }}</div>
                                    @if (!empty($entry['notes']))
                                        <div class="act-note">{{ $entry['notes'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Right column ── --}}
        <div class="col-12 col-xl-4">

            {{-- Requirements checklist --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-card-checklist"></i> Requirements</span>
                </div>
                <div class="vd-card__body">
                    @php
                        $checks = [
                            [$hasEngineer, 'bi-person-check-fill', 'bi-person-x-fill', 'Engineer assigned'],
                            [$hasSurveyDate, 'bi-calendar-check-fill', 'bi-calendar-x-fill', 'Site survey scheduled'],
                            [$hasContract, 'bi-file-earmark-check', 'bi-file-earmark-x', 'Contract created & approved'],
                            [$hasInstallDate, 'bi-calendar-check-fill', 'bi-calendar-x-fill', 'Installation scheduled'],
                        ];
                    @endphp
                    @foreach ($checks as [$met, $metIcon, $unmetIcon, $label])
                        <div class="req-check {{ $met ? 'met' : 'unmet' }}">
                            <i class="bi {{ $met ? $metIcon : $unmetIcon }}"></i>
                            <span>{{ $label }}</span>
                            @if (!$met)
                                <span
                                    style="margin-left:auto;font-size:.65rem;background:#fef3c7;color:#92400e;
                                padding:.1rem .4rem;border-radius:var(--r-full);font-weight:700;">Required</span>
                            @else
                                <i class="bi bi-check2 ms-auto" style="color:var(--g-500);font-size:.8rem;"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Assign engineer --}}
            <div class="vd-card gap-section {{ !$hasEngineer ? 'ring-warning' : '' }}"
                style="{{ !$hasEngineer ? 'border-color:#f59e0b;' : '' }}">
                <div class="vd-card__header">
                    <span class="vd-card__title">
                        <i class="bi bi-person-gear {{ !$hasEngineer ? 'text-warning' : '' }}"></i>
                        Assigned Engineer
                        @if (!$hasEngineer)
                            <span
                                style="font-size:.65rem;background:#fef3c7;color:#92400e;
                            padding:.1rem .4rem;border-radius:var(--r-full);font-weight:700;margin-left:.35rem;">
                                Required
                            </span>
                        @endif
                    </span>
                </div>
                <div class="vd-card__body">
                    @if ($sp->engineer)
                        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;">
                            <div
                                style="width:36px;height:36px;border-radius:50%;background:var(--g-100);
                            color:var(--g-700);display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:.8rem;flex-shrink:0;">
                                {{ strtoupper(substr($sp->engineer->user->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:.875rem;">{{ $sp->engineer->user->name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $sp->engineer->position ?? 'Engineer / Technician' }}</div>
                            </div>
                        </div>
                    @else
                        <div
                            style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--r-sm);
                        padding:.5rem .75rem;font-size:.78rem;color:#92400e;margin-bottom:.75rem;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Assign an engineer before scheduling or advancing the project.
                        </div>
                    @endif

                    @if ($sp->isActive())
                        <form method="POST" action="{{ route('vendor.solar.assign', $sp) }}">
                            @csrf @method('PATCH')
                            <div style="display:flex;gap:.5rem;">
                                <select name="assigned_engineer_id" class="vd-select" style="flex:1;font-size:.82rem;">
                                    <option value="">— Select engineer —</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ $sp->assigned_engineer_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm">
                                    <i class="bi bi-person-check"></i>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Contract link --}}
            @if ($activeContract)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-file-earmark-text"></i> Contract</span>
                    </div>
                    <div class="vd-card__body">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                            <div>
                                <div class="mono fw-700" style="font-size:.82rem;color:var(--g-700);">
                                    {{ $activeContract->contract_number }}
                                </div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $activeContract->payment_mode_label }}
                                    · ₱{{ number_format($activeContract->contract_amount, 2) }}
                                </div>
                            </div>
                            <span class="vd-badge {{ $activeContract->status_badge_class }}" style="font-size:.65rem;">
                                {{ $activeContract->status_label }}
                            </span>
                        </div>

                        {{-- Payment progress --}}
                        <div
                            style="height:5px;background:var(--card-border);border-radius:3px;overflow:hidden;margin-bottom:.35rem;">
                            <div
                                style="height:100%;border-radius:3px;background:var(--g-500);
                        width:{{ $activeContract->payment_progress_percent }}%;">
                            </div>
                        </div>
                        <div style="font-size:.7rem;color:var(--tx-muted);margin-bottom:.75rem;">
                            ₱{{ number_format($activeContract->total_paid, 2) }} paid
                            of ₱{{ number_format($activeContract->contract_amount, 2) }}
                            ({{ $activeContract->payment_progress_percent }}%)
                        </div>

                        <a href="{{ route('vendor.solar.contract.show', [$sp, $activeContract]) }}"
                            class="vd-btn vd-btn--primary vd-btn--sm vd-btn--full">
                            <i class="bi bi-file-earmark-text"></i> Manage Contract & Payments
                        </a>
                    </div>
                </div>
            @endif

            {{-- Schedule --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-calendar-event"></i> Schedule</span>
                </div>
                <div class="vd-card__body">
                    @if ($sp->survey_date || $sp->installation_date)
                        <div
                            style="display:flex;flex-direction:column;gap:.65rem;margin-bottom:1rem;
                        background:var(--n-50);border:1px solid var(--card-border);
                        border-radius:var(--r-sm);padding:.75rem;">
                            @if ($sp->survey_date)
                                <div>
                                    <div
                                        style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                        Site Survey</div>
                                    <div class="fw-600" style="font-size:.82rem;">
                                        {{ $sp->survey_date->format('M d, Y') }}
                                        @if ($sp->survey_time_slot)
                                            · {{ $sp->survey_time_slot }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($sp->installation_date)
                                <div>
                                    <div
                                        style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);">
                                        Installation</div>
                                    <div class="fw-600" style="font-size:.82rem;">
                                        {{ $sp->installation_date->format('M d, Y') }}
                                        @if ($sp->installation_time_slot)
                                            · {{ $sp->installation_time_slot }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($sp->isActive())
                        <form method="POST" action="{{ route('vendor.solar.schedule', $sp) }}">
                            @csrf @method('PATCH')
                            <div class="mb-2">
                                <label class="vd-label" style="font-size:.72rem;">Schedule type</label>
                                <select name="schedule_type" class="vd-select">
                                    <option value="survey">Site Survey</option>
                                    <option value="installation">Installation</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="vd-label" style="font-size:.72rem;">Date</label>
                                <input type="date" name="date" class="vd-input"
                                    min="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="vd-label" style="font-size:.72rem;">Time slot</label>
                                <select name="time_slot" class="vd-select">
                                    <option value="">— Select —</option>
                                    @foreach (['8:00 AM – 10:00 AM', '10:00 AM – 12:00 PM', '1:00 PM – 3:00 PM', '3:00 PM – 5:00 PM', 'Full Day'] as $slot)
                                        <option value="{{ $slot }}">{{ $slot }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm vd-btn--full">
                                <i class="bi bi-calendar-check"></i> Save Schedule
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Add note --}}
            @if ($sp->isActive())
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-journal-text"></i> Add Note</span>
                    </div>
                    <div class="vd-card__body">
                        <form method="POST" action="{{ route('vendor.solar.note', $sp) }}">
                            @csrf
                            <textarea name="note" rows="3" class="vd-input mb-2" placeholder="Internal note or update…"
                                style="font-size:.82rem;resize:vertical;" required></textarea>
                            <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm">
                                <i class="bi bi-save"></i> Save Note
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Permit & utility --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-card-checklist"></i> Permit & Utility</span>
                </div>
                <div class="vd-card__body">
                    <div style="display:flex;flex-direction:column;gap:.65rem;font-size:.82rem;">
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.15rem;">
                                Permit #</div>
                            <div class="fw-600">{{ $sp->permit_number ?? '—' }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.15rem;">
                                Utility App #</div>
                            <div class="fw-600">{{ $sp->utility_application_number ?? '—' }}</div>
                        </div>
                        @if ($sp->net_metering_approval_date)
                            <div>
                                <div
                                    style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.15rem;">
                                    Net Metering Approved</div>
                                <div class="fw-600">{{ $sp->net_metering_approval_date->format('M d, Y') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Status transition modal ── --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.solar.status', $sp) }}" id="statusForm">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" id="statusInput">
                    <div class="modal-header">
                        <h6 class="modal-title fw-700" id="statusModalTitle">Update Status</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Block reason shown here if guard fails on client --}}
                        <div id="blockAlert"
                            style="display:none;background:#fef3c7;border:1px solid #fde68a;
                        border-radius:var(--r-sm);padding:.65rem .85rem;font-size:.82rem;color:#92400e;margin-bottom:.85rem;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <span id="blockReason"></span>
                        </div>
                        <div id="statusModalDesc" class="text-muted mb-3" style="font-size:.82rem;"></div>
                        <label class="vd-label">Notes (optional)</label>
                        <textarea name="notes" rows="3" class="vd-input" placeholder="Add a note about this status change…"></textarea>
                        <div id="permitFields" style="display:none;margin-top:.75rem;">
                            <label class="vd-label" style="font-size:.72rem;">Permit Number</label>
                            <input type="text" name="permit_number" class="vd-input mb-2"
                                value="{{ $sp->permit_number }}" placeholder="e.g. BP-2024-001">
                            <label class="vd-label" style="font-size:.72rem;">Utility Application #</label>
                            <input type="text" name="utility_application_number" class="vd-input"
                                value="{{ $sp->utility_application_number }}" placeholder="e.g. APP-2024-001">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary" id="statusSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const STATUS_LABELS = @json(\App\Models\SolarProject::STATUS_LABELS);
        const TRANS_GUARDS = @json($transitionGuards);

        function openStatusModal(newStatus) {
            const guard = TRANS_GUARDS[newStatus] ?? {
                allowed: true,
                reason: null
            };
            const isCancel = newStatus === 'cancelled';

            document.getElementById('statusInput').value = newStatus;
            document.getElementById('statusModalTitle').textContent =
                isCancel ? 'Cancel Project' : 'Move to: ' + (STATUS_LABELS[newStatus] ?? newStatus);

            // Show block reason if any
            const blockAlert = document.getElementById('blockAlert');
            const blockReason = document.getElementById('blockReason');
            if (!guard.allowed && guard.reason) {
                blockAlert.style.display = 'block';
                blockReason.textContent = guard.reason;
                document.getElementById('statusSubmitBtn').disabled = true;
            } else {
                blockAlert.style.display = 'none';
                document.getElementById('statusSubmitBtn').disabled = false;
            }

            document.getElementById('statusModalDesc').textContent = isCancel ?
                'This will cancel the project. This cannot be undone after work has begun.' :
                'Confirm moving this project to "' + (STATUS_LABELS[newStatus] ?? newStatus) + '".';

            document.getElementById('permitFields').style.display =
                newStatus === 'permit_processing' ? 'block' : 'none';

            const btn = document.getElementById('statusSubmitBtn');
            btn.className = isCancel ? 'vd-btn vd-btn--danger' : 'vd-btn vd-btn--primary';

            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
    </script>
@endpush
