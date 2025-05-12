@php
    $search = request()->query('search', '');
    $status = request()->query('status', '');
@endphp

<div id="requests-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Requests Management</h2>
        <div id="datetime-clock" class="text-muted" style="font-size: 1.1rem; font-weight: 500;"></div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error') || isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') ?? $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="requests-search-form"
      hx-get="{{ route('requests.index') }}"
      hx-target="#requests-container"
      hx-swap="innerHTML"
      hx-push-url="false"
      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "text/html"}'
      class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search by Employee Name</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Enter employee name..." value="{{ $search }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Filter by Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="" {{ $status == '' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <div class="requests-container">
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
            @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
                @include('partials.leave-requests', ['leaveRequests' => $leaveRequests])
            @else
                <p>No leave requests found.</p>
            @endif
            <div class="d-flex justify-content-center mt-3">
                @if($leaveRequests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $leaveRequests->appends(['search' => $search, 'status' => $status])->links() }}
                @endif
            </div>
        </div>

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
            @if(isset($overtimeRequests) && $overtimeRequests->isNotEmpty())
                @include('partials.overtime-requests', ['overtimeRequests' => $overtimeRequests])
            @else
                <p>No overtime requests found.</p>
            @endif
            <div class="d-flex justify-content-center mt-3">
                @if($overtimeRequests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $overtimeRequests->appends(['search' => $search, 'status' => $status])->links() }}
                @endif
            </div>
        </div>
    </div>

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
#datetime-clock {
    background-color: #e9ecef;
    padding: 8px 12px;
    border-radius: 5px;
    color: #333333;
    font-family: 'Poppins', sans-serif;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.requests-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.request-wrapper {
    flex: 1;
    min-width: 300px;
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
    function updateClock() {
        const now = new Date();
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const formattedDateTime = now.toLocaleString('en-US', options);
        const clockElement = document.getElementById('datetime-clock');
        if (clockElement) {
            clockElement.textContent = formattedDateTime;
        }
    }
    updateClock();
    setInterval(updateClock, 1000);

    document.addEventListener('htmx:afterSwap', function(evt) {
    if (evt.detail.target.id === 'requests-container') {
        console.log('HTMX swap completed for requests-container');
        // Ensure HTMX processes new content
        htmx.process(evt.detail.target);
        // Reinitialize clock
        updateClock();
    }
});

    document.addEventListener('htmx:afterRequest', function(evt) {
        const formIds = ['leaveRequestForm', 'overtimeRequestForm'];
        if (evt.detail.target && formIds.includes(evt.detail.target.id) && evt.detail.successful) {
            const modalIds = {
                'leaveRequestForm': 'leaveRequestModal',
                'overtimeRequestForm': 'overtimeRequestModal'
            };
            const modalEl = document.getElementById(modalIds[evt.detail.target.id]);
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }
    });

    document.addEventListener('htmx:responseError', function(evt) {
        console.error('HTMX response error', evt.detail);
        const modalBody = document.querySelector('#overtimeRequestModal .modal-body') || document.querySelector('#leaveRequestModal .modal-body');
        if (evt.detail.xhr.status === 422 && modalBody) {
            modalBody.innerHTML = evt.detail.xhr.responseText;
            htmx.process(modalBody);
        } else {
            const errorContainer = document.getElementById('error-message') || document.createElement('div');
            errorContainer.className = 'alert alert-danger alert-dismissible fade show';
            errorContainer.innerHTML = `
                Request failed: ${evt.detail.xhr.statusText}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            const container = document.getElementById('requests-container');
            if (container) {
                container.prepend(errorContainer);
            }
        }
    });
})();
document.addEventListener('DOMContentLoaded', function() {
    if (typeof htmx !== 'undefined') {
        htmx.process(document.body);
    }
    document.querySelectorAll('form[data-htmx]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    });
});
</script>
@endpush