@isset($leaveRequests)
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Leave Requests</h5>
        <button class="btn btn-primary" 
                id="create-leave-request-btn"
                hx-get="{{ route('leave-requests.create') }}" 
                hx-target="#leave-request-modal-container"
                hx-swap="innerHTML"
                hx-indicator="#leave-request-modal-container"
                hx-trigger="click[debounce:500ms]"
                >Create Leave Request</button>
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
        <td>{{ $request->start_date->format('M d, Y') }}</td>
        <td>{{ $request->end_date->format('M d, Y') }}</td>
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
@endisset

@push('scripts')
<script>
(function() {
    if (window.leaveRequestsScriptInitialized) {
        console.log('leave-requests.blade.php script already initialized, skipping');
        return;
    }
    window.leaveRequestsScriptInitialized = true;

    console.log('Initializing leave-requests script - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);

    function initializeLeaveRequests() {
        if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
            console.warn('HTMX or Bootstrap not loaded, retrying in 100ms');
            setTimeout(initializeLeaveRequests, 100);
            return;
        }

        const leaveRequestsContainer = document.getElementById('leave-requests');
        if (!leaveRequestsContainer) {
            console.warn('leave-requests container not found');
            return;
        }

        try {
            htmx.process(leaveRequestsContainer);
            console.log('HTMX processed leave-requests');
        } catch (error) {
            console.error('Error processing HTMX for leave-requests:', error);
        }
    }

    function showModal(modalElement) {
        if (!modalElement) {
            console.error('Modal element not found');
            showMessage('Failed to load request form', 'danger');
            return;
        }
        try {
            const modal = new bootstrap.Modal(m-le, { backdrop: 'static' });
            modal.show();
            console.log('Leave Request modal shown');
        } catch (error) {
            console.error('Failed to initialize Bootstrap modal:', error);
            showMessage('Failed to open request form', 'danger');
        }
    }

    try {
        document.addEventListener('htmx:afterSwap', function(evt) {
            console.log('htmx:afterSwap triggered for target:', evt.detail.target.id);
            if (evt.detail.target.id === 'leave-request-modal-container') {
                console.log('Processing leave-request-modal-container');
                const modalElement = evt.detail.target.querySelector('#leaveRequestModal');
                showModal(modalElement);
            }

            if (evt.detail.target.id === 'leave-requests' || evt.detail.target.id === 'requests-container') {
                console.log('Re-initializing leave-requests');
                initializeLeaveRequests();
            }
        });

        document.body.addEventListener('htmx:afterRequest', function(evt) {
            const requestUrl = evt.detail.elt?.getAttribute('hx-get') || evt.detail.elt?.getAttribute('hx-post');
            if (requestUrl?.includes('leave-requests/create') && evt.detail.successful) {
                console.log('Leave request form loaded successfully');
                const modalElement = document.querySelector('#leaveRequestModal');
                if (modalElement && !modalElement.classList.contains('show')) {
                    console.log('Fallback: Attempting to show modal');
                    showModal(modalElement);
                }
            }
        });
    } catch (error) {
        console.error('Error setting up HTMX event listeners:', error);
    }

    function showMessage(message, type) {
        try {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            const container = document.getElementById('alert-container') || document.getElementById('requests-container');
            if (container) {
                container.prepend(alertDiv);
                setTimeout(() => alertDiv.remove(), 5000);
            } else {
                console.warn('Alert container not found');
            }
        } catch (error) {
            console.error('Error showing message:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded: Initializing leave-requests');
        initializeLeaveRequests();
    }, { once: true });
})();
</script>
@endpush