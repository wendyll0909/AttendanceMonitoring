<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nietes Design Builders - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/NDBLogo.png') }}">
</head>
<body>
    <div class="container-fluid d-flex">
        <!-- Hamburger Menu Button -->
        <div class="hamburger-menu">
            <i class="bi bi-list"></i>
        </div>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="d-flex align-items-center justify-content-center my-3">
                <img src="{{ asset('assets/img/NDBLogo.png') }}" class="img-fluid me-3" style="max-width: 80px;" alt="Company Logo">
                <h1 class="m-0">Nietes Design Builders</h1>
            </div>
            <h1>MENU</h1>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('dashboard') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('employees.index') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar data-toggle-dropdown>
                        <i class="bi bi-people-fill"></i> Employees
                    </a>
                    <ul class="employee-dropdown" style="display: none;">
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('employees.inactive') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                                View Inactive Employees
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('positions.index') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                                View Positions
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
             <a href="#" class="nav-link" data-toggle-dropdown hx-get="{{ route('attendance.record') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                 <i class="bi bi-calendar2-plus-fill"></i> Attendance
                 </a>
                  <ul class="attendance-dropdown" style="display: none;">
                   <li>
               <a href="#" class="nav-link dropdown-link" hx-get="{{ route('attendance.checkin') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                Check In
            </a>
                    </li>
                <li>
            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('attendance.checkout') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                Check Out
            </a>
              </li>
       
                     <li>
            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('attendance.report') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                Attendance Reports
               </a>
             </li>
                   </ul>
                    </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" 
                 hx-get="{{ route('requests.index') }}" 
               hx-target="#content-area" 
                  hx-swap="innerHTML" 
                     hx-push-url="false" 
               data-persist-sidebar
                hx-headers='{"Accept": "text/html", "X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                hx-ext="disable-json">
                                  <i class="bi bi-list-check"></i> Requests
                            </a>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('payroll.index') }}" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="false" data-persist-sidebar>
                        <i class="bi bi-currency-dollar"></i> Payroll Export
                    </a>
                </li>
               
            </ul>
             <li class="nav-item">
<footer class="sidebar-footer">
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="logout-link" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">Logout</button>
            </form>
        </footer>
            </li>
        </div>
        <div class="content" id="content-area">
            
           <!-- Main Content -->
        <div class="content">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>

            <!-- Dashboard Content -->
            <div id="dashboard-section" hx-get="{{ route('dashboard') }}" hx-trigger="every 60s">
                <h2>Dashboard</h2>
                <div class="row mb-4">
                    <!-- Top Employee Card -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-star-fill me-2 text-warning"></i>Top Employee</h5>
                                <p class="card-text">Highest hours worked this week</p>
                                <h4>{{ $topEmployee['name'] }}</h4>
                                <p class="text-muted">{{ $topEmployee['hours'] }} hours</p>
                            </div>
                        </div>
                    </div>
                    <!-- Employee Ranking Card -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-trophy-fill me-2 text-primary"></i>Employee Ranking</h5>
                                <p class="card-text">Earliest check-ins today</p>
                                <ol>
                                    @forelse ($employeeRanking as $employee)
                                        <li>{{ $employee['name'] }} - {{ $employee['check_in_time'] }}</li>
                                    @empty
                                        <li>No data available</li>
                                    @endforelse
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- Working Employees Card -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-person-check-fill me-2 text-success"></i>Working Employees</h5>
                                <p class="card-text">Total employees currently working</p>
                                <h4>{{ $presentCount }}</h4>
                                <p class="text-muted">Active today</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Attendance Report Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-bar-chart-fill me-2 text-info"></i>Attendance Report</h5>
                                <p class="card-text">Average attendance per week</p>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Week</th>
                                            <th>Average Hours</th>
                                            <th>Employees</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attendanceReport as $report)
                                            <tr>
                                                <td>{{ $report['week'] }}</td>
                                                <td>{{ $report['avg_hours'] }} hours</td>
                                                <td>{{ $report['employee_count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
     
<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit()">Logout</button>
            </div>
        </div>
    </div>
</div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm" hx-post="{{ route('employees.store') }}" hx-target="#employees-section" hx-swap="innerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="fname" name="fname" required>
                        </div>
                        <div class="mb-3">
                            <label for="mname" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="mname" name="mname">
                        </div>
                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lname" name="lname" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="position_id" class="form-label">Position</label>
                            <select class="form-control" id="position_id" name="position_id" required
                                    hx-get="{{ route('positions.list') }}"
                                    hx-target="this"
                                    hx-swap="innerHTML"
                                    hx-trigger="shown.bs.modal from:#addEmployeeModal">
                                <option value="">Loading positions...</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-employee-form">
                    <p>Loading employee data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-labelledby="addPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPositionModalLabel">Add Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPositionForm" hx-post="{{ route('positions.store') }}" hx-target="#positions-section" hx-swap="innerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="position_name" class="form-label">Position Name</label>
                            <input type="text" class="form-control" id="position_name" name="position_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="base_salary" class="form-label">Base Salary</label>
                            <input type="number" step="0.01" class="form-control" id="base_salary" name="base_salary">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Position</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div class="modal fade" id="editPositionModal" tabindex="-1" aria-labelledby="editPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPositionModalLabel">Edit Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-position-form">
                    <p>Loading position data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- View QR Code Modal -->
    <div class="modal fade" id="viewQrModal" tabindex="-1" aria-labelledby="viewQrModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewQrModalLabel">Employee QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="max-width: 100%; height: auto;">
                    <div class="mt-3">
                        <button class="btn btn-primary" id="downloadQrButton">Download QR Code</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-attendance-form">
                    <p>Loading attendance data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Load scripts in the correct order -->
    <script src="https://unpkg.com/htmx.org@2.0.3"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>