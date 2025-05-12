<form id="overtimeRequestForm" 
      hx-post="{{ route('overtime-requests.store') }}"
      hx-target="#requests-container"  
      hx-swap="innerHTML"
      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
      class="needs-validation"
      novalidate>
       @if(isset($error))
           <div class="alert alert-danger alert-dismissible fade show" role="alert">
               {{ $error }}
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
           </div>
       @endif

       <div class="mb-3">
           <label for="employee_id" class="form-label">Employee</label>
           <select name="employee_id" id="employee_id" class="form-control" required>
               <option value="">Select Employee</option>
               @foreach($employees as $employee)
                   <option value="{{ $employee->employee_id }}">{{ e($employee->full_name) }}</option>
               @endforeach
           </select>
           <div class="invalid-feedback">Please select an employee.</div>
       </div>

       <div class="mb-3">
           <label for="start_time" class="form-label">Start Time</label>
           <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
           <div class="invalid-feedback">Please select a valid start time in the future.</div>
       </div>

       <div class="mb-3">
           <label for="end_time" class="form-label">End Time</label>
           <input type="datetime-local" name="end_time" id="end_time" class="form-control" required>
           <div class="invalid-feedback">Please select a valid end time after start time.</div>
       </div>

       <div class="mb-3">
           <label for="reason" class="form-label">Reason</label>
           <textarea name="reason" id="reason" class="form-control" rows="4" maxlength="255" required></textarea>
           <div class="invalid-feedback">Please provide a reason (max 255 characters).</div>
       </div>

       <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
           <button type="submit" class="btn btn-primary">Submit</button>
       </div>
   </form>

   <script>
   (function() {
       const form = document.getElementById('overtimeRequestForm');
form.addEventListener('submit', function(event) {
    const startTime = new Date(document.getElementById('start_time').value);
    const now = new Date();
    
    if (startTime <= now) {
        event.preventDefault();
        event.stopPropagation();
        alert('Start time must be in the future');
        return false;
    }
    
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    form.classList.add('was-validated');
}, false);

       const now = new Date();
       const startTimeInput = document.getElementById('start_time');
       const endTimeInput = document.getElementById('end_time');
       if (startTimeInput && endTimeInput) {
           const minTime = new Date(now.getTime() + 5 * 60 * 1000).toISOString().slice(0, 16);
           startTimeInput.min = minTime;
           endTimeInput.min = minTime;

           startTimeInput.addEventListener('change', function() {
               endTimeInput.min = startTimeInput.value;
           });
       }
   })();
   </script>