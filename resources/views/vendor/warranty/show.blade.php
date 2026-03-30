{{-- ================================================================
     resources/views/vendor/warranty/show.blade.php
================================================================ --}}
@extends('layouts.vendor')
@section('title', 'Warranty Claim · ' . $warrantyRequest->request_number)
@section('page-title', 'Warranty Claim')

@section('breadcrumb')
    <a href="{{ route('vendor.warranty.index') }}" class="text-secondary">Warranty Claims</a>
    <span class="sep">›</span>
    <span class="current">{{ $warrantyRequest->request_number }}</span>
@endsection

@push('styles')
    <style>
        /* ── Pipeline stepper ─────────────────────────────── */
        .pipeline {
            display: flex;
            align-items: flex-start;
            gap: 0;
            overflow-x: auto;
            padding-bottom: .25rem;
            scrollbar-width: none;
        }

        .pipeline::-webkit-scrollbar {
            display: none;
        }

        .pipeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            min-width: 80px;
            position: relative;
        }

        .pipeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            left: calc(50% + 14px);
            right: calc(-50% + 14px);
            height: 2px;
            background: var(--card-border);
            z-index: 0;
        }

        .pipeline-step.done:not(:last-child)::after {
            background: var(--g-400);
        }

        .pipeline-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--card-border);
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            color: var(--tx-muted);
            z-index: 1;
            position: relative;
            transition: all .2s;
        }

        .pipeline-step.done .pipeline-dot {
            background: var(--g-500);
            border-color: var(--g-500);
            color: #fff;
        }

        .pipeline-step.current .pipeline-dot {
            background: var(--card-bg);
            border-color: var(--g-500);
            border-width: 2.5px;
            color: var(--g-600);
            box-shadow: 0 0 0 4px var(--g-50);
        }

        .pipeline-step.rejected .pipeline-dot {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }

        .pipeline-label {
            font-size: .65rem;
            font-weight: 600;
            color: var(--tx-muted);
            margin-top: .4rem;
            text-align: center;
            white-space: nowrap;
        }

        .pipeline-step.done .pipeline-label,
        .pipeline-step.current .pipeline-label {
            color: var(--g-700);
        }

        /* ── Detail grid ──────────────────────────────────── */
        .detail-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 600px) {
            .detail-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .detail-item__label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tx-muted);
            margin-bottom: .2rem;
        }

        .detail-item__value {
            font-size: .875rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        /* ── Image gallery ────────────────────────────────── */
        .img-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .img-gallery a {
            display: block;
            width: 72px;
            height: 72px;
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1.5px solid var(--card-border);
            transition: border-color .15s;
        }

        .img-gallery a:hover {
            border-color: var(--g-400);
        }

        .img-gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── History timeline ─────────────────────────────── */
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: .45rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--card-border);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -1.27rem;
            top: .25rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--g-500);
            border: 2px solid var(--card-bg);
        }

        .timeline-time {
            font-size: .7rem;
            color: var(--tx-muted);
            margin-bottom: .1rem;
        }

        .timeline-action {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        .timeline-notes {
            font-size: .78rem;
            color: var(--tx-secondary);
            margin-top: .1rem;
        }

        /* ── Transition action buttons ────────────────────── */
        .transition-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1rem;
            border-radius: var(--r-sm);
            font-size: .82rem;
            font-weight: 600;
            border: 1.5px solid var(--card-border);
            background: var(--card-bg);
            color: var(--tx-secondary);
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
        }

        .transition-btn:hover {
            border-color: var(--g-400);
            color: var(--g-700);
            background: var(--g-50);
        }

        .transition-btn--approve {
            background: var(--g-600);
            border-color: var(--g-600);
            color: #fff;
            box-shadow: 0 2px 8px rgba(21, 122, 48, .25);
        }

        .transition-btn--approve:hover {
            background: var(--g-700);
            border-color: var(--g-700);
            color: #fff;
        }

        .transition-btn--reject {
            border-color: #fca5a5;
            color: #dc2626;
            background: #fef2f2;
        }

        .transition-btn--reject:hover {
            background: #fee2e2;
            border-color: #ef4444;
        }

        /* ── Assign form ──────────────────────────────────── */
        .assign-form {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
        }
    </style>
