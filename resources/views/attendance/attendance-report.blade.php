@php
    $startDate = request()->query('start_date', now()->subDays(7)->toDateString());
    $endDate = request()->query('end_date', now()->toDateString());
    $presentCount = $present->count();
    $absentCount = $absent->count();
@endphp

<div id="attendance-report-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Attendance Report</h2>
        <div id="datetime-clock" class="text-muted" style="font-size: 1.1rem; font-weight: 500;"></div>
    </div>

    <!-- Date Range Selection Form -->
    <form id="date-selection-form"
          hx-get="{{ route('attendance.report') }}"
          hx-target="#attendance-report-section"
          hx-swap="innerHTML"
          hx-push-url="true"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">View Report</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('attendance.report.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-primary">Export to PDF</a>
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
                <h3>Present Employees ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h3>
                <span class="badge bg-success">{{ $presentCount }} employees</span>
            </div>
            <div class="mb-3">
                <input type="text" id="present-search" class="form-control" placeholder="Search by name..." oninput="filterTable('present-search', 'present-table')">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="present-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Total Days Present</th>
                            <th>Late Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($present as $record)
                            <tr>
                                <td>{{ $record['employee']->full_name }}</td>
                                <td>{{ $record['employee']->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $record['total_days'] }}</td>
                                <td>{{ $record['late_days'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No employees were present in this date range</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Absent Employees Table -->
        <div class="table-wrapper" style="flex: 1; min-width: 0;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Absent Employees ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h3>
                <span class="badge bg-danger">{{ $absentCount }} employees</span>
            </div>
            <div class="mb-3">
                <input type="text" id="absent-search" class="form-control" placeholder="Search by name..." oninput="filterTable('absent-search', 'absent-table')">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="absent-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Absent Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absent as $record)
                            <tr>
                                <td>{{ $record['employee']->full_name }}</td>
                                <td>{{ $record['employee']->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $record['absent_days'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">All employees were present in this date range</td>
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

    // Filter table rows based on search input
    window.filterTable = function(searchInputId, tableId) {
        const searchInput = document.getElementById(searchInputId);
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tbody tr');
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const nameCell = row.cells[0]; // Name is in the first column
            const nameText = nameCell.textContent.toLowerCase();
            row.style.display = nameText.includes(searchTerm) ? '' : 'none';
        });
    };

    // Ensure HTMX processes the form and reapply search after swap
    document.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.detail.target.id === 'attendance-report-section') {
            htmx.process(document.getElementById('attendance-report-section'));
            updateClock();
            // Reapply search filters if inputs have values
            const presentSearch = document.getElementById('present-search');
            const absentSearch = document.getElementById('absent-search');
            if (presentSearch && presentSearch.value) {
                filterTable('present-search', 'present-table');
            }
            if (absentSearch && absentSearch.value) {
                filterTable('absent-search', 'absent-table');
            }
        }
    });
})();
</script>