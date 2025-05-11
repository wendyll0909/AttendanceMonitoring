function initializeRequests(retryCount = 0, maxRetries = 50) {
    console.log('Initializing requests.js - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);

    // Retry if HTMX or Bootstrap is not available
    if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
        if (retryCount >= maxRetries) {
            console.error('Max retries reached. HTMX or Bootstrap unavailable.');
            return;
        }
        console.log('HTMX or Bootstrap not available, retrying...');
        setTimeout(() => initializeRequests(retryCount + 1, maxRetries), 100);
        return;
    }

    // Initialize components
    initializeTabs();
    setupRequestEventListeners();
    htmx.process(document.body);
    setupHtmxDebuggers();
}

// Single DOMContentLoaded listener
document.addEventListener('DOMContentLoaded', initializeRequests);

// Single htmx:afterSwap listener for content-area
document.body.addEventListener('htmx:afterSwap', function (evt) {
    if (evt.detail.target.id === 'content-area') {
        initializeRequests();
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeRequests);

// Initialize when content is swapped by HTMX
document.body.addEventListener('htmx:afterSwap', function(evt) {
    if (evt.detail.target.id === 'content-area') {
        initializeRequests();
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeRequests);

// Initialize when content is swapped by HTMX
document.body.addEventListener('htmx:afterSwap', function(evt) {
    if (evt.detail.target.id === 'content-area') {
        initializeRequests();
    }
});
// Initialize when page loads normally
document.addEventListener('DOMContentLoaded', initializeRequests);

// Initialize when content is swapped by HTMX
document.body.addEventListener('htmx:afterSwap', function(evt) {
    if (evt.detail.target.id === 'content-area') {
        initializeRequests();
    }
});

function initializeTabs() {
    const tabLinks = document.querySelectorAll('.requests-tab-link');
    if (!tabLinks.length) {
        console.warn('No tab links found');
        return;
    }

    tabLinks.forEach(tabLink => {
        tabLink.addEventListener('click', function (e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            console.log('Tab clicked:', targetTab);

            // Remove active class from all tabs and content
            tabLinks.forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.requests-tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const targetContent = document.getElementById(`${targetTab}-requests`);
            if (targetContent) {
                targetContent.classList.add('active');
                console.log('Switched to tab content:', `${targetTab}-requests`);
            } else {
                console.error('Target content not found:', `${targetTab}-requests`);
            }
        });
    });

    // Activate default tab (first tab or based on URL hash)
    let defaultTab = document.querySelector('.requests-tab-link.active');
    if (!defaultTab && window.location.hash) {
        const hashTab = window.location.hash.replace('#', '');
        defaultTab = document.querySelector(`.requests-tab-link[data-tab="${hashTab}"]`);
    }
    defaultTab = defaultTab || tabLinks[0];
    if (defaultTab) {
        console.log('Activating default tab:', defaultTab.getAttribute('data-tab'));
        defaultTab.click();
    } else {
        console.warn('No default tab found');
    }
}

function setupRequestEventListeners() {
    document.body.addEventListener('click', function (e) {
        // Handle Leave and Overtime request buttons
        const target = e.target.closest('button[hx-get*="requests/create"]');
        if (!target) return;

        e.preventDefault();
        const isLeave = target.getAttribute('hx-get').includes('leave-requests');
        const containerId = isLeave ? 'leave-request-modal-container' : 'overtime-request-modal-container';
        console.log(`Create ${isLeave ? 'Leave' : 'Overtime'} Request button clicked`);

        handleModalRequest(target, containerId);
    });

    // Handle form submissions
    document.body.addEventListener('submit', function (e) {
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
                    htmx.process(target); // Re-process HTMX bindings
                }

                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }

                showMessage('Request submitted successfully', 'success');
            })
            .catch(err => {
                console.error('Error submitting form:', err);
                showMessage('Failed to submit request', 'danger');
            });
    });

    // Approve/reject actions
    document.body.addEventListener('click', function (e) {
        const id = e.target.id;
        const isAction = id && (id.startsWith('approve-') || id.startsWith('reject-'));
        if (!isAction) return;

        e.preventDefault();
        const url = e.target.getAttribute('hx-post');
        const isLeave = id.includes('leave');
        const targetId = isLeave ? 'leave-requests' : 'overtime-requests';

        fetch(url, {
            method: 'POST',
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
                    htmx.process(target); // Re-process HTMX bindings
                }
                showMessage('Request updated successfully', 'success');
            })
            .catch(err => {
                console.error('Error updating request:', err);
                showMessage('Failed to update request', 'danger');
            });
    });
}

function handleModalRequest(target, containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Modal container "${containerId}" not found`);
        return;
    }

    const url = target.getAttribute('hx-get');
    if (!url) {
        console.error('hx-get URL not found on button');
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
            htmx.process(container); // Process HTMX bindings in modal
            const modalElement = container.querySelector('.modal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element not found in response');
            }
        })
        .catch(err => {
            console.error('Error loading modal content:', err);
            showMessage('Failed to load request form', 'danger');
        });
}

function setupHtmxDebuggers() {
    document.body.addEventListener('htmx:beforeRequest', function (e) {
        console.log('HTMX request started:', e.detail.path);
    });

    document.body.addEventListener('htmx:afterRequest', function (e) {
        console.log('HTMX request completed. Success:', e.detail.successful, 'Status:', e.detail.xhr.status);
        if (!e.detail.successful) {
            console.error('HTMX request failed:', e.detail.xhr.responseText);
        }
    });

    document.body.addEventListener('htmx:beforeSwap', function (e) {
        console.log('HTMX beforeSwap:', e.detail.target.id, 'Content length:', e.detail.serverResponse.length);
    });

    document.body.addEventListener('htmx:afterSwap', function (e) {
        console.log('HTMX afterSwap for target:', e.detail.target.id);
        const modalElement = e.detail.target.querySelector('.modal');
        if (modalElement) {
            console.log('Modal found in swapped content, initializing');
            try {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } catch (err) {
                console.error('Failed to initialize modal:', err);
            }
        }
    });
}

function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const container = document.getElementById('requests-container');
    if (container) {
        container.prepend(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
}
