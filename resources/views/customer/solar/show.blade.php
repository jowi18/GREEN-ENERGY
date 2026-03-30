{{-- resources/views/customer/solar/show.blade.php --}}
@extends('layouts.customer')
@section('title', 'Project · ' . $project->project_number)

@push('styles')
    <style>
        /* ── Pipeline tracker ─────────────────────────────── */
        .tracker {
            position: relative;
            padding: 0;
            list-style: none;
            margin: 0;
        }

        .tracker::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: var(--card-border);
            z-index: 0;
        }

        .tracker-item {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            padding-bottom: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .tracker-item:last-child {
            padding-bottom: 0;
        }

        .tracker-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--card-border);
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
            transition: all .2s;
        }

        .tracker-item.done .tracker-dot {
            background: var(--cg-500);
            border-color: var(--cg-500);
            color: #fff;
        }

        .tracker-item.current .tracker-dot {
            background: var(--card-bg);
            border-color: var(--cg-500);
            border-width: 2.5px;
            color: var(--cg-600);
            box-shadow: 0 0 0 4px var(--cg-50);
        }

        .tracker-item.cancelled .tracker-dot {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }

        .tracker-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-muted);
            padding-top: .55rem;
        }

        .tracker-item.done .tracker-label {
            color: var(--cg-700);
        }

        .tracker-item.current .tracker-label {
            color: var(--tx-primary);
            font-weight: 700;
        }

        .tracker-timestamp {
            font-size: .7rem;
            color: var(--tx-muted);
            margin-top: .1rem;
        }

        /* ── Quotation card ───────────────────────────────── */
        .quote-card {
            border: 2px solid var(--cg-200);
            border-radius: var(--r-xl);
            overflow: hidden;
        }

        .quote-card__header {
            background: linear-gradient(135deg, var(--cg-700), var(--cg-500));
            color: #fff;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .quote-line-item {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: .5rem;
            padding: .55rem .85rem;
            border-bottom: 1px solid var(--n-100);
            font-size: .82rem;
            align-items: start;
        }

        .quote-line-item:last-child {
            border-bottom: none;
        }

        .quote-type-badge {
            font-size: .65rem;
            font-weight: 700;
            padding: .1rem .45rem;
            border-radius: var(--r-full);
            background: var(--n-100);
            color: var(--tx-muted);
        }

        /* ── Document grid ────────────────────────────────── */
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: .65rem;
        }

        .doc-item {
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s;
            display: block;
        }

        .doc-item:hover {
            border-color: var(--cg-300);
            color: inherit;
        }

        .doc-item__thumb {
            width: 100%;
            height: 72px;
            background: var(--n-50);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            overflow: hidden;
        }

        .doc-item__thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-item__label {
            padding: .4rem .5rem;
            font-size: .7rem;
            font-weight: 600;
            color: var(--tx-secondary);
            line-height: 1.3;
            border-top: 1px solid var(--n-100);
        }

        /* ── Info detail rows ─────────────────────────────── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: .845rem;
            padding: .45rem 0;
            border-bottom: 1px solid var(--n-100);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row__label {
            color: var(--tx-muted);
            font-size: .78rem;
        }

        .info-row__value {
            font-weight: 600;
            color: var(--tx-primary);
            text-align: right;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:800px;">

        @php $wr = $project; @endphp

        {{-- Back + header --}}
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
            <a href="{{ route('customer.solar.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;">
                    <span class="mono fw-700" style="font-size:1rem;color:var(--cg-700);">
                        {{ $project->project_number }}
                    </span>
                    <span class="cu-badge {{ $project->status_badge_class }}" style="font-size:.65rem;">
                        {{ $project->status_label }}
                    </span>
                </div>
                <div class="text-muted" style="font-size:.75rem;margin-top:.1rem;">
                    {{ $project->vendor->business_name }}
                    &nbsp;·&nbsp; Submitted {{ $project->created_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="cu-alert cu-alert--success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="cu-alert cu-alert--danger mb-3">
                <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- ── Quotation awaiting response (prominent banner) ── --}}
        @if ($activeQuotation && $project->status === 'quotation_generated')
            <div class="quote-card mb-3">
                <div class="quote-card__header">
                    <div>
                        <div class="fw-800" style="font-size:1rem;font-family:'Nunito',sans-serif;">
                            📋 Quotation Ready for Your Review
                        </div>
                        <div style="font-size:.78rem;opacity:.85;margin-top:.2rem;">
                            {{ $activeQuotation->quotation_number }}
                            @if ($activeQuotation->valid_until)
                                &nbsp;·&nbsp; Valid until {{ $activeQuotation->valid_until->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:1.4rem;font-weight:800;font-family:'Nunito',sans-serif;">
                            ₱{{ number_format($activeQuotation->total_amount, 2) }}
                        </div>
                        <div style="font-size:.72rem;opacity:.8;">Total amount</div>
                    </div>
                </div>

                {{-- Line items --}}
                <div style="background:var(--card-bg);">
                    @foreach ($activeQuotation->items->groupBy('item_type') as $type => $items)
                        <div
                            style="padding:.5rem .85rem .25rem;font-size:.72rem;font-weight:700;
                                text-transform:uppercase;letter-spacing:.06em;color:var(--tx-muted);
                                background:var(--n-50);border-bottom:1px solid var(--n-100);">
                            @php
                                $typeLabels = [
                                    'equipment' => '⚙️ Equipment',
                                    'labor' => '🔧 Labor & Installation',
                                    'permit' => '📄 Permits & Fees',
                                    'other' => '📦 Other',
                                ];
                            @endphp
                            {{ $typeLabels[$type] ?? ucfirst($type) }}
                        </div>
                        @foreach ($items as $item)
                            <div class="quote-line-item">
                                <div>
                                    <div class="fw-600">{{ $item->description }}</div>
                                    @if ($item->brand || $item->model)
                                        <div class="text-muted" style="font-size:.72rem;">
                                            {{ $item->brand }} {{ $item->model }}
                                        </div>
                                    @endif
                                    @if ($item->notes)
                                        <div class="text-muted" style="font-size:.7rem;font-style:italic;">
                                            {{ $item->notes }}</div>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:.78rem;white-space:nowrap;">
                                    {{ number_format($item->quantity, 0) }} {{ $item->unit }}
                                    × ₱{{ number_format($item->unit_price, 2) }}
                                </div>
                                <div class="mono fw-700"
                                    style="font-size:.82rem;color:var(--tx-primary);white-space:nowrap;">
                                    ₱{{ number_format($item->total_price, 2) }}
                                </div>
                            </div>
                        @endforeach
                    @endforeach

                    {{-- Totals --}}
                    <div style="padding:.75rem .85rem;background:var(--n-50);border-top:1.5px solid var(--card-border);">
                        @if ($activeQuotation->discount_amount > 0)
                            <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;">
                                <span class="text-muted">Subtotal</span>
                                <span class="mono">₱{{ number_format($activeQuotation->subtotal, 2) }}</span>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;color:#16a34a;">
                                <span>Discount</span>
                                <span class="mono">— ₱{{ number_format($activeQuotation->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;">
                            <span>Total</span>
                            <span class="mono" style="color:var(--cg-700);">
                                ₱{{ number_format($activeQuotation->total_amount, 2) }}
                            </span>
                        </div>
                    </div>

                    @if ($activeQuotation->notes)
                        <div
                            style="padding:.75rem .85rem;border-top:1px solid var(--n-100);
                                font-size:.78rem;color:var(--tx-secondary);line-height:1.6;">
                            <strong>Vendor note:</strong> {{ $activeQuotation->notes }}
                        </div>
                    @endif
                </div>

                {{-- Response buttons --}}
                <div
                    style="padding:1rem 1.25rem;background:var(--card-bg);
                        border-top:1.5px solid var(--cg-100);display:flex;gap:.75rem;flex-wrap:wrap;">

                    <form method="POST"
                        action="{{ route('customer.solar.quotation.respond', [$project, $activeQuotation]) }}"
                        id="approveQuotationForm" style="display:contents;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="response" value="approved">
                        <button type="button" class="cu-btn cu-btn--primary cu-btn--lg" onclick="confirmApprove()">
                            <i class="bi bi-check-circle-fill"></i> Approve Quotation
                        </button>
                    </form>

                    <button type="button" class="cu-btn cu-btn--ghost" onclick="openRejectModal()">
                        <i class="bi bi-x-circle"></i> Request Revision
                    </button>
                </div>
            </div>
        @endif

        @if($project->activeContract)
             @if ($activeQuotation || in_array($project->activeContract->status, ['sent', 'adjustment_requested']))
                <div class="quote-card mb-3">
                    <div class="quote-card__header">
                        <div>
                            <div class="fw-800" style="font-size:1rem;font-family:'Nunito',sans-serif;">
                                📋 Contract Ready for Your Review
                            </div>
                            <div style="font-size:.78rem;opacity:.85;margin-top:.2rem;">
                                {{ $project->activeContract->contract_number ?? '--' }}
                                @if ($activeQuotation->valid_until)
                                    &nbsp;·&nbsp; Created At {{ $project->activeContract ? date('M d, Y', strtotime($project->activeContract->created_at)) : '--'  }}
                                @endif
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:1.4rem;font-weight:800;font-family:'Nunito',sans-serif;">
                                ₱{{ number_format($project->activeContract->contract_amount ?? 0, 2) }}
                            </div>
                            <div style="font-size:.72rem;opacity:.8;">Total amount</div>
                        </div>
                    </div>
                    {{-- Response buttons --}}
                    <div
                        style="padding:1rem 1.25rem;background:var(--card-bg);
                            border-top:1.5px solid var(--cg-100);display:flex;gap:.75rem;flex-wrap:wrap;">
                        @if($project->activeContract)
                        <a href="{{ route('customer.solar.contract.show', [$project, $project->activeContract]) }}" class="cu-btn cu-btn--ghost">
                                <i class="bi bi-document"></i> Review Contract </a>
                        @endif


                    </div>
                </div>
            @endif
        @endif


        {{-- ── Already responded to quotation ── --}}
        @if ($activeQuotation && in_array($project->status, ['proposal_approved', 'proposal_rejected']))
            <div class="cu-card mb-3"
                style="border-color:{{ $project->status === 'proposal_approved' ? 'var(--cg-200)' : '#fecaca' }};">
                <div class="cu-card__body" style="padding:.85rem 1.1rem;display:flex;gap:.75rem;align-items:center;">
                    <i class="bi {{ $project->status === 'proposal_approved' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"
                        style="font-size:1.2rem;color:{{ $project->status === 'proposal_approved' ? 'var(--cg-500)' : '#ef4444' }};"></i>
                    <div>
                        <div class="fw-700" style="font-size:.875rem;">
                            {{ $project->status === 'proposal_approved' ? 'Quotation Approved' : 'Revision Requested' }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $activeQuotation->quotation_number }}
                            · ₱{{ number_format($activeQuotation->total_amount, 2) }}
                            @if ($activeQuotation->customer_responded_at)
                                · Responded {{ $activeQuotation->customer_responded_at->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($project->activeContract)
            @if ($activeQuotation || in_array($project->activeContract->status, ['approved']))
                <div class="cu-card mb-3"
                    style="border-color:{{ $project->activeContract->status === 'signed' ? 'var(--cg-200)' : '#fecaca' }};">
                    <div class="cu-card__body" style="padding:.85rem 1.1rem;display:flex;gap:.75rem;align-items:center;">
                        <i class="bi {{ $project->activeContract->status === 'signed' || $project->activeContract->status === 'approved'  ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"
                            style="font-size:1.2rem;color:{{ $project->activeContract->status === 'signed' || $project->activeContract->status === 'approved' ? 'var(--cg-500)' : '#ef4444' }};"></i>
                        <div>
                            <div class="fw-700" style="font-size:.875rem;">
                                {{ $project->activeContract->status === 'signed' || $project->activeContract->status === 'approved'  ? 'Contract Signed/Approved' : 'Need Adjustment' }}
                            </div>
                            {{-- <div class="text-muted" style="font-size:.75rem;">
                                {{ $activeQuotation->quotation_number }}
                                · ₱{{ number_format($activeQuotation->total_amount, 2) }}
                                @if ($activeQuotation->customer_responded_at)
                                    · Responded {{ $activeQuotation->customer_responded_at->format('M d, Y') }}
                                @endif
                            </div> --}}
                        </div>
                    </div>
                </div>
            @endif
        @endif



        <div class="row g-3">

            {{-- ── Left: timeline + documents ── --}}
            <div class="col-12 col-lg-7">

                {{-- Project timeline --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-diagram-3 text-green me-1"></i> Project Timeline
                        </div>

                        @php
                            $curIdx = array_search($project->status, $pipeline) ?: 0;
                            $historyMap = collect($project->status_history ?? [])->keyBy('status');
                        @endphp

                        <ul class="tracker">
                            @foreach ($pipeline as $i => $step)
                                @php
                                    $done = $i < $curIdx;
                                    $current = $project->status === $step;
                                    $entry = $historyMap[$step] ?? null;
                                @endphp
                                <li class="tracker-item {{ $done ? 'done' : ($current ? 'current' : '') }}">
                                    <div class="tracker-dot">
                                        @if ($done)
                                            <i class="bi bi-check2"></i>
                                        @elseif ($current)
                                            <i class="bi bi-circle-fill" style="font-size:.4rem;"></i>
                                        @else
                                            <span
                                                style="font-size:.65rem;font-weight:700;color:var(--tx-muted);">{{ $i + 1 }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="tracker-label">{{ $statusLabels[$step] }}</div>
                                        @if ($entry)
                                            <div class="tracker-timestamp">
                                                {{ \Carbon\Carbon::parse($entry['timestamp'])->format('M d, Y · g:i A') }}
                                                @if (!empty($entry['user_name']))
                                                    &nbsp;· {{ $entry['user_name'] }}
                                                @endif
                                            </div>
                                            @if (!empty($entry['notes']))
                                                <div
                                                    style="font-size:.75rem;color:var(--tx-secondary);
                                                margin-top:.2rem;line-height:1.5;">
                                                    {{ $entry['notes'] }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </li>
                            @endforeach

                            @if ($project->status === 'cancelled')
                                <li class="tracker-item cancelled">
                                    <div class="tracker-dot"><i class="bi bi-x"></i></div>
                                    <div>
                                        <div class="tracker-label">Project Cancelled</div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    @if ($project->status === 'warranty_support_active')

                        @php
                            $alreadyReviewed = \App\Models\Review::where('customer_id', auth()->user()->customer->id)
                                ->where('reviewable_id', $project->id)
                                ->exists();
                        @endphp
                        @if (!$alreadyReviewed)
                            <a href="{{ route('customer.solar.review.create', $project) }}"
                                class="cu-btn cu-btn--primary cu-btn--full mb-3">
                                <i class="bi bi-star me-1"></i> Rate this Service
                            </a>
                        @else
                            <div class="cu-card mb-3">
                                <div class="cu-card__body text-center" style="padding:1rem;">
                                    <i class="bi bi-patch-check-fill text-green me-1"></i>
                                    <span style="font-size:.875rem;font-weight:700;">You have reviewed this service. Thank you!</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Documents section --}}
                <div class="cu-card">
                    <div class="cu-card__body">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                            <div class="fw-800" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-folder2-open text-green me-1"></i> Documents
                            </div>
                            <button class="cu-btn cu-btn--ghost cu-btn--sm"
                                onclick="document.getElementById('uploadDocPanel').classList.toggle('d-none')">
                                <i class="bi bi-upload me-1"></i> Upload
                            </button>
                        </div>

                        {{-- Upload form --}}
                        <div id="uploadDocPanel" class="d-none mb-3">
                            <form method="POST" action="{{ route('customer.solar.documents.store', $project) }}"
                                enctype="multipart/form-data"
                                style="background:var(--n-50);border:1.5px solid var(--card-border);
                                     border-radius:var(--r-md);padding:.85rem;">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-5">
                                        <label class="cu-label" style="font-size:.72rem;">Document Type</label>
                                        <select name="document_type" class="cu-input" style="font-size:.82rem;" required>
                                            @foreach ([
                                                'site_photo' => '📷 Site Photo',
                                                'electric_bill' => '💡 Electric Bill',
                                                'government_id' => '🪪 Government ID',
                                                'lot_plan' => '🗺️ Lot Plan',
                                                'barangay_clearance' => '📋 Barangay Clearance',
                                                'other' => '📎 Other',
                                            ] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-7">
                                        <label class="cu-label" style="font-size:.72rem;">File</label>
                                        <input type="file" name="file" class="cu-input" required
                                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                                            style="padding:.35rem .6rem;font-size:.78rem;">
                                    </div>
                                </div>
                                <input type="text" name="description" class="cu-input mb-2"
                                    placeholder="Description (optional)" style="font-size:.8rem;">
                                <button type="submit" class="cu-btn cu-btn--primary cu-btn--sm">
                                    <i class="bi bi-cloud-upload"></i> Upload
                                </button>
                            </form>
                        </div>

                        {{-- Customer documents --}}
                        @php
                            $customerDocs = $project->documents->where('uploaded_by_role', 'customer');
                            $vendorDocs = $project->documents->where('uploaded_by_role', 'vendor');
                        @endphp

                        @if ($customerDocs->count())
                            <div
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.6rem;">
                                Your Documents
                            </div>
                            <div class="doc-grid mb-3">
                                @foreach ($customerDocs as $doc)
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-item">
                                        <div class="doc-item__thumb">
                                            @if ($doc->isImage())
                                                <img src="{{ Storage::url($doc->file_path) }}"
                                                    alt="{{ $doc->file_name }}">
                                            @else
                                                📄
                                            @endif
                                        </div>
                                        <div class="doc-item__label">{{ $doc->document_type_label }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Vendor documents --}}
                        @if ($vendorDocs->count())
                            <div
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--tx-muted);margin-bottom:.6rem;">
                                From Vendor
                            </div>
                            <div class="doc-grid">
                                @foreach ($vendorDocs as $doc)
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-item">
                                        <div class="doc-item__thumb">
                                            @if ($doc->isImage())
                                                <img src="{{ Storage::url($doc->file_path) }}"
                                                    alt="{{ $doc->file_name }}">
                                            @else
                                                📄
                                            @endif
                                        </div>
                                        <div class="doc-item__label">{{ $doc->document_type_label }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($project->documents->count() === 0)
                            <div class="text-center text-muted" style="padding:1.5rem;font-size:.82rem;">
                                <i class="bi bi-folder2"
                                    style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                                No documents yet. Upload your site photos and electric bill above.
                            </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- ── Right: project details ── --}}
            <div class="col-12 col-lg-5">

                {{-- Project details --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                            <i class="bi bi-info-circle text-green me-1"></i> Project Details
                        </div>

                        <div class="info-row">
                            <span class="info-row__label">Property Type</span>
                            <span class="info-row__value">
                                @php $propIcons = ['residential'=>'🏠','commercial'=>'🏢','industrial'=>'🏭','agricultural'=>'🌾']; @endphp
                                {{ $propIcons[$project->property_type] ?? '' }}
                                {{ ucfirst($project->property_type) }}
                            </span>
                        </div>
                        @if ($project->system_type)
                            <div class="info-row">
                                <span class="info-row__label">System Type</span>
                                <span class="info-row__value">
                                    @php $sysLabels = ['grid_tie'=>'🔌 Grid-Tie','off_grid'=>'🔋 Off-Grid','hybrid'=>'⚡ Hybrid']; @endphp
                                    {{ $sysLabels[$project->system_type] ?? $project->system_type }}
                                </span>
                            </div>
                        @endif
                        @if ($project->estimated_monthly_bill)
                            <div class="info-row">
                                <span class="info-row__label">Monthly Bill</span>
                                <span
                                    class="info-row__value mono">₱{{ number_format($project->estimated_monthly_bill, 2) }}</span>
                            </div>
                        @endif
                        @if ($project->desired_system_size_kw)
                            <div class="info-row">
                                <span class="info-row__label">Desired Size</span>
                                <span class="info-row__value mono">{{ $project->desired_system_size_kw }} kW</span>
                            </div>
                        @endif
                        @if ($project->roof_type)
                            <div class="info-row">
                                <span class="info-row__label">Roof Type</span>
                                <span
                                    class="info-row__value">{{ ucfirst(str_replace('_', ' ', $project->roof_type)) }}</span>
                            </div>
                        @endif
                        @if ($project->roof_area_sqm)
                            <div class="info-row">
                                <span class="info-row__label">Roof Area</span>
                                <span class="info-row__value mono">{{ $project->roof_area_sqm }} sqm</span>
                            </div>
                        @endif
                        <div class="info-row">
                            <span class="info-row__label">Location</span>
                            <span class="info-row__value" style="font-size:.78rem;">
                                {{ $project->installation_city }}, {{ $project->installation_province }}
                            </span>
                        </div>
                        @if ($project->installation_date)
                            <div class="info-row">
                                <span class="info-row__label">Install Date</span>
                                <span class="info-row__value">{{ $project->installation_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Assigned engineer --}}
                @if ($project->engineer)
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;font-size:.875rem;">
                                <i class="bi bi-person-badge text-green me-1"></i> Assigned Engineer
                            </div>
                            <div style="display:flex;align-items:center;gap:.65rem;">
                                <div
                                    style="width:38px;height:38px;border-radius:50%;background:var(--cg-100);
                                        color:var(--cg-700);display:flex;align-items:center;justify-content:center;
                                        font-weight:800;font-size:.8rem;flex-shrink:0;">
                                    {{ strtoupper(substr($project->engineer->user->name ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-700" style="font-size:.875rem;">
                                        {{ $project->engineer->user->name ?? '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $project->engineer->position ?? 'Engineer / Technician' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Survey schedule --}}
                @if ($project->survey_date)
                    <div class="cu-card mb-3" style="border-color:var(--cg-200);">
                        <div class="cu-card__body" style="padding:.85rem 1rem;">
                            <div class="fw-700 mb-1" style="font-size:.82rem;">
                                <i class="bi bi-calendar-event text-green me-1"></i> Site Survey Scheduled
                            </div>
                            <div style="font-size:.875rem;font-weight:700;color:var(--cg-700);">
                                {{ $project->survey_date->format('F d, Y') }}
                                @if ($project->survey_time_slot)
                                    &nbsp;·&nbsp; {{ $project->survey_time_slot }}
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:.72rem;margin-top:.2rem;">
                                Please ensure someone is present at the installation site.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Vendor --}}
                <div class="cu-card">
                    <div class="cu-card__body">
                        <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;font-size:.875rem;">
                            <i class="bi bi-shop text-green me-1"></i> Vendor
                        </div>
                        <div class="fw-700" style="font-size:.875rem;">{{ $project->vendor->business_name }}</div>
                        @if ($project->vendor->business_phone)
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-telephone me-1"></i>{{ $project->vendor->business_phone }}
                            </div>
                        @endif
                        @if ($project->vendor->business_email)
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-envelope me-1"></i>{{ $project->vendor->business_email }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Reject / revision modal ── --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                @if ($activeQuotation)
                    <form method="POST"
                        action="{{ route('customer.solar.quotation.respond', [$project, $activeQuotation]) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="response" value="rejected">
                        <div class="modal-header">
                            <h6 class="modal-title fw-700">Request Revision</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="cu-label">
                                What would you like revised? <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="rejection_reason" rows="3" class="cu-textarea" required
                                placeholder="e.g. Price is too high, please use a different brand of panels…"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="cu-btn cu-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="cu-btn cu-btn--danger">
                                <i class="bi bi-arrow-clockwise"></i> Request Revision
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openRejectModal() {
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function confirmApprove() {
            Swal.fire({
                title: 'Approve this Quotation?',
                html: `
            <div style="font-size:.9rem;color:#374151;line-height:1.7;">
                <div style="margin-bottom:.5rem;">
                    <strong>Total Amount:</strong> ₱{{ number_format($activeQuotation->total_amount ?? 0, 2)  }}
                </div>
                <div style="font-size:.82rem;color:#6b7280;">
                    By approving, the vendor will be notified and will proceed with contract signing.
                    This action cannot be undone.
                </div>
            </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Yes, Approve',
                cancelButtonText: 'Not yet',
                confirmButtonColor: 'var(--cg-600)',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('approveQuotationForm').submit();
                }
            });
        }
    </script>
@endpush
