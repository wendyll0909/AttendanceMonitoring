@if($overtimeRequests->isNotEmpty())
       <table class="table">
           <thead>
               <tr>
                   <th>Employee</th>
                   <th>Start Time</th>
                   <th>End Time</th>
                   <th>Reason</th>
                   <th>Status</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               @foreach($overtimeRequests as $request)
                   <tr>
                       <td>{{ $request->employee ? e($request->employee->full_name) : 'Unknown' }}</td>
                       <td>{{ $request->start_time ? $request->start_time->format('Y-m-d H:i') : 'N/A' }}</td>
                       <td>{{ $request->end_time ? $request->end_time->format('Y-m-d H:i') : 'N/A' }}</td>
                       <td>{{ e($request->reason) }}</td>
                       <td>{{ $request->status }}</td>
                       <td>
                           @if($request->overtime_request_id && $request->status === 'pending')
                               <form action="{{ route('overtime-requests.approve', $request->overtime_request_id) }}" method="POST" style="display:inline;">
                                   @csrf
                                   <button type="submit" class="btn btn-sm btn-success">Approve</button>
                               </form>
                               <form action="{{ route('overtime-requests.reject', $request->overtime_request_id) }}" method="POST" style="display:inline;">
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
       <p>No overtime requests found.</p>
   @endif