<!-- requests.blade.php -->
<div id="requests-container">
    <h2>Requests Management</h2>
    
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link requests-tab-link {{ $tab === 'leave' ? 'active' : '' }}" href="#leave" data-tab="leave">Leave Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link requests-tab-link {{ $tab === 'overtime' ? 'active' : '' }}" href="#overtime" data-tab="overtime">Overtime Requests</a>
        </li>
    </ul>
    
    <div id="leave-requests" class="requests-tab-content {{ $tab === 'leave' ? 'active' : '' }}">
        @include('partials.leave-requests')
    </div>
    
    <div id="overtime-requests" class="requests-tab-content {{ $tab === 'overtime' ? 'active' : '' }}">
        @include('partials.overtime-requests')
    </div>
    
    <!-- Modal Containers -->
    <div id="leave-request-modal-container"></div>
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
    <script>
        // Ensure HTMX processes the content after swap
        document.addEventListener('htmx:afterSwap', function(evt) {
            if (evt.detail.target.id === 'requests-container' || evt.detail.target.id === 'content-area') {
                console.log('htmx:afterSwap: Processing requests-container');
                htmx.process(document.getElementById('requests-container'));
            }
        });
        // Debug HTMX initialization
        document.addEventListener('htmx:configRequest', function(evt) {
            console.log('HTMX request configured:', evt.detail.path);
        });
    </script>
@endpush