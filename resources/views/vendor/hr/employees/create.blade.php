@extends('layouts.vendor')
@section('title', 'HR — Add Employee Record')
@section('page-title', 'Add Employee Record')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.employees.index') }}" class="text-secondary">HR Records</a>
    <span class="sep">›</span><span class="current">Add</span>
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

        .rep-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: .75rem;
            align-items: start;
            margin-bottom: .75rem;
        }

        .form-hint {
            font-size: .72rem;
            color: var(--tx-muted);
            margin-top: .2rem;
        }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('vendor.hr.employees.store') }}" id="hrForm">
        @csrf
        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
            <div class="hr-tab-btn active" onclick="showTab('personal',this)">Personal Info</div>
            <div class="hr-tab-btn" onclick="showTab('employment',this)">Employment</div>
            <div class="hr-tab-btn" onclick="showTab('compensation',this)">Compensation</div>
            <div class="hr-tab-btn" onclick="showTab('govids',this)">Gov IDs</div>
            <div class="hr-tab-btn" onclick="showTab('emergency',this)">Emergency Contact</div>
            <div class="hr-tab-btn" onclick="showTab('history',this)">Work History</div>
            <button type="submit" class="vd-btn vd-btn--primary ms-auto"><i class="bi bi-floppy"></i> Save Record</button>
        </div>

        {{-- ── Personal Info ── --}}
        <div class="hr-tab-pane active vd-card" id="tab-personal">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-person"></i> Personal
                    Information</span></div>
            <div class="vd-card__body">

                {{-- Link to existing employee account --}}
                <div class="row g-3 mb-4 pb-3" style="border-bottom:1px solid var(--n-100);">
                    <div class="col-12">
                        <label class="vd-label">Link to Employee Account <span class="req">*</span></label>
                        <select name="employee_id" class="vd-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">Select employee account…</option>
                            @foreach ($unlinkedEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->user->name }} ({{ $emp->user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Links this HR record to an existing portal employee account.</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="vd-label">First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                            class="vd-input @error('first_name') is-invalid @enderror" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="vd-input">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                            class="vd-input @error('last_name') is-invalid @enderror" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Suffix</label>
                        <input type="text" name="suffix" value="{{ old('suffix') }}" class="vd-input"
                            placeholder="Jr., Sr., III">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Sex</label>
                        <select name="sex" class="vd-select">
                            <option value="">Select…</option>
                            <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Birth Date</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="vd-input"
                            max="{{ now()->subYears(15)->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Civil Status</label>
                        <select name="civil_status" class="vd-select">
                            <option value="">Select…</option>
                            @foreach (['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated', 'divorced' => 'Divorced'] as $v => $l)
                                <option value="{{ $v }}" {{ old('civil_status') === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Blood Type</label>
                        <select name="blood_type" class="vd-select">
                            <option value="">Select…</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>
                                    {{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Birth Place</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place') }}" class="vd-input"
                            placeholder="City, Province">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Citizenship</label>
                        <input type="text" name="citizenship" value="{{ old('citizenship', 'Filipino') }}"
                            class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Mobile / Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="vd-input"
                            placeholder="+63 9XX XXX XXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Personal Email</label>
                        <input type="email" name="personal_email" value="{{ old('personal_email') }}"
                            class="vd-input">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Employee Number</label>
                        <input type="text" name="employee_number" value="{{ old('employee_number') }}"
                            class="vd-input" placeholder="e.g. EMP-001">
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Permanent Address</label>
                        <textarea name="address_permanent" rows="2" class="vd-input" placeholder="Barangay, Municipality, Province">{{ old('address_permanent') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="vd-label">Present / Mailing Address</label>
                        <textarea name="address_present" rows="2" class="vd-input" placeholder="If same, write SAME">{{ old('address_present') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Employment ── --}}
        <div class="hr-tab-pane vd-card" id="tab-employment">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-briefcase"></i> Employment
                    Details</span></div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="vd-label">Department</label>
                        <select name="department_id" class="vd-select">
                            <option value="">No Department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Position / Job Title</label>
                        <select name="position_id" class="vd-select">
                            <option value="">No Position</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->id }}"
                                    {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Employment Status <span class="req">*</span></label>
                        <select name="employment_status"
                            class="vd-select @error('employment_status') is-invalid @enderror" required>
                            @foreach (['probationary' => 'Probationary', 'regular' => 'Regular', 'contractual' => 'Contractual', 'part_time' => 'Part-Time'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('employment_status', 'probationary') === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Employment Type <span class="req">*</span></label>
                        <select name="employment_type" class="vd-select" required>
                            @foreach (['full_time' => 'Full-Time', 'part_time' => 'Part-Time', 'project_based' => 'Project-Based', 'seasonal' => 'Seasonal'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('employment_type', 'full_time') === $v ? 'selected' : '' }}>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Date Hired</label>
                        <input type="date" name="date_hired" value="{{ old('date_hired') }}" class="vd-input">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Date Regularized</label>
                        <input type="date" name="date_regularized" value="{{ old('date_regularized') }}"
                            class="vd-input">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Work Hours/Day</label>
                        <input type="number" name="work_hours_per_day" value="{{ old('work_hours_per_day', 8) }}"
                            class="vd-input" min="1" max="24">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Work Days/Week</label>
                        <input type="number" name="work_days_per_week" value="{{ old('work_days_per_week', 5) }}"
                            class="vd-input" min="1" max="7">
                    </div>
                    <div class="col-12">
                        <label class="vd-label">Notes</label>
                        <textarea name="notes" rows="2" class="vd-input" placeholder="Internal HR notes…">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Compensation ── --}}
        <div class="hr-tab-pane vd-card" id="tab-compensation">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-currency-exchange"></i>
                    Compensation</span></div>
            <div class="vd-card__body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="vd-label">Monthly Rate (₱) <span class="req">*</span></label>
                        <input type="number" name="monthly_rate" value="{{ old('monthly_rate') }}" class="vd-input"
                            step="0.01" min="0" placeholder="0.00" id="monthlyRateInput"
                            oninput="computeRates()">
                        <div class="form-hint">Daily and hourly rates are auto-computed (÷26 and ÷8).</div>
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Daily Rate (auto)</label>
                        <input type="text" id="dailyRateDisplay" class="vd-input" disabled placeholder="—">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Hourly Rate (auto)</label>
                        <input type="text" id="hourlyRateDisplay" class="vd-input" disabled placeholder="—">
                    </div>
                    <div class="col-md-4">
                        <label class="vd-label">Pay Frequency <span class="req">*</span></label>
                        <select name="pay_frequency" class="vd-select" required>
                            <option value="semi_monthly"
                                {{ old('pay_frequency', 'semi_monthly') === 'semi_monthly' ? 'selected' : '' }}>Semi-Monthly
                                (1st & 15th)</option>
                            <option value="monthly" {{ old('pay_frequency') === 'monthly' ? 'selected' : '' }}>Monthly
                            </option>
                        </select>
                    </div>
                </div>

                <div class="fw-700 mb-2" style="font-size:.875rem;">Allowances</div>
                <div id="allowanceRows">
                    <div class="row g-2 mb-2 allowance-row">
                        <div class="col-md-6"><input type="text" name="allowance_name[]" class="vd-input"
                                placeholder="Allowance name (e.g. Transportation)"></div>
                        <div class="col-md-4"><input type="number" name="allowance_amount[]" class="vd-input"
                                step="0.01" placeholder="Amount ₱"></div>
                        <div class="col-md-2"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm"
                                onclick="removeRow(this)"><i class="bi bi-trash"></i></button></div>
                    </div>
                </div>
                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm mt-1" onclick="addAllowanceRow()"><i
                        class="bi bi-plus-lg"></i> Add Allowance</button>
            </div>
        </div>

        {{-- ── Gov IDs ── --}}
        <div class="hr-tab-pane vd-card" id="tab-govids">
            <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-id-card"></i> Government IDs</span>
            </div>
            <div class="vd-card__body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="vd-label">SSS Number</label>
                        <input type="text" name="sss_number" value="{{ old('sss_number') }}" class="vd-input"
                            placeholder="XX-XXXXXXX-X">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">PhilHealth Number</label>
                        <input type="text" name="philhealth_number" value="{{ old('philhealth_number') }}"
                            class="vd-input" placeholder="XXXX-XXXXXXXX-X">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">Pag-IBIG (HDMF)</label>
                        <input type="text" name="pagibig_number" value="{{ old('pagibig_number') }}"
                            class="vd-input" placeholder="XXXX-XXXX-XXXX">
                    </div>
                    <div class="col-md-3">
                        <label class="vd-label">TIN</label>
                        <input type="text" name="tin_number" value="{{ old('tin_number') }}" class="vd-input"
                            placeholder="XXX-XXX-XXX-XXX">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Emergency Contacts ── --}}
        <div class="hr-tab-pane vd-card" id="tab-emergency">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-telephone-outbound"></i> Emergency Contacts</span>
                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm" onclick="addEcRow()"><i
                        class="bi bi-plus-lg"></i> Add Contact</button>
            </div>
            <div class="vd-card__body">
                <div id="ecRows">
                    <div class="ec-row border rounded p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-4"><label class="vd-label">Full Name <span
                                        class="req">*</span></label><input type="text" name="ec_name[]"
                                    class="vd-input" placeholder="Contact name"></div>
                            <div class="col-md-3"><label class="vd-label">Relationship</label><input type="text"
                                    name="ec_relationship[]" class="vd-input" placeholder="e.g. Spouse, Parent"></div>
                            <div class="col-md-3"><label class="vd-label">Phone</label><input type="text"
                                    name="ec_phone[]" class="vd-input" placeholder="+63 9XX XXX XXXX"></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="button"
                                    class="vd-btn vd-btn--danger vd-btn--sm" onclick="removeRow(this)"><i
                                        class="bi bi-trash"></i></button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Work History ── --}}
        <div class="hr-tab-pane vd-card" id="tab-history">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-clock-history"></i> Previous Employment</span>
                <button type="button" class="vd-btn vd-btn--ghost vd-btn--sm" onclick="addEhRow()"><i
                        class="bi bi-plus-lg"></i> Add Previous Employer</button>
            </div>
            <div class="vd-card__body">
                <div id="ehRows">
                    <div class="eh-row border rounded p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-4"><label class="vd-label">Company Name</label><input type="text"
                                    name="eh_company[]" class="vd-input" placeholder="Company or Organization"></div>
                            <div class="col-md-3"><label class="vd-label">Position</label><input type="text"
                                    name="eh_position[]" class="vd-input" placeholder="Job title"></div>
                            <div class="col-md-2"><label class="vd-label">Date From</label><input type="date"
                                    name="eh_date_from[]" class="vd-input"></div>
                            <div class="col-md-2"><label class="vd-label">Date To</label><input type="date"
                                    name="eh_date_to[]" class="vd-input"></div>
                            <div class="col-md-1 d-flex align-items-end"><button type="button"
                                    class="vd-btn vd-btn--danger vd-btn--sm" onclick="removeRow(this)"><i
                                        class="bi bi-trash"></i></button></div>
                            <div class="col-12"><label class="vd-label">Reason for Leaving</label><input type="text"
                                    name="eh_reason[]" class="vd-input" placeholder="Optional"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vendor.hr.employees.index') }}" class="vd-btn vd-btn--ghost">Cancel</a>
            <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-floppy"></i> Save Employee
                Record</button>
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
            const d = m / 26;
            const h = d / 8;
            document.getElementById('dailyRateDisplay').value = m ? '₱' + d.toFixed(2) : '';
            document.getElementById('hourlyRateDisplay').value = m ? '₱' + h.toFixed(2) : '';
        }

        function addAllowanceRow() {
            document.getElementById('allowanceRows').insertAdjacentHTML('beforeend',
                `<div class="row g-2 mb-2 allowance-row">
            <div class="col-md-6"><input type="text" name="allowance_name[]" class="vd-input" placeholder="Allowance name"></div>
            <div class="col-md-4"><input type="number" name="allowance_amount[]" class="vd-input" step="0.01" placeholder="Amount ₱"></div>
            <div class="col-md-2"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></div>
        </div>`);
        }

        function addEcRow() {
            document.getElementById('ecRows').insertAdjacentHTML('beforeend',
                `<div class="ec-row border rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-4"><label class="vd-label">Full Name</label><input type="text" name="ec_name[]" class="vd-input" placeholder="Contact name"></div>
                <div class="col-md-3"><label class="vd-label">Relationship</label><input type="text" name="ec_relationship[]" class="vd-input"></div>
                <div class="col-md-3"><label class="vd-label">Phone</label><input type="text" name="ec_phone[]" class="vd-input"></div>
                <div class="col-md-2 d-flex align-items-end"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></div>
            </div>
        </div>`);
        }

        function addEhRow() {
            document.getElementById('ehRows').insertAdjacentHTML('beforeend',
                `<div class="eh-row border rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-4"><label class="vd-label">Company</label><input type="text" name="eh_company[]" class="vd-input"></div>
                <div class="col-md-3"><label class="vd-label">Position</label><input type="text" name="eh_position[]" class="vd-input"></div>
                <div class="col-md-2"><label class="vd-label">From</label><input type="date" name="eh_date_from[]" class="vd-input"></div>
                <div class="col-md-2"><label class="vd-label">To</label><input type="date" name="eh_date_to[]" class="vd-input"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="vd-btn vd-btn--danger vd-btn--sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></div>
                <div class="col-12"><label class="vd-label">Reason for Leaving</label><input type="text" name="eh_reason[]" class="vd-input"></div>
            </div>
        </div>`);
        }

        function removeRow(btn) {
            btn.closest('[class*="-row"], .border').remove();
        }
    </script>
@endpush
