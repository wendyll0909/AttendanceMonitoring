// requests.js
function initializeRequests() {
    console.log('Initializing requests.js - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);

    // Check if HTMX and Bootstrap are available
    if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
        console.error('HTMX or Bootstrap not loaded. Retrying in 100ms...');
        setTimeout(initializeRequests, 100);
        return;
    }

    // Process the entire requests container
    const requestsContainer = document.getElementById('requests-container');
    if (requestsContainer) {
        htmx.process(requestsContainer);
        console.log('HTMX processed requests-container');
    } else {
        console.warn('requests-container not found');
    }

    // Initialize tabs and event listeners
    initializeTabs();
    setupRequestEventListeners();
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: Initializing requests');
    initializeRequests();
}, { once: true });

// Re-initialize after HTMX swap
document.body.addEventListener('htmx:afterSwap', function(evt) {
    if (evt.detail.target.id === 'content-area' || evt.detail.target.id.includes('requests')) {
        console.log('htmx:afterSwap: Re-initializing requests for', evt.detail.target.id);
        initializeRequests();
    }
});

function initializeTabs() {
    const tabLinks = document.querySelectorAll('.requests-tab-link');
    console.log('Found tab links:', tabLinks.length);

    if (!tabLinks.length) {
        console.warn('No tab links found');
        return;
    }

    // Remove existing listeners to prevent duplicates
    tabLinks.forEach(tabLink => {
        tabLink.removeEventListener('click', handleTabClick);
        tabLink.addEventListener('click', handleTabClick);
    });

    function handleTabClick(e) {
        e.preventDefault();
        const targetTab = this.getAttribute('data-tab');
        console.log('Tab clicked:', targetTab);

        // Update tab and content visibility
        tabLinks.forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.requests-tab-content').forEach(content => content.classList.remove('active'));

        this.classList.add('active');
        const targetContent = document.getElementById(`${targetTab}-requests`);
        if (targetContent) {
            targetContent.classList.add('active');
            console.log('Switched to tab content:', `${targetTab}-requests`);
        } else {
            console.error('Target content not found:', `${targetTab}-requests`);
        }

        // Update URL hash
        window.location.hash = targetTab;
    }

    // Activate tab based on URL hash or default to 'leave'
    const hash = window.location.hash.replace('#', '') || 'leave';
    const defaultTab = document.querySelector(`.requests-tab-link[data-tab="${hash}"]`) || tabLinks[0];
    if (defaultTab) {
        console.log('Activating tab:', defaultTab.getAttribute('data-tab'));
        // Fallback if click() fails
        if (!defaultTab.classList.contains('active')) {
            defaultTab.click();
        } else {
            handleTabClick.call(defaultTab, { preventDefault: () => {} });
        }
    } else {
        console.warn('No default tab found');
    }
}

function setupRequestEventListeners() {
    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('button[hx-get*="requests/create"]');
        if (!target) return;

        e.preventDefault();
        const isLeave = target.getAttribute('hx-get').includes('leave-requests');
        const containerId = isLeave ? 'leave-request-modal-container' : 'overtime-request-modal-container';
        console.log(`Create ${isLeave ? 'Leave' : 'Overtime'} Request button clicked`, { url: target.getAttribute('hx-get') });

        handleModalRequest(target, containerId);
    });

    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        const isLeave = form.matches('#leave-request-form');
        const isOvertime = form.matches('#overtime-request-form');
        if (!isLeave && !isOvertime) return;

        e.preventDefault();
        console.log('Submitting form:', form.id);

        const formData = new FormData(form);
        const url = form.getAttribute('action');
        const targetId = isLeave ? 'leave-requests' : 'overtime-requests';
        const modalId = isLeave ? 'leaveRequestModal' : 'overtimeRequestModal';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                return res.text();
            })
            .then(html => {
                const target = document.getElementById(targetId);
                if (target) {
                    target.innerHTML = html;
                    htmx.process(target);
                }

                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modal.hide();
                }

                showMessage('Request submitted successfully', 'success');
            })
            .catch(err => {
                console.error('Error submitting form:', err);
                showMessage('Failed to submit request', 'danger');
            });
    });
}

function handleModalRequest(target, containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Modal container "${containerId}" not found`);
        showMessage('Modal container not found', 'danger');
        return;
    }

    const url = target.getAttribute('hx-get');
    if (!url) {
        console.error('hx-get URL not found on button');
        showMessage('Request URL not found', 'danger');
        return;
    }

    console.log('Fetching modal content from:', url);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
            return res.text();
        })
        .then(html => {
            container.innerHTML = html;
            htmx.process(container);
            const modalElement = container.querySelector('.modal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal shown for:', containerId);
            } else {
                console.error('Modal element not found in response');
                showMessage('Failed to load modal', 'danger');
            }
        })
        .catch(err => {
            console.error('Error loading modal content:', err);
            showMessage('Failed to load request form', 'danger');
        });
}

function showMessage(message, type) {
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
}