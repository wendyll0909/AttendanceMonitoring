document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded fired, app.js is running');
    console.log('Bootstrap Modal available:', typeof bootstrap.Modal);
    console.log('HTMX available:', typeof htmx);

    if (typeof htmx === 'undefined' || typeof bootstrap === 'undefined') {
        console.warn('HTMX or Bootstrap not loaded, retrying in 100ms');
        setTimeout(() => {
            console.log('Retrying initialization - HTMX:', typeof htmx, 'Bootstrap:', typeof bootstrap);
        }, 100);
    }

    const sidebar = document.querySelector('.sidebar');
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const contentArea = document.getElementById('content-area');
    const dropdownToggles = document.querySelectorAll('.nav-link[data-toggle-dropdown]');
    let isSidebarToggled = false;
    let isSidebarHovered = false;
    let isHamburgerHovered = false;
    let isNavigating = false;
    let dropdownTimeout;
    let currentQrCode = null;

    if (!sidebar) console.warn('Sidebar element not found');
    if (!hamburgerMenu) console.warn('Hamburger menu element not found');
    if (!contentArea) console.warn('Content area element not found');

    function toggleSidebar() {
    isSidebarToggled = !isSidebarToggled;
    console.log('Sidebar toggled, states:', { isSidebarToggled, isHamburgerHovered, isSidebarHovered, isNavigating });
    if (sidebar) {
        sidebar.classList.toggle('visible', isSidebarToggled);
    }
    // Add this to hide hamburger menu when sidebar is visible
    if (hamburgerMenu) {
        hamburgerMenu.style.opacity = isSidebarToggled ? '0' : '1';
        hamburgerMenu.style.pointerEvents = isSidebarToggled ? 'none' : 'auto';
    }
}

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', toggleSidebar);
    }

    function debouncedToggleSidebar() {
    clearTimeout(dropdownTimeout);
    dropdownTimeout = setTimeout(() => {
        if (!isSidebarToggled && !isSidebarHovered && !isHamburgerHovered && !isNavigating && sidebar) {
            sidebar.classList.remove('visible');
            console.log('Sidebar hidden due to no hover, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            // Show hamburger menu when sidebar is hidden
            if (hamburgerMenu) {
                hamburgerMenu.style.opacity = '1';
                hamburgerMenu.style.pointerEvents = 'auto';
            }
        }
    }, 200);
}

    if (sidebar) {
    sidebar.addEventListener('mouseenter', () => {
        isSidebarHovered = true;
        console.log('Sidebar mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        if (!isSidebarToggled && !isNavigating) {
            sidebar.classList.add('visible');
            // Hide hamburger menu when sidebar is shown via hover
            if (hamburgerMenu) {
                hamburgerMenu.style.opacity = '0';
                hamburgerMenu.style.pointerEvents = 'none';
            }
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        isSidebarHovered = false;
        console.log('Sidebar mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        debouncedToggleSidebar();
        // Show hamburger menu when sidebar is hidden
        if (!isSidebarToggled && hamburgerMenu) {
            hamburgerMenu.style.opacity = '1';
            hamburgerMenu.style.pointerEvents = 'auto';
        }
    });
}

    if (hamburgerMenu) {
    hamburgerMenu.addEventListener('mouseenter', () => {
        isHamburgerHovered = true;
        console.log('Hamburger mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        if (!isSidebarToggled && !isNavigating && sidebar) {
            sidebar.classList.add('visible');
            // Hide hamburger menu when sidebar is shown
            hamburgerMenu.style.opacity = '0';
            hamburgerMenu.style.pointerEvents = 'none';
        }
    });

    hamburgerMenu.addEventListener('mouseleave', () => {
        isHamburgerHovered = false;
        console.log('Hamburger mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        debouncedToggleSidebar();
        // Only show hamburger menu if sidebar is not visible
        if (!isSidebarToggled && !isSidebarHovered && hamburgerMenu) {
            hamburgerMenu.style.opacity = '1';
            hamburgerMenu.style.pointerEvents = 'auto';
        }
    });
}

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdown = this.nextElementSibling;
            const isVisible = dropdown.style.display === 'block';
            console.log('Dropdown toggle clicked', { toggle: this.textContent, isVisible });

            document.querySelectorAll('.employee-dropdown, .attendance-dropdown').forEach(menu => {
                menu.style.display = 'none';
            });

            dropdown.style.display = isVisible ? 'none' : 'block';
        });
    });

    document.body.addEventListener('htmx:beforeRequest', function (e) {
        if (e.target.tagName === 'A' && e.target.getAttribute('hx-get')) {
            isNavigating = true;
            console.log('HTMX navigation started, states:', { isNavigating, isSidebarToggled, isSidebarHovered, target: e.target.href });
        }
    });

    document.body.addEventListener('htmx:afterRequest', function(e) {
        const formIds = ['addEmployeeForm', 'editEmployeeForm', 'addPositionForm', 'editPositionForm', 'editAttendanceForm'];
        const isDeleteForm = e.target && e.target.id && (e.target.id.startsWith('deletePositionForm_') || e.target.id.startsWith('deleteAttendanceForm_'));
        const isFormRequest = e.target && e.target.id && (formIds.includes(e.target.id) || isDeleteForm);

        if (isFormRequest && e.detail.successful) {
            document.querySelectorAll('.modal').forEach(modalEl => {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });

            const errorContainer = document.getElementById('error-message');
            if (errorContainer) errorContainer.style.display = 'none';
            const fallbackError = document.getElementById('fallback-error');
            if (fallbackError) fallbackError.style.display = 'none';

            if (e.target.id === 'editEmployeeForm' || e.target.id === 'editAttendanceForm') {
                const successContainer = document.getElementById('success-message');
                if (successContainer) {
                    successContainer.style.display = 'block';
                    setTimeout(() => {
                        successContainer.style.display = 'none';
                    }, 3000);
                }
            }
        }

        if (isFormRequest && !e.detail.successful) {
            console.error(`HTMX request failed for ${e.target.id || 'unknown form'}:`, e.detail.xhr.status, e.detail.xhr.responseText);
            const errorContainer = document.getElementById('error-message') || document.getElementById('fallback-error');
            if (errorContainer) {
                let errorMessage = 'An unexpected error occurred. Please try again.';
                if (e.detail.xhr.status === 422) {
                    errorMessage = e.detail.xhr.responseText.match(/<li[^>]*>([^<]*)<\/li>/)?.[1] || 'Validation error occurred';
                } else if (e.detail.xhr.status === 500) {
                    errorMessage = e.detail.xhr.responseText.match(/<div[^>]*alert-danger[^>]*>([^<]*)<\/div>/)?.[1] || 'Server error occurred. Please try again later.';
                }
                errorContainer.innerHTML = errorMessage;
                errorContainer.style.display = 'block';
            }
        }

        if (typeof isNavigating !== 'undefined' && isNavigating) {
            isNavigating = false;
            console.log('Navigation completed, isNavigating reset, states:', { isNavigating, isSidebarToggled });
            if (!isSidebarToggled && !isSidebarHovered && sidebar) {
                sidebar.classList.remove('visible');
                console.log('Sidebar hidden after navigation, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            }
        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-position') || e.target.classList.contains('edit-employee')) {
            e.preventDefault();
            const isPosition = e.target.classList.contains('edit-position');
            const type = isPosition ? 'position' : 'employee';
            const id = e.target.getAttribute('data-id');
            console.log(`Edit ${type} button clicked`, { id });

            const modalId = isPosition ? 'editPositionModal' : 'editEmployeeModal';
            const formTarget = isPosition ? '#edit-position-form' : '#edit-employee-form';
            const url = isPosition ? `/dashboard/positions/${id}` : `/dashboard/employees/${id}`;

            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                console.error(`Edit ${type} modal not found`);
                return;
            }

            let modal;
            try {
                modal = new bootstrap.Modal(modalElement);
            } catch (error) {
                console.error(`Failed to initialize Bootstrap modal for ${type}:`, error);
                return;
            }

            htmx.ajax('GET', url, {
                target: formTarget,
                swap: 'innerHTML'
            }).then(() => {
                console.log(`HTMX request completed for ${type}, showing modal`);
                modal.show();
            }).catch(error => {
                console.error(`HTMX request failed for ${type}:`, error);
                const errorContainer = document.getElementById('error-message') || document.getElementById('fallback-error');
                if (errorContainer) {
                    errorContainer.innerHTML = `Failed to load ${type} data.`;
                    errorContainer.style.display = 'block';
                }
            });
        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-qr')) {
            const qrCode = e.target.getAttribute('data-qr');
            currentQrCode = qrCode;
            console.log('View QR clicked', { qrCode });
            const qrModal = new bootstrap.Modal(document.getElementById('viewQrModal'));
            const qrImage = document.getElementById('qrImage');
            qrImage.src = `/qr_codes/${qrCode}.png`;
            qrModal.show();
        }

        if (e.target.id === 'downloadQrButton') {
            if (currentQrCode) {
                console.log('Download QR button clicked', { currentQrCode });
                downloadQR(currentQrCode);
            } else {
                console.error('No QR code selected for download');
                const errorContainer = document.getElementById('fallback-error');
                if (errorContainer) {
                    errorContainer.innerHTML = 'No QR code available to download.';
                    errorContainer.style.display = 'block';
                }
            }
        }
        if (e.target.classList.contains('logout-link')) {
            console.log('Logout link clicked');
        }
    });

    const viewQrModal = document.getElementById('viewQrModal');
    if (viewQrModal) {
        viewQrModal.addEventListener('hidden.bs.modal', () => {
            currentQrCode = null;
            console.log('QR modal hidden, currentQrCode reset');
            const qrImage = document.getElementById('qrImage');
            if (qrImage) qrImage.src = '';
        });
    }

    document.body.addEventListener('htmx:afterSwap', function(evt) {
        console.log('app.js: htmx:afterSwap for target:', evt.detail.target.id);
        
        // Skip processing if the target doesn't exist or is already processed
        if (!evt.detail.target || !evt.detail.target.id) return;
        
        // Only process non-requests content to avoid conflicts
        if (!evt.detail.target.id.includes('requests') && 
            evt.detail.target.id !== 'content-area') {
            try {
                htmx.process(evt.detail.target);
            } catch (e) {
                console.error('HTMX processing error:', e);
            }
        }

        // Reinitialize scripts for specific sections
        if (evt.detail.target.id === 'attendance-checkin-section') {
            if (typeof window.initUploadListeners === 'function') {
                window.initUploadListeners();
            }
            if (typeof window.initCameraListeners === 'function') {
                window.initCameraListeners();
            }
        }

        // Reinitialize clock for attendance sections
        if (evt.detail.target.id === 'attendance-section' || evt.detail.target.id === 'attendance-report-section') {
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
        }
    });
});

