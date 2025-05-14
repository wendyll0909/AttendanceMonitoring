<script>
console.log('Initial script running: Confirming JavaScript execution');
</script>

<div id="attendance-checkin-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Check In</h2>
        <div id="datetime-clock" class="text-muted" style="font-size: 1.1rem; font-weight: 500;"></div>
    </div>
    <div class="mb-3">
        <div class="alert alert-info" id="deadline-display">
            <strong>Check-In Deadline:</strong> {{ date('h:i A', strtotime($checkInDeadline)) }} (Employees checking in after this time will be marked as late)
        </div>
        <form method="POST" 
              action="{{ route('attendance.deadline.update') }}" 
              class="mb-3"
              hx-post="{{ route('attendance.deadline.update') }}"
              hx-target="#attendance-checkin-section"
              hx-swap="innerHTML"
              hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="check_in_deadline" class="form-label">Set Check-In Deadline</label>
                    <input type="time" class="form-control" id="check_in_deadline" name="check_in_deadline" value="{{ substr($checkInDeadline, 0, 5) }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Update Deadline</button>
                </div>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
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
    </div>
    <div class="row">
        <div class="col-md-4">
            <h4>Check-In via Camera</h4>
            <div class="card p-3 mb-3">
                <div class="mb-3">
                    <label class="form-label">QR Code Scan</label>
                    <button id="startCamera" class="btn btn-primary w-100">Start Camera</button>
                    <button id="stopCamera" class="btn btn-danger w-100 mt-2" style="display: none;">Stop Camera</button>
                    <video id="qrVideo" style="display: none; width: 100%; max-height: 200px;" autoplay playsinline></video>
                    <canvas id="qrCanvas" style="display: none;"></canvas>
                    <div id="qrResult" class="mt-2" style="display: none;"></div>
                </div>
                <button id="submitCameraCheckin" class="btn btn-primary w-100" disabled>Submit Camera Check-In</button>
            </div>
        </div>
        <div class="col-md-4">
            <h4>Check-In via Image Upload</h4>
            <div class="card p-3 mb-3">
                <div class="mb-3">
                    <label for="qrUpload" class="form-label">Upload QR Code Image</label>
                    <input type="file" id="qrUpload" accept="image/*" class="form-control">
                    <div id="uploadPreview" class="mt-2 text-center"></div>
                </div>
                <button id="submitUploadCheckin" class="btn btn-primary w-100" disabled>Submit Upload Check-In</button>
            </div>
        </div>
        <div class="col-md-4">
            <h4>Manual Check-In</h4>
            <div class="card p-3 mb-3">
                <div class="mb-3">
                    <label for="manualEmployee" class="form-label">Select Employee</label>
                    <select id="manualEmployee" class="form-control">
                        <option value="">Select Employee</option>
                        @foreach (App\Models\Employee::where('status', 'active')->get() as $employee)
                            @if (!$checkins->contains('employee_id', $employee->employee_id))
                                <option value="{{ $employee->employee_id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <button id="submitManualCheckin" class="btn btn-primary w-100">Submit Manual Check-In</button>
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
                            <th>Method</th>
                            <th>Late Status</th>
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
                                <td>{{ ucfirst(str_replace('_', ' ', $checkin->check_in_method)) }}</td>
                                <td>
                                    @if ($checkin->late_status)
                                        <span class="badge bg-warning text-dark">Late</span>
                                    @else
                                        <span class="badge bg-info">On Time</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-attendance"
                                            hx-delete="{{ route('attendance.destroy', $checkin->attendance_id) }}"
                                            hx-target="#attendance-checkin-section"
                                            hx-swap="innerHTML"
                                            hx-confirm="Are you sure you want to delete this check-in?"
                                            hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                            data-attendance-id="{{ $checkin->attendance_id }}"
                                            onclick="this.disabled=true; setTimeout(() => this.disabled=false, 1000)">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No check-ins today</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
console.log('Initial script running: Confirming JavaScript execution');

// Ensure jsQR is loaded
function waitForJsQR(callback, timeout = 5000) {
    const start = Date.now();
    function check() {
        if (typeof jsQR !== 'undefined') {
            console.log('jsQR loaded successfully');
            callback();
        } else if (Date.now() - start > timeout) {
            console.error('jsQR failed to load within timeout');
            showError('QR code scanning library not loaded. Please refresh the page.');
        } else {
            console.log('Waiting for jsQR...');
            setTimeout(check, 100);
        }
    }
    check();
}

