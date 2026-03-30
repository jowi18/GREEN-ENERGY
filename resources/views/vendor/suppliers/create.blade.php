@extends('layouts.vendor')
@section('title', 'Add Supplier')
@section('page-title', 'Suppliers')

@section('breadcrumb')
    <a href="{{ route('vendor.suppliers.index') }}" class="text-secondary">Supplier Directory</a>
    <span class="sep">›</span>
    <span class="current">Add Supplier</span>
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

        /* ── Source toggle ── */
        .source-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .source-btn {
            border: 2px solid var(--card-border);
            border-radius: var(--r-md);
            padding: .85rem .75rem;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
            user-select: none;
            background: var(--card-bg);
        }

        .source-btn:hover {
            border-color: var(--g-400);
            background: var(--n-50);
        }

        .source-btn.active {
            border-color: var(--g-500);
            background: var(--n-50);
        }

        /* ── Portal supplier search result ── */
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

        .portal-result-item.selected {
            background: var(--n-50);
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('vendor.suppliers.store') }}" id="supplierForm">
        @csrf

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

        {{-- ── Card 1: Source selection ───────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-building-add"></i> Supplier Source</span>
            </div>
            <div class="vd-card__body">
                <div class="source-toggle mb-3">
                    <div class="source-btn {{ old('source_type', 'manual') === 'manual' ? 'active' : '' }}"
                        id="srcManualBtn" onclick="setSource('manual')">
                        <i class="bi bi-person-vcard" style="font-size:1.5rem;color:var(--g-500);"></i>
                        <div class="fw-700 mt-1" style="font-size:.85rem;">Add Manually</div>
                        <div style="font-size:.72rem;color:var(--tx-muted);margin-top:.2rem;">
                            Supplier not on the platform
                        </div>
                    </div>
                    <div class="source-btn {{ old('source_type') === 'portal' ? 'active' : '' }}" id="srcPortalBtn"
                        onclick="setSource('portal')">
                        <i class="bi bi-link-45deg" style="font-size:1.5rem;color:#6366f1;"></i>
                        <div class="fw-700 mt-1" style="font-size:.85rem;">Link Portal Supplier</div>
                        <div style="font-size:.72rem;color:var(--tx-muted);margin-top:.2rem;">
                            Already registered on platform
                        </div>
                    </div>
                </div>
                <input type="hidden" name="source_type" id="sourceTypeInput" value="{{ old('source_type', 'manual') }}">

                {{-- Portal supplier picker (hidden by default) --}}
                <div id="portalPickerSection" style="display:{{ old('source_type') === 'portal' ? 'block' : 'none' }};">
                    <label class="vd-label">Search Portal Suppliers</label>
                    <input type="text" id="portalSearch" class="vd-input" placeholder="Type to search by name or city…">
                    <div id="portalResultsList"
                        style="border:1.5px solid var(--card-border);border-radius:var(--r-md);margin-top:.4rem;max-height:220px;overflow-y:auto;display:none;">
                    </div>
                    <input type="hidden" name="supplier_id" id="supplierIdInput" value="{{ old('supplier_id') }}">
                    <div id="portalSelectedBadge" class="mt-2" style="display:none;">
                        <span class="badge bg-primary" style="font-size:.78rem;padding:.4rem .75rem;">
                            <i class="bi bi-link-45deg me-1"></i>
                            <span id="portalSelectedName"></span>
                            <button type="button" onclick="clearPortalSelection()" class="btn-close btn-close-white ms-2"
                                style="font-size:.55rem;"></button>
                        </span>
                    </div>
                    <div class="form-hint">
                        Linking pre-fills contact info and enables Purchase Requests & Orders.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Contact Information ────────────────────────────────── --}}
        <div class="vd-card mb-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-person-lines-fill"></i> Contact Information</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="vd-label">
                            Company Name <span class="req">*</span>
                        </label>
                        <input type="text" name="company_name" id="fieldCompanyName" value="{{ old('company_name') }}"
                            class="vd-input @error('company_name') is-invalid @enderror"
                            placeholder="e.g. SunTech Solar Supplies Inc." required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Contact Person</label>
                        <input type="text" name="contact_person" id="fieldContactPerson"
                            value="{{ old('contact_person') }}" class="vd-input" placeholder="Full name">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Phone</label>
                        <input type="text" name="phone" id="fieldPhone" value="{{ old('phone') }}" class="vd-input"
                            placeholder="+63 9XX XXX XXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Email</label>
                        <input type="email" name="email" id="fieldEmail" value="{{ old('email') }}"
                            class="vd-input @error('email') is-invalid @enderror" placeholder="supplier@email.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Address</label>
                        <input type="text" name="address_line1" id="fieldAddress" value="{{ old('address_line1') }}"
                            class="vd-input" placeholder="Street, Barangay">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">City</label>
                        <input type="text" name="city" id="fieldCity" value="{{ old('city') }}"
                            class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Province</label>
                        <input type="text" name="province_state" id="fieldProvince"
                            value="{{ old('province_state') }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="vd-input">
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
                        <input type="text" name="account_number" value="{{ old('account_number') }}"
                            class="vd-input" placeholder="Your account # with them">
                        <div class="form-hint">Optional reference for your records.</div>
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
                                    {{ old('payment_terms', 'cod') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="customTermsField"
                        style="display:{{ old('payment_terms') === 'custom' ? 'block' : 'none' }};">
                        <label class="vd-label">Custom Terms</label>
                        <input type="text" name="payment_terms_custom" value="{{ old('payment_terms_custom') }}"
                            class="vd-input" placeholder="e.g. 50% upfront, 50% on delivery">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Credit Limit (₱)</label>
                        <input type="number" name="credit_limit" value="{{ old('credit_limit') }}" class="vd-input"
                            min="0" step="0.01" placeholder="Leave blank for no limit">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Status</label>
                        <select name="status" class="vd-select">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                            <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_preferred" id="isPreferred"
                                value="1" {{ old('is_preferred') ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="isPreferred" style="font-size:.82rem;">
                                <i class="bi bi-star-fill me-1" style="color:#f59e0b;"></i>
                                Mark as Preferred Supplier
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="vd-label">Internal Notes</label>
                        <textarea name="notes" rows="3" class="vd-input"
                            placeholder="Delivery preferences, special conditions, contact tips…" style="resize:vertical;">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('vendor.suppliers.index') }}" class="vd-btn vd-btn--ghost">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="vd-btn vd-btn--primary" id="saveBtn" style="min-width:160px;">
                <i class="bi bi-building-add me-1"></i> Add Supplier
            </button>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        // ── Source toggle ────────────────────────────────────────────────────────
        function setSource(type) {
            document.getElementById('sourceTypeInput').value = type;
            document.getElementById('srcManualBtn').classList.toggle('active', type === 'manual');
            document.getElementById('srcPortalBtn').classList.toggle('active', type === 'portal');
            document.getElementById('portalPickerSection').style.display = type === 'portal' ? 'block' : 'none';

            if (type === 'manual') clearPortalSelection();
        }

        // ── Portal supplier search ───────────────────────────────────────────────
        const portalSuppliers = @json($availablePortalSuppliers);

        document.getElementById('portalSearch').addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const list = document.getElementById('portalResultsList');

            if (!term) {
                list.style.display = 'none';
                return;
            }

            const matches = portalSuppliers.filter(s =>
                s.company_name.toLowerCase().includes(term) ||
                (s.city ?? '').toLowerCase().includes(term) ||
                (s.contact_person ?? '').toLowerCase().includes(term)
            );

            list.innerHTML = matches.length ?
                matches.map(s => `
                <div class="portal-result-item" onclick="selectPortalSupplier(${s.id}, '${s.company_name.replace(/'/g,"\\'")}', ${JSON.stringify(s)})">
                    <div class="fw-700">${s.company_name}</div>
                    <div style="font-size:.72rem;color:#6b7280;">${s.contact_person ?? ''} · ${s.city ?? ''}</div>
                </div>`).join('') :
                `<div class="portal-result-item text-muted">No results found.</div>`;

            list.style.display = 'block';
        });

        function selectPortalSupplier(id, name, data) {
            document.getElementById('supplierIdInput').value = id;
            document.getElementById('portalSearch').value = '';
            document.getElementById('portalResultsList').style.display = 'none';

            document.getElementById('portalSelectedName').textContent = name;
            document.getElementById('portalSelectedBadge').style.display = 'block';

            // Auto-fill form fields
            if (data.company_name) document.getElementById('fieldCompanyName').value = data.company_name;
            if (data.contact_person) document.getElementById('fieldContactPerson').value = data.contact_person;
            if (data.email) document.getElementById('fieldEmail').value = data.email;
            if (data.city) document.getElementById('fieldCity').value = data.city;
        }

        function clearPortalSelection() {
            document.getElementById('supplierIdInput').value = '';
            document.getElementById('portalSelectedBadge').style.display = 'none';
            document.getElementById('portalSelectedName').textContent = '';
            document.getElementById('portalSearch').value = '';
        }

        // ── Custom payment terms ─────────────────────────────────────────────────
        function toggleCustomTerms() {
            const show = document.getElementById('paymentTermsSelect').value === 'custom';
            document.getElementById('customTermsField').style.display = show ? 'block' : 'none';
        }

        // ── Double submit guard ──────────────────────────────────────────────────
        document.getElementById('supplierForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        });

        // Close portal results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#portalPickerSection')) {
                document.getElementById('portalResultsList').style.display = 'none';
            }
        });
    </script>
@endpush
