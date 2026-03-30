@extends('layouts.customer')
@section('title', $serviceRequest->request_number)

@section('content')
    <div class="cu-page" style="max-width:720px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <a href="{{ route('customer.services.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div style="flex:1;">
                <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;line-height:1.3;">
                    {{ $serviceRequest->service_title }}
                </div>
                <div class="text-muted" style="font-size:0.78rem;">
                    {{ $serviceRequest->request_number }} &middot; {{ $serviceRequest->vendor->business_name }}
                </div>
            </div>
            <span class="cu-badge cu-badge--{{ $serviceRequest->status_color }}">
                {{ $serviceRequest->status_label }}
            </span>
        </div>

        {{-- ── Quote action card ── --}}
        @if ($serviceRequest->status === 'quoted')
            <div
                style="background:var(--cy-50);border:2px solid var(--cy-300);border-radius:var(--r-lg);
                padding:1.25rem;margin-bottom:1.25rem;">
                <div
                    style="font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;
                    color:var(--cy-600);margin-bottom:0.5rem;">
                    <i class="bi bi-tag-fill me-1"></i>Quote Received — Action Required
                </div>
                <div
                    style="font-size:2rem;font-weight:800;font-family:'JetBrains Mono',monospace;
                    color:var(--cn-900);margin-bottom:0.35rem;">
                    ₱{{ number_format($serviceRequest->quoted_amount, 2) }}
                </div>
                <p
                    style="font-size:0.875rem;color:var(--tx-secondary);line-height:1.7;
                  margin-bottom:0.85rem;white-space:pre-line;">
                    {{ $serviceRequest->quote_details }}
                </p>
                @if ($serviceRequest->quote_valid_until)
                    <p style="font-size:0.78rem;color:var(--cy-600);margin-bottom:0.85rem;">
                        <i class="bi bi-clock me-1"></i>
                        Valid until {{ $serviceRequest->quote_valid_until->format('F d, Y') }}
                    </p>
                @endif
                <form method="POST" action="{{ route('customer.services.action', $serviceRequest) }}" id="quoteActionForm"
                    style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    @csrf
                    <button type="submit" name="action" value="quote_accepted" class="cu-btn cu-btn--primary">
                        <i class="bi bi-check-circle"></i> Accept Quote
                    </button>
                    <button type="button" class="cu-btn cu-btn--danger" onclick="rejectQuote()">
                        <i class="bi bi-x-circle"></i> Reject Quote
                    </button>
                </form>
            </div>
        @endif

        {{-- ── Status timeline ── --}}
        @php
            $pipeline = [
                'submitted' => 'Submitted',
                'reviewing' => 'Under Review',
                'scheduled' => 'Scheduled',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
            ];
            $pipelineKeys = array_keys($pipeline);
            $cancelled = in_array($serviceRequest->status, ['cancelled', 'rejected', 'quote_rejected']);
            $currentStatus = $serviceRequest->status;
            if (in_array($currentStatus, ['quoted', 'quote_accepted'])) {
                $currentStatus = 'reviewing';
            }
            $currentIdx = array_search($currentStatus, $pipelineKeys);
        @endphp

        @if (!$cancelled)
            <div class="cu-card mb-3">
                <div class="cu-card__body" style="padding:1rem 1.25rem;">
                    <div style="display:flex;align-items:flex-start;">
                        @foreach ($pipeline as $key => $label)
                            @php
                                $i = $loop->index;
                                $done = $currentIdx !== false && $i < $currentIdx;
                                $cur = $currentIdx !== false && $i === $currentIdx;
                            @endphp
                            <div style="flex:1;text-align:center;position:relative;">
                                @if (!$loop->last)
                                    <div
                                        style="position:absolute;top:13px;left:50%;right:-50%;height:2px;z-index:0;
                                background:{{ $done ? 'var(--cg-400)' : 'var(--cn-200)' }};">
                                    </div>
                                @endif
                                <div
                                    style="width:26px;height:26px;border-radius:50%;margin:0 auto 0.35rem;
                                position:relative;z-index:1;display:flex;align-items:center;
                                justify-content:center;font-size:0.68rem;font-weight:700;
                                background:{{ $done ? 'var(--cg-400)' : '#fff' }};
                                border:2px solid {{ $done ? 'var(--cg-400)' : ($cur ? 'var(--cg-600)' : 'var(--cn-200)') }};
                                color:{{ $done ? '#fff' : ($cur ? 'var(--cg-700)' : 'var(--tx-muted)') }};
                                {{ $cur ? 'box-shadow:0 0 0 4px rgba(21,122,48,0.14);' : '' }}">
                                    @if ($done)
                                        <i class="bi bi-check2"></i>@else{{ $i + 1 }}
                                    @endif
                                </div>
                                <div
                                    style="font-size:0.62rem;font-weight:{{ $cur ? '700' : '500' }};
                                color:{{ $cur ? 'var(--cg-700)' : ($done ? 'var(--tx-secondary)' : 'var(--tx-muted)') }};">
                                    {{ $label }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="cu-flash cu-flash--error mb-3">
                <i class="bi bi-x-circle-fill"></i>
                <span>This request was {{ $serviceRequest->status_label }}.</span>
            </div>
        @endif

        {{-- ── Service details ── --}}
        <div class="cu-card mb-3">
            <div class="cu-card__body">
                <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;">Service Details</div>
                <div style="font-size:0.78rem;color:var(--tx-muted);margin-bottom:0.35rem;">
                    {{ ucwords(str_replace('_', ' ', $serviceRequest->service_type)) }}
                </div>
                <p style="font-size:0.875rem;color:var(--tx-secondary);line-height:1.8;white-space:pre-line;">
                    {{ $serviceRequest->description }}
                </p>
                @if ($serviceRequest->customer_notes)
                    <div class="mt-2" style="font-size:0.82rem;color:var(--tx-muted);font-style:italic;">
                        Note: {{ $serviceRequest->customer_notes }}
                    </div>
                @endif

                <hr style="border-color:var(--cn-100);margin:1rem 0;">

                <div style="font-size:0.875rem;display:grid;gap:0.55rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span class="text-muted">Vendor</span>
                        <span class="fw-700">{{ $serviceRequest->vendor->business_name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span class="text-muted">Location</span>
                        <span>{{ $serviceRequest->service_city }}, {{ $serviceRequest->service_province }}</span>
                    </div>
                    @if ($serviceRequest->preferred_date)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Preferred date</span>
                            <span>{{ $serviceRequest->preferred_date->format('M d, Y') }}
                                @if ($serviceRequest->preferred_time)
                                    · {{ ucfirst($serviceRequest->preferred_time) }}
                                @endif
                            </span>
                        </div>
                    @endif
                    @if ($serviceRequest->scheduled_date)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Scheduled</span>
                            <span class="fw-700" style="color:var(--cg-700);">
                                {{ $serviceRequest->scheduled_date->format('M d, Y') }}
                                @if ($serviceRequest->scheduled_time)
                                    · {{ ucfirst($serviceRequest->scheduled_time) }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Assigned team (shown once scheduled) ── --}}
        @if ($serviceRequest->assignees->count())
            <div class="cu-card mb-3" style="border-color:var(--cg-200);">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;font-size:0.9rem;">
                        <i class="bi bi-people-fill text-green me-1"></i>
                        Your Assigned Team
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0;">
                        @foreach ($serviceRequest->assignees as $emp)
                            <div
                                style="display:flex;align-items:center;gap:0.85rem;
                            padding:0.65rem 0;
                            border-bottom:1px solid var(--cn-100);">
                                {{-- Avatar --}}
                                <div
                                    style="width:40px;height:40px;border-radius:50%;
                                background:var(--cg-100);color:var(--cg-700);
                                display:flex;align-items:center;justify-content:center;
                                font-size:0.82rem;font-weight:800;flex-shrink:0;">
                                    {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-700" style="font-size:0.9rem;">{{ $emp->full_name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        @if ($emp->pivot->role)
                                            <span
                                                style="color:var(--cg-600);font-weight:600;">{{ $emp->pivot->role }}</span>
                                        @else
                                            @foreach ($emp->roles->take(2) as $role)
                                                {{ $role->name }}{{ !$loop->last ? ' · ' : '' }}
                                            @endforeach
                                            @if ($emp->roles->isEmpty())
                                                Technician
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <span
                                    style="background:var(--cg-50);color:var(--cg-700);
                                 border:1px solid var(--cg-200);border-radius:var(--r-full);
                                 font-size:0.68rem;font-weight:700;padding:0.2rem 0.55rem;">
                                    <i class="bi bi-person-check-fill me-1"></i>Assigned
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if ($serviceRequest->scheduled_date)
                        <div
                            style="margin-top:0.85rem;padding:0.65rem 0.85rem;
                        background:var(--cg-50);border-radius:var(--r-sm);
                        font-size:0.82rem;color:var(--cg-700);">
                            <i class="bi bi-calendar-event me-1"></i>
                            <strong>Service date:</strong>
                            {{ $serviceRequest->scheduled_date->format('F d, Y') }}
                            @if ($serviceRequest->scheduled_time)
                                — {{ ucfirst(str_replace('_', ' ', $serviceRequest->scheduled_time)) }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @elseif(in_array($serviceRequest->status, ['scheduled', 'in_progress']))
            {{-- Scheduled but no team assigned yet --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;font-size:0.9rem;">
                        <i class="bi bi-calendar-check text-green me-1"></i> Service Scheduled
                    </div>
                    <div style="font-size:0.875rem;color:var(--tx-secondary);">
                        {{ $serviceRequest->scheduled_date->format('F d, Y') }}
                        @if ($serviceRequest->scheduled_time)
                            — {{ ucfirst(str_replace('_', ' ', $serviceRequest->scheduled_time)) }}
                        @endif
                    </div>
                    <div style="font-size:0.78rem;color:var(--tx-muted);margin-top:0.35rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Team assignment will be confirmed by the vendor shortly.
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Proof of completion ── --}}
        @if ($serviceRequest->proofs->count())
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-patch-check-fill text-green me-1"></i>
                        Proof of Completion
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:0.6rem;">
                        @foreach ($serviceRequest->proofs as $proof)
                            <div style="position:relative;border-radius:var(--r-md);overflow:hidden;
                            border:1.5px solid var(--card-border);aspect-ratio:1;cursor:pointer;"
                                onclick="openLightbox('{{ asset('storage/' . $proof->file_path) }}')">
                                <img src="{{ asset('storage/' . $proof->file_path) }}"
                                    style="width:100%;height:100%;object-fit:cover;"
                                    alt="{{ $proof->caption ?? 'Proof' }}">
                                @if ($proof->caption)
                                    <div
                                        style="position:absolute;bottom:0;left:0;right:0;
                                background:rgba(0,0,0,0.6);color:#fff;
                                font-size:0.68rem;padding:0.2rem 0.4rem;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $proof->caption }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <p class="text-muted mt-2" style="font-size:0.78rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ $serviceRequest->proofs->count() }} completion photo(s) uploaded by the vendor.
                    </p>
                </div>
            </div>
        @endif

        @if ($serviceRequest->status === 'completed')
            @php
                $alreadyReviewed = \App\Models\Review::where('customer_id', auth()->user()->customer->id)
                    ->where('reviewable_type', \App\Models\ServiceRequest::class)
                    ->where('reviewable_id', $serviceRequest->id)
                    ->exists();
            @endphp
            @if (!$alreadyReviewed)
                <a href="{{ route('customer.reviews.service.create', $serviceRequest) }}"
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

        {{-- ── Cancel ── --}}
        @if (in_array('cancelled', $customerTransitions))
            <form method="POST" action="{{ route('customer.services.action', $serviceRequest) }}" id="cancelForm">
                @csrf
                <input type="hidden" name="action" value="cancelled">
                <button type="button" class="cu-btn cu-btn--danger cu-btn--full"
                    onclick="Swal.fire({
                    title:'Cancel this booking?',icon:'warning',
                    showCancelButton:true,confirmButtonText:'Yes, cancel',
                    confirmButtonColor:'#ef4444'
                }).then(r=>{ if(r.isConfirmed) document.getElementById('cancelForm').submit(); })">
                    <i class="bi bi-x-circle"></i> Cancel Booking
                </button>
            </form>
        @endif

    </div>

    {{-- Lightbox --}}
    <div id="lightbox"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);
     z-index:9999;align-items:center;justify-content:center;cursor:pointer;"
        onclick="closeLightbox()">
        <img id="lightboxImg" style="max-width:90vw;max-height:90vh;border-radius:12px;">
    </div>

@endsection

@push('scripts')
    <script>
        function rejectQuote() {
            Swal.fire({
                title: 'Reject this quote?',
                text: 'The vendor will be notified and you can request a revised quote.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject Quote',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                const form = document.getElementById('quoteActionForm');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'action';
                input.value = 'quote_rejected';
                form.appendChild(input);
                form.submit();
            });
        }

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
