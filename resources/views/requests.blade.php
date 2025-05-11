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
                <button class="btn btn-primary" 
                        hx-get="{{ route('leave-requests.create') }}"
                        hx-target="#leave-request-modal-container"
                        hx-swap="innerHTML">
                    Create Leave Request
                </button>
            </div>
            <div id="leave-request-modal-container"></div>
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
                <button class="btn btn-primary" 
                        hx-get="{{ route('overtime-requests.create') }}"
                        hx-target="#overtime-request-modal-container"
                        hx-swap="innerHTML">
                    Create Overtime Request
                </button>
            </div>
            <div id="overtime-request-modal-container"></div>
            @if($overtimeRequests->isEmpty())
                <p>No overtime requests found.</p>
            @else
                @include('partials.overtime-requests', ['overtimeRequests' => $overtimeRequests])
            @endif
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
            console.warn('requests-container not found, skipping HTMX processing');
            return;
        }

        try {
            htmx.process(requestsContainer);
            console.log('HTMX processed requests-container');
            const htmxElements = requestsContainer.querySelectorAll('[hx-get], [hx-post]');
            console.log('HTMX elements found:', htmxElements.length);
        } catch (error) {
            console.error('Error processing HTMX for requests-container:', error);
        }
    }

    function tryInitialize() {
        const requestsContainer = document.getElementById('requests-container');
        if (requestsContainer) {
            initializeRequests();
        } else {
            console.warn('requests-container not found during initialization');
        }
    }

    try {
        document.addEventListener('DOMContentLoaded', tryInitialize, { once: true });

        document.body.addEventListener('htmx:afterSwap', function(evt) {
            if (evt.detail.target.id === 'content-area' || evt.detail.target.id === 'requests-container') {
                console.log('Re-initializing requests after swap');
                tryInitialize();
            }
        });

        document.body.addEventListener('htmx:configRequest', function(evt) {
            console.log('HTMX request configured:', evt.detail.path, 'Target:', evt.detail.target.id);
        });

        document.body.addEventListener('htmx:afterRequest', function(evt) {
            console.log('HTMX request completed:', evt.detail.path, 'Success:', evt.detail.successful);
            if (!evt.detail.successful) {
                console.error('HTMX request failed:', evt.detail.xhr?.status, evt.detail.xhr?.responseText);
            }
        });
    } catch (error) {
        console.error('Error setting up HTMX event listeners:', error);
    }
})();
</script>
@endpush