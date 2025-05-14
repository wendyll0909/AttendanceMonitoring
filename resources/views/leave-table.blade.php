<div class="table-responsive" style="min-width: 600px;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="min-width: 120px;">Employee</th>
                <th style="min-width: 100px;">Start Date</th>
                <th style="min-width: 100px;">End Date</th>
                <th style="min-width: 150px;">Reason</th>
                <th style="min-width: 100px;">Status</th>
                <th style="min-width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody id="leave-requests-table-body">
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
        hx-swap="innerHTML"
        hx-vals='{"_token": "{{ csrf_token() }}"}'>Approve</button>

<button class="btn btn-danger btn-sm action-btn" 
        hx-post="{{ route('requests.leave.reject', $request->leave_request_id) }}" 
        hx-target="#leave-requests-table-body"
        hx-swap="innerHTML"
        hx-vals='{"_token": "{{ csrf_token() }}"}'>Reject</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No leave requests found</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $leaveRequests->appends(request()->query())->links() }}
</div>