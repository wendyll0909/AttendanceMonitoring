<div id="payroll-section">
    

    <h2>Payroll</h2>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" id="success-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" id="error-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('error') }}
        </div>
    @endif

    <form class="mb-4" 
          hx-get="{{ route('payroll.index') }}" 
          hx-target="#payroll-section" 
          hx-swap="innerHTML" 
          hx-push-url="false">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="month" class="form-label">Select Month</label>
                <input type="month" class="form-control" id="month" name="month" value="{{ $selectedMonth }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">View</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('payroll.export', ['month' => $selectedMonth]) }}" class="btn btn-success w-100">Export PDF</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Days Worked</th>
                    <th>Total Salary</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payrollData as $data)
                    <tr>
                        <td>{{ $data['employee']->full_name }}</td>
                        <td>{{ $data['employee']->position->position_name ?? 'N/A' }}</td>
                        <td>{{ $data['days_worked'] }}</td>
                        <td>₱{{ number_format($data['salary'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No payroll data found for this month</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>