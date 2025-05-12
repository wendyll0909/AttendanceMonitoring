<form id="leaveRequestForm" 
      hx-post="{{ route('leave-requests.store') }}"
      hx-target="#requests-container"  
      hx-swap="innerHTML"
      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "text/html"}'
      class="needs-validation"
      novalidate>
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
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
        <label for="reason" class="form-label">Reason</label>
        <textarea name="reason" id="reason" class="form-control" rows="4" maxlength="255" required></textarea>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>

<script>
document.addEventListener('htmx:afterRequest', function(evt) {
    if (evt.detail.target.id === 'leaveRequestForm' && evt.detail.successful) {
        const modalEl = document.getElementById('leaveRequestModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }
});
</script>