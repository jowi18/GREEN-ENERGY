{{--
    Shared form partial for both Add and Edit modals.
    $service = VendorService|null
--}}
@php $serviceTypes = App\Models\VendorService::serviceTypes(); @endphp

<div class="row g-3">

    {{-- Name + Type ── --}}
    <div class="col-md-7">
        <label class="vd-label">Service name <span class="req">*</span></label>
        <input type="text" name="name" value="{{ old('name', $service?->name) }}"
            class="vd-input @error('name') is-invalid @enderror" placeholder="e.g. Residential Solar Installation"
            required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-5">
        <label class="vd-label">Category <span class="req">*</span></label>
        <select name="service_type" class="vd-select @error('service_type') is-invalid @enderror"
            onchange="updatePriceVisibility(this.closest('form').querySelector('[name=pricing_type]'))" required>
            <option value="">Select type…</option>
            @foreach ($serviceTypes as $key => $label)
                <option value="{{ $key }}"
                    {{ old('service_type', $service?->service_type) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('service_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Short description ── --}}
    <div class="col-12">
        <label class="vd-label">Short description</label>
        <input type="text" name="short_description"
            value="{{ old('short_description', $service?->short_description) }}" class="vd-input"
            placeholder="One-line summary shown on your profile card" maxlength="300"
            oninput="document.getElementById('sdCount').textContent = 300 - this.value.length">
        <div class="char-count"><span id="sdCount">{{ 300 - strlen($service?->short_description ?? '') }}</span> chars
            left</div>
    </div>

    {{-- Full description ── --}}
    <div class="col-12">
        <label class="vd-label">Full description</label>
        <textarea name="description" rows="3" class="vd-input"
            placeholder="Detailed description of what this service covers, methodology, certifications…">{{ old('description', $service?->description) }}</textarea>
    </div>

    {{-- Pricing ── --}}
    <div class="col-md-5">
        <label class="vd-label">Pricing type <span class="req">*</span></label>
        <select name="pricing_type" class="vd-select" onchange="updatePriceVisibility(this)" required>
            <option value="starting_from"
                {{ old('pricing_type', $service?->pricing_type ?? 'starting_from') === 'starting_from' ? 'selected' : '' }}>
                Starting from</option>
            <option value="fixed"
                {{ old('pricing_type', $service?->pricing_type) === 'fixed' ? 'selected' : '' }}>Fixed price
            </option>
            <option value="per_hour"
                {{ old('pricing_type', $service?->pricing_type) === 'per_hour' ? 'selected' : '' }}>Per hour
            </option>
            <option value="custom_quote"
                {{ old('pricing_type', $service?->pricing_type) === 'custom_quote' ? 'selected' : '' }}>Custom quote
                only</option>
        </select>
    </div>

    <div class="col-md-3" id="priceWrap">
        <label class="vd-label">Price (₱)</label>
        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $service?->price) }}"
            class="vd-input" placeholder="0.00">
    </div>

    <div class="col-md-4" id="priceMaxWrap" style="display:none;">
        <label class="vd-label">Up to (₱) <span style="font-weight:400;font-size:0.75rem;">(optional)</span></label>
        <input type="number" name="price_max" step="0.01" min="0"
            value="{{ old('price_max', $service?->price_max) }}" class="vd-input" placeholder="0.00">
        <div class="form-hint">Leave blank to show "From ₱X"</div>
    </div>

    {{-- Duration ── --}}
    <div class="col-md-3">
        <label class="vd-label">Est. duration (min hrs)</label>
        <input type="number" name="duration_hours_min" min="0"
            value="{{ old('duration_hours_min', $service?->duration_hours_min) }}" class="vd-input"
            placeholder="e.g. 4">
    </div>

    <div class="col-md-3">
        <label class="vd-label">Max hrs (optional)</label>
        <input type="number" name="duration_hours_max" min="0"
            value="{{ old('duration_hours_max', $service?->duration_hours_max) }}" class="vd-input"
            placeholder="e.g. 8">
        <div class="form-hint">Leave same as min for fixed.</div>
    </div>

    <div class="col-md-6">
        <label class="vd-label">Coverage area</label>
        <input type="text" name="coverage_notes" value="{{ old('coverage_notes', $service?->coverage_notes) }}"
            class="vd-input" placeholder="e.g. Metro Manila only, Nationwide, Within 50km">
    </div>

    {{-- Inclusions ── --}}
    <div class="col-md-6">
        <label class="vd-label">What's included</label>
        <textarea name="inclusions" rows="4" class="vd-input"
            placeholder="One item per line:&#10;Labor and materials&#10;Site inspection&#10;1-year labor warranty">{{ old('inclusions', $service?->inclusions) }}</textarea>
        <div class="form-hint">One item per line — shown as bullet list to customers.</div>
    </div>

    <div class="col-md-6">
        <label class="vd-label">Customer requirements</label>
        <textarea name="requirements" rows="4" class="vd-input"
            placeholder="One item per line:&#10;Must provide roof access&#10;Electrical panel must be accessible&#10;Area must be cleared">{{ old('requirements', $service?->requirements) }}</textarea>
        <div class="form-hint">What the customer must provide or prepare.</div>
    </div>

    {{-- Flags ── --}}
    <div class="col-12">
        <div class="d-flex gap-4 flex-wrap">
            <label style="display:flex;align-items:center;gap:0.55rem;cursor:pointer;font-size:0.875rem;">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $service ? $service->is_active : true) ? 'checked' : '' }}
                    style="accent-color:var(--g-500);width:15px;height:15px;">
                <span><strong>Active</strong> — visible to customers on your profile</span>
            </label>
            <label style="display:flex;align-items:center;gap:0.55rem;cursor:pointer;font-size:0.875rem;">
                <input type="checkbox" name="is_featured" value="1"
                    {{ old('is_featured', $service?->is_featured) ? 'checked' : '' }}
                    style="accent-color:var(--g-500);width:15px;height:15px;">
                <span><strong>Featured</strong> — highlighted at the top of your service list</span>
            </label>
        </div>
    </div>

</div>
