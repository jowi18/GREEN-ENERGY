@extends('layouts.vendor')

@section('title', 'Service ' . $serviceRequest->request_number)
@section('page-title', 'Service Request Detail')
@section('breadcrumb')
    <a href="{{ route('vendor.services.index') }}" class="text-secondary">Services</a>
    <span class="sep">›</span>
    <span class="current">{{ $serviceRequest->request_number }}</span>
@endsection

@push('styles')
    <style>
        .proof-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.65rem;
        }

        .proof-item {
            position: relative;
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1.5px solid var(--card-border);
            aspect-ratio: 1;
            cursor: pointer;
        }

        .proof-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s;
        }

        .proof-item:hover img {
            transform: scale(1.04);
        }

        .proof-item__del {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(239, 68, 68, 0.85);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.72rem;
        }

        .proof-item__cap {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 0.68rem;
            padding: 0.2rem 0.4rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .upload-zone {
            border: 2px dashed var(--card-border);
            border-radius: var(--r-md);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--g-400);
            background: var(--g-50);
        }

        .pipeline {
            display: flex;
            align-items: flex-start;
            gap: 0;
            margin-bottom: 0;
        }

        .pip-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .pip-step::after {
            content: '';
            position: absolute;
            top: 13px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: var(--n-200);
            z-index: 0;
        }

        .pip-step:last-child::after {
            display: none;
        }

        .pip-step.done::after {
            background: var(--g-400);
        }

        .pip-dot {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            margin: 0 auto 0.35rem;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            font-weight: 700;
            border: 2px solid var(--n-300);
            background: #fff;
            color: var(--tx-muted);
        }

        .pip-step.done .pip-dot {
            background: var(--g-400);
            border-color: var(--g-400);
            color: #fff;
        }

        .pip-step.current .pip-dot {
            background: #fff;
            border-color: var(--g-600);
            color: var(--g-700);
            box-shadow: 0 0 0 4px rgba(21, 122, 48, 0.14);
        }

        .pip-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--tx-muted);
        }

        .pip-step.done .pip-label {
            color: var(--tx-secondary);
        }

        .pip-step.current .pip-label {
            color: var(--g-700);
            font-weight: 700;
        }
    </style>
@endpush

