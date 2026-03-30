<?php
// ════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/HrPayrollController.php
// ════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\HrEmployeeProfile;
use App\Models\HrPayrollItem;
use App\Models\HrPayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HrPayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payroll) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Payroll period list ────────────────────────────────────────────

    public function index()
    {
        $vendor  = $this->vendor();
        $periods = HrPayrollPeriod::forVendor($vendor->id)
            ->with('creator')
            ->latest()
            ->paginate(15);

        return view('vendor.hr.payroll.index', compact('periods'));
    }

    // ── Create period ──────────────────────────────────────────────────

    public function create()
    {
        return view('vendor.hr.payroll.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'frequency' => ['required', 'in:semi_monthly,monthly'],
            'date_from' => ['required', 'date'],
            'date_to'   => ['required', 'date', 'after:date_from'],
        ]);

        $vendor = $this->vendor();
        $period = $this->payroll->createPeriod(
            $vendor->id, $data['frequency'],
            $data['date_from'], $data['date_to'],
            auth()->id()
        );

        return redirect()
            ->route('vendor.hr.payroll.show', $period)
            ->with('success', "Payroll period \"{$period->period_name}\" created.");
    }

    // ── Show / compute ─────────────────────────────────────────────────

    public function show(HrPayrollPeriod $payrollPeriod)
    {
        $this->authorizeVendor($payrollPeriod);
        $payrollPeriod->load(['items.profile.department','items.profile.position']);

        $totalEmployees = HrEmployeeProfile::forVendor($payrollPeriod->vendor_id)->active()->count();

        return view('vendor.hr.payroll.show', compact('payrollPeriod','totalEmployees'));
    }

    // ── Compute (bulk or individual) ───────────────────────────────────

    public function compute(Request $request, HrPayrollPeriod $payrollPeriod)
    {
        $this->authorizeVendor($payrollPeriod);

        if (! $payrollPeriod->is_editable) {
            return back()->with('error', 'Payroll cannot be modified after approval.');
        }

        if ($request->filled('employee_id')) {
            $profile = HrEmployeeProfile::findOrFail($request->employee_id);
            $this->payroll->computeEmployee($payrollPeriod, $profile);
            return back()->with('success', "Payroll computed for {$profile->full_name}.");
        }

        // Bulk compute
        $items = $this->payroll->computeAll($payrollPeriod);
        return back()->with('success', count($items) . ' employee payrolls computed.');
    }

    // ── Submit for approval ────────────────────────────────────────────

    public function submitApproval(HrPayrollPeriod $payrollPeriod)
    {
        $this->authorizeVendor($payrollPeriod);
        $this->payroll->submitForApproval($payrollPeriod, auth()->id());
        return back()->with('success', 'Payroll submitted for approval.');
    }

    // ── Approve / reject ───────────────────────────────────────────────

    public function approve(Request $request, HrPayrollPeriod $payrollPeriod)
    {
        $this->authorizeVendor($payrollPeriod);

        if ($request->input('action') === 'reject') {
            $request->validate(['notes' => ['required','string','max:500']]);
            $this->payroll->reject($payrollPeriod, auth()->id(), $request->notes);
            return back()->with('error', 'Payroll rejected.');
        }

        $this->payroll->approve($payrollPeriod, auth()->id(), $request->notes);
        return back()->with('success', 'Payroll approved and payslips generated.');
    }

    // ── Export to Excel ────────────────────────────────────────────────

    public function export(HrPayrollPeriod $payrollPeriod)
    {
        $this->authorizeVendor($payrollPeriod);
        $payrollPeriod->load('items.profile');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');

        // Headers
        $headers = [
            'A1'=>'Employee #','B1'=>'Full Name','C1'=>'Department',
            'D1'=>'Basic Pay','E1'=>'Overtime','F1'=>'Allowances','G1'=>'Gross Pay',
            'H1'=>'Late Ded.','I1'=>'Undertime','J1'=>'Absent Ded.',
            'K1'=>'SSS','L1'=>'PhilHealth','M1'=>'Pag-IBIG','N1'=>'W/Tax',
            'O1'=>'Total Ded.','P1'=>'NET PAY',
            'Q1'=>'Days Worked','R1'=>'Days Absent','S1'=>'OT Hours',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Bold headers
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        $sheet->getStyle('A1:S1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('157a30');
        $sheet->getStyle('A1:S1')->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($payrollPeriod->items as $item) {
            $sheet->fromArray([
                $item->profile->employee_number ?? 'N/A',
                $item->profile->full_name,
                $item->profile->department?->name ?? '—',
                $item->basic_pay,
                $item->overtime_pay,
                $item->allowances,
                $item->gross_pay,
                $item->late_deduction,
                $item->undertime_deduction,
                $item->absent_deduction,
                $item->sss_contribution,
                $item->philhealth_contribution,
                $item->pagibig_contribution,
                $item->withholding_tax,
                $item->total_deductions,
                $item->net_pay,
                $item->days_worked,
                $item->days_absent,
                $item->hours_overtime,
            ], null, "A{$row}");
            $row++;
        }

        // Totals
        $totRow = $row;
        $sheet->setCellValue("A{$totRow}", 'TOTAL');
        $sheet->setCellValue("D{$totRow}", $payrollPeriod->items->sum('basic_pay'));
        $sheet->setCellValue("G{$totRow}", $payrollPeriod->items->sum('gross_pay'));
        $sheet->setCellValue("P{$totRow}", $payrollPeriod->items->sum('net_pay'));
        $sheet->getStyle("A{$totRow}:S{$totRow}")->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Format currency columns
        $currencyFormat = '#,##0.00';
        foreach (['D','E','F','G','H','I','J','K','L','M','N','O','P'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$row}")
                ->getNumberFormat()->setFormatCode($currencyFormat);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'Payroll_' . str_replace([' ','–',','], '_', $payrollPeriod->period_name) . '.xlsx';
        $tmpPath  = sys_get_temp_dir() . '/' . $filename;
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    private function authorizeVendor(HrPayrollPeriod $p): void
    {
        if ($p->vendor_id !== $this->vendor()->id) abort(403);
    }
}





