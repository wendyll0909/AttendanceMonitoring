<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    public function index()
    {
        try {
            $leaveRequests = LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page');
            $overtimeRequests = OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page');
            $employees = Employee::active()->get();
            return view('requests', compact('leaveRequests', 'overtimeRequests', 'employees'));
        } catch (\Exception $e) {
            Log::error('Request index failed', ['error' => $e->getMessage()]);
            return response()->view('requests', [
                'leaveRequests' => collect([]),
                'overtimeRequests' => collect([]),
                'employees' => collect([]),
                'error' => 'Failed to load requests'
            ], 500);
        }
    }

    public function storeLeave(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,employee_id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->view('requests-content', [
                    'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                    'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                    'employees' => Employee::active()->get(),
                    'error' => $validator->errors()->first()
                ], 422);
            }

            LeaveRequest::create($request->only(['employee_id', 'start_date', 'end_date', 'reason']));

            return response()->view('requests-content', [
                'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                'employees' => Employee::active()->get(),
                'success' => 'Leave request created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Leave request store failed', ['error' => $e->getMessage()]);
            return response()->view('requests-content', [
                'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                'employees' => Employee::active()->get(),
                'error' => 'Failed to create leave request'
            ], 500);
        }
    }

    public function storeOvertime(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,employee_id',
                'start_time' => 'required|date_format:Y-m-d\TH:i|after:now',
                'end_time' => 'required|date_format:Y-m-d\TH:i|after:start_time',
                'reason' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->view('requests-content', [
                    'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                    'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                    'employees' => Employee::active()->get(),
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $employee = Employee::with('position')->findOrFail($request->employee_id);
            $baseSalary = $employee->position ? $employee->position->base_salary : 0;
            $overtimeRate = ($baseSalary / 8) * 1.25;

            OvertimeRequest::create([
                'employee_id' => $request->employee_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
                'overtime_rate' => $overtimeRate,
            ]);

            return response()->view('requests-content', [
                'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                'employees' => Employee::active()->get(),
                'success' => 'Overtime request created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Overtime request store failed', ['error' => $e->getMessage()]);
            return response()->view('requests-content', [
                'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
                'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
                'employees' => Employee::active()->get(),
                'error' => 'Failed to create overtime request'
            ], 500);
        }
    }

    public function searchLeave(Request $request)
{
    try {
        $query = LeaveRequest::with('employee');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee', function ($q2) use ($request) {
                    $q2->where('fname', 'like', '%' . $request->search . '%')
                       ->orWhere('lname', 'like', '%' . $request->search . '%')
                       ->orWhere('mname', 'like', '%' . $request->search . '%');
                })->orWhere('status', 'like', '%' . $request->search . '%');
            });
        }
        $leaveRequests = $query->paginate(10, ['*'], 'leave_page');
        return view('leave-rows', compact('leaveRequests'));
    } catch (\Exception $e) {
        Log::error('Leave request search failed', ['error' => $e->getMessage()]);
        return response()->view('leave-rows', [
            'leaveRequests' => collect([]),
            'error' => 'Failed to search leave requests'
        ], 500);
    }
}

    public function searchOvertime(Request $request)
    {
        try {
            $query = OvertimeRequest::with('employee');
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('employee', function ($q2) use ($request) {
                        $q2->where('fname', 'like', '%' . $request->search . '%')
                           ->orWhere('lname', 'like', '%' . $request->search . '%')
                           ->orWhere('mname', 'like', '%' . $request->search . '%');
                    })->orWhere('status', 'like', '%' . $request->search . '%');
                });
            }
            $overtimeRequests = $query->paginate(10, ['*'], 'overtime_page');
            return view('overtime-table', compact('overtimeRequests'));
        } catch (\Exception $e) {
            Log::error('Overtime request search failed', ['error' => $e->getMessage()]);
            return response()->view('overtime-table', [
                'overtimeRequests' => collect([]),
                'error' => 'Failed to search overtime requests'
            ], 500);
        }
    }

    public function approveLeave($id)
{
    try {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => LeaveRequest::STATUS_APPROVED]);
        return view('leave-rows', [
            'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page')
        ]);
    } catch (\Exception $e) {
        Log::error('Leave request approve failed', ['error' => $e->getMessage()]);
        return response()->view('leave-rows', [
            'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
            'error' => 'Failed to approve leave request'
        ], 500);
    }
}

    public function rejectLeave($id)
{
    try {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => LeaveRequest::STATUS_REJECTED]);
        return view('leave-rows', [
            'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page')
        ]);
    } catch (\Exception $e) {
        Log::error('Leave request reject failed', ['error' => $e->getMessage()]);
        return response()->view('leave-rows', [
            'leaveRequests' => LeaveRequest::with('employee')->paginate(10, ['*'], 'leave_page'),
            'error' => 'Failed to reject leave request'
        ], 500);
    }
}

    public function approveOvertime($id)
{
    try {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->update(['status' => OvertimeRequest::STATUS_APPROVED]);
        return view('overtime-rows', [
            'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page')
        ]);
    } catch (\Exception $e) {
        Log::error('Overtime request approve failed', ['error' => $e->getMessage()]);
        return response()->view('overtime-rows', [
            'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
            'error' => 'Failed to approve overtime request'
        ], 500);
    }
}

    public function rejectOvertime($id)
{
    try {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->update(['status' => OvertimeRequest::STATUS_REJECTED]);
        return view('overtime-rows', [
            'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page')
        ]);
    } catch (\Exception $e) {
        Log::error('Overtime request reject failed', ['error' => $e->getMessage()]);
        return response()->view('overtime-rows', [
            'overtimeRequests' => OvertimeRequest::with('employee')->paginate(10, ['*'], 'overtime_page'),
            'error' => 'Failed to reject overtime request'
        ], 500);
    }
}
}