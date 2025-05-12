<form id="overtimeRequestForm" hx-post="{{ route('overtime-requests.store') }}" hx-target="#requests-container" hx-swap="innerHTML" onsubmit="return validateOvertimeForm(this)">    @csrf
    <div class="mb-3">
        <label for="overtime_employee_id" class="form-label">Employee</label>
        @if($employees->isEmpty())
            <p class="text-danger">No employees available. Please add employees first.</p>
            <input type="hidden" name="employee_id" value="" disabled>
        @else
            <select class="form-control" id="overtime_employee_id" name="employee_id" required>
                @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
                @endforeach
            </select>
        @endif
    </div>
    <div class="mb-3">
    <label for="overtime_start_time" class="form-label">Start Time</label>
    <input type="datetime-local" class="form-control" id="overtime_start_time" name="start_time" required>
</div>
<div class="mb-3">
    <label for="overtime_end_time" class="form-label">End Time</label>
    <input type="datetime-local" class="form-control" id="overtime_end_time" name="end_time" required>
</div>
    <div class="mb-3">
        <label for="overtime_reason" class="form-label">Reason</label>
        <textarea class="form-control" id="overtime_reason" name="reason" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary" @if($employees->isEmpty()) disabled @endif>Submit</button>
</form>
<script>
function validateOvertimeForm(form) {
    const startTime = new Date(form.start_time.value);
    const endTime = new Date(form.end_time.value);
    
    if (endTime <= startTime) {
        alert('End time must be after start time');
        return false;
    }
    return true;
}
</script>