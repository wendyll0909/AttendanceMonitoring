<div id="requests-content">
    @if (session('success') || isset($success))
        <div class="alert alert-success fade-out" id="success-message">
            {{ session('success') ?? $success }}
        </div>
    @endif
    @if (session('error') || isset($error))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') ?? $error }}
        </div>
    @endif

   

    <div class="row">
        <!-- Leave Requests Section -->
        <div class="col-md-6 request-section">
            <h3>Leave Requests</h3>
            <div class="col-md-6 mb-2">
         <button class="btn btn-primary w-20" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">Add Leave Request</button>
        </div>
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search by employee name or status" 
                       hx-get="{{ route('requests.leave.search') }}" 
                       hx-target="#leave-requests-table-body" 
                       hx-trigger="keyup delay:300ms" 
                       name="search">
            </div>
            <div id="leave-requests-table">
                @include('leave-table', ['leaveRequests' => $leaveRequests])
            </div>
        </div>

        <!-- Overtime Requests Section -->
        <div class="col-md-6 request-section">
            <h3>Overtime Requests</h3>
                    <div class="col-md-6 mb-2">
            <button class="btn btn-primary w-20" data-bs-toggle="modal" data-bs-target="#addOvertimeRequestModal">Add Overtime Request</button>
        </div>
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search by employee name or status" 
                       hx-get="{{ route('requests.overtime.search') }}" 
                       hx-target="#overtime-requests-table-body" 
                       hx-trigger="keyup delay:300ms" 
                       name="search">
            </div>
            <div id="overtime-requests-table">
                @include('overtime-table', ['overtimeRequests' => $overtimeRequests])
            </div>
        </div>
    </div>
</div>