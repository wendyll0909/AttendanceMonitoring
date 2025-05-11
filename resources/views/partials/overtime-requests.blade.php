@isset($overtimeRequests)
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Overtime Requests</h5>
   <button class="btn btn-primary" 
        id="create-overtime-request-btn"
        hx-get="{{ route('overtime-requests.create') }}" 
        hx-target="#overtimeRequestModal .modal-body" 
        hx-swap="innerHTML"
        hx-indicator="#overtimeRequestModal .htmx-indicator"
        hx-trigger="click[debounce:500ms]"
        data-bs-toggle="modal" 
        data-bs-target="#overtimeRequestModal">
    Create Overtime Request
</button>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
          <tbody>
    @foreach($overtimeRequests as $request)
    <tr>
        <td>{{ $request->employee ? e($request->employee->full_name) : 'Unknown' }}</td>
        <td>{{ $request->start_time->format('M d, Y H:i') }}</td>
        <td>{{ $request->end_time->format('M d, Y H:i') }}</td>
        <td>{{ e(Str::limit($request->reason, 30)) }}</td>
        <td>
            <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                {{ ucfirst($request->status) }}
            </span>
        </td>
        <td>
            @if($request->status === 'pending')
            <button class="btn btn-sm btn-success" 
                    id="approve-overtime-{{ $request->overtime_request_id }}"
                    hx-post="{{ route('overtime-requests.approve', $request->overtime_request_id) }}"
                    hx-target="#overtime-requests"
                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                Approve
            </button>
            <button class="btn btn-sm btn-danger" 
                    id="reject-overtime-{{ $request->overtime_request_id }}"
                    hx-post="{{ route('overtime-requests.reject', $request->overtime_request_id) }}"
                    hx-target="#overtime-requests"
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
@endisset
