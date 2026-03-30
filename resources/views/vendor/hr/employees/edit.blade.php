@extends('layouts.vendor')
@section('title', 'Edit — ' . $profile->full_name)
@section('page-title', 'Edit Employee Record')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.employees.index') }}" class="text-secondary">HR Records</a>
    <span class="sep">›</span>
    <a href="{{ route('vendor.hr.employees.show', $profile) }}" class="text-secondary">{{ $profile->full_name }}</a>
    <span class="sep">›</span><span class="current">Edit</span>
@endsection

@push('styles')
    <style>
        .hr-tab-btn {
            padding: .45rem 1rem;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            color: var(--tx-muted);
            transition: all .15s;
        }

        .hr-tab-btn.active {
            border-bottom-color: var(--g-500);
            color: var(--g-700);
        }

        .hr-tab-pane {
            display: none;
        }

        .hr-tab-pane.active {
            display: block;
        }

        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .2rem;
        }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('vendor.hr.employees.update', $profile) }}" id="hrEditForm">
        @csrf @method('PUT')

        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
            <div class="hr-tab-btn active" onclick="showTab('personal',this)">Personal</div>
            <div class="hr-tab-btn" onclick="showTab('employment',this)">Employment</div>
            <div class="hr-tab-btn" onclick="showTab('compensation',this)">Compensation</div>
            <div class="hr-tab-btn" onclick="showTab('govids',this)">Gov IDs</div>
            <div class="d-flex gap-2 ms-auto">
                <a href="{{ route('vendor.hr.employees.show', $profile) }}" class="vd-btn vd-btn--ghost">Cancel</a>
                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save Changes</button>
            </div>
        </div>

        {{-- Personal --}}
        <div class="hr-tab-pane active vd-card" id="tab-personal">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-person"></i> Personal
                    Information</span></div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-3"><label class="vd-label">First Name <span class="req">*</span></label><input
                            type="text" name="first_name" value="{{ old('first_name', $profile->first_name) }}"
                            class="vd-input" required></div>
                    <div class="col-md-3"><label class="vd-label">Middle Name</label><input type="text"
                            name="middle_name" value="{{ old('middle_name', $profile->middle_name) }}" class="vd-input">
                    </div>
                    <div class="col-md-3"><label class="vd-label">Last Name <span class="req">*</span></label><input
                            type="text" name="last_name" value="{{ old('last_name', $profile->last_name) }}"
                            class="vd-input" required></div>
                    <div class="col-md-3"><label class="vd-label">Suffix</label><input type="text" name="suffix"
                            value="{{ old('suffix', $profile->suffix) }}" class="vd-input"></div>
                    <div class="col-md-3"><label class="vd-label">Sex</label>
                        <select name="sex" class="vd-select">
                            <option value="">Select…</option>
                            <option value="male" {{ old('sex', $profile->sex) === 'male' ? 'selected' : '' }}>Male
                            </option>
                            <option value="female" {{ old('sex', $profile->sex) === 'female' ? 'selected' : '' }}>Female
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="vd-label">Birth Date</label><input type="date" name="birth_date"
                            value="{{ old('birth_date', $profile->birth_date?->format('Y-m-d')) }}" class="vd-input"></div>
                    <div class="col-md-3"><label class="vd-label">Civil Status</label>
                        <select name="civil_status" class="vd-select">
                            <option value="">Select…</option>
                            @foreach (['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated', 'divorced' => 'Divorced'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('civil_status', $profile->civil_status) === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="vd-label">Blood Type</label>
                        <select name="blood_type" class="vd-select">
                            <option value="">Select…</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}"
                                    {{ old('blood_type', $profile->blood_type) === $bt ? 'selected' : '' }}>
                                    {{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="vd-label">Phone</label><input type="text" name="phone"
                            value="{{ old('phone', $profile->phone) }}" class="vd-input"></div>
                    <div class="col-md-4"><label class="vd-label">Personal Email</label><input type="email"
                            name="personal_email" value="{{ old('personal_email', $profile->personal_email) }}"
                            class="vd-input"></div>
                    <div class="col-md-4"><label class="vd-label">Employee Number</label><input type="text"
                            name="employee_number" value="{{ old('employee_number', $profile->employee_number) }}"
                            class="vd-input"></div>
                    <div class="col-md-6"><label class="vd-label">Permanent Address</label>
                        <textarea name="address_permanent" rows="2" class="vd-input">{{ old('address_permanent', $profile->address_permanent) }}</textarea>
                    </div>
                    <div class="col-md-6"><label class="vd-label">Present Address</label>
                        <textarea name="address_present" rows="2" class="vd-input">{{ old('address_present', $profile->address_present) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employment --}}
        <div class="hr-tab-pane vd-card" id="tab-employment">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-briefcase"></i> Employment</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="vd-label">Department</label>
                        <select name="department_id" class="vd-select">
                            <option value="">No Department</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}"
                                    {{ old('department_id', $profile->department_id) == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="vd-label">Position</label>
                        <select name="position_id" class="vd-select">
                            <option value="">No Position</option>
                            @foreach ($positions as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('position_id', $profile->position_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="vd-label">Employment Status <span class="req">*</span></label>
                        <select name="employment_status" class="vd-select" required>
                            @foreach (['probationary' => 'Probationary', 'regular' => 'Regular', 'contractual' => 'Contractual', 'part_time' => 'Part-Time', 'resigned' => 'Resigned', 'terminated' => 'Terminated'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('employment_status', $profile->employment_status) === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="vd-label">Employment Type</label>
                        <select name="employment_type" class="vd-select">
                            @foreach (['full_time' => 'Full-Time', 'part_time' => 'Part-Time', 'project_based' => 'Project-Based', 'seasonal' => 'Seasonal'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('employment_type', $profile->employment_type) === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="vd-label">Date Hired</label><input type="date"
                            name="date_hired" value="{{ old('date_hired', $profile->date_hired?->format('Y-m-d')) }}"
                            class="vd-input"></div>
                    <div class="col-md-4"><label class="vd-label">Date Regularized</label><input type="date"
                            name="date_regularized"
                            value="{{ old('date_regularized', $profile->date_regularized?->format('Y-m-d')) }}"
                            class="vd-input"></div>
                    <div class="col-md-3"><label class="vd-label">Hours/Day</label><input type="number"
                            name="work_hours_per_day"
                            value="{{ old('work_hours_per_day', $profile->work_hours_per_day) }}" class="vd-input"
                            min="1" max="24"></div>
                    <div class="col-md-3"><label class="vd-label">Days/Week</label><input type="number"
                            name="work_days_per_week"
                            value="{{ old('work_days_per_week', $profile->work_days_per_week) }}" class="vd-input"
                            min="1" max="7"></div>
                    <div class="col-12"><label class="vd-label">Notes</label>
                        <textarea name="notes" rows="2" class="vd-input">{{ old('notes', $profile->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compensation --}}
        <div class="hr-tab-pane vd-card" id="tab-compensation">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-currency-exchange"></i>
                    Compensation</span></div>
            <div class="vd-card__body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="vd-label">Monthly Rate (₱)</label><input type="number"
                            name="monthly_rate" value="{{ old('monthly_rate', $profile->monthly_rate) }}"
                            class="vd-input" step="0.01" id="monthlyRateInput" oninput="computeRates()"></div>
                    <div class="col-md-4"><label class="vd-label">Daily Rate (auto)</label><input type="text"
                            id="dailyRateDisplay" class="vd-input" disabled
                            value="{{ $profile->daily_rate ? '₱' . number_format($profile->daily_rate, 2) : '' }}"></div>
                    <div class="col-md-4"><label class="vd-label">Hourly Rate (auto)</label><input type="text"
                            id="hourlyRateDisplay" class="vd-input" disabled
                            value="{{ $profile->hourly_rate ? '₱' . number_format($profile->hourly_rate, 2) : '' }}"></div>
                    <div class="col-md-4"><label class="vd-label">Pay Frequency</label>
                        <select name="pay_frequency" class="vd-select">
                            <option value="semi_monthly"
                                {{ old('pay_frequency', $profile->pay_frequency) === 'semi_monthly' ? 'selected' : '' }}>
                                Semi-Monthly</option>
                            <option value="monthly"
                                {{ old('pay_frequency', $profile->pay_frequency) === 'monthly' ? 'selected' : '' }}>
                                Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="fw-700 mb-2" style="font-size:.875rem;">Allowances</div>
                <div id="allowanceRows">
                    @foreach ($profile->allowances ?? [] as $a)
                        <div class="row g-2 mb-2 allowance-row">
                            <div class="col-md-6"><input type="text" name="allowance_name[]" class="vd-input"
                                    value="{{ $a['name'] }}" placeholder="Allowance name"></div>
                            <div class="col-md-4"><input type="number" name="allowance_amount[]" class="vd-input"
                                    step="0.01" value="{{ $a['amount'] }}" placeholder="Amount ₱"></div>
                            <div class="col-md-2"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm"
                                    onclick="this.closest('.allowance-row').remove()"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm mt-1" onclick="addAllowanceRow()"><i
                        class="bi bi-plus-lg"></i> Add Allowance</button>
            </div>
        </div>

        {{-- Gov IDs --}}
        <div class="hr-tab-pane vd-card" id="tab-govids">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-id-card"></i> Government IDs</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-3"><label class="vd-label">SSS Number</label><input type="text"
                            name="sss_number" value="{{ old('sss_number', $profile->sss_number) }}" class="vd-input">
                    </div>
                    <div class="col-md-3"><label class="vd-label">PhilHealth Number</label><input type="text"
                            name="philhealth_number" value="{{ old('philhealth_number', $profile->philhealth_number) }}"
                            class="vd-input"></div>
                    <div class="col-md-3"><label class="vd-label">Pag-IBIG (HDMF)</label><input type="text"
                            name="pagibig_number" value="{{ old('pagibig_number', $profile->pagibig_number) }}"
                            class="vd-input"></div>
                    <div class="col-md-3"><label class="vd-label">TIN</label><input type="text" name="tin_number"
                            value="{{ old('tin_number', $profile->tin_number) }}" class="vd-input"></div>
                </div>
            </div>
        </div>

    </form>
@endsection

@push('scripts')
    <script>
        function showTab(name, el) {
            document.querySelectorAll('.hr-tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.hr-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            el.classList.add('active');
        }

        function computeRates() {
            const m = parseFloat(document.getElementById('monthlyRateInput').value) || 0;
            document.getElementById('dailyRateDisplay').value = m ? '₱' + (m / 26).toFixed(2) : '';
            document.getElementById('hourlyRateDisplay').value = m ? '₱' + (m / 26 / 8).toFixed(2) : '';
        }

        function addAllowanceRow() {
            document.getElementById('allowanceRows').insertAdjacentHTML('beforeend',
                `<div class="row g-2 mb-2 allowance-row">
            <div class="col-md-6"><input type="text" name="allowance_name[]" class="vd-input" placeholder="Allowance name"></div>
            <div class="col-md-4"><input type="number" name="allowance_amount[]" class="vd-input" step="0.01" placeholder="Amount ₱"></div>
            <div class="col-md-2"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm" onclick="this.closest('.allowance-row').remove()"><i class="bi bi-trash"></i></button></div>
        </div>`);
        }
    </script>
@endpush
