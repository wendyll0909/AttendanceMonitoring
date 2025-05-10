<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function checkin(Request $request)
    {
        try {
            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();

            return view('attendance.checkin', compact('checkins'));
        } catch (\Exception $e) {
            Log::error('Check-in page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkin', [
                'checkins' => collect([]),
                'error' => 'Failed to load check-in page: ' . $e->getMessage()
            ], 500);
        }
    }

     public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // First validate and get employee ID
            $validation = $this->validateRequest($request);
            if ($validation['error']) {
                DB::commit();
                return response()->json(['error' => $validation['error']], $validation['status']);
            }
            
            $employeeId = $validation['employeeId'];
            $checkInMethod = $validation['method'];

            // Check for existing check-in with lock
            $existingCheckin = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->lockForUpdate()
                ->first();

            if ($existingCheckin) {
                DB::commit();
                return response()->json(['error' => 'Employee already checked in today'], 422);
            }

            // Process the check-in
            $now = now();
            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'date' => $now->toDateString(),
                'check_in_time' => $now->toTimeString(),
                'check_in_method' => $checkInMethod,
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

        return response()->view('attendance.checkin', [
            'checkins' => $checkins,
            'success' => 'Check-in recorded successfully'
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
                $checkOutMethod = 'manual'; // Used for Check-Out button
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
            // Clear check-out time and method
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
            // Delete check-in record
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
            return response()->view('attendance.checkin', [
                'checkins' => $checkins,
                'success' => 'Check-in deleted successfully'
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
}