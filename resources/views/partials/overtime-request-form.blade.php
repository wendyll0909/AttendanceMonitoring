<div class="modal fade" id="overtimeRequestModal" tabindex="-1" aria-labelledby="overtimeRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="overtimeRequestModalLabel">Create Overtime Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="overtime-request-form" action="{{ route('overtime-requests.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                     <select class="form-control" id="employee_id" name="employee_id" required>
    @if($employees->isEmpty())
        <option value="">No active employees available</option>
    @else
        @foreach($employees as $employee)
            <option value="{{ $employee->employee_id }}">{{ $employee->full_name }}</option>
        @endforeach
    @endif
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
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('overtimeRequestModal'));
        modal.show();
        console.log('Overtime Request Modal shown via script');
    });
</script>