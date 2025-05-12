@php
    $searchPresent = request()->query('search_present', '');
    $searchAbsent = request()->query('search_absent', '');
@endphp

<div id="attendance-section">
    <div class="header-container">
        <h2>Record Attendance</h2>
        <div class="right-content">
            <div id="datetime-clock" class="text-muted"></div>
            <!-- Clear Data Button -->
            <div class="clear-button">
                <form hx-post="{{ route('attendance.clear') }}"
                      hx-target="#attendance-section"
                      hx-swap="innerHTML"
                      hx-push-url="false"
                      hx-confirm="Are you sure you want to clear today's attendance data? This will save the data as a batch and clear the current records."
                      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                    @csrf
                    <button type="submit" class="btn btn-danger">Clear Today’s Attendance Data</button>
                </form>
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
    <div class="tables-container">
        <!-- Present Employees Table -->
        <div class="table-wrapper">
            <h3>Present Employees ({{ now()->toDateString() }})</h3>
            <div class="mb-3">
                <form hx-get="{{ route('attendance.record') }}"
                      hx-target="#attendance-section"
                      hx-swap="innerHTML"
                      hx-push-url="false"
                      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                    <div class="input-group">
                        <input type="text" name="search_present" class="form-control" placeholder="Search present employees..." value="{{ $searchPresent }}">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
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
                                <td>{{ $attendance->late_status ? 'Late' : 'On Time' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $present->appends(['search_present' => $searchPresent, 'search_absent' => $searchAbsent])->links() }}
            </div>
        </div>

        <!-- Absent Employees Table -->
        <div class="table-wrapper">
            <h3>Absent Employees ({{ now()->toDateString() }})</h3>
            <div class="mb-3">
                <form hx-get="{{ route('attendance.record') }}"
                      hx-target="#attendance-section"
                      hx-swap="innerHTML"
                      hx-push-url="false"
                      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                    <div class="input-group">
                        <input type="text" name="search_absent" class="form-control" placeholder="Search absent employees..." value="{{ $searchAbsent }}">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
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
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->position->position_name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $absent->appends(['search_present' => $searchPresent, 'search_absent' => $searchAbsent])->links() }}
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
    font-size: 1.1rem;
    font-weight: 500;
}

/* Header container styling */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.right-content {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.clear-button {
    margin-top: 10px;
}

/* Horizontal layout for tables */
.tables-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.table-wrapper {
    flex: 1;
    min-width: 300px; /* Minimum width to prevent tables from becoming too narrow */
}

/* Stack tables vertically on smaller screens */
@media (max-width: 768px) {
    .tables-container {
        flex-direction: column;
    }
    .right-content {
        align-items: flex-start;
    }
}
</style>

<script>
(function() {
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
})();
</script>