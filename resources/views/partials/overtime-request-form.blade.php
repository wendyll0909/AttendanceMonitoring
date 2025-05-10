<div class="modal-body">
    <form id="overtime-request-form" hx-post="{{ route('overtime-requests.store') }}" hx-target="#overtime-requests">
        @csrf
        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select class="form-control" id="employee_id" name="employee_id" required>
                @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
        </div>
        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" id="reason" name="reason" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>