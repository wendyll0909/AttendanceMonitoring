<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBatch extends Model
{
    protected $primaryKey = 'batch_id';
    protected $fillable = [
        'batch_date',
        'employee_id',
        'check_in_time',
        'check_out_time',
        'check_in_method',
        'check_out_method',
        'check_in_deadline',
        'late_status',
        'absent',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}