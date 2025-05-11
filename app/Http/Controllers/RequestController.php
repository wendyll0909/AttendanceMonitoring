<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
public function index(Request $request)
{
    try {
        $tab = $request->query('tab', 'leave');
        
        \Log::info('Fetching leave requests');
        $leaveRequests = LeaveRequest::with('employee')->get();
        \Log::info('Leave requests fetched', ['count' => $leaveRequests->count()]);
        
        \Log::info('Fetching overtime requests');
        $overtimeRequests = OvertimeRequest::with('employee')->get();
        \Log::info('Overtime requests fetched', ['count' => $overtimeRequests->count()]);
        
        \Log::info('Fetching employees');
        $employees = Employee::all();
        \Log::info('Employees fetched', ['count' => $employees->count()]);
        
        if ($request->header('HX-Request')) {
            $response = view('requests', compact('leaveRequests', 'overtimeRequests', 'tab', 'employees'))->render();
            \Log::info('HTMX Requests Response', ['content' => substr($response, 0, 500)]);
            return $response;
        }
        
        return view('dashboard', [
            'content' => view('requests', compact('leaveRequests', 'overtimeRequests', 'tab', 'employees'))
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in Requests index', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        if ($request->header('HX-Request')) {
            return response('<div class="alert alert-danger">Server error: ' . e($e->getMessage()) . '</div>', 500);
        }
        throw $e;
    }
}
    // Leave Request Methods
public function createLeaveRequest()
{
    $employees = Employee::all();
    \Log::info('createLeaveRequest called', ['employee_count' => $employees->count()]);
    $response = view('partials.leave-request-form', compact('employees'))->render();
    \Log::info('createLeaveRequest response', ['content' => substr($response, 0, 1000)]); // Log first 1000 chars
    return $response;
}
   public function storeLeaveRequest(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|max:255',
    ]);

    LeaveRequest::create($validated + ['status' => 'pending']);

    if ($request->header('HX-Request')) {
        $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return view('partials.leave-requests', compact('leaveRequests'));
    }

    return redirect()->route('requests.index')
        ->with('success', 'Leave request submitted successfully');
}


    public function approveLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => 'approved']);

        if (request()->header('HX-Request')) {
            $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
            return view('partials.leave-requests', compact('leaveRequests'));
        }

        return response()->json(['success' => true]);
    }

    public function rejectLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => 'rejected']);

        if (request()->header('HX-Request')) {
            $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
            return view('partials.leave-requests', compact('leaveRequests'));
        }

        return response()->json(['success' => true]);
    }

    // Overtime Request Methods
 public function createOvertimeRequest()
{
    $employees = Employee::all();
    \Log::info('createOvertimeRequest called', ['employee_count' => $employees->count()]);
    $response = view('partials.overtime-request-form', compact('employees'))->render();
    \Log::info('createOvertimeRequest response', ['content' => substr($response, 0, 1000)]); // Log first 1000 chars
    return $response;
}

   public function storeOvertimeRequest(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
        'reason' => 'required|string|max:255',
    ]);

    OvertimeRequest::create($validated + ['status' => 'pending']);

    if ($request->header('HX-Request')) {
        $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return view('partials.overtime-requests', compact('overtimeRequests'));
    }

    return redirect()->route('requests.index')
        ->with('success', 'Overtime request submitted successfully');
}

    public function approveOvertimeRequest($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->update(['status' => 'approved']);

        if (request()->header('HX-Request')) {
            $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
            return view('partials.overtime-requests', compact('overtimeRequests'));
        }

        return response()->json(['success' => true]);
    }

    public function rejectOvertimeRequest($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->update(['status' => 'rejected']);

        if (request()->header('HX-Request')) {
            $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
            return view('partials.overtime-requests', compact('overtimeRequests'));
        }

        return response()->json(['success' => true]);
    }
}