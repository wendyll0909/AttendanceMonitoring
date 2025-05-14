<style>
    /* Custom styles for horizontal table layout */
    .request-section {
        padding: 15px;
    }
    .request-section h3 {
        margin-bottom: 15px;
    }
    .request-section .table {
        font-size: 0.9rem;
        table-layout: fixed;
    }
    .request-section .table th,
    .request-section .table td {
        padding: 8px;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .request-section .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .request-section .pagination {
        display: none !important;
    }
    .action-btn {
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    @media (max-width: 768px) {
        .request-section .table {
            font-size: 0.8rem;
        }
        .request-section {
            padding: 10px;
        }
        .action-btn {
            padding: 3px 8px;
            font-size: 0.7rem;
        }
    }
    .fade-out {
        animation: fadeOut 0.5s forwards;
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>

<div class="content" id="content-area">


    <h2>Requests Management</h2>

    <div id="requests-content">
        @include('requests-content', [
            'leaveRequests' => $leaveRequests,
            'overtimeRequests' => $overtimeRequests,
            'employees' => $employees
        ])
    </div>

    <!-- Add Leave Request Modal -->
    <div class="modal fade" id="addLeaveRequestModal" tabindex="-1" aria-labelledby="addLeaveRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLeaveRequestModalLabel">Add Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addLeaveRequestForm" hx-post="{{ route('requests.leave.store') }}" hx-target="#requests-content" hx-swap="outerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="leave_employee_id" class="form-label">Employee</label>
                            <select name="employee_id" id="leave_employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="leave_start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="leave_start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="leave_end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="leave_end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="leave_reason" class="form-label">Reason</label>
                            <textarea name="reason" id="leave_reason" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Leave Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Overtime Request Modal -->
    <div class="modal fade" id="addOvertimeRequestModal" tabindex="-1" aria-labelledby="addOvertimeRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOvertimeRequestModalLabel">Add Overtime Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addOvertimeRequestForm" hx-post="{{ route('requests.overtime.store') }}" hx-target="#requests-content" hx-swap="outerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="overtime_employee_id" class="form-label">Employee</label>
                            <select name="employee_id" id="overtime_employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="overtime_start_time" class="form-label">Start Time</label>
                            <input type="datetime-local" name="start_time" id="overtime_start_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="overtime_end_time" class="form-label">End Time</label>
                            <input type="datetime-local" name="end_time" id="overtime_end_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="overtime_reason" class="form-label">Reason</label>
                            <textarea name="reason" id="overtime_reason" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Overtime Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/requests.js') }}"></script>
    @endpush
</div>