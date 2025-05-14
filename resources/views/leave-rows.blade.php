@forelse ($leaveRequests as $request)
    <tr>
        <td>{{ $request->employee->full_name ?? 'N/A' }}</td>
        <td>{{ $request->start_date->format('Y-m-d') }}</td>
        <td>{{ $request->end_date->format('Y-m-d') }}</td>
        <td>{{ $request->reason }}</td>
        <td @class([
            'badge bg-success text-white' => $request->status === \App\Models\LeaveRequest::STATUS_APPROVED,
            'badge bg-danger text-white' => $request->status === \App\Models\LeaveRequest::STATUS_REJECTED,
            'badge bg-warning text-dark' => $request->status === \App\Models\LeaveRequest::STATUS_PENDING,
        ])>{{ ucfirst($request->status) }}</td>
        <td>
            @if ($request->status === \App\Models\LeaveRequest::STATUS_PENDING)
                <button class="btn btn-primary btn-sm action-btn" 
                        hx-post="{{ route('requests.leave.approve', $request->leave_request_id) }}" 
                        hx-target="#leave-requests-table-body"
                        hx-swap="outerHTML"
                        hx-vals='{"_token": "{{ csrf_token() }}"}'>Approve</button>
                <button class="btn btn-danger btn-sm action-btn" 
                        hx-post="{{ route('requests.leave.reject', $request->leave_request_id) }}" 
                        hx-target="#leave-requests-table-body"
                        hx-swap="outerHTML"
                        hx-vals='{"_token": "{{ csrf_token() }}"}'>Reject</button>
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center">No leave requests found</td></tr>
@endforelse
{{ $leaveRequests->appends(request()->query())->links() }}