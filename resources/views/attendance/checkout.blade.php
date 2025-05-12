<script>
console.log('Initial script running: Confirming JavaScript execution');
</script>

<div id="attendance-checkout-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Check Out</h2>
        <div id="datetime-clock" class="text-muted" style="font-size: 1.1rem; font-weight: 500;"></div>
    </div>
    <div class="mb-3">
        <div id="error-container" class="alert alert-danger alert-dismissible" style="display: none;">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <span id="error-message"></span>
        </div>
        <div id="success-container" class="alert alert-success alert-dismissible" style="display: none;">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <span id="success-message"></span>
        </div>
        <div id="loading" style="display: none;" class="text-center my-3">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>Check-Out via Camera</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label">QR Code Scan</label>
                        <button id="startCamera" class="btn btn-primary w-100">Start Camera</button>
                        <button id="stopCamera" class="btn btn-danger w-100 mt-2" style="display: none;">Stop Camera</button>
                        <video id="qrVideo" style="display: none; width: 100%; max-height: 200px;" autoplay playsinline></video>
                        <canvas id="qrCanvas" style="display: none;"></canvas>
                        <div id="qrResult" class="mt-2" style="display: none;"></div>
                    </div>
                    <button id="submitCameraCheckout" class="btn btn-primary w-100" disabled>Submit Camera Check-Out</button>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Check-Out via Image Upload</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label for="qrUpload" class="form-label">Upload QR Code Image</label>
                        <input type="file" id="qrUpload" accept="image/*" class="form-control">
                        <div id="uploadPreview" class="mt-2 text-center"></div>
                    </div>
                    <button id="submitUploadCheckout" class="btn btn-primary w-100" disabled>Submit Upload Check-Out</button>
                </div>
            </div>
            <div class="col-12">
                <h4>Today's Check-Ins</h4>
                <div class="mb-3">
                    <input type="text" id="searchCheckins" class="form-control" placeholder="Search employees...">
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        {{ session('error') }}
                    </div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered" id="checkinsTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Check-In Time</th>
                                <th>Late Status</th>
                                <th>Check-In Method</th>
                                <th>Check-Out Time</th>
                                <th>Check-Out Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checkins as $checkin)
                                <tr>
                                    <td>{{ $checkin->employee ? ($checkin->employee->fname . ' ' . $checkin->employee->lname) : 'Unknown' }}</td>
                                    <td>
                                        <span class="checkin-time" 
                                              data-date="{{ $checkin->date }}" 
                                              data-time="{{ $checkin->check_in_time }}">
                                            {{ $checkin->date }} {{ $checkin->check_in_time }}
                                        </span>
                                    </td>
                                    <td>
                                    @if ($checkin->late_status)
                                        <span class="badge bg-danger">Late</span>
                                    @else
                                        <span class="badge bg-info">On Time</span>
                                    @endif
                                </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $checkin->check_in_method)) }}</td>
                                    <td>
                                        @if ($checkin->check_out_time)
                                            <span class="checkout-time" 
                                                  data-date="{{ $checkin->date }}" 
                                                  data-time="{{ $checkin->check_out_time }}">
                                                {{ $checkin->date }} {{ $checkin->check_out_time }}
                                            </span>
                                        @else
                                            Not checked out
                                        @endif
                                    </td>
                                    <td>{{ $checkin->check_out_method ? ucfirst(str_replace('_', ' ', $checkin->check_out_method)) : '-' }}</td>
                                    <td>
                                        @if (!$checkin->check_out_time)
                                            <button class="btn btn-sm btn-success checkout-attendance"
                                                    data-employee-id="{{ $checkin->employee_id }}"
                                                    onclick="this.disabled=true; setTimeout(() => this.disabled=false, 1500)">Check Out</button>
                                        @endif
                                        @if ($checkin->check_out_time)
                                            <button class="btn btn-sm btn-danger delete-attendance"
                                                    hx-delete="{{ route('attendance.destroy', $checkin->attendance_id) }}"
                                                    hx-target="#attendance-checkout-section"
                                                    hx-swap="innerHTML"
                                                    hx-confirm="Are you sure you want to clear this check-out?"
                                                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                                    data-attendance-id="{{ $checkin->attendance_id }}"
                                                    onclick="this.disabled=true; setTimeout(() => this.disabled=false, 1000)">Remove Check Out</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No check-ins today</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
