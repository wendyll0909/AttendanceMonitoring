@php
    $searchPresent = request()->query('search_present', '');
    $searchAbsent = request()->query('search_absent', '');
    $presentCount = $present->count();
    $absentCount = $absent->count();
@endphp

<div id="attendance-section">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Daily Attendance</h2>
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div id="datetime-clock" class="badge bg-light text-dark fs-6"></div>
            <form hx-post="{{ route('attendance.clear') }}"
                  hx-target="#attendance-section"
                  hx-swap="innerHTML"
                  hx-push-url="false"
                  hx-confirm="Are you sure you want to clear today's attendance data? This will save the data as a batch and clear the current records."
                  hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash-fill"></i> Clear Today's Data
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1 text-success">Present Employees</h5>
                            <p class="mb-0 text-muted">Checked in today</p>
                        </div>
                        <span class="badge bg-success rounded-pill fs-5">{{ $presentCount }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1 text-danger">Absent Employees</h5>
                            <p class="mb-0 text-muted">No check-in recorded</p>
                        </div>
                        <span class="badge bg-danger rounded-pill fs-5">{{ $absentCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tables Section -->
    <div class="row g-4">
        <!-- Present Employees -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Present Employees</h5>
                    <span class="badge bg-success">{{ $presentCount }}</span>
                </div>
                <div class="card-body">
                    <form hx-get="{{ route('attendance.record') }}"
                          hx-target="#attendance-section"
                          hx-swap="innerHTML"
                          hx-push-url="false"
                          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                          class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search_present" class="form-control" 
                                   placeholder="Search present employees..." value="{{ $searchPresent }}">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Status</th>
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
                                                <span class="badge bg-warning text-dark">Late</span>
                                            @else
                                                <span class="badge bg-success">On Time</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No employees have checked in today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($present->hasPages())
                        <div class="d-flex justify-content-center mt-3 pagination-hidden">
                            {{ $present->appends(['search_present' => $searchPresent, 'search_absent' => $searchAbsent])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Absent Employees -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Absent Employees</h5>
                    <span class="badge bg-danger">{{ $absentCount }}</span>
                </div>
                <div class="card-body">
                    <form hx-get="{{ route('attendance.record') }}"
                          hx-target="#attendance-section"
                          hx-swap="innerHTML"
                          hx-push-url="false"
                          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                          class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search_absent" class="form-control" 
                                   placeholder="Search absent employees..." value="{{ $searchAbsent }}">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($absent as $employee)
                                    <tr>
                                        <td>{{ $employee->full_name }}</td>
                                        <td>{{ $employee->position->position_name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">All employees are present today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($absent->hasPages())
                        <div class="d-flex justify-content-center mt-3 pagination-hidden">
                            {{ $absent->appends(['search_present' => $searchPresent, 'search_absent' => $searchAbsent])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#datetime-clock {
    padding: 8px 12px;
    border-radius: 20px;
    font-family: 'Poppins', sans-serif;
    min-width: 220px;
    text-align: center;
}

.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    font-weight: 600;
}

.table th {
    white-space: nowrap;
    font-weight: 500;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.badge {
    font-weight: 500;
    padding: 5px 10px;
}

.pagination-hidden {
    display: none;
}

@media (max-width: 992px) {
    .header-container {
        flex-direction: column;
        gap: 15px;
    }
    
    .right-content {
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
    }
}

@media (max-width: 576px) {
    .right-content {
        flex-direction: column;
        gap: 10px;
    }
    
    #datetime-clock {
        width: 100%;
    }
}
</style>

<script>
(function() {
    const now = new Date();
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = days[now.getDay()];
    const hours = now.getHours() % 12 || 12; // Convert to 12-hour format
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
    const timeString = `${dayName} ${hours}:${minutes}:${seconds} ${ampm}`;
    const dateString = now.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });

    const clockElement = document.getElementById('datetime-clock');
    if (clockElement) {
        clockElement.innerHTML = `<i class="bi bi-clock"></i> ${timeString}<br>${dateString}`;
    }
})();
</script>