@section('content')

    {{-- ── Header ── --}}
    <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="mono fw-700" style="font-size:1rem;">{{ $serviceRequest->request_number }}</span>
                @php
                    $statusColors = [
                        'submitted' => 'vd-badge--pending',
                        'reviewing' => 'vd-badge--info',
                        'quoted' => 'vd-badge--warning',
                        'quote_accepted' => 'vd-badge--active',
                        'quote_rejected' => 'vd-badge--danger',
                        'scheduled' => 'vd-badge--info',
                        'in_progress' => 'vd-badge--warning',
                        'completed' => 'vd-badge--active',
                        'rejected' => 'vd-badge--danger',
                        'cancelled' => 'vd-badge--neutral',
                    ];
                @endphp
                <span class="vd-badge {{ $statusColors[$serviceRequest->status] ?? 'vd-badge--neutral' }}" id="statusBadge">
                    {{ $serviceRequest->status_label }}
                </span>
            </div>
            <div class="text-muted mt-1" style="font-size:0.8rem;">
                Submitted {{ $serviceRequest->created_at->format('F d, Y · g:i A') }}
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex gap-2 flex-wrap" id="actionButtons">
            @foreach ($transitions as $t)
                @php
                    $btnMap = [
                        'reviewing' => ['vd-btn--primary', 'bi-eye', 'Start Review'],
                        'quoted' => ['vd-btn--secondary', 'bi-tag', 'Send Quote'],
                        'scheduled' => ['vd-btn--primary', 'bi-calendar-check', 'Schedule'],
                        'in_progress' => ['vd-btn--primary', 'bi-gear-wide-connected', 'Start Work'],
                        'completed' => ['vd-btn--primary', 'bi-patch-check', 'Mark Complete'],
                        'rejected' => ['vd-btn--danger', 'bi-x-circle', 'Reject'],
                        'cancelled' => ['vd-btn--danger', 'bi-x-circle', 'Cancel'],
                    ];
                    [$btnClass, $btnIcon, $btnLabel] = $btnMap[$t] ?? ['vd-btn--ghost', 'bi-arrow-right', ucfirst($t)];
                @endphp

                @if ($t === 'quoted')
                    <button class="vd-btn {{ $btnClass }}" data-bs-toggle="modal" data-bs-target="#quoteModal">
                        <i class="bi {{ $btnIcon }}"></i> {{ $btnLabel }}
                    </button>
                @elseif($t === 'scheduled')
                    <button class="vd-btn {{ $btnClass }}" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="bi {{ $btnIcon }}"></i> {{ $btnLabel }}
                    </button>
                @else
                    <button class="vd-btn {{ $btnClass }}" onclick="updateStatus('{{ $t }}')">
                        <i class="bi {{ $btnIcon }}"></i> {{ $btnLabel }}
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Pipeline ── --}}
    @php
        $pipeline = ['submitted', 'reviewing', 'scheduled', 'in_progress', 'completed'];
        $cancelled = in_array($serviceRequest->status, ['cancelled', 'rejected', 'quote_rejected']);
        $currentIdx = array_search($serviceRequest->status, $pipeline);
        if ($currentIdx === false && in_array($serviceRequest->status, ['quoted', 'quote_accepted'])) {
            $currentIdx = 1; // between reviewing and scheduled
        }
    @endphp

    @if (!$cancelled)
        <div class="vd-card gap-section">
            <div class="vd-card__body" style="padding:1rem 1.5rem;">
                <div class="pipeline">
                    @foreach ($pipeline as $i => $step)
                        @php
                            $done = $currentIdx !== false && $i < $currentIdx;
                            $cur = $currentIdx !== false && $i === $currentIdx;
                        @endphp
                        <div class="pip-step {{ $done ? 'done' : ($cur ? 'current' : '') }}">
                            <div class="pip-dot">
                                @if ($done)
                                    <i class="bi bi-check2"></i>@else{{ $i + 1 }}
                                @endif
                            </div>
                            <div class="pip-label">{{ ucfirst($step === 'in_progress' ? 'In Progress' : $step) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="vd-flash vd-flash--error gap-section">
            <i class="bi bi-x-circle-fill"></i>
            <span>This service request was {{ $serviceRequest->status_label }}.</span>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Left: Service details + Proof ── --}}
        <div class="col-12 col-xl-8">

            {{-- Service info --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-tools"></i> Service Details</span>
                    <span class="vd-badge vd-badge--neutral" style="font-size:0.7rem;">
                        {{ ucwords(str_replace('_', ' ', $serviceRequest->service_type)) }}
                    </span>
                </div>
                <div class="vd-card__body">
                    <h6 class="fw-700 outfit mb-2">{{ $serviceRequest->service_title }}</h6>
                    <p class="text-secondary" style="font-size:0.875rem;line-height:1.8;white-space:pre-line;">
                        {{ $serviceRequest->description }}
                    </p>

                    @if ($serviceRequest->product)
                        <div class="mt-3 p-3"
                            style="background:var(--g-50);border-radius:var(--r-sm);border:1px solid var(--g-100);">
                            <div style="font-size:0.78rem;color:var(--g-700);font-weight:700;margin-bottom:0.25rem;">
                                <i class="bi bi-box-seam me-1"></i>Related Product
                            </div>
                            <div class="fw-600" style="font-size:0.875rem;">{{ $serviceRequest->product->name }}</div>
                        </div>
                    @endif

                    @if ($serviceRequest->customer_notes)
                        <div class="mt-3 p-3"
                            style="background:var(--y-50,#fffbe8);border-radius:var(--r-sm);border:1px solid var(--y-100,#fef3c7);">
                            <div
                                style="font-size:0.75rem;font-weight:700;color:var(--y-700,#b45309);margin-bottom:0.25rem;">
                                <i class="bi bi-chat-left-text me-1"></i>Customer Notes
                            </div>
                            <p style="font-size:0.875rem;color:var(--tx-secondary);">{{ $serviceRequest->customer_notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quote info (if quoted) --}}
            @if ($serviceRequest->quoted_amount)
                <div class="vd-card gap-section" style="border-color:var(--y-400);">
                    <div class="vd-card__header" style="background:var(--y-50);">
                        <span class="vd-card__title" style="color:var(--y-700);">
                            <i class="bi bi-tag-fill"></i> Quote Sent
                        </span>
                        <span class="mono fw-700" style="color:var(--y-700);font-size:1rem;">
                            ₱{{ number_format($serviceRequest->quoted_amount, 2) }}
                        </span>
                    </div>
                    <div class="vd-card__body">
                        <p style="font-size:0.875rem;color:var(--tx-secondary);line-height:1.7;white-space:pre-line;">
                            {{ $serviceRequest->quote_details }}
                        </p>
                        @if ($serviceRequest->quote_valid_until)
                            <div class="text-muted mt-2" style="font-size:0.78rem;">
                                <i class="bi bi-clock me-1"></i>
                                Valid until {{ $serviceRequest->quote_valid_until->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Proof of completion --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-images"></i> Proof of Completion</span>
                    <span class="vd-card__subtitle">{{ $serviceRequest->proofs->count() }} image(s)</span>
                </div>
                <div class="vd-card__body">

                    {{-- Existing proofs --}}
                    @if ($serviceRequest->proofs->count())
                        <div class="proof-grid mb-3">
                            @foreach ($serviceRequest->proofs as $proof)
                                <div class="proof-item" id="proof-{{ $proof->id }}">
                                    <img src="{{ asset('storage/' . $proof->file_path) }}"
                                        alt="{{ $proof->caption ?? 'Proof' }}"
                                        onclick="openLightbox('{{ asset('storage/' . $proof->file_path) }}')">
                                    @if ($proof->caption)
                                        <div class="proof-item__cap">{{ $proof->caption }}</div>
                                    @endif
                                    @if ($serviceRequest->status !== 'completed')
                                        <button class="proof-item__del" onclick="deleteProof({{ $proof->id }})"
                                            title="Delete">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Upload zone --}}
                    @if (in_array($serviceRequest->status, ['in_progress', 'completed']))
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('proofInput').click()"
                            ondragover="event.preventDefault();this.classList.add('drag-over')"
                            ondragleave="this.classList.remove('drag-over')" ondrop="handleDrop(event)">
                            <i class="bi bi-cloud-upload"
                                style="font-size:2rem;color:var(--tx-muted);display:block;margin-bottom:0.5rem;"></i>
                            <div style="font-size:0.875rem;color:var(--tx-secondary);font-weight:600;">
                                Click or drag images here to upload proof
                            </div>
                            <div style="font-size:0.75rem;color:var(--tx-muted);margin-top:0.3rem;">
                                JPG, PNG, WebP — max 5MB each, up to 8 images
                            </div>
                        </div>
                        <input type="file" id="proofInput" accept="image/*" multiple style="display:none;"
                            onchange="previewAndUpload(this.files)">

                        <div class="row g-2 mt-2" id="uploadPreview"></div>

                        <div class="mt-3">
                            <label class="vd-label" style="font-size:0.8rem;">Caption (optional)</label>
                            <input type="text" id="proofCaption" class="vd-input"
                                placeholder="e.g. Panels installed and connected">
                        </div>

                        <button class="vd-btn vd-btn--primary mt-3 w-100" id="uploadBtn" onclick="uploadProofs()"
                            style="display:none;">
                            <i class="bi bi-upload"></i>
                            Upload & Mark Complete
                        </button>
                    @elseif(
                        $serviceRequest->status === 'scheduled' ||
                            in_array($serviceRequest->status, ['submitted', 'reviewing', 'quoted', 'quote_accepted']))
                        <p class="text-muted" style="font-size:0.82rem;text-align:center;padding:1rem 0;">
                            <i class="bi bi-info-circle me-1"></i>
                            Proof upload will be available once the service is started.
                        </p>
                    @endif

                </div>
            </div>

        </div>

        {{-- ── Right: Customer + Scheduling + Notes ── --}}
        <div class="col-12 col-xl-4">

            {{-- Customer --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-person"></i> Customer</span>
                </div>
                <div class="vd-card__body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="vd-user__avatar" style="width:40px;height:40px;font-size:0.85rem;flex-shrink:0;">
                            {{ strtoupper(substr($serviceRequest->customer->first_name, 0, 1) . substr($serviceRequest->customer->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-600">{{ $serviceRequest->customer->full_name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                {{ $serviceRequest->customer->user->email }}</div>
                        </div>
                    </div>
                    <div style="font-size:0.82rem;color:var(--tx-secondary);">
                        <i class="bi bi-telephone me-1"></i> {{ $serviceRequest->customer->phone ?? '—' }}
                    </div>
                </div>
            </div>

            {{-- Service location --}}
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-geo-alt"></i> Service Location</span>
                </div>
                <div class="vd-card__body">
                    <address style="font-style:normal;font-size:0.875rem;line-height:1.8;color:var(--tx-secondary);">
                        {{ $serviceRequest->service_address }}<br>
                        {{ $serviceRequest->service_city }}, {{ $serviceRequest->service_province }}
                        @if ($serviceRequest->service_postal)
                            {{ $serviceRequest->service_postal }}
                        @endif
                    </address>
                    @if ($serviceRequest->preferred_date)
                        <hr class="divider">
                        <div style="font-size:0.82rem;">
                            <div class="text-muted"
                                style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.3rem;">
                                Preferred Schedule</div>
                            <div class="fw-600">{{ $serviceRequest->preferred_date->format('F d, Y') }}</div>
                            @if ($serviceRequest->preferred_time)
                                <div class="text-muted">{{ ucfirst($serviceRequest->preferred_time) }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Scheduled info --}}
            @if ($serviceRequest->scheduled_date)
                <div class="vd-card gap-section" style="border-color:var(--g-300);">
                    <div class="vd-card__header" style="background:var(--g-50);">
                        <span class="vd-card__title text-green"><i class="bi bi-calendar-check"></i> Scheduled</span>
                    </div>
                    <div class="vd-card__body">
                        <div class="fw-700" style="font-size:0.95rem;color:var(--g-700);">
                            {{ $serviceRequest->scheduled_date->format('F d, Y') }}
                        </div>
                        @if ($serviceRequest->scheduled_time)
                            <div class="text-secondary" style="font-size:0.85rem;">
                                {{ ucfirst($serviceRequest->scheduled_time) }}
                            </div>
                        @endif
                        {{-- Assigned team ── --}}
                        @if ($serviceRequest->assignees->count())
                            <div class="mt-2">
                                <div
                                    style="font-size:0.72rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:0.06em;color:var(--tx-muted);margin-bottom:0.4rem;">
                                    Assigned Team
                                </div>
                                <div class="d-flex flex-wrap gap-1" id="assigneeChips">
                                    @foreach ($serviceRequest->assignees as $emp)
                                        <span
                                            style="display:inline-flex;align-items:center;gap:0.35rem;
                                     background:var(--g-50);border:1px solid var(--g-200);
                                     color:var(--g-800);border-radius:var(--r-full);
                                     padding:0.2rem 0.6rem;font-size:0.75rem;font-weight:700;">
                                            <i class="bi bi-person-badge" style="font-size:0.7rem;"></i>
                                            {{ $emp->full_name }}
                                            @if ($emp->pivot->role)
                                                <span style="font-weight:400;color:var(--g-600);">·
                                                    {{ $emp->pivot->role }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="mt-2" style="font-size:0.82rem;color:var(--tx-muted);">
                                <i class="bi bi-person-dash me-1"></i> No team assigned yet.
                                <button class="vd-btn vd-btn--ghost vd-btn--sm ms-2" data-bs-toggle="modal"
                                    data-bs-target="#assignModal">
                                    <i class="bi bi-person-plus"></i> Assign
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Vendor notes --}}
            <div class="vd-card">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-pencil-square"></i> Internal Notes</span>
                </div>
                <div class="vd-card__body">
                    <textarea id="vendorNotes" rows="3" class="vd-input" style="min-height:70px;"
                        placeholder="Internal notes (not visible to customer)…">{{ $serviceRequest->vendor_notes }}</textarea>
                    <button class="vd-btn vd-btn--ghost vd-btn--sm mt-2" onclick="saveNotes()">
                        <i class="bi bi-floppy"></i> Save Notes
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ════ Quote Modal ════ --}}
    <div class="modal fade" id="quoteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-700"><i class="bi bi-tag me-2 text-green"></i>Send Quote</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="vd-label">Quote amount (₱) <span class="req">*</span></label>
                        <input type="number" id="quoteAmount" class="vd-input" step="0.01" min="0"
                            placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="vd-label">Scope of work / details <span class="req">*</span></label>
                        <textarea id="quoteDetails" rows="4" class="vd-input"
                            placeholder="Describe what is included in the quote, materials, labor, timeline…"></textarea>
                    </div>
                    <div>
                        <label class="vd-label">Quote valid until</label>
                        <input type="date" id="quoteValidUntil" class="vd-input"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--primary" id="sendQuoteBtn" onclick="sendQuote()">
                        <i class="bi bi-send"></i> Send Quote
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ Schedule Modal ════ --}}
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-700">
                        <i class="bi bi-calendar-check me-2 text-green"></i>Schedule Service
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="vd-label">Scheduled date <span class="req">*</span></label>
                            <input type="date" id="schedDate" class="vd-input" min="{{ now()->format('Y-m-d') }}"
                                value="{{ $serviceRequest->preferred_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="vd-label">Time slot</label>
                            <select id="schedTime" class="vd-select">
                                <option value="">Select time slot…</option>
                                <option value="morning"
                                    {{ $serviceRequest->preferred_time === 'morning' ? 'selected' : '' }}>Morning (8 AM –
                                    12 PM)</option>
                                <option value="afternoon"
                                    {{ $serviceRequest->preferred_time === 'afternoon' ? 'selected' : '' }}>Afternoon (1 PM
                                    – 5 PM)</option>
                                <option value="full_day">Full Day</option>
                            </select>
                        </div>
                    </div>

                    {{-- Employee assignment ── --}}
                    <div class="mt-3">
                        <label class="vd-label" style="margin-bottom:0.65rem;">
                            Assign Team Members
                            <span class="text-muted fw-400" style="font-size:0.75rem;">(select all that will work on this
                                job)</span>
                        </label>

                        @if ($employees->count())
                            <div
                                style="border:1.5px solid var(--card-border);border-radius:var(--r-sm);
                                max-height:240px;overflow-y:auto;">
                                @foreach ($employees as $emp)
                                    <label
                                        style="display:flex;align-items:center;gap:0.75rem;
                                      padding:0.65rem 1rem;cursor:pointer;
                                      border-bottom:1px solid var(--n-100);
                                      transition:background 0.1s;"
                                        onmouseover="this.style.background='var(--g-50)'"
                                        onmouseout="this.style.background=''">
                                        <input type="checkbox" name="sched_employee[]" value="{{ $emp->id }}"
                                            class="sched-emp-check"
                                            {{ in_array($emp->id, $assignedIds) ? 'checked' : '' }}
                                            style="accent-color:var(--g-500);width:15px;height:15px;flex-shrink:0;">
                                        {{-- Avatar --}}
                                        <div
                                            style="width:32px;height:32px;border-radius:50%;
                                        background:var(--g-100);color:var(--g-700);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:0.72rem;font-weight:800;flex-shrink:0;">
                                            {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $emp->full_name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                @foreach ($emp->roles->take(2) as $role)
                                                    {{ $role->name }}{{ !$loop->last ? ' · ' : '' }}
                                                @endforeach
                                                @if ($emp->roles->isEmpty())
                                                    Employee
                                                @endif
                                            </div>
                                        </div>
                                        <span
                                            class="vd-badge {{ $emp->is_active ? 'vd-badge--active' : 'vd-badge--neutral' }}"
                                            style="font-size:0.63rem;flex-shrink:0;">
                                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div style="font-size:0.75rem;color:var(--tx-muted);margin-top:0.4rem;">
                                {{ $employees->count() }} employee{{ $employees->count() !== 1 ? 's' : '' }} on your team
                            </div>
                        @else
                            <div
                                style="padding:1rem;background:var(--n-50);border-radius:var(--r-sm);
                                font-size:0.82rem;color:var(--tx-muted);text-align:center;">
                                <i class="bi bi-people me-1"></i>
                                No employees registered yet.
                                <a href="{{ route('vendor.employees.create') }}" class="text-green">Add employees →</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--primary" id="scheduleBtn" onclick="scheduleService()">
                        <i class="bi bi-calendar-check"></i> Confirm Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ Manage Assignees Modal (post-schedule) ════ --}}
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-700">
                        <i class="bi bi-people me-2 text-green"></i>Manage Team Assignment
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($employees->count())
                        <div
                            style="border:1.5px solid var(--card-border);border-radius:var(--r-sm);
                            max-height:260px;overflow-y:auto;">
                            @foreach ($employees as $emp)
                                <label
                                    style="display:flex;align-items:center;gap:0.75rem;
                                  padding:0.65rem 1rem;cursor:pointer;
                                  border-bottom:1px solid var(--n-100);"
                                    onmouseover="this.style.background='var(--g-50)'"
                                    onmouseout="this.style.background=''">
                                    <input type="checkbox" name="assign_employee[]" value="{{ $emp->id }}"
                                        class="assign-emp-check" {{ in_array($emp->id, $assignedIds) ? 'checked' : '' }}
                                        style="accent-color:var(--g-500);width:15px;height:15px;flex-shrink:0;">
                                    <div
                                        style="width:32px;height:32px;border-radius:50%;
                                    background:var(--g-100);color:var(--g-700);
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:0.72rem;font-weight:800;flex-shrink:0;">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                    </div>
                                    <div style="flex:1;">
                                        <div class="fw-600" style="font-size:0.875rem;">{{ $emp->full_name }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            @foreach ($emp->roles->take(2) as $role)
                                                {{ $role->name }}{{ !$loop->last ? ' · ' : '' }}
                                            @endforeach
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center" style="padding:1rem;">No employees to assign.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="vd-btn vd-btn--primary" id="saveAssignBtn" onclick="saveAssignees()">
                        <i class="bi bi-check-lg"></i> Save Assignment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="lightbox"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);
     z-index:9999;align-items:center;justify-content:center;cursor:pointer;"
        onclick="closeLightbox()">
        <img id="lightboxImg" style="max-width:90vw;max-height:90vh;border-radius:var(--r-md);">
    </div>

@endsection

@push('scripts')
    <script>
        const SR_ID = {{ $serviceRequest->id }};

        /* ── Status update ─────────────────────────────── */
        function updateStatus(status) {
            const labels = {
                reviewing: 'Start Review',
                in_progress: 'Start Work',
                completed: 'Mark Complete',
                rejected: 'Reject Request',
                cancelled: 'Cancel'
            };

            Swal.fire({
                title: labels[status] ?? 'Update Status',
                input: 'textarea',
                inputPlaceholder: 'Optional vendor note…',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                confirmButtonColor: status === 'cancelled' || status === 'rejected' ? '#ef4444' : '#157a30',
            }).then(r => {
                if (!r.isConfirmed) return;
                doStatusUpdate({
                    status,
                    vendor_notes: r.value
                });
            });
        }

        function doStatusUpdate(payload) {
            $.ajax({
                url: '{{ route('vendor.services.status', $serviceRequest) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success(res) {
                    if (res.success) {
                        Swal.fire({
                                icon: 'success',
                                text: res.message,
                                toast: true,
                                position: 'top-end',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                },
                error(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong.'
                    });
                }
            });
        }

        /* ── Send quote ────────────────────────────────── */
        function sendQuote() {
            const amount = document.getElementById('quoteAmount').value;
            const details = document.getElementById('quoteDetails').value;
            const valid = document.getElementById('quoteValidUntil').value;

            if (!amount || !details) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please fill in amount and details.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            const btn = document.getElementById('sendQuoteBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';

            $.ajax({
                url: '{{ route('vendor.services.quote', $serviceRequest) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    quoted_amount: amount,
                    quote_details: details,
                    quote_valid_until: valid || null
                }),
                success(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('quoteModal')).hide();
                        Swal.fire({
                                icon: 'success',
                                text: res.message,
                                toast: true,
                                position: 'top-end',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-send"></i> Send Quote';
                    }
                }
            });
        }

        /* ── Schedule service ──────────────────────────── */
        function scheduleService() {
            const date = document.getElementById('schedDate').value;
            if (!date) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please select a date.',
                    toast: true,
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false
                });
                return;
            }

            const btn = document.getElementById('scheduleBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling…';

            // Collect checked employee IDs from schedule modal
            const empIds = [...document.querySelectorAll('.sched-emp-check:checked')].map(c => parseInt(c.value));

            doStatusUpdate({
                status: 'scheduled',
                scheduled_date: date,
                scheduled_time: document.getElementById('schedTime').value || null,
                employee_ids: empIds,
            });
        }

        /* ── Save assignees (standalone modal) ────────── */
        function saveAssignees() {
            const btn = document.getElementById('saveAssignBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';

            const empIds = [...document.querySelectorAll('.assign-emp-check:checked')].map(c => parseInt(c.value));

            $.ajax({
                url: '{{ route('vendor.services.assignees', $serviceRequest) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    employee_ids: empIds
                }),
                success(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                        // Rebuild chips
                        const chips = document.getElementById('assigneeChips');
                        if (chips) {
                            chips.innerHTML = res.assignees.map(e =>
                                `<span style="display:inline-flex;align-items:center;gap:0.35rem;
                                      background:var(--g-50);border:1px solid var(--g-200);
                                      color:var(--g-800);border-radius:var(--r-full);
                                      padding:0.2rem 0.6rem;font-size:0.75rem;font-weight:700;">
                            <i class="bi bi-person-badge" style="font-size:0.7rem;"></i>${e.full_name}
                         </span>`
                            ).join('');
                        }
                        Swal.fire({
                            icon: 'success',
                            text: 'Team updated.',
                            toast: true,
                            position: 'top-end',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Assignment';
                },
                error() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Assignment';
                }
            });
        }

        /* ── Proof upload ──────────────────────────────── */
        let pendingFiles = [];

        function previewAndUpload(files) {
            pendingFiles = [...files];
            const preview = document.getElementById('uploadPreview');
            preview.innerHTML = '';
            pendingFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.insertAdjacentHTML('beforeend',
                        `<div class="col-4 col-md-3">
                    <img src="${e.target.result}"
                         style="width:100%;aspect-ratio:1;object-fit:cover;
                                border-radius:var(--r-sm);border:1px solid var(--card-border);">
                </div>`);
                };
                reader.readAsDataURL(file);
            });
            document.getElementById('uploadBtn').style.display = pendingFiles.length ? '' : 'none';
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('uploadZone').classList.remove('drag-over');
            previewAndUpload(e.dataTransfer.files);
        }

        function uploadProofs() {
            if (!pendingFiles.length) return;

            const btn = document.getElementById('uploadBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading…';

            const fd = new FormData();
            pendingFiles.forEach(f => fd.append('proofs[]', f));
            fd.append('caption', document.getElementById('proofCaption').value);
            fd.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route('vendor.services.proof.upload', $serviceRequest) }}',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success(res) {
                    if (res.success) {
                        Swal.fire({
                                icon: 'success',
                                text: res.message,
                                toast: true,
                                position: 'top-end',
                                timer: 2000,
                                showConfirmButton: false
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload failed',
                            text: res.message
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-upload"></i> Upload & Mark Complete';
                    }
                },
                error(xhr) {
                    const msg = xhr.responseJSON?.message ?? 'Upload failed.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-upload"></i> Upload & Mark Complete';
                }
            });
        }

        function deleteProof(proofId) {
            Swal.fire({
                title: 'Delete proof image?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route('vendor.services.proof.delete', [$serviceRequest, '__ID__']) }}'.replace('__ID__',
                    proofId), {}, res => {
                    if (res.success) document.getElementById(`proof-${proofId}`)?.remove();
                });
            });
        }

        /* ── Save notes ────────────────────────────────── */
        function saveNotes() {
            $.ajax({
                url: '{{ route('vendor.services.status', $serviceRequest) }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    status: '{{ $serviceRequest->status }}',
                    vendor_notes: document.getElementById('vendorNotes').value
                }),
                success() {
                    Swal.fire({
                        icon: 'success',
                        text: 'Notes saved.',
                        toast: true,
                        position: 'top-end',
                        timer: 1600,
                        showConfirmButton: false
                    });
                }
            });
        }

        /* ── Lightbox ──────────────────────────────────── */
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightbox').style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
@endpush
