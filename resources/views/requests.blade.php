<div id="requests-container">
    <h2>Requests Management</h2>
    <ul class="nav nav-tabs" id="requestTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'leave' ? 'active' : '' }}" 
               id="leave-tab" 
               data-bs-toggle="tab" 
               href="#leave-requests" 
               role="tab" 
               aria-controls="leave-requests" 
               aria-selected="{{ $tab === 'leave' ? 'true' : 'false' }}"
               hx-get="{{ route('requests.index', ['tab' => 'leave']) }}"
               hx-target="#content-area"
               hx-swap="innerHTML">
                Leave Requests
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'overtime' ? 'active' : '' }}" 
               id="overtime-tab" 
               data-bs-toggle="tab" 
               href="#overtime-requests" 
               role="tab" 
               aria-controls="overtime-requests" 
               aria-selected="{{ $tab === 'overtime' ? 'true' : 'false' }}"
               hx-get="{{ route('requests.index', ['tab' => 'overtime']) }}"
               hx-target="#content-area"
               hx-swap="innerHTML">
                Overtime Requests
            </a>
        </li>
    </ul>
    <div class="tab-content" id="requestTabContent">
        <div class="tab-pane fade {{ $tab === 'leave' ? 'show active' : '' }}" 
             id="leave-requests" 
             role="tabpanel" 
             aria-labelledby="leave-tab">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h3>Leave Requests</h3>
               <!-- Leave Request Button -->
<button class="btn btn-primary" 
        data-bs-toggle="modal" 
        data-bs-target="#leaveRequestModal"
        hx-get="{{ route('leave-requests.create') }}"
        hx-target="#leaveRequestModal .modal-body"
        hx-swap="innerHTML"
        hx-indicator="#leaveRequestModal .htmx-indicator">
    Create Leave Request
</button>
            </div>
            @if($leaveRequests->isEmpty())
                <p>No leave requests found.</p>
            @else
                @include('partials.leave-requests', ['leaveRequests' => $leaveRequests])
            @endif
        </div>
        <div class="tab-pane fade {{ $tab === 'overtime' ? 'show active' : '' }}" 
             id="overtime-requests" 
             role="tabpanel" 
             aria-labelledby="overtime-tab">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h3>Overtime Requests</h3>
              <!-- Overtime Request Button -->
<button class="btn btn-primary" 
        data-bs-toggle="modal" 
        data-bs-target="#overtimeRequestModal"
        hx-get="{{ route('overtime-requests.create') }}"
        hx-target="#overtimeRequestModal .modal-body"
        hx-swap="innerHTML"
        hx-indicator="#overtimeRequestModal .htmx-indicator">
    Create Overtime Request
</button>
            </div>
            @if($overtimeRequests->isEmpty())
                <p>No overtime requests found.</p>
            @else
                @include('partials.overtime-requests', ['overtimeRequests' => $overtimeRequests])
            @endif
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1" aria-labelledby="leaveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveRequestModalLabel">Create Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="htmx-indicator">Loading form...</div>
            </div>
        </div>
    </div>
</div>

<!-- Overtime Request Modal -->
<div class="modal fade" id="overtimeRequestModal" tabindex="-1" aria-labelledby="overtimeRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="overtimeRequestModalLabel">Create Overtime Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="htmx-indicator">Loading form...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    if (window.requestsScriptInitialized) {
        console.log('requests.blade.php script already initialized, skipping');
        return;
    }
    window.requestsScriptInitialized = true;

    console.log('Initializing requests script - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);

    function initializeRequests() {
        if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
            console.warn('HTMX or Bootstrap not loaded, retrying in 100ms');
            setTimeout(initializeRequests, 100);
            return;
        }

        const requestsContainer = document.getElementById('requests-container');
        if (!requestsContainer) {
            console.warn('requests-container not found');
            return;
        }

        try {
            htmx.process(requestsContainer);
            console.log('HTMX processed requests-container');
        } catch (error) {
            console.error('Error processing HTMX for requests-container:', error);
        }
    }

    function showModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('Modal element not found:', modalId);
            showMessage('Failed to load request form', 'danger');
            return;
        }
        try {
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
            modal.show();
            console.log(`${modalId} shown`);
        } catch (error) {
            console.error('Failed to initialize Bootstrap modal:', error);
            showMessage('Failed to open request form', 'danger');
        }
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

    document.addEventListener('htmx:afterRequest', function(evt) {
        const requestUrl = evt.detail.elt?.getAttribute('hx-get') || evt.detail.elt?.getAttribute('hx-post');
        if (!evt.detail.successful) {
            console.error('HTMX request failed:', requestUrl);
            showMessage('Failed to load form', 'danger');
            return;
        }

        if (requestUrl?.includes('leave-requests/create')) {
            console.log('Leave request form loaded successfully');
            showModal('leaveRequestModal');
        } else if (requestUrl?.includes('overtime-requests/create')) {
            console.log('Overtime request form loaded successfully');
            showModal('overtimeRequestModal');
        }
    });

    document.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.detail.target.id === 'content-area' || evt.detail.target.id === 'requests-container') {
            console.log('Re-initializing requests after swap');
            initializeRequests();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded: Initializing requests');
        initializeRequests();
    }, { once: true });
})();
</script>
@endpush