@extends('layouts.admin')

@section('title', 'Review: ' . $vendor->business_name)
@section('page-title', 'Vendor Review')

@section('breadcrumb')
    <a href="{{ route('admin.vendors.pending') }}" class="text-secondary">Approval Queue</a>
    <span class="sep">›</span>
    <span class="current">{{ $vendor->business_name }}</span>
@endsection

@section('content')

    {{-- ── Header bar ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar--vendor avatar--lg">{{ strtoupper(substr($vendor->business_name, 0, 2)) }}</div>
            <div>
                <h5 class="fw-700 mb-0" style="letter-spacing:-0.02em;">{{ $vendor->business_name }}</h5>
                <div class="text-muted" style="font-size:0.82rem;">
                    Submitted {{ $vendor->created_at->format('F d, Y') }} &middot;
                    {{ $vendor->created_at->diffForHumans() }}
                </div>
            </div>
            <span class="badge-status {{ $vendor->status === 'pending' ? 'badge--pending' : 'badge--review' }} ms-1">
                {{ ucwords(str_replace('_', ' ', $vendor->status)) }}
            </span>
        </div>

        @if (in_array($vendor->status, ['pending', 'under_review']))
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn--warning" data-bs-toggle="modal" data-bs-target="#revisionModal">
                    <i class="bi bi-pencil-square"></i> Request Revision
                </button>
                <button class="btn btn--danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
                <button class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </div>
        @endif
    </div>

    <div class="row g-3">

        {{-- ── Left: business details ── --}}
        <div class="col-12 col-xl-8">

            {{-- Business information --}}
            <div class="panel section-gap">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-building"></i> Business Information</span>
                </div>
                <div class="panel__body">
                    <div class="row g-3">
                        @foreach ([['Business Name', $vendor->business_name], ['Business Type', ucwords(str_replace('_', ' ', $vendor->business_type))], ['Registration Number', $vendor->business_registration_number], ['Business Phone', $vendor->business_phone], ['Business Email', $vendor->business_email ?: '—'], ['Website', $vendor->business_website ?: '—']] as [$label, $value])
                            <div class="col-6">
                                <div class="detail-row"
                                    style="border:none;padding:0;flex-direction:column;align-items:flex-start;">
                                    <span class="detail-row__label"
                                        style="font-size:0.7rem;letter-spacing:0.06em;text-transform:uppercase;">{{ $label }}</span>
                                    <span class="detail-row__value" style="margin-top:0.2rem;">{{ $value }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Owner information --}}
            <div class="panel section-gap">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-person"></i> Owner Information</span>
                </div>
                <div class="panel__body">
                    <div class="row g-3">
                        @foreach ([['Full Name', $vendor->owner_full_name], ['Phone', $vendor->owner_phone], ['Email', $vendor->user->email], ['Registered', $vendor->user->created_at->format('M d, Y')]] as [$label, $value])
                            <div class="col-6">
                                <div class="detail-row"
                                    style="border:none;padding:0;flex-direction:column;align-items:flex-start;">
                                    <span class="detail-row__label"
                                        style="font-size:0.7rem;letter-spacing:0.06em;text-transform:uppercase;">{{ $label }}</span>
                                    <span class="detail-row__value" style="margin-top:0.2rem;">{{ $value }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Address + map --}}
            <div class="panel section-gap">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-geo-alt"></i> Business Address</span>
                </div>
                <div class="panel__body">
                    <p style="font-size:0.9rem;margin-bottom:1rem;">{{ $vendor->full_address }}</p>
                    @if ($vendor->latitude && $vendor->longitude)
                        <div id="vendorMap" class="admin-map" style="height:240px;"></div>
                    @else
                        <div class="empty-state"
                            style="padding:2rem 0;border:1px solid var(--card-border);border-radius:var(--radius-sm);">
                            <i class="bi bi-geo-alt"></i>
                            <p>No coordinates provided</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Documents --}}
            <div class="panel">
                <div class="panel__header">
                    <span class="panel__title"><i class="bi bi-file-earmark-text"></i> Submitted Documents</span>
                    <span class="panel__subtitle">{{ $vendor->documents->count() }} file(s)</span>
                </div>
                <div class="panel__body">
                    @forelse($vendor->documents as $doc)
                        <div class="doc-card">
                            <div
                                class="doc-card__icon {{ $doc->isPdf() ? 'doc-card__icon--pdf' : 'doc-card__icon--img' }}">
                                <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                            </div>
                            <div class="doc-card__info">
                                <div class="doc-card__name">{{ $doc->document_type_label }}</div>
                                <div class="doc-card__meta">{{ $doc->document_label }} &middot;
                                    {{ $doc->file_size_formatted }}</div>
                                @if ($doc->reviewer_notes)
                                    <div class="doc-card__note"><i
                                            class="bi bi-exclamation-circle"></i>{{ $doc->reviewer_notes }}</div>
                                @endif
                            </div>
                            <div class="doc-card__actions">
                                <span
                                    class="badge-status
                            {{ $doc->review_status === 'accepted'
                                ? 'badge--active'
                                : ($doc->review_status === 'rejected'
                                    ? 'badge--rejected'
                                    : 'badge--pending') }}">
                                    {{ ucfirst($doc->review_status) }}
                                </span>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                    class="btn btn--ghost btn--icon">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state" style="padding:1.5rem 0;"><i class="bi bi-folder-x"></i>
                            <p>No documents uploaded.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── Right: sidebar ── --}}
        <div class="col-12 col-xl-4">

            {{-- Admin notes / rejection --}}
            @if ($vendor->admin_notes || $vendor->rejection_reason)
                <div class="panel section-gap"
                    style="border-color:{{ $vendor->isRejected() ? 'var(--red-500)' : 'var(--yellow-500)' }};">
                    <div class="panel__header"
                        style="background:{{ $vendor->isRejected() ? 'var(--red-50)' : 'var(--yellow-50)' }};">
                        <span class="panel__title"
                            style="color:{{ $vendor->isRejected() ? 'var(--red-600)' : 'var(--yellow-600)' }};">
                            <i class="bi bi-{{ $vendor->isRejected() ? 'x-circle' : 'exclamation-triangle' }}"></i>
                            {{ $vendor->isRejected() ? 'Rejection Reason' : 'Admin Notes' }}
                        </span>
                    </div>
                    <div class="panel__body">
                        <p style="font-size:0.875rem;line-height:1.7;">
                            {{ $vendor->rejection_reason ?: $vendor->admin_notes }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Application details --}}
            <div class="panel section-gap">
                <div class="panel__header">
                    <span class="panel__title">Application Details</span>
                </div>
                <div class="panel__body" style="padding-bottom:0.5rem;">
                    @foreach ([['bi-calendar3', 'Submitted', $vendor->created_at->format('M d, Y')], ['bi-file-earmark', 'Documents', $vendor->documents->count() . ' uploaded'], ['bi-check-circle', 'Docs Accepted', $vendor->documents->where('review_status', 'accepted')->count()], ['bi-x-circle', 'Docs Rejected', $vendor->documents->where('review_status', 'rejected')->count()], ['bi-geo-alt', 'Has Coordinates', $vendor->latitude ? 'Yes ✓' : 'No']] as [$icon, $label, $value])
                        <div class="detail-row">
                            <span class="detail-row__label"><i
                                    class="bi {{ $icon }} me-1"></i>{{ $label }}</span>
                            <span class="detail-row__value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Review checklist --}}
            @if (in_array($vendor->status, ['pending', 'under_review']))
                <div class="panel section-gap" style="border-color:var(--green-300);">
                    <div class="panel__header" style="background:var(--green-50);">
                        <span class="panel__title text-green"><i class="bi bi-clipboard-check"></i> Review
                            Checklist</span>
                    </div>
                    <div class="panel__body">
                        @php
                            $checks = [
                                [
                                    $vendor->documents
                                        ->whereIn('document_type', [
                                            'business_permit',
                                            'dti_registration',
                                            'sec_registration',
                                        ])
                                        ->count() > 0,
                                    'Business registration document',
                                ],
                                [
                                    $vendor->documents->where('document_type', 'government_id')->count() > 0,
                                    'Government ID provided',
                                ],
                                [
                                    $vendor->documents->where('document_type', 'proof_of_address')->count() > 0,
                                    'Proof of address',
                                ],
                                [$vendor->latitude && $vendor->longitude, 'Location pinned on map'],
                                [!empty($vendor->business_phone), 'Business phone number'],
                                [!empty($vendor->business_registration_number), 'Registration number'],
                            ];
                        @endphp
                        <ul class="checklist">
                            @foreach ($checks as [$ok, $label])
                                <li class="checklist__item">
                                    <i
                                        class="bi {{ $ok ? 'bi-check-circle-fill checklist__icon--ok' : 'bi-circle checklist__icon--fail' }}"></i>
                                    <span
                                        class="{{ $ok ? 'checklist__label--ok' : 'checklist__label--fail' }}">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ══ Modals ══ --}}

    {{-- Approve --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title text-green"><i class="bi bi-check-circle me-2"></i>Approve Application</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1rem;">
                            Approving <strong>{{ $vendor->business_name }}</strong> — they will be notified and redirected
                            to subscribe.
                        </p>
                        <label class="form-label">Internal notes (optional)</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Any notes for internal reference…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--primary"><i class="bi bi-check-circle"></i> Approve &
                            Notify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title text-red"><i class="bi bi-x-circle me-2"></i>Reject Application</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1rem;">
                            Rejecting <strong>{{ $vendor->business_name }}</strong> will notify them by email.
                        </p>
                        <label class="form-label">Rejection reason <span class="text-red">*</span></label>
                        <textarea name="reason" rows="4" class="form-control" required
                            placeholder="Explain why the application was rejected. This will be shown to the vendor."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--danger"><i class="bi bi-x-circle"></i> Reject
                            Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Request revision --}}
    <div class="modal fade" id="revisionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.vendors.revision', $vendor) }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title text-yellow"><i class="bi bi-pencil-square me-2"></i>Request Revision</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary" style="font-size:0.875rem;margin-bottom:1.25rem;">
                            Ask <strong>{{ $vendor->business_name }}</strong> to correct or re-upload specific items.
                            The application will reset to <em>Pending</em>.
                        </p>
                        <label class="form-label">Overall revision notes <span class="text-red">*</span></label>
                        <textarea name="notes" rows="3" class="form-control" required
                            placeholder="Summarise what needs to be corrected…" style="margin-bottom:1.25rem;"></textarea>

                        @if ($vendor->documents->count())
                            <div class="fw-600" style="font-size:0.82rem;margin-bottom:0.6rem;">Per-document feedback
                                (optional)</div>
                            @foreach ($vendor->documents as $doc)
                                <div class="doc-card" style="margin-bottom:0.5rem;">
                                    <div
                                        class="doc-card__icon {{ $doc->isPdf() ? 'doc-card__icon--pdf' : 'doc-card__icon--img' }}">
                                        <i
                                            class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                                    </div>
                                    <div class="doc-card__info">
                                        <div class="doc-card__name">{{ $doc->document_type_label }}</div>
                                        <input type="text" name="document_feedback[{{ $doc->id }}]"
                                            class="form-control mt-1" style="font-size:0.82rem;"
                                            placeholder="Feedback for this document (leave blank if OK)">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--warning"><i class="bi bi-send"></i> Send Revision
                            Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
    @if ($vendor->latitude && $vendor->longitude)
        <script>
            const map = L.map('vendorMap', {
                    scrollWheelZoom: false
                })
                .setView([{{ $vendor->latitude }}, {{ $vendor->longitude }}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            const icon = L.divIcon({
                html: `<div style="width:28px;height:28px;background:var(--green-600);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                className: ''
            });
            L.marker([{{ $vendor->latitude }}, {{ $vendor->longitude }}], {
                    icon
                })
                .addTo(map)
                .bindPopup('<strong>{{ addslashes($vendor->business_name) }}</strong>')
                .openPopup();
        </script>
    @endif
@endpush
