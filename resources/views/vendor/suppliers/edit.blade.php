@extends('layouts.vendor')
@section('title', 'Edit · ' . $supplier->company_name)
@section('page-title', 'Suppliers')

@section('breadcrumb')
    <a href="{{ route('vendor.suppliers.index') }}" class="text-secondary">Supplier Directory</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.suppliers.show', $supplier) }}" class="text-secondary">
        {{ $supplier->company_name }}
    </a>
    <span class="sep">›</span>
    <span class="current">Edit</span>
@endsection

@push('styles')
    <style>
        .act-log {
            position: relative;
            padding-left: 1.5rem;
        }

        .act-log::before {
            content: '';
            position: absolute;
            left: .45rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--card-border);
        }

        .act-item {
            position: relative;
            margin-bottom: .85rem;
        }

        .act-item:last-child {
            margin-bottom: 0;
        }

        .act-dot {
            position: absolute;
            left: -1.27rem;
            top: .2rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--g-500);
            border: 2px solid var(--card-bg);
        }

        .act-time {
            font-size: .7rem;
            color: var(--tx-muted);
        }

        .act-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-primary);
        }

        .act-note {
            font-size: .78rem;
            color: var(--tx-secondary);
            margin-top: .1rem;
        }

        .req-check.met i {
            color: var(--g-500);
        }

        .req-check.unmet i {
            color: #f59e0b;
        }

        .vd-btn--blocked {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .25rem;
        }

        .req {
            color: #ef4444;
        }

        .locked-field__label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--tx-muted);
            margin-bottom: .15rem;
        }

        .locked-field__value {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-primary);
        }

        /* ── Portal picker ── */
        .portal-result-item {
            padding: .6rem .85rem;
            border-bottom: 1px solid var(--card-border);
            cursor: pointer;
            transition: background .1s;
            font-size: .85rem;
        }

        .portal-result-item:last-child {
            border-bottom: none;
        }

        .portal-result-item:hover {
            background: var(--n-50);
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('vendor.suppliers.update', $supplier) }}" id="editForm">
        @csrf
        @method('PATCH')

        @if ($errors->any())
            <div class="vd-card mb-3" style="border-color:#fca5a5;background:#fef2f2;">
                <div class="vd-card__body">
                    <div class="fw-700 mb-1" style="font-size:.82rem;color:#dc2626;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size:.78rem;color:#dc2626;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ── Card 1: Portal Link ─────────────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-link-45deg"></i> Portal Account Link
                </span>
            </div>
            <div class="vd-card__body">

                @if ($supplier->hasPortalAccount())
                    {{-- Currently linked --}}
                    <div class="d-flex align-items-center gap-3 mb-3 p-3"
                        style="background:var(--n-50);border:1.5px solid var(--card-border);border-radius:var(--r-md);">
                        <i class="bi bi-patch-check-fill" style="color:var(--g-500);font-size:1.3rem;flex-shrink:0;"></i>
                        <div>
                            <div class="fw-700" style="font-size:.85rem;">{{ $supplier->portalSupplier->company_name }}
                            </div>
                            <div style="font-size:.72rem;color:var(--tx-muted);">Currently linked portal account</div>
                        </div>
                        <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm ms-auto text-danger"
                            onclick="unlinkPortal()">
                            <i class="bi bi-x-lg me-1"></i> Unlink
                        </button>
                    </div>
                    <input type="hidden" name="supplier_id" id="supplierIdInput"
                        value="{{ old('supplier_id', $supplier->supplier_id) }}">
                    <div id="portalPickerSection" style="display:none;">
                    @else
                        <input type="hidden" name="supplier_id" id="supplierIdInput" value="{{ old('supplier_id') }}">
                        <div id="portalPickerSection" style="display:{{ old('supplier_id') ? 'block' : 'none' }};">
                @endif

                <label class="vd-label">Search Portal Suppliers</label>
                <input type="text" id="portalSearch" class="vd-input"
                    placeholder="Type to search by company name or city…">
                <div id="portalResultsList"
                    style="border:1.5px solid var(--card-border);border-radius:var(--r-md);margin-top:.4rem;max-height:220px;overflow-y:auto;display:none;">
                </div>
                @if (old('supplier_id'))
                    <div class="mt-2">
                        <span class="badge bg-primary" style="font-size:.78rem;padding:.4rem .75rem;">
                            <i class="bi bi-link-45deg me-1"></i>
                            <span id="portalSelectedName">Supplier selected</span>
                        </span>
                    </div>
                @endif
                <div class="form-hint">
                    Link to a portal supplier to enable Purchase Requests & Orders.
                </div>
            </div>

            @if (!$supplier->hasPortalAccount())
                <button type="button" class="vd-btn vd-btn--ghost mt-2" onclick="togglePortalPicker()"
                    id="showPortalPickerBtn">
                    <i class="bi bi-link-45deg me-1"></i>
                    Link a Portal Account
                </button>
            @endif

        </div>
        </div>

        {{-- ── Card 2: Contact Information ────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-person-lines-fill"></i> Contact Information
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="vd-label">Company Name <span class="req">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}"
                            class="vd-input @error('company_name') is-invalid @enderror" required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Contact Person</label>
                        <input type="text" name="contact_person"
                            value="{{ old('contact_person', $supplier->contact_person) }}" class="vd-input">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="vd-input">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                            class="vd-input @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Address</label>
                        <input type="text" name="address_line1"
                            value="{{ old('address_line1', $supplier->address_line1) }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">City</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                            class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Province</label>
                        <input type="text" name="province_state"
                            value="{{ old('province_state', $supplier->province_state) }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Postal Code</label>
                        <input type="text" name="postal_code"
                            value="{{ old('postal_code', $supplier->postal_code) }}" class="vd-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 3: Business Relationship ──────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title">
                    <i class="bi bi-briefcase-fill"></i> Business Relationship
                </span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="vd-label">Account Number</label>
                        <input type="text" name="account_number"
                            value="{{ old('account_number', $supplier->account_number) }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Payment Terms</label>
                        <select name="payment_terms" id="paymentTermsSelect" class="vd-select"
                            onchange="toggleCustomTerms()">
                            @foreach ([
            'cod' => 'Cash on Delivery',
            'net7' => 'Net 7 Days',
            'net15' => 'Net 15 Days',
            'net30' => 'Net 30 Days',
            'net60' => 'Net 60 Days',
            'custom' => 'Custom…',
        ] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('payment_terms', $supplier->payment_terms) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="customTermsField"
                        style="display:{{ old('payment_terms', $supplier->payment_terms) === 'custom' ? 'block' : 'none' }};">
                        <label class="vd-label">Custom Terms</label>
                        <input type="text" name="payment_terms_custom"
                            value="{{ old('payment_terms_custom', $supplier->payment_terms_custom) }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Credit Limit (₱)</label>
                        <input type="number" name="credit_limit"
                            value="{{ old('credit_limit', $supplier->credit_limit) }}" class="vd-input" min="0"
                            step="0.01" placeholder="Leave blank for no limit">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Status</label>
                        <select name="status" class="vd-select">
                            <option value="active"
                                {{ old('status', $supplier->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive"
                                {{ old('status', $supplier->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked"
                                {{ old('status', $supplier->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_preferred" id="isPreferred"
                                value="1" {{ old('is_preferred', $supplier->is_preferred) ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="isPreferred" style="font-size:.82rem;">
                                <i class="bi bi-star-fill me-1" style="color:#f59e0b;"></i>
                                Preferred Supplier
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="vd-label">Internal Notes</label>
                        <textarea name="notes" rows="3" class="vd-input" style="resize:vertical;">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('vendor.suppliers.show', $supplier) }}" class="vd-btn vd-btn--ghost">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="vd-btn vd-btn--primary" id="saveBtn" style="min-width:160px;">
                <i class="bi bi-floppy-fill me-1"></i> Save Changes
            </button>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        const portalSuppliers = @json($availablePortalSuppliers);

        // ── Portal picker toggle ─────────────────────────────────────────────────
        function togglePortalPicker() {
            const section = document.getElementById('portalPickerSection');
            const btn = document.getElementById('showPortalPickerBtn');
            const isHidden = section.style.display === 'none';
            section.style.display = isHidden ? 'block' : 'none';
            if (btn) btn.innerHTML = isHidden ?
                '<i class="bi bi-x-lg me-1"></i> Cancel' :
                '<i class="bi bi-link-45deg me-1"></i> Link a Portal Account';
        }

        function unlinkPortal() {
            document.getElementById('supplierIdInput').value = '';
            // Reload to reflect the unlinked state cleanly after save
            document.getElementById('portalPickerSection').style.display = 'block';
        }

        // ── Portal search ────────────────────────────────────────────────────────
        document.getElementById('portalSearch')?.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const list = document.getElementById('portalResultsList');

            if (!term) {
                list.style.display = 'none';
                return;
            }

            const matches = portalSuppliers.filter(s =>
                s.company_name.toLowerCase().includes(term) ||
                (s.city ?? '').toLowerCase().includes(term)
            );

            list.innerHTML = matches.length ?
                matches.map(s => `
                <div class="portal-result-item"
                    onclick="selectPortal(${s.id}, '${s.company_name.replace(/'/g,"\\'")}')">
                    <div class="fw-700">${s.company_name}</div>
                    <div style="font-size:.72rem;color:var(--tx-muted);">${s.contact_person ?? ''} · ${s.city ?? ''}</div>
                </div>`).join('') :
                `<div class="portal-result-item text-muted">No results found.</div>`;

            list.style.display = 'block';
        });

        function selectPortal(id, name) {
            document.getElementById('supplierIdInput').value = id;
            document.getElementById('portalSearch').value = '';
            document.getElementById('portalResultsList').style.display = 'none';

            const badge = document.getElementById('portalSelectedName');
            if (badge) {
                badge.textContent = name;
                badge.closest('.mt-2').style.display = 'block';
            }
        }

        // ── Custom payment terms ─────────────────────────────────────────────────
        function toggleCustomTerms() {
            const show = document.getElementById('paymentTermsSelect').value === 'custom';
            document.getElementById('customTermsField').style.display = show ? 'block' : 'none';
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('#portalPickerSection'))
                document.getElementById('portalResultsList').style.display = 'none';
        });

        document.getElementById('editForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        });
    </script>
@endpush
