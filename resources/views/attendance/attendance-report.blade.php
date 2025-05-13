@php
    $selectedDate = request()->query('date', now()->toDateString());
    $presentCount = $present->count();
    $absentCount = $absent->count();
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Present Employees</h5>
                    <p class="card-text display-4">{{ $presentCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Absent Employees</h5>
                    <p class="card-text display-4">{{ $absentCount }}</p>
                </div>
            </div>
        </div>
    </div>

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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Present Employees ({{ $selectedDate }})</h3>
                <span class="badge bg-success">{{ $presentCount }} employees</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Late Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($present as $attendance)
                            <tr>
                                <td>{{ $attendance->employee->full_name }}</td>
                                <td>{{ $attendance->employee->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') : 'N/A' }}</td>
                                <td>
                                    @if($attendance->late_status)
                                        <span class="badge bg-danger">Late</span>
                                    @else
                                        <span class="badge bg-success">On Time</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No employees were present on this date</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Absent Employees Table -->
        <div class="table-wrapper" style="flex: 1; min-width: 0;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Absent Employees ({{ $selectedDate }})</h3>
                <span class="badge bg-danger">{{ $absentCount }} employees</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absent as $employee)
                            <tr>
                                <td>{{ $employee->employee->full_name }}</td>
                                <td>{{ $employee->employee->position->position_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">All employees were present on this date</td>
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

.tables-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.table-wrapper {
    flex: 1;
    min-width: 300px;
}

@media (max-width: 768px) {
    .tables-container {
        flex-direction: column;
    }
    
    .card .display-4 {
        font-size: 2rem;
    }
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.table th {
    white-space: nowrap;
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
            htmx.process(document.getElementById('attendance-report-section'));
            updateClock();
        }
    });
})();
</script>