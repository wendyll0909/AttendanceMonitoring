<div id="requests-container">
    <h2>Requests Management</h2>
    <div class="requests-container">
        <!-- Leave Requests Section -->
        <div class="request-wrapper">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h3>Leave Requests</h3>
                <button class="btn btn-primary" 
                        data-bs-toggle="modal" 
                        data-bs-target="#leaveRequestModal"
                        hx-get="{{ route('leave-requests.create') }}"
                        hx-target="#leaveRequestModal .modal-body"
                        hx-swap="innerHTML"
                        hx-trigger="click[debounce:500ms]"
                        hx-indicator="#leaveRequestModal .htmx-indicator"
                        hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                    Create Leave Request
                </button>
            </div>
            @if($leaveRequests->isEmpty())
                <p>No leave requests found.</p>
            @else
                @include('partials.leave-requests', ['leaveRequests' => $leaveRequests])
            @endif
        </div>

        <!-- Overtime Requests Section -->
        <div class="request-wrapper">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h3>Overtime Requests</h3>
                <button class="btn btn-primary" 
                        data-bs-toggle="modal" 
                        data-bs-target="#overtimeRequestModal"
                        hx-get="{{ route('overtime-requests.create') }}"
                        hx-target="#overtimeRequestModal .modal-body"
                        hx-swap="innerHTML"
                        hx-trigger="click[debounce:500ms]"
                        hx-indicator="#overtimeRequestModal .htmx-indicator"
                        hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
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
</div>

<style>
.requests-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.request-wrapper {
    flex: 1;
    min-width: 300px; /* Minimum width to prevent sections from becoming too narrow */
}

.htmx-indicator {
    text-align: center;
    padding: 10px;
    color: #666;
}

@media (max-width: 768px) {
    .requests-container {
        flex-direction: column;
    }
}
</style>

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

    document.addEventListener('htmx:beforeRequest', function(evt) {
        console.log('HTMX request started:', {
            url: evt.detail.elt?.getAttribute('hx-get') || evt.detail.elt?.getAttribute('hx-post'),
            target: evt.detail.target?.id
        });
    });

    document.addEventListener('htmx:afterRequest', function(evt) {
        const requestUrl = evt.detail.elt?.getAttribute('hx-get') || evt.detail.elt?.getAttribute('hx-post');
        const modalBody = evt.detail.elt.closest('.modal')?.querySelector('.modal-body');
        const target = modalBody || evt.detail.target || document.getElementById('requests-container');

        if (!evt.detail.successful) {
            console.error('HTMX request failed:', {
                url: requestUrl,
                status: evt.detail.xhr?.status,
                response: evt.detail.xhr?.responseText?.substring(0, 500),
                error: evt.detail.error
            });

            let errorMessage = 'An error occurred while processing your request.';
            let errorsHtml = '';

            try {
                const response = JSON.parse(evt.detail.xhr.responseText);
                errorMessage = response.message || errorMessage;
                if (response.errors) {
                    errorsHtml = Object.values(response.errors).flat().join('<br>');
                    // Highlight problematic fields in modals
                    if (modalBody && requestUrl?.includes('overtime-requests')) {
                        Object.keys(response.errors).forEach(field => {
                            const input = modalBody.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = response.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
                        });
                    }
                }
            } catch (e) {
                // Fallback: Extract error from HTML or use default message
                const match = evt.detail.xhr?.responseText?.match(/<div[^>]*class="alert alert-danger"[^>]*>([^<]*)<\/div>/);
                if (match) {
                    errorMessage = match[1];
                }
                console.error('Error parsing error response:', e);
            }

            if (target) {
                target.innerHTML = `
                    <div class="alert alert-danger">
                        ${errorMessage}
                        ${errorsHtml ? '<br>' + errorsHtml : ''}
                    </div>
                    ${target.innerHTML}
                `;
            }
            return;
        }

        console.log('HTMX request succeeded:', { 
            url: requestUrl,
            response: evt.detail.xhr?.responseText?.substring(0, 500)
        });

        if (evt.detail.xhr?.responseText?.includes('<!DOCTYPE html>')) {
            console.error('Response contains full HTML document, which may cause parsing issues');
        }

        if (requestUrl?.includes('leave-requests/create')) {
            console.log('Leave request form loaded successfully');
            const modal = new bootstrap.Modal(document.getElementById('leaveRequestModal'), { backdrop: 'static' });
            modal.show();
        } else if (requestUrl?.includes('overtime-requests/create')) {
            console.log('Overtime request form loaded successfully');
            const modal = new bootstrap.Modal(document.getElementById('overtimeRequestModal'), { backdrop: 'static' });
            modal.show();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Leave request form validation
        document.getElementById('leaveRequestForm')?.addEventListener('submit', function(e) {
            const startDate = new Date(this.elements.start_date.value);
            const endDate = new Date(this.elements.end_date.value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('End date must be after or equal to start date');
                return false;
            }
            return true;
        });

        // Overtime request form validation
        document.getElementById('overtimeRequestForm')?.addEventListener('submit', function(e) {
            const startTime = new Date(this.elements.start_time.value);
            const endTime = new Date(this.elements.end_time.value);
            
            if (endTime <= startTime) {
                e.preventDefault();
                alert('End time must be after start time');
                return false;
            }
            return true;
        });

        console.log('DOMContentLoaded: Initializing requests');
        initializeRequests();
    }, { once: true });
})();
</script>
@endpush