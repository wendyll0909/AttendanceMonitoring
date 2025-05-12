@if($leaveRequests->isNotEmpty())
       <table class="table">
           <thead>
               <tr>
                   <th>Employee</th>
                   <th>Start Date</th>
                   <th>End Date</th>
                   <th>Reason</th>
                   <th>Status</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               @foreach($leaveRequests as $request)
                   <tr>
                       <td>{{ $request->employee ? e($request->employee->full_name) : 'Unknown' }}</td>
                       <td>{{ $request->start_date ? $request->start_date->format('Y-m-d') : 'N/A' }}</td>
                       <td>{{ $request->end_date ? $request->end_date->format('Y-m-d') : 'N/A' }}</td>
                       <td>{{ e($request->reason) }}</td>
                       <td>{{ $request->status }}</td>
                       <td>
                           @if($request->leave_request_id && $request->status === 'pending')
                               <form action="{{ route('leave-requests.approve', $request->leave_request_id) }}" method="POST" style="display:inline;">
                                   @csrf
                                   <button type="submit" class="btn btn-sm btn-success">Approve</button>
                               </form>
                               <form action="{{ route('leave-requests.reject', $request->leave_request_id) }}" method="POST" style="display:inline;">
                                   @csrf
                                   <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                               </form>
                           @else
                               <span class="text-muted">No actions available</span>
                           @endif
                       </td>
                   </tr>
               @endforeach
           </tbody>
       </table>
   @else
       <p>No leave requests found.</p>
   @endif