@isset($overtimeRequests)
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Overtime Requests</h5>
        <button class="btn btn-primary" 
                id="create-overtime-request-btn"
                hx-get="{{ route('overtime-requests.create') }}" 
                hx-target="#overtime-request-modal-container"
                hx-swap="innerHTML"
                hx-indicator="#overtime-request-modal-container"
                hx-trigger="click[debounce:500ms]"
                >Create Overtime Request</button>
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

@push('scripts')
<script>
(function() {
    if (window.overtimeRequestsScriptInitialized) {
        console.log('overtime-requests.blade.php script already initialized, skipping');
        return;
    }
    window.overtimeRequestsScriptInitialized = true;

    console.log('Initializing overtime-requests script - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);

    function initializeOvertimeRequests() {
        if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
            console.warn('HTMX or Bootstrap not loaded, retrying in 100ms');
            setTimeout(initializeOvertimeRequests, 100);
            return;
        }

        const overtimeRequestsContainer = document.getElementById('overtime-requests');
        if (!overtimeRequestsContainer) {
            console.warn('overtime-requests container not found');
            return;
        }

        try {
            htmx.process(overtimeRequestsContainer);
            console.log('HTMX processed overtime-requests');
        } catch (error) {
            console.error('Error processing HTMX for overtime-requests:', error);
        }
    }

    function showModal(modalElement) {
        if (!modalElement) {
            console.error('Modal element not found');
            showMessage('Failed to load request form', 'danger');
            return;
        }
        try {
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
            modal.show();
            console.log('Overtime Request modal shown');
        } catch (error) {
            console.error('Failed to initialize Bootstrap modal:', error);
            showMessage('Failed to open request form', 'danger');
        }
    }

    try {
        document.addEventListener('htmx:afterSwap', function(evt) {
            console.log('htmx:afterSwap triggered for target:', evt.detail.target.id);
            if (evt.detail.target.id === 'overtime-request-modal-container') {
                console.log('Processing overtime-request-modal-container');
                const modalElement = evt.detail.target.querySelector('#overtimeRequestModal');
                showModal(modalElement);
            }

            if (evt.detail.target.id === 'overtime-requests' || evt.detail.target.id === 'requests-container') {
                console.log('Re-initializing overtime-requests');
                initializeOvertimeRequests();
            }
        });

        document.body.addEventListener('htmx:afterRequest', function(evt) {
            const requestUrl = evt.detail.elt?.getAttribute('hx-get') || evt.detail.elt?.getAttribute('hx-post');
            if (requestUrl?.includes('overtime-requests/create') && evt.detail.successful) {
                console.log('Overtime request form loaded successfully');
                const modalElement = document.querySelector('#overtimeRequestModal');
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
        console.log('DOMContentLoaded: Initializing overtime-requests');
        initializeOvertimeRequests();
    }, { once: true });
})();
</script>
@endpush