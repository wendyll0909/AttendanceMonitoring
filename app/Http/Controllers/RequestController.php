<?php

   namespace App\Http\Controllers;

   use App\Models\Employee;
   use App\Models\LeaveRequest;
   use App\Models\OvertimeRequest;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Log;
   use Illuminate\Database\QueryException;
   use Illuminate\Validation\ValidationException;

   class RequestController extends Controller
   {
       public function index(Request $request)
{
    try {
        Log::info('Requests index method called', [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'headers' => $request->headers->all()
        ]);

        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $leaveQuery = LeaveRequest::with('employee');
        $overtimeQuery = OvertimeRequest::with('employee');

        if ($search) {
            $leaveQuery->whereHas('employee', function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%");
            });
            $overtimeQuery->whereHas('employee', function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $leaveQuery->where('status', $status);
            $overtimeQuery->where('status', $status);
        }

        $leaveRequests = $leaveQuery->latest()->paginate(10);
        $overtimeRequests = $overtimeQuery->latest()->paginate(10);

        Log::info('Requests data fetched', [
            'leave_count' => $leaveRequests->count(),
            'overtime_count' => $overtimeRequests->count(),
            'is_htmx' => $request->header('HX-Request') ? true : false
        ]);

        return response()->view('requests', [
            'leaveRequests' => $leaveRequests,
            'overtimeRequests' => $overtimeRequests,
            'search' => $search,
            'status' => $status
        ])->header('Content-Type', 'text/html; charset=UTF-8');

    } catch (\Exception $e) {
        Log::error('Error in RequestController@index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        return response()->view('requests', [
            'leaveRequests' => collect(),
            'overtimeRequests' => collect(),
            'error' => 'Failed to load requests: ' . $e->getMessage()
        ], 500)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}

       public function createLeaveRequest()
       {
           $employees = Employee::active()->get();
           return view('partials.leave-request-form', compact('employees'));
       }

       public function storeLeaveRequest(Request $request)
{
    try {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ]);

        LeaveRequest::create($validated);

        // For HTMX requests
        if ($request->header('HX-Request')) {
            $leaveRequests = LeaveRequest::with('employee')->latest()->paginate(10);
            $overtimeRequests = OvertimeRequest::with('employee')->latest()->paginate(10);
            
            return view('requests', compact('leaveRequests', 'overtimeRequests'))
                ->with('success', 'Leave request created successfully.');
        }

        return redirect()->route('requests.index')
            ->with('success', 'Leave request created successfully.');
    } catch (ValidationException $e) {
        Log::error('Validation error storing leave request: ' . $e->getMessage());
        return response()->view('partials.leave-request-form', [
            'employees' => Employee::active()->get(),
            'error' => $e->validator->errors()->first()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error storing leave request: ' . $e->getMessage());
        return response()->view('partials.leave-request-form', [
            'employees' => Employee::active()->get(),
            'error' => 'Failed to create leave request. Please try again.'
        ], 500);
    }
}

       public function approveLeaveRequest($id)
       {
           try {
               $leaveRequest = LeaveRequest::findOrFail($id);
               $leaveRequest->update(['status' => 'approved']);
               return redirect()->route('requests.index')->with('success', 'Leave request approved.');
           } catch (\Exception $e) {
               Log::error('Error approving leave request: ' . $e->getMessage());
               return response()->json(['error' => 'Failed to approve leave request.'], 500);
           }
       }

       public function rejectLeaveRequest($id)
       {
           try {
               $leaveRequest = LeaveRequest::findOrFail($id);
               $leaveRequest->update(['status' => 'rejected']);
               return redirect()->route('requests.index')->with('success', 'Leave request rejected.');
           } catch (\Exception $e) {
               Log::error('Error rejecting leave request: ' . $e->getMessage());
               return response()->json(['error' => 'Failed to reject leave request.'], 500);
           }
       }

       public function createOvertimeRequest()
       {
           $employees = Employee::active()->get();
           return view('partials.overtime-request-form', compact('employees'));
       }

       public function storeOvertimeRequest(Request $request)
{
    try {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'required|string|max:255',
        ]);

        OvertimeRequest::create($validated);

        // For HTMX requests
        if ($request->header('HX-Request')) {
            $leaveRequests = LeaveRequest::with('employee')->latest()->paginate(10);
            $overtimeRequests = OvertimeRequest::with('employee')->latest()->paginate(10);
            
            return view('requests', compact('leaveRequests', 'overtimeRequests'))
                ->with('success', 'Overtime request created successfully.');
        }

        return redirect()->route('requests.index')
            ->with('success', 'Overtime request created successfully.');

    } catch (ValidationException $e) {
        return response()->view('partials.overtime-request-form', [
            'employees' => Employee::active()->get(),
            'error' => $e->validator->errors()->first()
        ], 422);
    } catch (\Exception $e) {
        return response()->view('partials.overtime-request-form', [
            'employees' => Employee::active()->get(),
            'error' => 'Failed to create overtime request. Please try again.'
        ], 500);
    }
}

       public function approveOvertimeRequest($id)
       {
           try {
               $overtimeRequest = OvertimeRequest::findOrFail($id);
               $overtimeRequest->update(['status' => 'approved']);
               return redirect()->route('requests.index')->with('success', 'Overtime request approved.');
           } catch (\Exception $e) {
               Log::error('Error approving overtime request: ' . $e->getMessage());
               return response()->json(['error' => 'Failed to approve overtime request.'], 500);
           }
       }

       public function rejectOvertimeRequest($id)
       {
           try {
               $overtimeRequest = OvertimeRequest::findOrFail($id);
               $overtimeRequest->update(['status' => 'rejected']);
               return redirect()->route('requests.index')->with('success', 'Overtime request rejected.');
           } catch (\Exception $e) {
               Log::error('Error approving overtime request: ' . $e->getMessage());
               return response()->json(['error' => 'Failed to reject overtime request.'], 500);
           }
       }
   }