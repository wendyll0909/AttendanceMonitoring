document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing requests.js'); // Confirm script initialization

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Tab switching functionality
    const tabLinks = document.querySelectorAll('.requests-tab-link');
    console.log('Tab links found:', tabLinks.length); // Log number of tab links
    tabLinks.forEach(link => {
        console.log('Tab link:', link, 'Data-tab:', link.getAttribute('data-tab')); // Log each tab link and its data-tab
    });

    if (tabLinks.length) {
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabType = this.getAttribute('data-tab');
                
                // Update active tab
                document.querySelectorAll('.requests-tab-link').forEach(tab => {
                    tab.classList.remove('active');
                });
                this.classList.add('active');
                
                // Show/hide content
                document.querySelectorAll('.requests-tab-content').forEach(content => {
                    content.style.display = 'none';
                });
                document.getElementById(`${tabType}-requests`).style.display = 'block';
                
                // Update URL without reload
                history.pushState(null, null, `/dashboard/requests?type=${tabType}`);
            });
        });
        
        // Handle initial tab based on URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('type') || 'leave';
        const initialTabLink = document.querySelector(`.requests-tab-link[data-tab="${initialTab}"]`);
        if (initialTabLink) {
            initialTabLink.click();
        }
    }
    
    // Handle form submissions
    document.body.addEventListener('htmx:afterRequest', function(e) {
        const isLeaveRequest = e.target && e.target.id && (
            e.target.id === 'leave-request-form' || 
            e.target.id.startsWith('approve-leave-') || 
            e.target.id.startsWith('reject-leave-')
        );
        
        const isOvertimeRequest = e.target && e.target.id && (
            e.target.id === 'overtime-request-form' || 
            e.target.id.startsWith('approve-overtime-') || 
            e.target.id.startsWith('reject-overtime-')
        );
        
        if ((isLeaveRequest || isOvertimeRequest) && e.detail.successful) {
            // Show success message
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success mt-3';
            successMessage.textContent = 'Request processed successfully';
            document.getElementById('requests-container').prepend(successMessage);
            
            // Remove message after 3 seconds
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
            
            // Reload the appropriate tab
            const activeTab = document.querySelector('.requests-tab-link.active');
            if (activeTab) {
                const tabType = activeTab.getAttribute('data-tab');
                htmx.ajax('GET', `/dashboard/requests?type=${tabType}`, {
                    target: `#${tabType}-requests`,
                    swap: 'innerHTML'
                });
            }
        }
    });

    // Initialize modals when they're loaded via HTMX
    document.body.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.detail.target.id === 'leave-request-modal-container' || 
            evt.detail.target.id === 'overtime-request-modal-container') {
            const modal = new bootstrap.Modal(evt.detail.target.querySelector('.modal'));
            modal.show();
        }
    });
});