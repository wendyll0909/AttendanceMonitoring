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
    $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
    $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
    \Log::info('Rendering requests page', [
        'leaveRequests_count' => $leaveRequests->count(),
        'overtimeRequests_count' => $overtimeRequests->count()
    ]);
    $tab = $request->get('type', 'leave');
    return view('requests', compact('leaveRequests', 'overtimeRequests', 'tab'));
}

    // Leave Request Methods
 public function createLeaveRequest()
{
    try {
        $employees = Employee::active()->get();
        return view('partials.leave-request-form', compact('employees'));
    } catch (\Exception $e) {
        \Log::error('Failed to load leave request form: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Failed to load form'], 500);
    }
}
    public function storeLeaveRequest(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ]);

        $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date']);
        $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date']);

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
        $employees = Employee::active()->get();
        return view('partials.overtime-request-form', compact('employees'));
    }

    public function storeOvertimeRequest(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
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