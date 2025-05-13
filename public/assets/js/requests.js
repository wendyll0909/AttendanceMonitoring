document.addEventListener('DOMContentLoaded', function () {
    console.log('requests.js loaded');

    // Initialize Bootstrap modals
    const leaveModalEl = document.getElementById('addLeaveRequestModal');
    const overtimeModalEl = document.getElementById('addOvertimeRequestModal');
    let leaveModal, overtimeModal;

    if (leaveModalEl) {
        leaveModal = new bootstrap.Modal(leaveModalEl);
        console.log('Leave modal initialized');
    } else {
        console.warn('Leave request modal element not found');
    }
    if (overtimeModalEl) {
        overtimeModal = new bootstrap.Modal(overtimeModalEl);
        console.log('Overtime modal initialized');
    } else {
        console.warn('Overtime request modal element not found');
    }

    // Handle HTMX after request for form submissions
    document.body.addEventListener('htmx:afterRequest', function (e) {
        console.log('HTMX afterRequest triggered', {
            url: e.detail.xhr.responseURL,
            status: e.detail.xhr.status,
            target: e.detail.target?.id,
            response: e.detail.xhr.responseText.substring(0, 200) + (e.detail.xhr.responseText.length > 200 ? '...' : '')
        });

        // Check if target still exists in DOM
        const targetExists = document.getElementById('requests-content');
        console.log('Target #requests-content exists after request:', !!targetExists);

        const isLeaveRequest = e.detail.xhr.responseURL.includes('/dashboard/requests/leave');
        const isOvertimeRequest = e.detail.xhr.responseURL.includes('/dashboard/requests/overtime');

        if ((isLeaveRequest || isOvertimeRequest) && e.detail.xhr.status === 200) {
            console.log('Successful submission detected');

            // Close modals
            if (isLeaveRequest && leaveModal) {
                try {
                    leaveModal.hide();
                    console.log('Leave modal closed');
                } catch (err) {
                    console.error('Failed to close leave modal:', err);
                    // Fallback: Hide modal using DOM
                    leaveModalEl.classList.remove('show');
                    leaveModalEl.setAttribute('aria-hidden', 'true');
                    leaveModalEl.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    document.querySelector('.modal-backdrop')?.remove();
                    console.log('Leave modal closed via fallback');
                }
            }
            if (isOvertimeRequest && overtimeModal) {
                try {
                    overtimeModal.hide();
                    console.log('Overtime modal closed');
                } catch (err) {
                    console.error('Failed to close overtime modal:', err);
                    // Fallback: Hide modal using DOM
                    overtimeModalEl.classList.remove('show');
                    overtimeModalEl.setAttribute('aria-hidden', 'true');
                    overtimeModalEl.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    document.querySelector('.modal-backdrop')?.remove();
                    console.log('Overtime modal closed via fallback');
                }
            }

            // Show success message
            const successContainer = document.getElementById('success-message');
            if (successContainer) {
                console.log('Success message found, displaying');
                successContainer.style.display = 'block';
                setTimeout(() => {
                    successContainer.classList.add('fade-out');
                    setTimeout(() => {
                        successContainer.style.display = 'none';
                        successContainer.classList.remove('fade-out');
                    }, 500);
                }, 3000);
            } else {
                console.warn('Success message container (#success-message) not found in DOM after update');
            }
        } else if ((isLeaveRequest || isOvertimeRequest) && e.detail.xhr.status !== 200) {
            console.log('Error response detected', { status: e.detail.xhr.status });

            // Display error in modal
            let errorMessage = 'An unexpected error occurred. Please try again.';
            if (e.detail.xhr.status === 422) {
                errorMessage = e.detail.xhr.responseText.match(/<div class="alert alert-danger" id="error-message">([^<]*)<\/div>/)?.[1] || 'Validation error occurred';
            } else if (e.detail.xhr.status === 500) {
                errorMessage = e.detail.xhr.responseText.match(/<div class="alert alert-danger" id="error-message">([^<]*)<\/div>/)?.[1] || 'Server error occurred. Please try again later.';
            }
            
            if (isLeaveRequest) {
                const leaveErrorContainer = document.getElementById('leave-modal-error');
                if (leaveErrorContainer) {
                    leaveErrorContainer.innerHTML = errorMessage;
                    leaveErrorContainer.style.display = 'block';
                    console.log('Leave modal error displayed:', errorMessage);
                } else {
                    console.warn('Leave modal error container (#leave-modal-error) not found');
                }
            }
            if (isOvertimeRequest) {
                const overtimeErrorContainer = document.getElementById('overtime-modal-error');
                if (overtimeErrorContainer) {
                    overtimeErrorContainer.innerHTML = errorMessage;
                    overtimeErrorContainer.style.display = 'block';
                    console.log('Overtime modal error displayed:', errorMessage);
                } else {
                    console.warn('Overtime modal error container (#overtime-modal-error) not found');
                }
            }

            // Also show general error message
            const generalErrorContainer = document.getElementById('error-message');
            if (generalErrorContainer) {
                generalErrorContainer.innerHTML = errorMessage;
                generalErrorContainer.style.display = 'block';
                console.log('General error message displayed:', errorMessage);
            } else {
                console.warn('General error message container (#error-message) not found in DOM');
            }
        }
    });

    // Clear form fields and errors when modals are hidden
    leaveModalEl?.addEventListener('hidden.bs.modal', () => {
        const form = document.getElementById('addLeaveRequestForm');
        const errorContainer = document.getElementById('leave-modal-error');
        if (form) {
            form.reset();
            console.log('Leave form reset');
        }
        if (errorContainer) {
            errorContainer.style.display = 'none';
            errorContainer.innerHTML = '';
            console.log('Leave modal error cleared');
        }
    });

    overtimeModalEl?.addEventListener('hidden.bs.modal', () => {
        const form = document.getElementById('addOvertimeRequestForm');
        const errorContainer = document.getElementById('overtime-modal-error');
        if (form) {
            form.reset();
            console.log('Overtime form reset');
        }
        if (errorContainer) {
            errorContainer.style.display = 'none';
            errorContainer.innerHTML = '';
            console.log('Overtime modal error cleared');
        }
    });

    // Focus search inputs on page load
    const leaveSearch = document.querySelector('#leave-requests-table input[name="search"]');
    const overtimeSearch = document.querySelector('#overtime-requests-table input[name="search"]');
    if (leaveSearch) leaveSearch.focus();
});