function downloadQR(qrCode) {
    try {
        if (!qrCode) {
            throw new Error('QR code is not provided');
        }
        const url = `/qr_codes/${qrCode}.png`;
        const link = document.createElement('a');
        link.href = url;
        link.download = `qr_code_${qrCode}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        console.log('QR code downloaded:', qrCode);
    } catch (error) {
        console.error('Error in downloadQR:', error);
        const errorContainer = document.getElementById('fallback-error');
        if (errorContainer) {
            errorContainer.innerHTML = 'An unexpected error occurred while downloading the QR code.';
            errorContainer.style.display = 'block';
        }
    }
}
document.addEventListener('htmx:responseError', function(evt) {
    console.error('HTMX Response Error:', evt.detail);
    console.log('Raw Response:', evt.detail.xhr.responseText);
    console.log('Response Headers:', evt.detail.xhr.getAllResponseHeaders());
    console.log('Target Element:', evt.detail.target);

    const target = evt.detail.target || document.getElementById('content-area');
    if (!target) return;

    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.textContent = `Request failed: ${evt.detail.xhr.statusText} (Status: ${evt.detail.xhr.status})`;
    target.prepend(errorDiv);
});
document.addEventListener('htmx:afterRequest', function(evt) {
    // Handle successful form submissions
    if (evt.detail.target.id === 'overtimeRequestForm' && evt.detail.successful) {
        const modalEl = document.getElementById('overtimeRequestModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
        
        // HTMX will automatically handle the redirect response
    }
});
document.addEventListener('htmx:configRequest', function(evt) {
    // Force HTML responses
    evt.detail.headers['Accept'] = 'text/html';
    evt.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
});