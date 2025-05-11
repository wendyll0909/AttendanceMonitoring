<!-- requests.blade.php -->
<div id="requests-container">
    <h2>Requests Management</h2>
    
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link requests-tab-link {{ $tab === 'leave' ? 'active' : '' }}" href="#" data-tab="leave">Leave Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link requests-tab-link {{ $tab === 'overtime' ? 'active' : '' }}" href="#" data-tab="overtime">Overtime Requests</a>
        </li>
    </ul>
    
    <div id="leave-requests" class="requests-tab-content {{ $tab === 'leave' ? 'active' : '' }}">
        @include('partials.leave-requests')
    </div>
    
    <div id="overtime-requests" class="requests-tab-content {{ $tab === 'overtime' ? 'active' : '' }}">
        @include('partials.overtime-requests')
    </div>
    
    <!-- Leave Request Modal Container -->
    <div id="leave-request-modal-container"></div>

    <!-- Overtime Request Modal Container -->
    <div id="overtime-request-modal-container"></div>
</div>

<style>
    .requests-tab-content { 
        display: none; 
    }
    .requests-tab-content.active { 
        display: block !important; 
    }
</style>

@push('scripts')
    <script src="{{ asset('assets/js/requests.js') }}"></script>
@endpush