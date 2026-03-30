@extends('layouts.customer')
@section('title', $warrantyRequest->request_number)

@section('content')
    <div class="cu-page" style="max-width:720px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <a href="{{ route('customer.warranty.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div style="flex:1;">
                <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;">
                    🛡️ {{ $warrantyRequest->product?->name ?? $warrantyRequest->orderItem->product_name }}
                </div>
                <div class="text-muted" style="font-size:.78rem;">
                    {{ $warrantyRequest->request_number }}
                    &middot; {{ $warrantyRequest->request_type_label }}
                    &middot; {{ $warrantyRequest->vendor->business_name }}
                </div>
            </div>
            <span class="cu-badge cu-badge--{{ $warrantyRequest->status_color }}">
                {{ $warrantyRequest->status_label }}
            </span>
        </div>

        {{-- Pipeline tracker --}}
        @php
            $pipeline = \App\Models\WarrantyRequest::STATUS_PIPELINE;
            $cancelled = in_array($warrantyRequest->status, ['rejected', 'closed']);
            $curIdx = array_search($warrantyRequest->status, $pipeline) ?: 0;
        @endphp

        @if (!$cancelled)
            <div class="cu-card mb-3">
                <div class="cu-card__body" style="padding:1rem 1.25rem;">
                    <div style="display:flex;align-items:flex-start;">
                        @foreach ($pipeline as $i => $step)
                            @php
                                $done = $i < $curIdx;
                                $cur = $i === $curIdx;
                            @endphp
                            <div style="flex:1;text-align:center;position:relative;">
                                @if ($i < count($pipeline) - 1)
                                    <div
                                        style="position:absolute;top:13px;left:50%;right:-50%;height:2px;z-index:0;
                                background:{{ $done ? 'var(--cg-400)' : 'var(--cn-200)' }};">
                                    </div>
                                @endif
                                <div
                                    style="width:26px;height:26px;border-radius:50%;margin:0 auto .35rem;
                                position:relative;z-index:1;
                                display:flex;align-items:center;justify-content:center;
                                font-size:.68rem;font-weight:700;
                                background:{{ $done ? 'var(--cg-400)' : ($cur ? '#fff' : '#fff') }};
                                border:2px solid {{ $done ? 'var(--cg-400)' : ($cur ? 'var(--cg-600)' : 'var(--cn-200)') }};
                                color:{{ $done ? '#fff' : ($cur ? 'var(--cg-700)' : 'var(--tx-muted)') }};
                                {{ $cur ? 'box-shadow:0 0 0 4px rgba(21,122,48,.14);' : '' }}">
                                    @if ($done)
                                        <i class="bi bi-check2"></i>@else{{ $i + 1 }}
                                    @endif
                                </div>
                                <div
                                    style="font-size:.6rem;font-weight:{{ $cur ? '700' : '500' }};
                                color:{{ $cur ? 'var(--cg-700)' : ($done ? 'var(--tx-secondary)' : 'var(--tx-muted)') }};">
                                    {{ ucfirst(str_replace('_', ' ', $step)) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="cu-flash cu-flash--{{ $warrantyRequest->status === 'rejected' ? 'error' : 'warning' }} mb-3">
                <i class="bi bi-{{ $warrantyRequest->status === 'rejected' ? 'x-circle' : 'archive' }}-fill"></i>
                <span>
                    This claim was <strong>{{ $warrantyRequest->status_label }}</strong>.
                    @if ($warrantyRequest->rejection_reason)
                        Reason: {{ $warrantyRequest->rejection_reason }}
                    @endif
                </span>
            </div>
        @endif

        {{-- Claim details --}}
        <div class="cu-card mb-3">
            <div class="cu-card__body">
                <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;">Claim Details</div>
                <div style="display:grid;gap:.5rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span class="text-muted">Type</span>
                        <span class="fw-600">{{ $warrantyRequest->request_type_label }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span class="text-muted">Product</span>
                        <span class="fw-600">{{ $warrantyRequest->orderItem->product_name }}</span>
                    </div>
                    @if ($warrantyRequest->serial_number)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Serial No.</span>
                            <span class="mono fw-600">{{ $warrantyRequest->serial_number }}</span>
                        </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;">
                        <span class="text-muted">Warranty</span>
                        <span
                            style="color:{{ !$warrantyRequest->is_within_warranty ? '#ef4444' : 'var(--cg-700)' }};font-weight:700;">
                            {{ $warrantyRequest->is_within_warranty ? '✅ Within warranty' : '❌ Expired' }}
                            @if ($warrantyRequest->warranty_expires_at)
                                ({{ $warrantyRequest->warranty_expires_at->format('M d, Y') }})
                            @endif
                        </span>
                    </div>
                    @if ($warrantyRequest->service_date)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Service Date</span>
                            <span class="fw-700" style="color:var(--cg-700);">
                                {{ \Carbon\Carbon::parse($warrantyRequest->service_date)->format('F d, Y') }}
                                @if ($warrantyRequest->service_time_slot)
                                    — {{ ucfirst($warrantyRequest->service_time_slot) }}
                                @endif
                            </span>
                        </div>
                    @endif
                    @if ($warrantyRequest->technician_name)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Technician</span>
                            <span class="fw-600">{{ $warrantyRequest->technician_name }}</span>
                        </div>
                    @endif
                    @if ($warrantyRequest->resolution_type)
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Resolution</span>
                            <span class="fw-700"
                                style="color:var(--cg-700);">{{ ucfirst(str_replace('_', ' ', $warrantyRequest->resolution_type)) }}</span>
                        </div>
                    @endif
                </div>

                <hr style="border-color:var(--cn-100);margin:1rem 0;">
                <div class="fw-700 mb-1" style="font-size:.82rem;">Issue Description</div>
                <p style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;">
                    {{ $warrantyRequest->issue_description }}</p>

                @if ($warrantyRequest->resolution_notes)
                    <hr style="border-color:var(--cn-100);margin:1rem 0;">
                    <div class="fw-700 mb-1" style="font-size:.82rem;color:var(--cg-700);">Resolution Notes from Vendor
                    </div>
                    <p style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;">
                        {{ $warrantyRequest->resolution_notes }}</p>
                @endif
            </div>
        </div>

        {{-- Images --}}
        @php $allImgs = array_merge($warrantyRequest->issue_images??[], $warrantyRequest->product_images??[]); @endphp
        @if (count($allImgs))
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;">Your Uploaded Photos</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;">
                        @foreach ($allImgs as $img)
                            <div style="aspect-ratio:1;border-radius:var(--r-sm);overflow:hidden;border:1.5px solid var(--card-border);cursor:pointer;"
                                onclick="document.getElementById('lb').style.display='flex';document.getElementById('lbImg').src='{{ asset('storage/' . $img) }}'">
                                <img src="{{ asset('storage/' . $img) }}" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Completion images --}}
        @if ($warrantyRequest->completion_images)
            <div class="cu-card mb-3" style="border-color:var(--cg-200);">
                <div class="cu-card__body">
                    <div class="fw-800 mb-2" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-patch-check-fill text-green me-1"></i> Completion Photos from Vendor
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;">
                        @foreach ($warrantyRequest->completion_images as $img)
                            <div style="aspect-ratio:1;border-radius:var(--r-sm);overflow:hidden;border:1.5px solid var(--cg-200);cursor:pointer;"
                                onclick="document.getElementById('lb').style.display='flex';document.getElementById('lbImg').src='{{ asset('storage/' . $img) }}'">
                                <img src="{{ asset('storage/' . $img) }}" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Service history timeline --}}
        @if ($warrantyRequest->service_history)
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">📋 Activity Timeline</div>
                    <div style="display:flex;flex-direction:column;gap:.65rem;">
                        @foreach (array_reverse($warrantyRequest->service_history) as $entry)
                            <div style="display:flex;gap:.75rem;align-items:flex-start;">
                                <div
                                    style="width:10px;height:10px;border-radius:50%;background:var(--cg-400);flex-shrink:0;margin-top:.35rem;">
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:.82rem;">{{ $entry['action'] }}</div>
                                    @if ($entry['notes'])
                                        <div style="font-size:.78rem;color:var(--tx-secondary);">{{ $entry['notes'] }}
                                        </div>
                                    @endif
                                    <div style="font-size:.68rem;color:var(--tx-muted);">
                                        {{ \Carbon\Carbon::parse($entry['timestamp'])->format('M d, Y · g:i A') }}
                                        · {{ $entry['user_name'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Cancel --}}
        @if (in_array($warrantyRequest->status, ['submitted', 'under_review']))
            <form method="POST" action="{{ route('customer.warranty.cancel', $warrantyRequest) }}" id="cancelForm">
                @csrf @method('PATCH')
                <button type="button" class="cu-btn cu-btn--danger cu-btn--full"
                    onclick="Swal.fire({title:'Cancel this claim?',icon:'warning',showCancelButton:true,confirmButtonText:'Yes, cancel',confirmButtonColor:'#ef4444'}).then(r=>{if(r.isConfirmed)document.getElementById('cancelForm').submit()})">
                    <i class="bi bi-x-circle"></i> Cancel Claim
                </button>
            </form>
        @endif
    </div>

    {{-- Lightbox --}}
    <div id="lb"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;cursor:pointer;"
        onclick="this.style.display='none'">
        <img id="lbImg" style="max-width:90vw;max-height:90vh;border-radius:8px;">
    </div>
@endsection
