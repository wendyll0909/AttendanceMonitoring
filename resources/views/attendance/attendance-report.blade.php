@php
    $selectedDate = request()->query('date', now()->toDateString());
@endphp

<div id="attendance-report-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Attendance Report</h2>
        <div id="datetime-clock" class="text-muted" style="font-size: 1.1rem; font-weight: 500;"></div>
    </div>

    <!-- Date Selection Form -->
    <form id="date-selection-form"
          hx-get="{{ route('attendance.report') }}"
          hx-target="#attendance-report-section"
          hx-swap="innerHTML"
          hx-push-url="false"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="date" class="form-label">Select Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">View Report</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('attendance.report.pdf', ['date' => $selectedDate]) }}" class="btn btn-primary">Export to PDF</a>
            </div>
        </div>
    </form>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tables Container -->
    <div class="tables-container" style="display: flex; gap: 20px; flex-wrap: wrap;">
        <!-- Present Employees Table -->
        <div class="table-wrapper" style="flex: 1; min-width: 0;">
            <h3>Present Employees ({{ $selectedDate }})</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Late Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($present as $attendance)
                            <tr>
                                <td>{{ $attendance->employee->full_name }}</td>
                                <td>{{ $attendance->employee->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $attendance->check_in_time ?? 'N/A' }}</td>
                                <td>{{ $attendance->check_out_time ?? 'N/A' }}</td>
                                <td>{{ $attendance->late_status ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Absent Employees Table -->
        <div class="table-wrapper" style="flex: 1; min-width: 0;">
            <h3>Absent Employees ({{ $selectedDate }})</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($absent as $employee)
                            <tr>
                                <td>{{ $employee->employee->full_name }}</td>
                                <td>{{ $employee->employee->position->position_name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
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

/* Ensure tables take equal width and adjust for smaller screens */
.tables-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.table-wrapper {
    flex: 1;
    min-width: 300px; /* Minimum width to prevent tables from becoming too narrow */
}

@media (max-width: 768px) {
    .tables-container {
        flex-direction: column;
    }
}
</style>

<script>
(function() {
    // Update clock
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

    // Ensure HTMX processes the form
    document.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.detail.target.id === 'attendance-report-section') {
            console.log('HTMX swap completed for attendance-report-section');
            htmx.process(document.getElementById('attendance-report-section'));
            updateClock();
        }
    });

    // Debug HTMX requests
    document.addEventListener('htmx:beforeRequest', function(evt) {
        console.log('HTMX request starting', evt.detail);
    });

    document.addEventListener('htmx:responseError', function(evt) {
        console.error('HTMX response error', evt.detail);
        alert('Failed to load report: ' + evt.detail.xhr.statusText);
    });
})();
</script>