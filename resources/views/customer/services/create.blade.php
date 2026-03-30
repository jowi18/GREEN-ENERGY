@extends('layouts.customer')

@section('title', 'Book a Service')

@section('content')
    <div class="cu-page" style="max-width:720px;">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ url()->previous() }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">Book a Service</h5>
        </div>

        {{-- Show what service was pre-selected from vendor profile ── --}}
        @if ($preSelectedType && $preSelectedTitle)
            <div
                style="display:flex;align-items:center;gap:0.75rem;padding:0.85rem 1rem;
                background:var(--cg-50);border:1.5px solid var(--cg-200);
                border-radius:var(--r-md);margin-bottom:1.25rem;">
                <div
                    style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--cg-100);
                    display:flex;align-items:center;justify-content:center;
                    color:var(--cg-600);font-size:1rem;flex-shrink:0;">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <div
                        style="font-size:0.75rem;color:var(--cg-600);font-weight:700;text-transform:uppercase;letter-spacing:0.06em;">
                        Booking
                    </div>
                    <div class="fw-700" style="font-size:0.9rem;">{{ $preSelectedTitle }}</div>
                </div>
                <a href="{{ url()->previous() }}" class="cu-btn cu-btn--ghost cu-btn--sm ms-auto">
                    Change
                </a>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.services.store') }}" id="bookingForm">
            @csrf

            <div class="row g-3">

                {{-- ── Left: form ── --}}
                <div class="col-12 col-md-7">

                    {{-- Vendor selection ── --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-shop text-green me-1"></i> Vendor
                            </div>

                            @if ($vendor)
                                <div
                                    style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;
                                    background:var(--cg-50);border:1.5px solid var(--cg-200);
                                    border-radius:var(--r-md);">
                                    <div
                                        style="width:42px;height:42px;border-radius:var(--r-md);
                                        background:var(--cg-100);display:flex;align-items:center;
                                        justify-content:center;font-weight:800;color:var(--cg-700);
                                        font-size:1rem;flex-shrink:0;">
                                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-700">{{ $vendor->business_name }}</div>
                                        <div class="text-muted" style="font-size:0.78rem;">
                                            {{ $vendor->city }}, {{ $vendor->province_state }}
                                        </div>
                                    </div>
                                    <i class="bi bi-check-circle-fill ms-auto"
                                        style="color:var(--cg-500);font-size:1.1rem;"></i>
                                </div>
                                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                            @else
                                <label class="cu-label">Choose a vendor <span class="req">*</span></label>
                                <select name="vendor_id" class="cu-select @error('vendor_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select vendor…</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}"
                                            {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->business_name }} — {{ $v->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    {{-- Service details ── --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-tools text-green me-1"></i> Service Details
                            </div>

                            {{-- Service type ── --}}
                            <div class="mb-3">
                                <label class="cu-label">Service type <span class="req">*</span></label>
                                <select name="service_type" id="serviceTypeSelect"
                                    class="cu-select @error('service_type') is-invalid @enderror"
                                    onchange="onTypeChange(this)" required>
                                    <option value="">Select service type…</option>
                                    @foreach ($serviceTypes as $key => $label)
                                        <option value="{{ $key }}" {{-- Priority: old() → preSelectedType → product match --}}
                                            {{ old('service_type', $preSelectedType ?? '') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Service title ── --}}
                            <div class="mb-3">
                                <label class="cu-label">Service title <span class="req">*</span></label>
                                <input type="text" name="service_title" id="serviceTitleInput" {{-- Priority: old() → preSelectedTitle → product name --}}
                                    value="{{ old('service_title', $preSelectedTitle ?? ($product?->name ?? '')) }}"
                                    class="cu-input @error('service_title') is-invalid @enderror"
                                    placeholder="Brief title for your request" required>
                                @error('service_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description ── --}}
                            <div class="mb-3">
                                <label class="cu-label">Description <span class="req">*</span></label>
                                <textarea name="description" rows="4" class="cu-textarea @error('description') is-invalid @enderror"
                                    placeholder="Describe your solar system, the issue or service needed, and any relevant details…" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($product)
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div
                                    style="background:var(--cg-50);border:1px solid var(--cg-100);
                                    border-radius:var(--r-sm);padding:0.65rem 0.85rem;font-size:0.82rem;">
                                    <i class="bi bi-box-seam me-1 text-green"></i>
                                    Related to: <strong>{{ $product->name }}</strong>
                                </div>
                            @endif

                            {{-- Customer notes ── --}}
                            <div class="mt-3">
                                <label class="cu-label">Additional notes</label>
                                <textarea name="customer_notes" rows="2" class="cu-textarea" placeholder="Anything else the vendor should know…">{{ old('customer_notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Preferred schedule ── --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-calendar2-check text-green me-1"></i> Preferred Schedule
                                <span class="text-muted fw-400" style="font-size:0.78rem;">(optional)</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-7">
                                    <label class="cu-label">Preferred date</label>
                                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                                        min="{{ now()->addDay()->format('Y-m-d') }}" class="cu-input">
                                </div>
                                <div class="col-5">
                                    <label class="cu-label">Time slot</label>
                                    <select name="preferred_time" class="cu-select">
                                        <option value="">Any</option>
                                        <option value="morning"
                                            {{ old('preferred_time') === 'morning' ? 'selected' : '' }}>Morning (8–12)
                                        </option>
                                        <option value="afternoon"
                                            {{ old('preferred_time') === 'afternoon' ? 'selected' : '' }}>Afternoon (1–5)
                                        </option>
                                        <option value="full_day"
                                            {{ old('preferred_time') === 'full_day' ? 'selected' : '' }}>Full Day</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Service location ── --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-geo-alt text-green me-1"></i> Service Location
                            </div>

                            <div class="mb-3">
                                <label class="cu-label">Street address <span class="req">*</span></label>
                                <input type="text" name="service_address"
                                    value="{{ old('service_address', $customer->address_line1) }}"
                                    class="cu-input @error('service_address') is-invalid @enderror"
                                    placeholder="Where should the technician go?" required>
                                @error('service_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="cu-label">City <span class="req">*</span></label>
                                    <input type="text" name="service_city"
                                        value="{{ old('service_city', $customer->city) }}"
                                        class="cu-input @error('service_city') is-invalid @enderror" required>
                                    @error('service_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="cu-label">Province <span class="req">*</span></label>
                                    <input type="text" name="service_province"
                                        value="{{ old('service_province', $customer->province_state) }}"
                                        class="cu-input @error('service_province') is-invalid @enderror" required>
                                    @error('service_province')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="cu-label">Postal code</label>
                                <input type="text" name="service_postal"
                                    value="{{ old('service_postal', $customer->postal_code) }}" class="cu-input"
                                    style="max-width:140px;">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg" id="submitBtn">
                        <i class="bi bi-send-check"></i> Submit Service Request
                    </button>

                </div>

                {{-- ── Right: What happens next sidebar ── --}}
                <div class="col-12 col-md-5">
                    <div class="cu-card" style="position:sticky;top:calc(var(--navbar-h) + 1rem);">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;font-size:0.9rem;">
                                <i class="bi bi-lightbulb-fill text-yellow me-1"></i> What happens next?
                            </div>

                            <div style="display:flex;flex-direction:column;gap:0.85rem;">
                                @php
                                    $steps = [
                                        ['bi-send', 'You submit', 'Your request is sent to the vendor.'],
                                        ['bi-eye', 'Vendor reviews', 'They review and may contact you for more info.'],
                                        ['bi-tag', 'Quote sent', 'Vendor sends a cost estimate for your approval.'],
                                        [
                                            'bi-calendar-check',
                                            'Scheduled',
                                            'A date and technician are confirmed with you.',
                                        ],
                                        [
                                            'bi-gear-wide-connected',
                                            'In progress',
                                            'The technician performs the service on-site.',
                                        ],
                                        [
                                            'bi-images',
                                            'Proof uploaded',
                                            'Vendor uploads photos as proof of completion.',
                                        ],
                                    ];
                                @endphp
                                @foreach ($steps as $i => [$icon, $title, $desc])
                                    <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                                        <div
                                            style="width:30px;height:30px;border-radius:50%;
                                            background:{{ $i === 0 ? 'var(--cg-600)' : 'var(--cn-100)' }};
                                            display:flex;align-items:center;justify-content:center;
                                            flex-shrink:0;font-size:0.8rem;
                                            color:{{ $i === 0 ? '#fff' : 'var(--tx-muted)' }};">
                                            <i class="bi {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <div style="font-size:0.82rem;font-weight:700;">{{ $title }}</div>
                                            <div style="font-size:0.75rem;color:var(--tx-muted);">{{ $desc }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($vendor)
                                <hr style="border-color:var(--cn-100);margin:1.1rem 0;">
                                <div style="font-size:0.78rem;color:var(--tx-muted);text-align:center;">
                                    <i class="bi bi-shield-check me-1 text-green"></i>
                                    Booking with <strong
                                        style="color:var(--tx-secondary);">{{ $vendor->business_name }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // All service type labels for JS auto-fill
        const typeLabels = @json($serviceTypes);

        // Pre-selected values from controller (from vendor profile "Book" button)
        const preType = @json($preSelectedType);
        const preTitle = @json($preSelectedTitle);

        function onTypeChange(select) {
            const titleInput = document.getElementById('serviceTitleInput');

            // Only auto-fill title if the field is still empty or was auto-filled before
            if (select.value && typeLabels[select.value]) {
                if (!titleInput.value || titleInput.dataset.autoFilled === '1') {
                    titleInput.value = typeLabels[select.value];
                    titleInput.dataset.autoFilled = '1';
                }
            }
        }

        // Once the user manually edits the title, stop auto-filling it
        document.getElementById('serviceTitleInput').addEventListener('input', function() {
            this.dataset.autoFilled = '0';
        });

        // On page load — if a type was pre-selected from vendor profile, mark title as auto-filled
        // so onTypeChange can still replace it if they switch types
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('serviceTitleInput');
            if (preType && preTitle && titleInput.value === preTitle) {
                titleInput.dataset.autoFilled = '1';
            }
        });

        document.getElementById('bookingForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        });
    </script>
@endpush
