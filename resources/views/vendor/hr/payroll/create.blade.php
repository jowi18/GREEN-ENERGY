@extends('layouts.vendor')
@section('title', 'New Payroll Period')
@section('page-title', 'New Payroll Period')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.payroll.index') }}" class="text-secondary">Payroll</a>
    <span class="sep">›</span><span class="current">New Period</span>
@endsection

@section('content')
    <div style="max-width:100%;">
        <div class="vd-card">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-plus-circle"></i> Create Payroll
                    Period</span></div>
            <div class="vd-card__body">
                <form method="POST" action="{{ route('vendor.hr.payroll.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="vd-label">Pay Frequency <span class="req">*</span></label>
                        <select name="frequency" id="frequencySelect"
                            class="vd-select @error('frequency') is-invalid @enderror" onchange="suggestDates()" required>
                            <option value="semi_monthly"
                                {{ old('frequency', 'semi_monthly') === 'semi_monthly' ? 'selected' : '' }}>Semi-Monthly
                                (1st–15th / 16th–end)</option>
                            <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        @error('frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="vd-label">Date From <span class="req">*</span></label>
                            <input type="date" name="date_from" id="dateFrom" value="{{ old('date_from') }}"
                                class="vd-input @error('date_from') is-invalid @enderror" required>
                            @error('date_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="vd-label">Date To <span class="req">*</span></label>
                            <input type="date" name="date_to" id="dateTo" value="{{ old('date_to') }}"
                                class="vd-input @error('date_to') is-invalid @enderror" required>
                            @error('date_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Quick date shortcuts --}}
                    <div class="mb-3">
                        <div class="text-muted mb-2" style="font-size:.78rem;font-weight:700;">Quick Date Shortcuts</div>
                        <div class="d-flex flex-wrap gap-2" id="shortcuts"></div>
                    </div>

                    <div
                        style="background:var(--g-50);border:1px solid var(--g-100);border-radius:var(--r-sm);padding:.75rem 1rem;font-size:.82rem;color:var(--g-700);margin-bottom:1.25rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        After creating the period, click <strong>Compute All</strong> to calculate payroll for all active
                        employees. You can then review and submit for approval.
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('vendor.hr.payroll.index') }}" class="vd-btn vd-btn--ghost">Cancel</a>
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-plus-lg"></i> Create
                            Period</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function suggestDates() {
            const freq = document.getElementById('frequencySelect').value;
            const now = new Date();
            const y = now.getFullYear(),
                m = now.getMonth();
            const shortcuts = document.getElementById('shortcuts');
            shortcuts.innerHTML = '';

            const addBtn = (label, from, to) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'vd-btn vd-btn--ghost vd-btn--sm';
                btn.textContent = label;
                btn.onclick = () => {
                    document.getElementById('dateFrom').value = from;
                    document.getElementById('dateTo').value = to;
                };
                shortcuts.appendChild(btn);
            };

            if (freq === 'semi_monthly') {
                const fmt = d => d.toISOString().split('T')[0];
                const p1from = new Date(y, m, 1),
                    p1to = new Date(y, m, 15);
                const p2from = new Date(y, m, 16),
                    p2to = new Date(y, m + 1, 0);
                const lm1from = new Date(y, m - 1, 1),
                    lm1to = new Date(y, m - 1, 15);
                const lm2from = new Date(y, m - 1, 16),
                    lm2to = new Date(y, m, 0);
                addBtn(`This month 1st–15th`, fmt(p1from), fmt(p1to));
                addBtn(`This month 16th–end`, fmt(p2from), fmt(p2to));
                addBtn(`Last month 1st–15th`, fmt(lm1from), fmt(lm1to));
                addBtn(`Last month 16th–end`, fmt(lm2from), fmt(lm2to));
            } else {
                const fmt = d => d.toISOString().split('T')[0];
                addBtn(`This month`, fmt(new Date(y, m, 1)), fmt(new Date(y, m + 1, 0)));
                addBtn(`Last month`, fmt(new Date(y, m - 1, 1)), fmt(new Date(y, m, 0)));
                addBtn(`2 months ago`, fmt(new Date(y, m - 2, 1)), fmt(new Date(y, m - 1, 0)));
            }
        }
        suggestDates();
        document.getElementById('frequencySelect').addEventListener('change', suggestDates);
    </script>
@endpush
