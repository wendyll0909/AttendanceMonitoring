<div class="table-responsive" style="min-width: 800px;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="min-width: 120px;">Employee</th>
                <th style="min-width: 120px;">Start Time</th>
                <th style="min-width: 120px;">End Time</th>
                <th style="min-width: 150px;">Reason</th>
                <th style="min-width: 100px;">Status</th>
                <th style="min-width: 100px;">Overtime Rate</th>
                <th style="min-width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody id="overtime-requests-table-body">
            @forelse ($overtimeRequests as $request)
                <tr>
                    <td>{{ $request->employee->full_name ?? 'N/A' }}</td>
                    <td>{{ $request->start_time->format('Y-m-d H:i') }}</td>
                    <td>{{ $request->end_time->format('Y-m-d H:i') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td @class([
    'badge bg-success text-white' => $request->status === \App\Models\OvertimeRequest::STATUS_APPROVED,
    'badge bg-danger text-white' => $request->status === \App\Models\OvertimeRequest::STATUS_REJECTED,
    'badge bg-warning text-dark' => $request->status === \App\Models\OvertimeRequest::STATUS_PENDING,
])>{{ ucfirst($request->status) }}</td>
<td>₱{{ number_format($request->overtime_rate, 2) }}</td>
<td>
    @if ($request->status === \App\Models\OvertimeRequest::STATUS_PENDING)
                            <button class="btn btn-primary btn-sm action-btn" 
        hx-post="{{ route('requests.overtime.approve', $request->overtime_request_id) }}" 
        hx-target="#overtime-requests-table-body"
        hx-swap="innerHTML"
        hx-vals='{"_token": "{{ csrf_token() }}"}'>Approve</button>

<button class="btn btn-danger btn-sm action-btn" 
        hx-post="{{ route('requests.overtime.reject', $request->overtime_request_id) }}" 
        hx-target="#overtime-requests-table-body"
        hx-swap="innerHTML"
        hx-vals='{"_token": "{{ csrf_token() }}"}'>Reject</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No overtime requests found</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $overtimeRequests->appends(request()->query())->links() }}
</div>