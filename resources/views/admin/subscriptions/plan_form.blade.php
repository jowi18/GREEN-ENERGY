@extends('layouts.admin')
@section('title', $plan->exists ? 'Edit Plan' : 'New Plan')
@section('page-title', $plan->exists ? 'Edit Subscription Plan' : 'New Subscription Plan')
@section('breadcrumb')
    <a href="{{ route('admin.subscriptions.plans') }}" class="text-secondary">Plans</a>
    <span class="sep">›</span><span class="current">{{ $plan->exists ? $plan->name : 'New' }}</span>
@endsection

@section('content')
    <div style="max-width:680px;">
        <form method="POST"
            action="{{ $plan->exists ? route('admin.subscriptions.plans.update', $plan) : route('admin.subscriptions.plans.store') }}"
            class="panel">
            @csrf
            @if ($plan->exists)
                @method('PUT')
            @endif
            <div style="padding:1.5rem;">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-600">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="form-control"
                            required maxlength="80" placeholder="e.g. Basic, Professional, Enterprise">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" class="form-control"
                            required maxlength="80" placeholder="basic" {{ $plan->exists ? 'readonly' : '' }}>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea name="description" rows="2" class="form-control" maxlength="500"
                            placeholder="Short description shown on the pricing page">{{ old('description', $plan->description) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Price (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $plan->price) }}" class="form-control"
                            min="0" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Billing Cycle <span class="text-danger">*</span></label>
                        <select name="billing_cycle" class="form-select" required>
                            @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annually' => 'Annually'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('billing_cycle', $plan->billing_cycle) === $v ? 'selected' : '' }}>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Duration (days) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_days"
                            value="{{ old('duration_days', $plan->duration_days ?? 30) }}" class="form-control" min="1"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Max Products <span class="text-muted fw-400">(-1 =
                                unlimited)</span></label>
                        <input type="number" name="max_products" value="{{ old('max_products', $plan->max_products ?? -1) }}"
                            class="form-control" min="-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Max Employees <span class="text-muted fw-400">(-1 =
                                unlimited)</span></label>
                        <input type="number" name="max_employees"
                            value="{{ old('max_employees', $plan->max_employees ?? -1) }}" class="form-control"
                            min="-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                            class="form-control" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Features</label>
                        <div id="featuresList">
                            @php $features = old('features', $plan->features ?? ['','','']); @endphp
                            @foreach ($features as $i => $f)
                                <div class="d-flex gap-2 mb-2" id="feat-{{ $i }}">
                                    <input type="text" name="features[]" value="{{ $f }}"
                                        class="form-control form-control-sm" placeholder="e.g. Unlimited product listings">
                                    <button type="button" onclick="this.closest('[id^=feat]').remove()"
                                        class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addFeature()" class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="bi bi-plus-lg"></i> Add Feature
                        </button>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-4">
                            <label style="cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}
                                    style="accent-color:#157a30;">
                                <span class="fw-600">Active</span>
                            </label>
                            <label style="cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" name="is_featured" value="1"
                                    {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}
                                    style="accent-color:#157a30;">
                                <span class="fw-600">Featured</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding:1rem 1.5rem;border-top:1px solid #f1f5f1;display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-floppy me-1"></i> {{ $plan->exists ? 'Update Plan' : 'Create Plan' }}
                </button>
                <a href="{{ route('admin.subscriptions.plans') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let fi = {{ count($features ?? []) }};

            function addFeature() {
                const d = document.createElement('div');
                d.className = 'd-flex gap-2 mb-2';
                d.id = 'feat-' + fi;
                d.innerHTML =
                    `<input type="text" name="features[]" class="form-control form-control-sm" placeholder="Feature…">
        <button type="button" onclick="this.closest('[id^=feat]').remove()" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>`;
                document.getElementById('featuresList').appendChild(d);
                fi++;
            }
            // Auto-slug from name
            document.querySelector('[name=name]').addEventListener('input', function() {
                const slugField = document.querySelector('[name=slug]');
                if (!slugField.readOnly) {
                    slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                }
            });
        </script>
    @endpush
@endsection
