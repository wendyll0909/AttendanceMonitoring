<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
   protected $primaryKey = 'overtime_request_id';
    protected $fillable = ['employee_id', 'start_time', 'end_time', 'reason', 'status'];
protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
 public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
}
}
