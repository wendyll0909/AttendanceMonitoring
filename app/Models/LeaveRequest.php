<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $primaryKey = 'leave_request_id';
    protected $fillable = ['employee_id', 'start_date', 'end_date', 'reason', 'status'];
    protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
];

   public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}