@endpush

@section('content')

    @php
        $wr = $warrantyRequest;
        $pipeline = \App\Models\WarrantyRequest::STATUS_PIPELINE;
        $curIdx = array_search($wr->status, $pipeline);
        $isRejected = $wr->status === 'rejected';
        $isClosed = $wr->status === 'closed';
    @endphp

    {{-- ── Top: claim header ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">

                {{-- Left: number + meta --}}
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="mono fw-700" style="font-size:1.1rem;color:var(--g-700);">
                            {{ $wr->request_number }}
                        </span>
                        <span class="vd-badge {{ $wr->status_badge_class }}" style="font-size:.68rem;">
                            {{ $wr->status_label }}
                        </span>
                        <span
                            style="font-size:.7rem;font-weight:700;padding:.1rem .45rem;border-radius:var(--r-full);
                        background:{{ $wr->is_within_warranty ? 'var(--g-50)' : '#fef2f2' }};
                        color:{{ $wr->is_within_warranty ? 'var(--g-700)' : '#dc2626' }};
                        border:1px solid {{ $wr->is_within_warranty ? 'var(--g-200)' : '#fecaca' }};">
                            {{ $wr->is_within_warranty ? '✅ Valid Warranty' : '❌ Warranty Expired' }}
                        </span>
                    </div>
                    <div class="text-muted" style="font-size:.78rem;">
                        {{ $wr->request_type_label }}
                        &nbsp;·&nbsp; Filed {{ $wr->created_at->format('M d, Y · g:i A') }}
                        @if ($wr->warranty_expires_at)
                            &nbsp;·&nbsp; Warranty expires
                            <strong>{{ $wr->warranty_expires_at->format('M d, Y') }}</strong>
                        @endif
                    </div>
                </div>

                {{-- Right: actions --}}
                <div class="d-flex gap-2 flex-wrap">
                    @foreach ($allowedTransitions as $transition)
                        @if ($transition === 'rejected')
                            <button class="transition-btn transition-btn--reject" onclick="openRejectModal()">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        @elseif ($transition === 'closed')
                            <form method="POST" action="{{ route('vendor.warranty.updateStatus', $wr) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="transition-btn">
                                    <i class="bi bi-archive"></i> Close Claim
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vendor.warranty.updateStatus', $wr) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $transition }}">
                                <button type="submit" class="transition-btn transition-btn--approve">
                                    <i class="bi bi-arrow-right-circle"></i>
                                    Move to {{ \App\Models\WarrantyRequest::STATUS_LABELS[$transition] }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Pipeline stepper ── --}}
    @if (!$isRejected && !$isClosed)
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:1.25rem;">
                <div class="pipeline">
                    @foreach ($pipeline as $i => $step)
                        @php
                            $done = $curIdx !== false && $i < $curIdx;
                            $current = $curIdx !== false && $i === $curIdx;
                        @endphp
                        <div class="pipeline-step {{ $done ? 'done' : ($current ? 'current' : '') }}">
                            <div class="pipeline-dot">
                                @if ($done)
                                    <i class="bi bi-check2" style="font-size:.75rem;"></i>
                                @elseif ($current)
                                    <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div class="pipeline-label">
                                {{ \App\Models\WarrantyRequest::STATUS_LABELS[$step] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif ($isRejected)
        <div class="vd-card gap-section" style="border-left:3px solid #ef4444;">
            <div class="vd-card__body" style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1.25rem;">
                <i class="bi bi-x-circle-fill" style="color:#ef4444;font-size:1.2rem;"></i>
                <div>
                    <div class="fw-700" style="color:#dc2626;font-size:.875rem;">Claim Rejected</div>
                    @if ($wr->rejection_reason)
                        <div class="text-muted" style="font-size:.78rem;">{{ $wr->rejection_reason }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Left column ── --}}
        <div class="col-12 col-xl-8">

            {{-- Customer & Product info --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person"></i> Customer & Product</span>
                </div>
                <div class="vd-card__body">
                    <div class="detail-grid-2 mb-4">
                        <div>
                            <div class="detail-item__label">Customer</div>
                            <div class="detail-item__value">{{ $wr->customer->full_name }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $wr->customer->email }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $wr->customer->phone ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="detail-item__label">Product</div>
                            <div class="detail-item__value">{{ $wr->orderItem->product_name ?? '—' }}</div>
                            @if ($wr->serial_number)
                                <div class="mono text-muted" style="font-size:.78rem;">S/N: {{ $wr->serial_number }}</div>
                            @endif
                            @if ($wr->orderItem?->order)
                                <div class="text-muted" style="font-size:.72rem;">
                                    Order #{{ $wr->orderItem->order->order_number }}
                                    · {{ $wr->orderItem->order->created_at->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Issue description --}}
                    <div class="mb-3">
                        <div class="detail-item__label">Issue Description</div>
                        <div
                            style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;
                        background:var(--n-50);padding:.75rem 1rem;border-radius:var(--r-md);
                        border:1px solid var(--card-border);margin-top:.25rem;">
                            {{ $wr->issue_description }}
                        </div>
                    </div>

                    @if ($wr->customer_notes)
                        <div>
                            <div class="detail-item__label">Additional Notes from Customer</div>
                            <div style="font-size:.82rem;color:var(--tx-secondary);line-height:1.7;margin-top:.25rem;">
                                {{ $wr->customer_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Evidence images --}}
            @php
                $hasImages =
                    $wr->issue_images || $wr->product_images || $wr->proof_of_purchase_images || $wr->completion_images;
            @endphp
            @if ($hasImages)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-images"></i> Submitted Evidence</span>
                    </div>
                    <div class="vd-card__body">

                        @if ($wr->issue_images)
                            <div class="mb-3">
                                <div class="detail-item__label mb-2">Issue Photos</div>
                                <div class="img-gallery">
                                    @foreach ($wr->issue_images as $img)
                                        <a href="{{ Storage::url($img) }}" target="_blank">
                                            <img src="{{ Storage::url($img) }}" alt="Issue photo">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($wr->product_images)
                            <div class="mb-3">
                                <div class="detail-item__label mb-2">Product Photos</div>
                                <div class="img-gallery">
                                    @foreach ($wr->product_images as $img)
                                        <a href="{{ Storage::url($img) }}" target="_blank">
                                            <img src="{{ Storage::url($img) }}" alt="Product photo">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($wr->proof_of_purchase_images)
                            <div class="mb-3">
                                <div class="detail-item__label mb-2">Proof of Purchase</div>
                                <div class="img-gallery">
                                    @foreach ($wr->proof_of_purchase_images as $img)
                                        <a href="{{ Storage::url($img) }}" target="_blank">
                                            <img src="{{ Storage::url($img) }}" alt="Receipt">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($wr->completion_images)
                            <div>
                                <div class="detail-item__label mb-2">Completion Photos</div>
                                <div class="img-gallery">
                                    @foreach ($wr->completion_images as $img)
                                        <a href="{{ Storage::url($img) }}" target="_blank">
                                            <img src="{{ Storage::url($img) }}" alt="Completion photo">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            {{-- Resolution (if completed/rejected) --}}
            @if ($wr->resolution_type || $wr->resolution_notes)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-patch-check"></i> Resolution</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="detail-grid-2">
                            @if ($wr->resolution_type)
                                <div>
                                    <div class="detail-item__label">Resolution Type</div>
                                    <div class="detail-item__value">
                                        {{ ucfirst(str_replace('_', ' ', $wr->resolution_type)) }}</div>
                                </div>
                            @endif
                            @if ($wr->completed_at)
                                <div>
                                    <div class="detail-item__label">Completed At</div>
                                    <div class="detail-item__value">{{ $wr->completed_at->format('M d, Y · g:i A') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($wr->resolution_notes)
                            <div class="mt-3">
                                <div class="detail-item__label">Resolution Notes</div>
                                <div
                                    style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;
                            background:var(--g-50);padding:.75rem 1rem;border-radius:var(--r-md);
                            border:1px solid var(--g-100);margin-top:.25rem;">
                                    {{ $wr->resolution_notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Upload completion proof (only when in_progress) --}}
            @if ($wr->status === 'in_progress')
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-upload"></i> Upload Completion Proof</span>
                        <span class="vd-card__subtitle">Required to mark claim as completed</span>
                    </div>
                    <div class="vd-card__body">
                        <form method="POST" action="{{ route('vendor.warranty.uploadProof', $wr) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="vd-label">Resolution Type <span style="color:#ef4444;">*</span></label>
                                <select name="resolution_type" class="vd-select" required>
                                    <option value="">— Select resolution —</option>
                                    @foreach (['repaired' => '🔧 Repaired', 'replaced' => '🔁 Replaced', 'refunded' => '💰 Refunded', 'no_fault_found' => '✅ No Fault Found'] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('resolution_type') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="vd-label">Resolution Notes <span style="color:#ef4444;">*</span></label>
                                <textarea name="resolution_notes" rows="3" class="vd-input" required
                                    placeholder="Describe what was done to resolve the issue…" style="font-size:.82rem;resize:vertical;">{{ old('resolution_notes') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="vd-label">Completion Photos <span style="color:#ef4444;">*</span></label>
                                <div class="text-muted mb-2" style="font-size:.75rem;">
                                    Upload 1–6 photos showing the completed repair/service.
                                </div>
                                <input type="file" name="completion_images[]" class="vd-input"
                                    accept="image/jpg,image/jpeg,image/png,image/webp" multiple required
                                    style="font-size:.82rem;padding:.45rem .75rem;">
                                <div class="text-muted mt-1" style="font-size:.72rem;">Max 5MB per photo · JPG, PNG, WebP
                                </div>
                            </div>

                            <button type="submit" class="vd-btn vd-btn--primary vd-btn--full">
                                <i class="bi bi-patch-check"></i> Submit & Mark as Completed
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Service history timeline --}}
            @if ($wr->service_history)
                <div class="vd-card">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-clock-history"></i> Activity Log</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="timeline">
                            @foreach (array_reverse($wr->service_history) as $entry)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-time">
                                        {{ \Carbon\Carbon::parse($entry['timestamp'])->format('M d, Y · g:i A') }}
                                        @if (!empty($entry['user_name']))
                                            &nbsp;·&nbsp; <strong>{{ $entry['user_name'] }}</strong>
                                        @endif
                                    </div>
                                    <div class="timeline-action">{{ $entry['action'] }}</div>
                                    @if (!empty($entry['notes']))
                                        <div class="timeline-notes">{{ $entry['notes'] }}</div>
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

            {{-- Assign technician --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person-gear"></i> Assigned Technician</span>
                </div>
                <div class="vd-card__body">
                    @if ($wr->technician)
                        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;">
                            <div
                                style="width:36px;height:36px;border-radius:50%;background:var(--g-100);
                            color:var(--g-700);display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:.8rem;flex-shrink:0;">
                                {{ strtoupper(substr($wr->technician->user->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:.875rem;">
                                    {{ $wr->technician->user->name ?? ($wr->technician_name ?? '—') }}
                                </div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $wr->technician->position ?? 'Technician' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted" style="font-size:.82rem;margin-bottom:.75rem;">
                            <i class="bi bi-person-dash me-1"></i> No technician assigned yet.
                        </div>
                    @endif

                    @if ($wr->isOpen())
                        <form method="POST" action="{{ route('vendor.warranty.updateStatus', $wr) }}"
                            class="assign-form">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="assign"> {{-- no status field --}}
                            <select name="assigned_technician" class="vd-select" style="flex:1;font-size:.82rem;">
                                <option value="">— Select technician —</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ $wr->assigned_technician == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm">
                                <i class="bi bi-person-check"></i> Assign
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Schedule --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-calendar-event"></i> Schedule</span>
                </div>
                <div class="vd-card__body">
                    <div class="detail-grid-2 mb-3">
                        <div>
                            <div class="detail-item__label">Service Date</div>
                            <div class="detail-item__value" style="font-size:.82rem;">
                               {{ $wr->service_date ? \Carbon\Carbon::parse($wr->service_date)->format('M d, Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Time Slot</div>
                            <div class="detail-item__value" style="font-size:.82rem;">
                                {{ $wr->service_time_slot ?? '—' }}
                            </div>
                        </div>
                    </div>

                    @if ($wr->isOpen())
                        <form method="POST" action="{{ route('vendor.warranty.updateStatus', $wr) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="schedule"> {{-- no status field --}}
                            <div class="mb-2">
                                <label class="vd-label" style="font-size:.72rem;">Service Date</label>
                                <input type="date" name="service_date" class="vd-input"
                                    value="{{ $wr->service_date ? \Carbon\Carbon::parse($wr->service_date)->format('Y-m-d') : '' }}"
                                    min="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="vd-label" style="font-size:.72rem;">Time Slot</label>
                                <select name="service_time_slot" class="vd-select">
                                    <option value="">— Select —</option>
                                    @foreach (['8:00 AM – 10:00 AM', '10:00 AM – 12:00 PM', '1:00 PM – 3:00 PM', '3:00 PM – 5:00 PM'] as $slot)
                                        <option value="{{ $slot }}"
                                            {{ $wr->service_time_slot === $slot ? 'selected' : '' }}>
                                            {{ $slot }}
                                        </option>
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

            {{-- Technician notes --}}
            @if ($wr->isOpen() || $wr->technician_notes)
                <div class="vd-card gap-section">
                    <div class="vd-card__header">
                        <span class="vd-card__title"><i class="bi bi-journal-text"></i> Technician Notes</span>
                    </div>
                    <div class="vd-card__body">
                        @if ($wr->technician_notes)
                            <div
                                style="font-size:.82rem;color:var(--tx-secondary);line-height:1.7;
                        background:var(--n-50);padding:.65rem .85rem;border-radius:var(--r-sm);
                        border:1px solid var(--card-border);margin-bottom:.75rem;">
                                {{ $wr->technician_notes }}
                            </div>
                        @endif

                        @if ($wr->isOpen())
                            <form method="POST" action="{{ route('vendor.warranty.addNote', $wr) }}">
                                @csrf
                                <textarea name="note" rows="3" class="vd-input" placeholder="Add technician notes or internal remarks…"
                                    style="font-size:.82rem;resize:vertical;"></textarea>
                                <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm mt-2">
                                    <i class="bi bi-save"></i> Save Notes
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Claim metadata --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-info-circle"></i> Claim Details</span>
                </div>
                <div class="vd-card__body">
                    <div style="display:flex;flex-direction:column;gap:.65rem;">
                        <div>
                            <div class="detail-item__label">Claim Number</div>
                            <div class="mono fw-700" style="font-size:.875rem;color:var(--g-700);">
                                {{ $wr->request_number }}
                            </div>
                        </div>
                        <div>
                            <div class="detail-item__label">Filed On</div>
                            <div class="detail-item__value" style="font-size:.82rem;">
                                {{ $wr->created_at->format('M d, Y · g:i A') }}
                            </div>
                        </div>
                        @if ($wr->approved_at)
                            <div>
                                <div class="detail-item__label">Approved On</div>
                                <div class="detail-item__value" style="font-size:.82rem;">
                                    {{ $wr->approved_at->format('M d, Y · g:i A') }}
                                </div>
                            </div>
                        @endif
                        @if ($wr->warranty_expires_at)
                            <div>
                                <div class="detail-item__label">Warranty Expiry</div>
                                <div class="detail-item__value"
                                    style="font-size:.82rem;
                            color:{{ $wr->is_within_warranty ? 'var(--g-700)' : '#dc2626' }};">
                                    {{ $wr->warranty_expires_at->format('M d, Y') }}
                                </div>
                            </div>
                        @endif
                        @if ($wr->purchase_receipt_path)
                            <div>
                                <div class="detail-item__label">Receipt</div>
                                <a href="{{ Storage::url($wr->purchase_receipt_path) }}" target="_blank"
                                    class="vd-btn vd-btn--ghost vd-btn--sm mt-1">
                                    <i class="bi bi-file-earmark-text"></i> View Receipt
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Reject modal ── --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.warranty.updateStatus', $wr) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Reject Warranty Claim</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="vd-label">Reason for rejection <span style="color:#ef4444;">*</span></label>
                        <textarea name="rejection_reason" rows="3" class="vd-input" required
                            placeholder="Explain why this warranty claim is being rejected…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--danger">
                            <i class="bi bi-x-circle"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openRejectModal() {
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
@endpush
