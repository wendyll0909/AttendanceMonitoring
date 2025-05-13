<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\AttendanceBatch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF;

class PayrollController extends Controller
{
    public function index(Request $request)
{
    try {
        $selectedMonth = $request->query('month', now()->format('Y-m'));
        if (!Carbon::hasFormat($selectedMonth, 'Y-m')) {
            $selectedMonth = now()->format('Y-m');
            session()->flash('error', 'Invalid month format. Using current month.');
        }

        $startDate = Carbon::parse($selectedMonth)->startOfMonth()->toDateString();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth()->toDateString();

        $payrollData = Employee::with([
                'position', 
                'attendanceBatches' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('batch_date', [$startDate, $endDate]);
                }
            ])
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($startDate, $endDate) {
                $daysWorked = $employee->attendanceBatches->where('absent', false)->count();
                $baseSalaryDaily = $employee->position->base_salary ?? 0;
                $totalSalary = $daysWorked * $baseSalaryDaily;

                // Get approved overtime requests for the month
                $overtimePay = 0;
                if (method_exists($employee, 'overtimeRequests')) {
                    $overtimeRequests = OvertimeRequest::where('employee_id', $employee->employee_id)
                        ->where('status', OvertimeRequest::STATUS_APPROVED)
                        ->whereDate('start_time', '>=', $startDate)
                        ->whereDate('end_time', '<=', $endDate)
                        ->get();

                    $overtimePay = $overtimeRequests->sum(function ($overtime) {
                        $hours = $overtime->start_time->diffInHours($overtime->end_time);
                        return $hours * $overtime->overtime_rate;
                    });
                }

                return [
                    'employee' => $employee,
                    'days_worked' => $daysWorked,
                    'salary' => $totalSalary,
                    'overtime_pay' => $overtimePay,
                    'total_pay' => $totalSalary + $overtimePay,
                ];
            });

        return view('payroll.payroll', compact('payrollData', 'selectedMonth'));
    } catch (\Exception $e) {
        Log::error('Payroll page load failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->view('payroll.payroll', [
            'payrollData' => collect([]),
            'selectedMonth' => now()->format('Y-m'),
            'error' => 'Failed to load payroll page: ' . $e->getMessage()
        ], 500);
    }
}


    public function exportPdf(Request $request, $month)
{
    try {
        if (!Carbon::hasFormat($month, 'Y-m')) {
            throw new \Exception('Invalid month format');
        }

        $startDate = Carbon::parse($month)->startOfMonth()->toDateString();
        $endDate = Carbon::parse($month)->endOfMonth()->toDateString();

        $payrollData = Employee::with([
                'position', 
                'attendanceBatches' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('batch_date', [$startDate, $endDate]);
                }
            ])
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($startDate, $endDate) {
                $daysWorked = $employee->attendanceBatches->where('absent', false)->count();
                $baseSalaryDaily = $employee->position->base_salary ?? 0;
                $totalSalary = $daysWorked * $baseSalaryDaily;

                // Get approved overtime requests for the month
                $overtimePay = 0;
                if (method_exists($employee, 'overtimeRequests')) {
                    $overtimeRequests = OvertimeRequest::where('employee_id', $employee->employee_id)
                        ->where('status', OvertimeRequest::STATUS_APPROVED)
                        ->whereDate('start_time', '>=', $startDate)
                        ->whereDate('end_time', '<=', $endDate)
                        ->get();

                    $overtimePay = $overtimeRequests->sum(function ($overtime) {
                        $hours = $overtime->start_time->diffInHours($overtime->end_time);
                        return $hours * $overtime->overtime_rate;
                    });
                }

                return [
                    'employee' => $employee,
                    'days_worked' => $daysWorked,
                    'salary' => $totalSalary,
                    'overtime_pay' => $overtimePay,
                    'total_pay' => $totalSalary + $overtimePay,
                ];
            });

        $pdf = PDF::loadView('payroll.payroll-pdf', [
            'payrollData' => $payrollData,
            'selectedMonth' => $month
        ]);

        return $pdf->download("payroll_report_$month.pdf");
    } catch (\Exception $e) {
        Log::error('Payroll PDF export failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        session()->flash('error', 'Failed to export PDF: ' . $e->getMessage());
        return redirect()->route('payroll.index', ['month' => $month]);
    }
}
}