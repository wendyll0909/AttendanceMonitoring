<div id="requests-container">
    <h2>Requests Management</h2>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link requests-tab-link active" href="#" data-tab="leave">Leave Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link requests-tab-link" href="#" data-tab="overtime">Overtime Requests</a>
        </li>
    </ul>
    
    <div id="leave-requests" class="requests-tab-content">
        @include('partials.leave-requests')
    </div>
    
    <div id="overtime-requests" class="requests-tab-content" style="display: none;">
        @include('partials.overtime-requests')
    </div>
    <!-- Leave Request Modal Container -->
<div id="leave-request-modal-container"></div>

<!-- Overtime Request Modal Container -->
<div id="overtime-request-modal-container"></div>
@push('scripts')
<script src="{{ asset('assets/js/requests.js') }}"></script>
@endpush
</div>