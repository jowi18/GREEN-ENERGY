<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HrDepartment;
use App\Models\HrEmergencyContact;
use App\Models\HrEmployeeProfile;
use App\Models\HrEmploymentHistory;
use App\Models\HrPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrEmployeeController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(HrEmployeeProfile $profile): void
    {
        if ($profile->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── List ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = HrEmployeeProfile::with(['department','position','employee.user'])
            ->forVendor($vendor->id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name',  'like', "%{$request->search}%")
                  ->orWhere('employee_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        } else {
            $query->where('is_archived', false);
        }

        $profiles    = $query->orderBy('last_name')->paginate(20)->withQueryString();
        $departments = HrDepartment::forVendor($vendor->id)->active()->get();

        $stats = [
            'total'        => HrEmployeeProfile::forVendor($vendor->id)->where('is_archived',false)->count(),
            'regular'      => HrEmployeeProfile::forVendor($vendor->id)->where('employment_status','regular')->count(),
            'probationary' => HrEmployeeProfile::forVendor($vendor->id)->where('employment_status','probationary')->count(),
            'archived'     => HrEmployeeProfile::forVendor($vendor->id)->where('is_archived',true)->count(),
        ];

        return view('vendor.hr.employees.index', compact('profiles','departments','stats'));
    }

    // ── Create ─────────────────────────────────────────────────────────

    public function create()
    {
        $vendor      = $this->vendor();
        $departments = HrDepartment::forVendor($vendor->id)->active()->get();
        $positions   = HrPosition::forVendor($vendor->id)->active()->get();

        // Employees without HR profiles yet
        $unlinkedEmployees = Employee::with('user')
            ->forVendor($vendor->id)
            ->whereDoesntHave('hrProfile')
            ->get();

        return view('vendor.hr.employees.create', compact('departments','positions','unlinkedEmployees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'       => ['required', 'exists:employees,id'],
            'employee_number'   => ['nullable', 'string', 'max:30'],
            'first_name'        => ['required', 'string', 'max:80'],
            'middle_name'       => ['nullable', 'string', 'max:80'],
            'last_name'         => ['required', 'string', 'max:80'],
            'suffix'            => ['nullable', 'string', 'max:10'],
            'sex'               => ['nullable', 'in:male,female'],
            'birth_date'        => ['nullable', 'date', 'before:today'],
            'birth_place'       => ['nullable', 'string', 'max:150'],
            'civil_status'      => ['nullable', 'in:single,married,widowed,separated,divorced'],
            'citizenship'       => ['nullable', 'string', 'max:80'],
            'blood_type'        => ['nullable', 'string', 'max:5'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'personal_email'    => ['nullable', 'email', 'max:120'],
            'address_permanent' => ['nullable', 'string'],
            'address_present'   => ['nullable', 'string'],
            'department_id'     => ['nullable', 'exists:hr_departments,id'],
            'position_id'       => ['nullable', 'exists:hr_positions,id'],
            'date_hired'        => ['nullable', 'date'],
            'employment_status' => ['required', 'in:probationary,regular,contractual,part_time,resigned,terminated'],
            'employment_type'   => ['required', 'in:full_time,part_time,project_based,seasonal'],
            'work_hours_per_day'=> ['nullable', 'integer', 'min:1', 'max:24'],
            'work_days_per_week'=> ['nullable', 'integer', 'min:1', 'max:7'],
            'monthly_rate'      => ['nullable', 'numeric', 'min:0'],
            'pay_frequency'     => ['required', 'in:semi_monthly,monthly'],
            'sss_number'        => ['nullable', 'string', 'max:30'],
            'philhealth_number' => ['nullable', 'string', 'max:30'],
            'pagibig_number'    => ['nullable', 'string', 'max:30'],
            'tin_number'        => ['nullable', 'string', 'max:30'],
            'notes'             => ['nullable', 'string'],
            // Emergency contacts
            'ec_name.*'         => ['nullable', 'string', 'max:150'],
            'ec_relationship.*' => ['nullable', 'string', 'max:60'],
            'ec_phone.*'        => ['nullable', 'string', 'max:30'],
            // Employment history
            'eh_company.*'      => ['nullable', 'string', 'max:150'],
            'eh_position.*'     => ['nullable', 'string', 'max:100'],
            'eh_date_from.*'    => ['nullable', 'date'],
            'eh_date_to.*'      => ['nullable', 'date'],
            // Allowances
            'allowance_name.*'  => ['nullable', 'string', 'max:100'],
            'allowance_amount.*'=> ['nullable', 'numeric', 'min:0'],
        ]);

        $vendor = $this->vendor();

        $profile = DB::transaction(function () use ($data, $vendor, $request) {
            // Build allowances array
            $allowances = [];
            if ($request->filled('allowance_name')) {
                foreach ($request->allowance_name as $i => $name) {
                    if ($name) {
                        $allowances[] = ['name'=>$name,'amount'=>(float)($request->allowance_amount[$i] ?? 0)];
                    }
                }
            }

            $profile = HrEmployeeProfile::create(array_merge($data, [
                'vendor_id'  => $vendor->id,
                'allowances' => $allowances ?: null,
            ]));

            // Emergency contacts
            foreach (($request->ec_name ?? []) as $i => $name) {
                if ($name) {
                    HrEmergencyContact::create([
                        'hr_profile_id' => $profile->id,
                        'name'          => $name,
                        'relationship'  => $request->ec_relationship[$i] ?? null,
                        'phone'         => $request->ec_phone[$i] ?? null,
                        'is_primary'    => $i === 0,
                    ]);
                }
            }

            // Employment history
            foreach (($request->eh_company ?? []) as $i => $company) {
                if ($company) {
                    HrEmploymentHistory::create([
                        'hr_profile_id'     => $profile->id,
                        'company'           => $company,
                        'position'          => $request->eh_position[$i] ?? null,
                        'date_from'         => $request->eh_date_from[$i] ?? null,
                        'date_to'           => $request->eh_date_to[$i] ?? null,
                        'reason_for_leaving'=> $request->eh_reason[$i] ?? null,
                    ]);
                }
            }

            return $profile;
        });

        return redirect()
            ->route('vendor.hr.employees.show', $profile)
            ->with('success', "{$profile->full_name}'s HR profile created.");
    }

    // ── Show ──────────────────────────────────────────────────────────

    public function show(HrEmployeeProfile $profile)
    {
        $this->authorize($profile);
        $profile->load([
            'department','position','employee.user','employee.roles',
            'emergencyContacts','employmentHistory',
            'leaveRequests.leaveType','overtimeRequests',
            'attendance' => fn($q) => $q->orderByDesc('attendance_date')->limit(30),
        ]);

        return view('vendor.hr.employees.show', compact('profile'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────

    public function edit(HrEmployeeProfile $profile)
    {
        $this->authorize($profile);
        $profile->load(['emergencyContacts','employmentHistory','department','position']);
        $vendor      = $this->vendor();
        $departments = HrDepartment::forVendor($vendor->id)->active()->get();
        $positions   = HrPosition::forVendor($vendor->id)->active()->get();
        return view('vendor.hr.employees.edit', compact('profile','departments','positions'));
    }

    public function update(Request $request, HrEmployeeProfile $profile)
    {
        $this->authorize($profile);

        $data = $request->validate([
            'employee_number'   => ['nullable','string','max:30'],
            'first_name'        => ['required','string','max:80'],
            'middle_name'       => ['nullable','string','max:80'],
            'last_name'         => ['required','string','max:80'],
            'suffix'            => ['nullable','string','max:10'],
            'sex'               => ['nullable','in:male,female'],
            'birth_date'        => ['nullable','date','before:today'],
            'civil_status'      => ['nullable','in:single,married,widowed,separated,divorced'],
            'phone'             => ['nullable','string','max:30'],
            'personal_email'    => ['nullable','email','max:120'],
            'address_permanent' => ['nullable','string'],
            'address_present'   => ['nullable','string'],
            'department_id'     => ['nullable','exists:hr_departments,id'],
            'position_id'       => ['nullable','exists:hr_positions,id'],
            'date_hired'        => ['nullable','date'],
            'date_regularized'  => ['nullable','date'],
            'employment_status' => ['required','in:probationary,regular,contractual,part_time,resigned,terminated'],
            'employment_type'   => ['required','in:full_time,part_time,project_based,seasonal'],
            'monthly_rate'      => ['nullable','numeric','min:0'],
            'pay_frequency'     => ['required','in:semi_monthly,monthly'],
            'sss_number'        => ['nullable','string','max:30'],
            'philhealth_number' => ['nullable','string','max:30'],
            'pagibig_number'    => ['nullable','string','max:30'],
            'tin_number'        => ['nullable','string','max:30'],
            'notes'             => ['nullable','string'],
            'allowance_name.*'  => ['nullable','string','max:100'],
            'allowance_amount.*'=> ['nullable','numeric','min:0'],
        ]);

        $allowances = [];
        foreach (($request->allowance_name ?? []) as $i => $name) {
            if ($name) $allowances[] = ['name'=>$name,'amount'=>(float)($request->allowance_amount[$i] ?? 0)];
        }

        $profile->update(array_merge($data, ['allowances' => $allowances ?: null]));

        return redirect()
            ->route('vendor.hr.employees.show', $profile)
            ->with('success', "{$profile->full_name}'s record updated.");
    }

    // ── Archive ───────────────────────────────────────────────────────

    public function archive(HrEmployeeProfile $profile)
    {
        $this->authorize($profile);
        $profile->update(['is_archived' => ! $profile->is_archived]);
        $msg = $profile->is_archived ? 'archived' : 'restored';
        return back()->with('success', "{$profile->full_name} has been {$msg}.");
    }
}
