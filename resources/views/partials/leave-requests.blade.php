<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Leave Requests</h5>
        <button class="btn btn-primary" hx-get="{{ route('leave-requests.create') }}" hx-target="#leave-request-modal-container">
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
                    <td>{{ $request->employee ? $request->employee->full_name : 'Unknown' }}</td>
                    <td>{{ $request->start_date->format('M d, Y') }}</td>
                    <td>{{ $request->end_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($request->reason, 30) }}</td>
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
                                hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                            Approve
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                id="reject-leave-{{ $request->leave_request_id }}"
                                hx-post="{{ route('leave-requests.reject', $request->leave_request_id) }}"
                                hx-target="#leave-requests"
                                hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
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