(function() {
    console.log('Main script running');

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    if (!csrfToken) {
        console.error('CSRF token not found');
        showError('CSRF token missing. Please refresh the page.');
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
    function formatCheckinTimes() {
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
    }
    formatCheckinTimes();

    // Search Functionality
    function initSearch() {
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
    }
    initSearch();

    // Camera Elements
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const submitCameraBtn = document.getElementById('submitCameraCheckin');
    const submitUploadBtn = document.getElementById('submitUploadCheckin');
    const submitManualBtn = document.getElementById('submitManualCheckin');
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

    // Check Server for Existing Check-In
    async function checkServerCheckin(employeeId) {
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
            console.log('Server check-in response:', data);
            return data.hasCheckin;
        } catch (err) {
            console.error('Failed to check server for check-in:', err);
            showError('Unable to verify check-in status. Please try again.');
            throw err;
        }
    }

    // Initialize Upload Event Listeners
    function initUploadListeners() {
        console.log('initUploadListeners called');
        const qrUpload = document.getElementById('qrUpload');
        const submitUploadBtn = document.getElementById('submitUploadCheckin');
        const uploadPreview = document.getElementById('uploadPreview');

        if (!qrUpload || !submitUploadBtn || !uploadPreview) {
            console.error('Upload elements not found:', {
                qrUpload: !!qrUpload,
                submitUploadBtn: !!submitUploadBtn,
                uploadPreview: !!uploadPreview
            });
            return;
        }

        // Remove existing listeners to prevent duplicates
        if (qrUpload._uploadHandler) {
            qrUpload.removeEventListener('change', qrUpload._uploadHandler);
        }
        if (submitUploadBtn._submitHandler) {
            submitUploadBtn.removeEventListener('click', submitUploadBtn._submitHandler);
        }

        qrUpload._uploadHandler = function(e) {
            console.log('Upload input changed, processing file:', e.target.files[0]?.name);
            const file = e.target.files[0];
            if (!file) {
                console.warn('No file selected');
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
                    console.log('FileReader loaded, displaying preview');
                    uploadPreview.innerHTML = `<img src="${event.target.result}" style="max-width: 100%; max-height: 200px;">`;
                };
                reader.onerror = (err) => {
                    console.error('FileReader error:', err);
                    showError('Failed to read file');
                    submitUploadBtn.disabled = true;
                    qrCode = null;
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
                        inversionAttempts: 'dontInvert'
                    });
                    
                    if (code) {
                        qrCode = code.data;
                        console.log('QR code detected:', qrCode);
                        submitUploadBtn.disabled = false;
                    } else {
                        console.warn('No QR code found in the image');
                        showError('No QR code found in the image. Please ensure the image contains a valid QR code.');
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
            console.log('Submit upload button clicked, qrCode:', qrCode, 'isSubmitting:', isSubmitting);
            if (qrCode && !isSubmitting) {
                debouncedSubmitCheckin(qrCode, 'qr_upload');
            } else {
                showError(isSubmitting ? 'Please wait, submission in progress' : 'No QR code detected');
            }
        };

        qrUpload.addEventListener('change', qrUpload._uploadHandler);
        submitUploadBtn.addEventListener('click', submitUploadBtn._submitHandler);
        console.log('Upload listeners attached');

        submitUploadBtn.disabled = true;
        qrCode = null;
    }

    // Camera Functionality
    function initCameraListeners() {
        console.log('initCameraListeners called');
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

        submitCameraBtn.addEventListener('click', () => {
            console.log('Submit camera button clicked, qrCode:', qrCode, 'isSubmitting:', isSubmitting);
            if (qrCode && !isSubmitting) {
                debouncedSubmitCheckin(qrCode, 'qr_camera');
            } else {
                showError(isSubmitting ? 'Please wait, submission in progress' : 'No QR code detected');
            }
        });
    }

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
                    inversionAttempts: 'dontInvert'
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

    // Manual Check-In Functionality
    async function handleManualCheckin(e) {
        if (e.target && e.target.id === 'submitManualCheckin' && !isSubmitting) {
            console.log('Manual check-in button clicked');
            submitManualBtn.disabled = true;
            const manualEmployeeSelect = document.getElementById('manualEmployee');
            const employeeId = manualEmployeeSelect.value;
            if (!employeeId) {
                showError('Please select an employee for manual check-in.');
                submitManualBtn.disabled = false;
                return;
            }

            const checkinRows = document.querySelectorAll('#attendance-checkin-section table tbody tr');
            let isCheckedIn = false;
            checkinRows.forEach(row => {
                const employeeCell = row.querySelector('td:first-child');
                if (employeeCell && employeeCell.textContent.includes(manualEmployeeSelect.selectedOptions[0].text)) {
                    isCheckedIn = true;
                }
            });

            if (isCheckedIn) {
                showError('This employee has already checked in today (client-side check).');
                submitManualBtn.disabled = false;
                return;
            }

            try {
                const hasCheckin = await checkServerCheckin(employeeId);
                if (hasCheckin) {
                    showError('This employee has already checked in today (server-side check).');
                    submitManualBtn.disabled = false;
                    return;
                }
            } catch (err) {
                submitManualBtn.disabled = false;
                return;
            }

            debouncedSubmitCheckin(employeeId, 'manual');
        }
    }

    // Helper Function to Submit Check-In
    function submitCheckin(employeeIdOrCode, method) {
        console.log('Submitting check-in:', { employeeIdOrCode, method });
        if (isSubmitting) {
            showError('Please wait, a submission is already in progress.');
            return;
        }

        isSubmitting = true;
        const loading = document.getElementById('loading');
        if (loading) loading.style.display = 'block';

        if (submitCameraBtn) submitCameraBtn.disabled = true;
        if (submitUploadBtn) submitUploadBtn.disabled = true;
        if (submitManualBtn) submitManualBtn.disabled = true;

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

        fetch('/dashboard/attendance/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/html,application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Fetch response status:', response.status);
            if (!response.ok) {
                return response.json().then(json => {
                    throw new Error(json.error || `Server error: ${response.status}`);
                });
            }
            return response.text();
        })
        .then(html => {
            console.log('Check-in successful, updating DOM');
            document.getElementById('attendance-checkin-section').innerHTML = html;
            showSuccess('Check-in recorded successfully');
            resetFormState();
            updateClock();
            formatCheckinTimes();
            htmx.process(document.getElementById('attendance-checkin-section'));
            waitForJsQR(() => {
                initUploadListeners();
                initCameraListeners();
            });
        })
        .catch(error => {
            console.error('Check-in submission failed:', error);
            showError(error.message || 'Submission failed. Check console for details.');
            return fetch('/dashboard/attendance/checkin', {
                headers: { 
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('attendance-checkin-section').innerHTML = html;
                updateClock();
                formatCheckinTimes();
                htmx.process(document.getElementById('attendance-checkin-section'));
                waitForJsQR(() => {
                    initUploadListeners();
                    initCameraListeners();
                });
            });
        })
        .finally(() => {
            isSubmitting = false;
            console.log('Submission complete, isSubmitting reset');
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
        if (submitManualBtn) submitManualBtn.disabled = document.getElementById('manualEmployee')?.value === '';
    }

    const debouncedSubmitCheckin = debounce(submitCheckin, 1500);

    // Initialize HTMX
    function initializeHtmx() {
        if (typeof htmx === 'undefined') {
            console.warn('HTMX not loaded, attempting to load dynamically');
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/htmx.org@2.0.3';
            script.onload = () => {
                console.log('HTMX loaded dynamically');
                setupHtmxListeners();
            };
            script.onerror = () => {
                console.error('Failed to load HTMX dynamically');
                showError('HTMX library not loaded. Please refresh the page.');
            };
            document.head.appendChild(script);
        } else {
            setupHtmxListeners();
        }
    }

    function setupHtmxListeners() {
        htmx.on('htmx:beforeRequest', (e) => console.log('HTMX request started', e.detail));
        htmx.on('htmx:afterSwap', (e) => {
            console.log('HTMX swap completed, reinitializing', e.detail);
            formatCheckinTimes();
            initSearch(); // Re-initialize search after HTMX swap
            if (document.getElementById('attendance-checkin-section')) {
                waitForJsQR(() => {
                    initUploadListeners();
                    initCameraListeners();
                });
                document.removeEventListener('click', handleManualCheckin);
                document.addEventListener('click', handleManualCheckin);
            }
        });
    }

    // Initialize
    waitForJsQR(() => {
        console.log('jsQR loaded, initializing QR functionality');
        initUploadListeners();
        initCameraListeners();
    });

    initializeHtmx();
    document.removeEventListener('click', handleManualCheckin);
    document.addEventListener('click', handleManualCheckin);

    // Debug event listener attachment
    document.getElementById('qrUpload')?.addEventListener('change', () => {
        console.log('Debug: qrUpload change event triggered');
    });
})();
</script>