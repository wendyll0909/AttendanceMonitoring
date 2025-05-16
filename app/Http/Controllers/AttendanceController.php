<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF;

class AttendanceController extends Controller
{
    public function checkin(Request $request)
    {
        try {
            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();
            $checkInDeadline = session('check_in_deadline', '08:00:00');

            return view('attendance.checkin', compact('checkins', 'checkInDeadline'));
        } catch (\Exception $e) {
            Log::error('Check-in page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkin', [
                'checkins' => collect([]),
                'error' => 'Failed to load check-in page: ' . $e->getMessage(),
                'checkInDeadline' => '08:00:00'
            ], 500);
        }
    }

    public function updateDeadline(Request $request)
    {
        try {
            $request->validate([
                'check_in_deadline' => 'required|date_format:H:i',
            ]);

            $deadline = $request->check_in_deadline;
            session(['check_in_deadline' => $deadline]);

            \Log::info('Attempting to render view: checkin');
            $view = view('attendance.checkin', [
                'checkInDeadline' => $deadline,
                'checkins' => Attendance::whereDate('date', now()->toDateString())->get(),
                'success' => 'Check-in deadline updated successfully.'
            ]);
            \Log::info('View rendered successfully');
            return $view;
        } catch (ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return redirect()->route('attendance.checkin')->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error in updateDeadline: ' . $e->getMessage());
            return redirect()->route('attendance.checkin')->with('error', 'Failed to update deadline: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validation = $this->validateRequest($request);
            if ($validation['error']) {
                DB::commit();
                return response()->json(['error' => $validation['error']], $validation['status']);
            }
            
            $employeeId = $validation['employeeId'];
            $checkInMethod = $validation['method'];

            $existingCheckin = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->lockForUpdate()
                ->first();

            if ($existingCheckin) {
                DB::commit();
                return response()->json(['error' => 'Employee already checked in today'], 422);
            }

            $now = now();
            $checkInDeadlineTime = session('check_in_deadline', '08:00:00');
            $checkInDeadline = Carbon::today()->setTimeFromTimeString($checkInDeadlineTime);
            $isLate = $now->greaterThan($checkInDeadline);

            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'date' => $now->toDateString(),
                'check_in_time' => $now->toTimeString(),
                'check_in_method' => $checkInMethod,
                'check_in_deadline' => $checkInDeadlineTime,
                'late_status' => $isLate
            ]);

            DB::commit();

            return $this->successfulCheckinResponse();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check-in failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Check-in failed: ' . $e->getMessage()], 500);
        }
    }

    private function validateRequest(Request $request)
    {
        if ($request->has('qr_code')) {
            $qrCode = $request->input('qr_code');
            if (!str_starts_with($qrCode, 'EMP-')) {
                return ['error' => 'Invalid QR code format', 'status' => 422];
            }
            $employeeId = (int) str_replace('EMP-', '', $qrCode);
            $method = $request->input('method') === 'camera' ? 'qr_camera' : 'qr_upload';
        } elseif ($request->has('employee_id')) {
            $employeeId = $request->input('employee_id');
            $method = 'manual';
        } else {
            return ['error' => 'No employee selected or QR code provided', 'status' => 422];
        }

        $employee = Employee::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            return ['error' => 'Employee not found or inactive', 'status' => 404];
        }

        return [
            'employeeId' => $employeeId,
            'method' => $method,
            'error' => null,
            'status' => 200
        ];
    }

    private function successfulCheckinResponse()
    {
        $checkins = Attendance::with('employee')
            ->where('date', now()->toDateString())
            ->get();
        $checkInDeadline = session('check_in_deadline', '08:00:00');

        return response()->view('attendance.checkin', [
            'checkins' => $checkins,
            'success' => 'Check-in recorded successfully',
            'checkInDeadline' => $checkInDeadline
        ]);
    }

    public function checkout(Request $request)
    {
        try {
            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();

            return view('attendance.checkout', compact('checkins'));
        } catch (\Exception $e) {
            Log::error('Check-out page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkout', [
                'checkins' => collect([]),
                'error' => 'Failed to load check-out page: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkoutStore(Request $request)
    {
        try {
            $employeeId = null;
            $checkOutMethod = null;

            if ($request->has('qr_code')) {
                $qrCode = $request->input('qr_code');
                if (!str_starts_with($qrCode, 'EMP-')) {
                    return response()->json(['error' => 'Invalid QR code'], 422);
                }
                $employeeId = (int) str_replace('EMP-', '', $qrCode);
                $checkOutMethod = $request->input('method') === 'qr_camera' ? 'qr_camera' : 'qr_upload';
            } elseif ($request->has('employee_id')) {
                $employeeId = $request->input('employee_id');
                $checkOutMethod = 'manual';
            } else {
                return response()->json(['error' => 'No employee selected or QR code provided'], 422);
            }

            $employee = Employee::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found or inactive'], 404);
            }

            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['error' => 'Employee has not checked in today'], 422);
            }

            if ($attendance->check_out_time) {
                return response()->json(['error' => 'Employee has already checked out today'], 422);
            }

            $attendance->update([
                'check_out_time' => now()->toTimeString(),
                'check_out_method' => $checkOutMethod,
            ]);

            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();

            session()->flash('success', 'Check-out recorded successfully');

            return response()->view('attendance.checkout', [
                'checkins' => $checkins
            ]);
        } catch (\Exception $e) {
            Log::error('Check-out failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Check-out failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $attendance = Attendance::findOrFail($id);
            $employeeId = $attendance->employee_id;
            $date = $attendance->date;

            if ($attendance->check_out_time) {
                $attendance->update([
                    'check_out_time' => null,
                    'check_out_method' => null,
                ]);
                Log::info('Check-out cleared', [
                    'attendance_id' => $id,
                    'employee_id' => $employeeId,
                    'date' => $date
                ]);
                DB::commit();

                $checkins = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->get();
                return response()->view('attendance.checkout', [
                    'checkins' => $checkins,
                    'success' => 'Check-out cleared successfully'
                ]);
            } else {
                $attendance->delete();
                Log::info('Check-in record deleted', [
                    'attendance_id' => $id,
                    'employee_id' => $employeeId,
                    'date' => $date
                ]);
                DB::commit();

                $checkins = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->get();
                $checkInDeadline = session('check_in_deadline', '08:00:00');
                return response()->view('attendance.checkin', [
                    'checkins' => $checkins,
                    'success' => 'Check-in deleted successfully',
                    'checkInDeadline' => $checkInDeadline
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check-in/check-out deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to delete or clear record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function check($employeeId)
    {
        try {
            $hasCheckin = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->exists();

            return response()->json(['hasCheckin' => $hasCheckin]);
        } catch (\Exception $e) {
            Log::error('Check-in status check failed', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['hasCheckin' => false, 'error' => 'Failed to check status'], 500);
        }
    }

    public function record(Request $request)
{
    try {
        $searchPresent = $request->query('search_present');
        $searchAbsent = $request->query('search_absent');

        // Present employees (with check-in data, only active employees)
        $present = Attendance::with('employee.position')
            ->where('date', now()->toDateString())
            ->whereHas('employee', function ($query) {
                $query->where('status', 'active');
            })
            ->when($searchPresent, function ($query, $search) {
                return $query->whereHas('employee', function ($q) use ($search) {
                    $q->whereRaw("CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) LIKE ?", ["%$search%"]);
                });
            })
            ->paginate(1000, ['*'], 'present_page');

        // Absent employees (already filters for active employees)
        $absent = Employee::with('position')
            ->where('status', 'active')
            ->whereNotIn('employee_id', function ($query) {
                $query->select('employee_id')
                    ->from('attendances')
                    ->where('date', now()->toDateString());
            })
            ->when($searchAbsent, function ($query, $search) {
                return $query->whereRaw("CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) LIKE ?", ["%$search%"]);
            })
            ->paginate(1000, ['*'], 'absent_page');

        return view('attendance.record-attendance', compact('present', 'absent'));
    } catch (\Exception $e) {
        Log::error('Record attendance page load failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->view('attendance.record-attendance', [
            'present' => collect([]),
            'absent' => collect([]),
            'error' => 'Failed to load record attendance page: ' . $e->getMessage()
        ], 500);
    }
}

    public function clear(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get today's date
            $batchDate = now()->toDateString();

            // Delete existing attendance batch records for the same batch_date
            AttendanceBatch::where('batch_date', $batchDate)->delete();

            // Get today's attendance records
            $attendances = Attendance::where('date', $batchDate)->get();
            
            // Get all active employees
            $allEmployees = Employee::where('status', 'active')->pluck('employee_id')->toArray();
            $presentEmployeeIds = $attendances->pluck('employee_id')->toArray();
            $absentEmployeeIds = array_diff($allEmployees, $presentEmployeeIds);

            // Save present employees to attendance_batches
            foreach ($attendances as $attendance) {
                AttendanceBatch::create([
                    'batch_date' => $batchDate,
                    'employee_id' => $attendance->employee_id,
                    'check_in_time' => $attendance->check_in_time,
                    'check_out_time' => $attendance->check_out_time,
                    'check_in_method' => $attendance->check_in_method,
                    'check_out_method' => $attendance->check_out_method,
                    'check_in_deadline' => $attendance->check_in_deadline,
                    'late_status' => $attendance->late_status,
                    'absent' => false,
                ]);
            }

            // Save absent employees to attendance_batches
            foreach ($absentEmployeeIds as $employeeId) {
                AttendanceBatch::create([
                    'batch_date' => $batchDate,
                    'employee_id' => $employeeId,
                    'absent' => true,
                ]);
            }

            // Clear today's attendance records
            Attendance::where('date', $batchDate)->delete();

            DB::commit();

            // Create an empty paginator for present
            $emptyPresent = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 1000, 1, [
                'path' => route('attendance.record'),
                'pageName' => 'present_page'
            ]);

            session()->flash('success', 'Attendance data cleared and saved as a batch successfully.');
            return response()->view('attendance.record-attendance', [
                'present' => $emptyPresent,
                'absent' => Employee::with('position')
                    ->where('status', 'active')
                    ->paginate(1000, ['*'], 'absent_page'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear attendance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to clear attendance data: ' . $e->getMessage());

            // Create an empty paginator for present in case of error
            $emptyPresent = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 100, 1, [
                'path' => route('attendance.record'),
                'pageName' => 'present_page'
            ]);

            return response()->view('attendance.record-attendance', [
                'present' => $emptyPresent,
                'absent' => Employee::with('position')
                    ->where('status', 'active')
                    ->paginate(1000, ['*'], 'absent_page'),
                'error' => 'Failed to clear attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function report(Request $request)
{
    try {
        $startDate = $request->query('start_date', now()->subDays(7)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        // Validate date formats
        if (!Carbon::hasFormat($startDate, 'Y-m-d') || !Carbon::hasFormat($endDate, 'Y-m-d')) {
            $startDate = now()->subDays(7)->toDateString();
            $endDate = now()->toDateString();
            session()->flash('error', 'Invalid date format. Using default date range.');
        }

        // Ensure start_date is not after end_date
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
            session()->flash('error', 'Start date was after end date. Dates have been swapped.');
        }

        // Present employees: Aggregate total days and late days, only active employees
        $present = AttendanceBatch::with('employee.position')
            ->whereBetween('batch_date', [$startDate, $endDate])
            ->where('absent', false)
            ->whereHas('employee', function ($query) {
                $query->where('status', 'active');
            })
            ->groupBy('employee_id')
            ->select('employee_id', 
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(late_status) as late_days'))
            ->get()
            ->map(function ($item) {
                return [
                    'employee' => $item->employee,
                    'total_days' => $item->total_days,
                    'late_days' => $item->late_days
                ];
            });

        // Absent employees: Aggregate absent days, only active employees
        $absent = AttendanceBatch::with('employee.position')
            ->whereBetween('batch_date', [$startDate, $endDate])
            ->where('absent', true)
            ->whereHas('employee', function ($query) {
                $query->where('status', 'active');
            })
            ->groupBy('employee_id')
            ->select('employee_id', 
                DB::raw('COUNT(*) as absent_days'))
            ->get()
            ->map(function ($item) {
                return [
                    'employee' => $item->employee,
                    'absent_days' => $item->absent_days
                ];
            });

        return view('attendance.attendance-report', compact('present', 'absent', 'startDate', 'endDate'));
    } catch (\Exception $e) {
        Log::error('Attendance report page load failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->view('attendance.attendance-report', [
            'present' => collect([]),
            'absent' => collect([]),
            'startDate' => now()->subDays(7)->toDateString(),
            'endDate' => now()->toDateString(),
            'error' => 'Failed to load attendance report: ' . $e->getMessage()
        ], 500);
    }
}

    public function exportPdf(Request $request)
{
    try {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Validate date formats
        if (!Carbon::hasFormat($startDate, 'Y-m-d') || !Carbon::hasFormat($endDate, 'Y-m-d')) {
            throw new \Exception('Invalid date format');
        }

        // Ensure start_date is not after end_date
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Fetch present employees, only active
        $present = AttendanceBatch::with('employee.position')
            ->whereBetween('batch_date', [$startDate, $endDate])
            ->where('absent', false)
            ->whereHas('employee', function ($query) {
                $query->where('status', 'active');
            })
            ->groupBy('employee_id')
            ->select('employee_id', 
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(late_status) as late_days'))
            ->get()
            ->map(function ($item) {
                return [
                    'employee' => $item->employee,
                    'total_days' => $item->total_days,
                    'late_days' => $item->late_days
                ];
            });

        // Fetch absent employees, only active
        $absent = AttendanceBatch::with('employee.position')
            ->whereBetween('batch_date', [$startDate, $endDate])
            ->where('absent', true)
            ->whereHas('employee', function ($query) {
                $query->where('status', 'active');
            })
            ->groupBy('employee_id')
            ->select('employee_id', 
                DB::raw('COUNT(*) as absent_days'))
            ->get()
            ->map(function ($item) {
                return [
                    'employee' => $item->employee,
                    'absent_days' => $item->absent_days
                ];
            });

        // Load the PDF view
        $pdf = PDF::loadView('attendance.report-pdf', [
            'present' => $present,
            'absent' => $absent,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download("attendance_report_{$startDate}_to_{$endDate}.pdf");
    } catch (\Exception $e) {
        Log::error('PDF export failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        session()->flash('error', 'Failed to export PDF: ' . $e->getMessage());
        return redirect()->route('attendance.report', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}
}