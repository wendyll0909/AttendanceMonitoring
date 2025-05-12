<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RequestController extends Controller
{
    public function index(Request $request)
{
    try {
        Log::info('Starting Requests index method');

        Log::info('Fetching leave requests');
        $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
        Log::info('Leave requests fetched', ['count' => $leaveRequests->count()]);

        Log::info('Fetching overtime requests');
        $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
        Log::info('Overtime requests fetched', ['count' => $overtimeRequests->count()]);

        Log::info('Fetching employees');
        $employees = Employee::all();
        Log::info('Employees fetched', ['count' => $employees->count()]);

        Log::info('Rendering requests view');
        $requestsView = view('requests', compact('leaveRequests', 'overtimeRequests', 'employees'));
        $requestsContent = $requestsView->render();
        Log::info('Requests view rendered', ['content' => substr($requestsContent, 0, 2000)]);

        if ($request->header('HX-Request')) {
            Log::info('Returning HTMX response for requests');
            return response($requestsContent)->header('Content-Type', 'text/html');
        }

        Log::info('Rendering dashboard view');
        return view('dashboard', ['content' => $requestsView]);
    } catch (QueryException $e) {
        Log::error('Database error in Requests index', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $errorMessage = '<div class="alert alert-danger">Database error: ' . e($e->getMessage()) . '</div>';
        if ($request->header('HX-Request')) {
            return response($errorMessage, 500)->header('Content-Type', 'text/html');
        }
        return response()->view('dashboard', ['content' => $errorMessage], 500);
    } catch (\Exception $e) {
        Log::error('General error in Requests index', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $errorMessage = '<div class="alert alert-danger">Server error: ' . e($e->getMessage()) . '</div>';
        if ($request->header('HX-Request')) {
            return response($errorMessage, 500)->header('Content-Type', 'text/html');
        }
        return response()->view('dashboard', ['content' => $errorMessage], 500);
    }
}

    public function createLeaveRequest()
    {
        try {
            Log::info('createLeaveRequest called');
            $employees = Employee::all();
            Log::info('Employees fetched for leave request form', ['count' => $employees->count()]);
            $response = view('partials.leave-request-form', compact('employees'))->render();
            Log::info('createLeaveRequest response', ['content' => substr($response, 0, 1000)]);
            return response($response)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('Error in createLeaveRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('<div class="alert alert-danger">Failed to load leave request form: ' . e($e->getMessage()) . '</div>', 500)
                ->header('Content-Type', 'text/html');
        }
    }

    public function storeLeaveRequest(Request $request)
{
    try {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ], [
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.'
        ]);

        LeaveRequest::create($validated + ['status' => 'pending']);

        if ($request->header('HX-Request')) {
            $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
            $response = view('partials.leave-requests', compact('leaveRequests'))->render();
            return response($response)->header('Content-Type', 'text/html');
        }

        return redirect()->route('requests.index')
            ->with('success', 'Leave request submitted successfully');
    } catch (\Exception $e) {
        Log::error('Error in storeLeaveRequest', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($request->header('HX-Request')) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException 
                    ? $e->errors() 
                    : ['general' => [$e->getMessage()]]
            ], 500);
        }
        
        return redirect()->route('requests.index')
            ->with('error', 'Failed to store leave request: ' . e($e->getMessage()));
    }
}


    public function approveLeaveRequest($id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            $leaveRequest->update(['status' => 'approved']);

            if (request()->header('HX-Request')) {
                $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
                $response = view('partials.leave-requests', compact('leaveRequests'))->render();
                Log::info('approveLeaveRequest HTMX response', ['content' => substr($response, 0, 500)]);
                return response($response)->header('Content-Type', 'text/html');
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in approveLeaveRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->header('HX-Request')) {
                return response('<div class="alert alert-danger">Failed to approve leave request: ' . e($e->getMessage()) . '</div>', 500)
                    ->header('Content-Type', 'text/html');
            }
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function rejectLeaveRequest($id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            $leaveRequest->update(['status' => 'rejected']);

            if (request()->header('HX-Request')) {
                $leaveRequests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
                $response = view('partials.leave-requests', compact('leaveRequests'))->render();
                Log::info('rejectLeaveRequest HTMX response', ['content' => substr($response, 0, 500)]);
                return response($response)->header('Content-Type', 'text/html');
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in rejectLeaveRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->header('HX-Request')) {
                return response('<div class="alert alert-danger">Failed to reject leave request: ' . e($e->getMessage()) . '</div>', 500)
                    ->header('Content-Type', 'text/html');
            }
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function createOvertimeRequest()
    {
        try {
            Log::info('createOvertimeRequest called');
            $employees = Employee::all();
            Log::info('Employees fetched for overtime request form', ['count' => $employees->count()]);
            $response = view('partials.overtime-request-form', compact('employees'))->render();
            Log::info('createOvertimeRequest response', ['content' => substr($response, 0, 1000)]);
            return response($response)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('Error in createOvertimeRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('<div class="alert alert-danger">Failed to load overtime request form: ' . e($e->getMessage()) . '</div>', 500)
                ->header('Content-Type', 'text/html');
        }
    }

    public function storeOvertimeRequest(Request $request)
{
    try {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'required|string|max:255',
        ], [
            'end_time.after' => 'The end time must be after the start time.'
        ]);

        OvertimeRequest::create($validated + ['status' => 'pending']);

        if ($request->header('HX-Request')) {
            $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
            $response = view('partials.overtime-requests', compact('overtimeRequests'))->render();
            return response($response)->header('Content-Type', 'text/html');
        }

        return redirect()->route('requests.index')
            ->with('success', 'Overtime request submitted successfully');
    } catch (\Exception $e) {
        Log::error('Error in storeOvertimeRequest', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($request->header('HX-Request')) {
    return response()->json([
        'message' => $e->getMessage(),
        'errors' => $e instanceof \Illuminate\Validation\ValidationException 
            ? $e->errors() 
            : ['general' => [$e->getMessage()]]
    ], 500);
}
        
        return redirect()->route('requests.index')
            ->with('error', 'Failed to store overtime request: ' . e($e->getMessage()));
    }
}

    public function approveOvertimeRequest($id)
    {
        try {
            $overtimeRequest = OvertimeRequest::findOrFail($id);
            $overtimeRequest->update(['status' => 'approved']);

            if (request()->header('HX-Request')) {
                $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
                $response = view('partials.overtime-requests', compact('overtimeRequests'))->render();
                Log::info('approveOvertimeRequest HTMX response', ['content' => substr($response, 0, 500)]);
                return response($response)->header('Content-Type', 'text/html');
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in approveOvertimeRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->header('HX-Request')) {
                return response('<div class="alert alert-danger">Failed to approve overtime request: ' . e($e->getMessage()) . '</div>', 500)
                    ->header('Content-Type', 'text/html');
            }
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function rejectOvertimeRequest($id)
    {
        try {
            $overtimeRequest = OvertimeRequest::findOrFail($id);
            $overtimeRequest->update(['status' => 'rejected']);

            if (request()->header('HX-Request')) {
                $overtimeRequests = OvertimeRequest::with('employee')->orderBy('created_at', 'desc')->get();
                $response = view('partials.overtime-requests', compact('overtimeRequests'))->render();
                Log::info('rejectOvertimeRequest HTMX response', ['content' => substr($response, 0, 500)]);
                return response($response)->header('Content-Type', 'text/html');
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in rejectOvertimeRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->header('HX-Request')) {
                return response('<div class="alert alert-danger">Failed to reject overtime request: ' . e($e->getMessage()) . '</div>', 500)
                    ->header('Content-Type', 'text/html');
            }
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}