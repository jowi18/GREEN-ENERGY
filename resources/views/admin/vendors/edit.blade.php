@extends('layouts.admin')
@section('title', 'Edit — ' . $vendor->business_name)
@section('page-title', 'Edit Vendor')
@section('breadcrumb')
    <a href="{{ route('admin.vendors.index') }}" class="text-secondary">Vendors</a>
    <span class="sep">›</span>
    <a href="{{ route('admin.vendors.show', $vendor) }}" class="text-secondary">{{ $vendor->business_name }}</a>
    <span class="sep">›</span><span class="current">Edit</span>
@endsection

@section('content')
    <div style="max-width:680px;">

        <div class="panel mb-3" style="padding:.75rem 1.25rem;background:#fefce8;border-left:3px solid #f59e0b;">
            <div style="font-size:.82rem;color:#92400e;">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Admin Override:</strong> Only use this to correct data errors.
                Vendor store settings (logo, description, hours) are managed by the vendor in their own portal.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" class="panel">
            @csrf @method('PUT')
            <div style="padding:1.5rem;">

                <div
                    style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                        color:#6b7a70;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid #f1f5f1;">
                    Business Information
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-600">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name"
                            value="{{ old('business_name', $vendor->business_name) }}"
                            class="form-control @error('business_name') is-invalid @enderror" required>
                        @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Business Phone <span class="text-danger">*</span></label>
                        <input type="text" name="business_phone"
                            value="{{ old('business_phone', $vendor->business_phone) }}"
                            class="form-control @error('business_phone') is-invalid @enderror" required>
                        @error('business_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Business Email</label>
                        <input type="email" name="business_email"
                            value="{{ old('business_email', $vendor->business_email) }}"
                            class="form-control @error('business_email') is-invalid @enderror">
                        @error('business_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Registration No.</label>
                        <input type="text" value="{{ $vendor->business_registration_number }}" class="form-control"
                            readonly style="background:#f8faf8;cursor:not-allowed;"
                            title="Cannot be changed — contact system admin">
                        <div class="form-text">Cannot be changed after approval.</div>
                    </div>
                </div>

                <div
                    style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                        color:#6b7a70;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid #f1f5f1;">
                    Address
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-600">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" value="{{ old('city', $vendor->city) }}"
                            class="form-control @error('city') is-invalid @enderror" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Province / State <span class="text-danger">*</span></label>
                        <input type="text" name="province_state"
                            value="{{ old('province_state', $vendor->province_state) }}"
                            class="form-control @error('province_state') is-invalid @enderror" required>
                        @error('province_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div
                    style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                        color:#6b7a70;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid #f1f5f1;">
                    Admin Notes
                </div>
                <div>
                    <label class="form-label fw-600">Internal Notes</label>
                    <textarea name="admin_notes" rows="4" class="form-control" placeholder="Internal notes visible only to admins…">{{ old('admin_notes', $vendor->admin_notes) }}</textarea>
                    <div class="form-text">Only visible to admins. Never shown to the vendor.</div>
                </div>
            </div>

            <div style="padding:1rem 1.5rem;border-top:1px solid #f1f5f1;display:flex;gap:.75rem;align-items:center;">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-floppy me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-light">Cancel</a>
                <span class="text-muted ms-auto" style="font-size:.72rem;">
                    Last updated: {{ $vendor->updated_at->format('M d, Y g:i A') }}
                </span>
            </div>
        </form>

    </div>
@endsection
