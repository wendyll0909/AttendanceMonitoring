<form id="leaveRequestForm" hx-post="{{ route('leave-requests.store') }}" hx-target="#requests-container" hx-swap="innerHTML">
    @csrf
    <div class="mb-3">
        <label for="leave_employee_id" class="form-label">Employee</label>
        @if($employees->isEmpty())
            <p class="text-danger">No employees available. Please add employees first.</p>
            <input type="hidden" name="employee_id" value="" disabled>
        @else
            <select class="form-control" id="leave_employee_id" name="employee_id" required>
                @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
                @endforeach
            </select>
        @endif
    </div>
    <div class="mb-3">
    <label for="leave_start_date" class="form-label">Start Date</label>
    <input type="date" class="form-control" id="leave_start_date" name="start_date" required 
           min="{{ now()->format('Y-m-d') }}">
</div>
<div class="mb-3">
    <label for="leave_end_date" class="form-label">End Date</label>
    <input type="date" class="form-control" id="leave_end_date" name="end_date" required 
           min="{{ now()->format('Y-m-d') }}">
</div>
    <div class="mb-3">
        <label for="leave_reason" class="form-label">Reason</label>
        <textarea class="form-control" id="leave_reason" name="reason" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary" @if($employees->isEmpty()) disabled @endif>Submit</button>
</form>