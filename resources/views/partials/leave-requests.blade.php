@isset($leaveRequests)
<div class="card" id="leave-requests">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Leave Requests</h5>
        <button class="btn btn-primary" 
        data-bs-toggle="modal" 
        data-bs-target="#leaveRequestModal"
        hx-get="{{ route('leave-requests.create') }}" 
        hx-target="#leaveRequestModal .modal-body"
        hx-swap="innerHTML"
        hx-trigger="click"
        hx-indicator="#leaveRequestModal .htmx-indicator">
    Create Leave Request
</button>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveRequests as $request)
                <tr>
                    <td>{{ $request->employee ? e($request->employee->full_name) : 'Unknown' }}</td>
                    <td>
                        @if($request->start_date && $request->start_date instanceof \Illuminate\Support\Carbon)
                            {{ $request->start_date->format('M d, Y') }}
                        @else
                            Invalid Date
                        @endif
                    </td>
                    <td>
                        @if($request->end_date && $request->end_date instanceof \Illuminate\Support\Carbon)
                            {{ $request->end_date->format('M d, Y') }}
                        @else
                            Invalid Date
                        @endif
                    </td>
                    <td>{{ e(Str::limit($request->reason, 30)) }}</td>
                    <td>
                        <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td>
                        @if($request->status === 'pending')
                        <button class="btn btn-sm btn-success" 
                                id="approve-leave-{{ $request->leave_request_id }}"
                                hx-post="{{ route('leave-requests.approve', $request->leave_request_id) }}"
                                hx-target="#leave-requests"
                                hx-swap="innerHTML">
                            Approve
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                id="reject-leave-{{ $request->leave_request_id }}"
                                hx-post="{{ route('leave-requests.reject', $request->leave_request_id) }}"
                                hx-target="#leave-requests"
                                hx-swap="innerHTML">
                            Reject
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endisset