</style>

<script>
// Ensure jsQR is loaded
if (typeof jsQR === 'undefined') {
    console.error('jsQR library not loaded');
    document.addEventListener('DOMContentLoaded', () => {
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        if (errorContainer && errorMessage) {
            errorMessage.textContent = 'QR code scanning library not loaded. Please refresh the page.';
            errorContainer.style.display = 'block';
        }
    });
}

(function() {
    console.log('Main script running');

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF token not found');
        alert('CSRF token missing. Please refresh the page.');
    }

    // Date-Time Clock
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

    // Convert server times to local time
    function formatTimes() {
        document.querySelectorAll('.checkin-time').forEach(span => {
            const date = span.dataset.date;
            const time = span.dataset.time;
            const serverDateTime = new Date(`${date}T${time}`);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            span.textContent = serverDateTime.toLocaleString('en-US', options);
        });
        document.querySelectorAll('.checkout-time').forEach(span => {
            const date = span.dataset.date;
            const time = span.dataset.time;
            const serverDateTime = new Date(`${date}T${time}`);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            span.textContent = serverDateTime.toLocaleString('en-US', options);
        });
    }
    formatTimes();

    // Search Functionality
    const searchInput = document.getElementById('searchCheckins');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#checkinsTable tbody tr');
            rows.forEach(row => {
                const employeeCell = row.querySelector('td:first-child');
                if (employeeCell) {
                    const employeeName = employeeCell.textContent.toLowerCase();
                    row.style.display = employeeName.includes(filter) ? '' : 'none';
                }
            });
        });
    }

    // Camera Elements
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const submitCameraBtn = document.getElementById('submitCameraCheckout');
    const submitUploadBtn = document.getElementById('submitUploadCheckout');
    let stream = null;
    let qrCode = null;
    let isSubmitting = false;

    // Error Handling
    function showError(message) {
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        } else {
            console.error('Error container or message element not found');
        }
    }

    function showSuccess(message) {
        const successContainer = document.getElementById('success-container');
        const successMessage = document.getElementById('success-message');
        if (successContainer && successMessage) {
            successMessage.textContent = message;
            successContainer.style.display = 'block';
            setTimeout(() => {
                successContainer.style.display = 'none';
            }, 5000);
        } else {
            console.error('Success container or message element not found');
        }
    }

    // Debounce Utility
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Check Server for Check-In and Check-Out Status
    async function checkServerStatus(employeeId) {
        try {
            const response = await fetch(`/dashboard/attendance/check/${employeeId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (!response.ok) {
                throw new Error(`Server responded with status ${response.status}`);
            }
            const data = await response.json();
            console.log('Server check status response:', data);
            return data.hasCheckin;
        } catch (err) {
            console.error('Failed to check server status:', err);
            showError('Unable to verify check-in status. Please try again.');
            throw err;
        }
    }

    // Initialize Upload Event Listeners
    function initUploadListeners() {
        const qrUpload = document.getElementById('qrUpload');
        const submitUploadBtn = document.getElementById('submitUploadCheckout');
        const uploadPreview = document.getElementById('uploadPreview');

        if (!qrUpload || !submitUploadBtn || !uploadPreview) {
            console.warn('Upload elements not found, skipping initialization');
            return;
        }

        if (qrUpload._uploadHandler) {
            qrUpload.removeEventListener('change', qrUpload._uploadHandler);
        }
        if (submitUploadBtn._submitHandler) {
            submitUploadBtn.removeEventListener('click', submitUploadBtn._submitHandler);
        }

        qrUpload._uploadHandler = function(e) {
            console.log('Upload input changed, processing file');
            const file = e.target.files[0];
            if (!file) {
                showError('No file selected');
                submitUploadBtn.disabled = true;
                qrCode = null;
                return;
            }

            if (typeof jsQR === 'undefined') {
                console.error('jsQR not available');
                showError('QR code scanning library not loaded. Please refresh the page.');
                submitUploadBtn.disabled = true;
                qrCode = null;
                return;
            }

            try {
                console.log('Processing uploaded file:', file.name, 'Size:', file.size, 'Type:', file.type);
                const reader = new FileReader();
                reader.onload = (event) => {
                    uploadPreview.innerHTML = `<img src="${event.target.result}" style="max-width: 100%; max-height: 200px;">`;
                };
                reader.readAsDataURL(file);

                const image = new Image();
                image.src = URL.createObjectURL(file);
                
                image.onload = () => {
                    console.log('Image loaded, dimensions:', image.width, 'x', image.height);
                    const canvas = document.createElement('canvas');
                    canvas.width = image.width;
                    canvas.height = image.height;
                    const context = canvas.getContext('2d');
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);
                    
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    console.log('Image data retrieved, size:', imageData.width, 'x', imageData.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert',
                    });
                    
                    if (code) {
                        qrCode = code.data;
                        console.log('QR code detected:', qrCode);
                        submitUploadBtn.disabled = false;
                    } else {
                        console.warn('No QR code found in the image');
                        showError('No QR code found in the image');
                        submitUploadBtn.disabled = true;
                        qrCode = null;
                    }
                };
                image.onerror = (err) => {
                    console.error('Image load error:', err);
                    showError('Failed to load image');
                    submitUploadBtn.disabled = true;
                    qrCode = null;
                };
            } catch (err) {
                console.error('Error processing image:', err);
                showError('Error processing image: ' + err.message);
                submitUploadBtn.disabled = true;
                qrCode = null;
            }
        };

        submitUploadBtn._submitHandler = function() {
            if (qrCode && !isSubmitting) {
                console.log('Submitting QR code:', qrCode);
                debouncedSubmitCheckout(qrCode, 'qr_upload');
            } else {
                showError(isSubmitting ? 'Please wait, submission in progress' : 'No QR code detected');
            }
        };

        qrUpload.addEventListener('change', qrUpload._uploadHandler);
        submitUploadBtn.addEventListener('click', submitUploadBtn._submitHandler);

        submitUploadBtn.disabled = true;
        qrCode = null;
    }

    // Camera Functionality
    startCameraBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            video.style.display = 'block';
            startCameraBtn.style.display = 'none';
            stopCameraBtn.style.display = 'block';
            scanQRCode();
        } catch (err) {
            showError('Could not access camera: ' + err.message);
        }
    });

    stopCameraBtn.addEventListener('click', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            video.style.display = 'none';
            startCameraBtn.style.display = 'block';
            stopCameraBtn.style.display = 'none';
            submitCameraBtn.disabled = true;
            qrCode = null;
        }
    });

    function scanQRCode() {
        if (!stream) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        
        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });
                
                if (code) {
                    qrCode = code.data;
                    document.getElementById('qrResult').textContent = `Detected: ${qrCode}`;
                    document.getElementById('qrResult').style.display = 'block';
                    submitCameraBtn.disabled = false;
                } else {
                    submitCameraBtn.disabled = true;
                }
            }
            requestAnimationFrame(tick);
        }
        tick();
    }

    submitCameraBtn.addEventListener('click', () => {
        if (qrCode && !isSubmitting) {
            console.log('Submitting camera check-out, button disabled:', submitCameraBtn.disabled);
            debouncedSubmitCheckout(qrCode, 'qr_camera');
        } else {
            showError(isSubmitting ? 'Please wait, submission in progress' : 'No QR code detected');
        }
    });

    // Check-Out Button Functionality
    function handleCheckoutButton(e) {
        if (e.target && e.target.classList.contains('checkout-attendance') && !isSubmitting) {
            const employeeId = e.target.getAttribute('data-employee-id');
            if (!employeeId) {
                showError('Employee ID not found.');
                return;
            }

            const checkoutButton = document.querySelector(`.checkout-attendance[data-employee-id="${employeeId}"]`);
            if (!checkoutButton) {
                showError('This employee has already checked out or has not checked in (client-side check).');
                return;
            }

            checkServerStatus(employeeId)
                .then(hasCheckin => {
                    if (!hasCheckin) {
                        showError('This employee has not checked in today (server-side check).');
                        return;
                    }
                    console.log('Proceeding with check-out for employee:', employeeId);
                    debouncedSubmitCheckout(employeeId, 'manual');
                })
                .catch(() => {
                    // Error already shown in checkServerStatus
                });
        }
    }
    document.removeEventListener('click', handleCheckoutButton);
    document.addEventListener('click', handleCheckoutButton);

    // Helper Function to Submit Check-Out
    function submitCheckout(employeeIdOrCode, method) {
        if (isSubmitting) {
            showError('Please wait, a submission is already in progress.');
            return;
        }

        isSubmitting = true;
        const loading = document.getElementById('loading');
        if (loading) loading.style.display = 'block';

        if (submitCameraBtn) submitCameraBtn.disabled = true;
        if (submitUploadBtn) submitUploadBtn.disabled = true;

        let data = {};
        if (method === 'manual') {
            data = { employee_id: employeeIdOrCode };
        } else {
            if (!employeeIdOrCode.startsWith('EMP-')) {
                showError('Invalid QR code format');
                resetFormState();
                isSubmitting = false;
                return;
            }
            data = { qr_code: employeeIdOrCode, method: method === 'qr_camera' ? 'camera' : 'upload' };
        }

        fetch('/dashboard/attendance/checkout/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/html,application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(json => {
                    throw new Error(json.error || `Server error: ${response.status}`);
                });
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('attendance-checkout-section').innerHTML = html;
            showSuccess('Check-out recorded successfully');
            resetFormState();
            updateClock();
            formatTimes();
            htmx.process(document.getElementById('attendance-checkout-section'));
            initUploadListeners();
        })
        .catch(error => {
            console.error('Check-out submission failed:', error);
            showError(error.message || 'Submission failed. Check console for details.');
            return fetch('/dashboard/attendance/checkout', {
                headers: { 
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('attendance-checkout-section').innerHTML = html;
                updateClock();
                formatTimes();
                htmx.process(document.getElementById('attendance-checkout-section'));
                initUploadListeners();
            });
        })
        .finally(() => {
            isSubmitting = false;
        });
    }

    function resetFormState() {
        console.log('Resetting form state');
        const loading = document.getElementById('loading');
        const qrUpload = document.getElementById('qrUpload');
        const uploadPreview = document.getElementById('uploadPreview');
        
        if (loading) loading.style.display = 'none';
        if (qrUpload) {
            qrUpload.value = '';
            const event = new Event('change');
            qrUpload.dispatchEvent(event);
        }
        if (uploadPreview) uploadPreview.innerHTML = '';
        
        qrCode = null;
        if (submitUploadBtn) submitUploadBtn.disabled = true;
        if (submitCameraBtn) submitCameraBtn.disabled = true;
    }

    // Debounced submitCheckout
    const debouncedSubmitCheckout = debounce(submitCheckout, 1500);

    // HTMX Debugging
    htmx.on('htmx:beforeRequest', (e) => console.log('HTMX request started', e.detail));
    htmx.on('htmx:afterSwap', (e) => {
        console.log('HTMX swap completed, reinitializing upload listeners', e.detail);
        formatTimes();
        if (document.getElementById('attendance-checkout-section')) {
            initUploadListeners();
            document.removeEventListener('click', handleCheckoutButton);
            document.addEventListener('click', handleCheckoutButton);
        }
    });

    // Initialize
    if (document.getElementById('attendance-checkout-section')) {
        initUploadListeners();
        document.addEventListener('click', handleCheckoutButton);
    }
})();
</script>